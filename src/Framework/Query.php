<?php

declare(strict_types = 1);

namespace Framework;

use \PDO;
use \PDOStatement;
use \Exception;

/** Class for which contains all database queries.
 * This class contains all the backend database calls. To use this, provide a valid instance of a session object and an valid database connection (PDO).
 */
final class Query extends Permissions
{
	const _QUERY_RETURN_SINGLE_VALUE = 1 << 0;
	const _QUERY_RETURN_SINGLE_ROW = 1 << 1;
	const _QUERY_RETURN_ARRAY = 1 << 2;

	private $dbh = null;
	private $stmt = array();
	private $methods;

	/** \fn getArticle($id)
	 * \brief get article content of given id
	 * This function retrieves the content of an article from the backend database.
	 * \param id	The id number of the article to retrieve.
	 * \return		The content of the article.
	 */
	/** \fn getArticleMeta($id)
	 * \brief get article meta data of given id
	 * This function retrieves the meta data of an article from the backend database.
	 * \param id	The id number of the article from which the data to retrieve.
	 * \return		An array which can be fed to an \ref Meta object.
	 */

	function __construct(Session $session, PDO $dbh)
	{
		$this->methods = array(
			'getArticle' => new Q('SELECT articles.content as content FROM articles WHERE articles.a_id == ? AND articles.required_permissions == (articles.required_permissions & ?)', 2, self::_QUERY_RETURN_SINGLE_VALUE, self::_PREM_NO),
			'getArticleMeta' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description, articles.date_created as date_created, articles.date_modified as date_modified, users.display_name as display_name, users.u_id as u_id FROM articles INNER JOIN users ON articles.u_id = users.u_id WHERE articles.a_id == ? AND articles.required_permissions == (articles.required_permissions & ?)', 2, self::_QUERY_RETURN_SINGLE_ROW, self::_PREM_NO),
			'getSubArticles' => new Q('SELECT articles.a_id as id FROM articles INNER JOIN users ON articles.u_id = structure.p_id WHERE structure.a_id == ? AND articles.required_permissions == (articles.required_permissions & ?)', 2, self::_QUERY_RETURN_ARRAY, self::_PREM_NO),
			'getSideArticles' => new Q('SELECT GROUP_CONCAT(articlesSide.a_id) as sides FROM articles LEFT JOIN sidesMap ON articles.a_id = sidesMap.a_id LEFT JOIN articles as articlesSide ON sidesMap.s_id = articlesSide.a_id WHERE articles.a_id == ? AND articlesSide.required_permissions == (articlesSide.required_permissions & ?) GROUP BY articles.a_id', 2, self::_QUERY_RETURN_SINGLE_VALUE, self::_PREM_NO),
			
			'getArticleTags' => new Q('SELECT GROUP_CONCAT(tagsMap.t_id) as tags FROM tagsMap WHERE tagsMap.a_id == ? GROUP BY tagsMap.a_id', 1, self::_QUERY_RETURN_SINGLE_VALUE, self::_PREM_NO),
			
			'getArticlesByTags' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description FROM tagsMap INNER JOIN articles ON tagsMap.a_id = articles.a_id WHERE tagsMap.t_id IN (?) AND articles.required_permissions == (articles.required_permissions & ?)', 2, self::_QUERY_RETURN_ARRAY, self::_PREM_NO),
			
			'getArticlesByTag' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description FROM tagsMap INNER JOIN articles ON tagsMap.a_id = articles.a_id WHERE tagsMap.t_id == ? AND articles.required_permissions == (articles.required_permissions & ?)', 2, self::_QUERY_RETURN_ARRAY, self::_PREM_NO),
			
			'getNumberOfArticlesByTag' => new Q('SELECT tags.t_id as id, tags.tag as tag, COUNT(articles.a_id) as number FROM tags LEFT JOIN tagsMap ON tags.t_id = tagsMap.t_id LEFT JOIN articles ON tagsMap.a_id = articles.a_id WHERE articles.required_permissions == (articles.required_permissions & ?) GROUP BY tags.t_id, tags.tags', 1, self::_QUERY_RETURN_ARRAY, self::_PREM_NO),
			
			'getSettingValue' => new Q('SELECT value FROM settings WHERE name = ?', 1, self::_QUERY_RETURN_SINGLE_VALUE, self::_PREM_NO),
			
			'getPassword' => new Q('SELECT password.password as password FROM password INNER JOIN users ON password.u_id = users.u_id WHERE email_address = ?', 1, self::_QUERY_RETURN_SINGLE_VALUE, self::_PREM_ONLY_FRAMEWORK),
			
			'getUserdata' => new Q('SELECT u_id, first_name, middle_name, last_name, display_name, email_address, permissions FROM users WHERE email_address = ?', 1, self::_QUERY_RETURN_SINGLE_ROW, self::_PREM_ONLY_FRAMEWORK),
			
			'getUserdataById' => new Q('SELECT u_id, first_name, middle_name, last_name, display_name, email_address, permissions FROM users WHERE u_id = ?', 1, self::_QUERY_RETURN_SINGLE_ROW, self::_PREM_ONLY_FRAMEWORK)
			);

		if($session->isValid())
		{
			$this->session = $session;
			$this->dbh = $dbh;
		}
		else throw new Exception("No valid session is active, queries not availible.");
	}

	/** Helper method for generic query execution.
	 * An overloading method which is used as a generic method to execute queries given in \ref this->methods.
	 */
	public function __call(string $fn, array $arguments = null)
    {
		if(!array_key_exists($fn, $this->methods)) throw new Exception('Query ' . $fn . ' does not exists.');
		$q = $this->methods[$fn];
		if($this->checkPerms($q->permissions))
		{
			if(count($arguments) < $q->n) throw new Exception("Not enough parameters given for query " . $fn . ".");

			if(!array_key_exists($fn, $this->stmt) || $this->stmt[$fn] === null || $this->stmt[$fn] === false)
			{
				try
				{
					if(($this->stmt[$fn] = $this->dbh->prepare($q->query)) === false)
						throw new Exception($fn . ": Cannot prepare SQL statement: " . $q->query);
				}
				catch(PDOException $e)
				{
					throw new Exception($fn . ": Cannot prepare SQL statement: " . $q->query, 0, $e);
				}
			}

			$stmt = $this->stmt[$fn];
			for($n = 0; $n < $q->n; $n++)
			{
				$stmt->bindValue($n + 1, $arguments[$n]);
			}

			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if(self::hasBitSet($q->options, self::_QUERY_RETURN_SINGLE_ROW))
			{
				if(count($res) == 1) return $res[0];
				else throw new Exception("There are multiple rows in the return array. Review the database statement.");
			}
			elseif(self::hasBitSet($q->options, self::_QUERY_RETURN_SINGLE_VALUE))
			{
				if(count($res) == 1)
				{
					if(count($res[0]) == 1)
					{
						if(function_exists('array_key_first'))
							return $res[0][array_key_first($res[0])];
						else
						{
							reset($res[0]);
							return $res[0][key($res[0])];
						}
					}
					else throw new Exception("There are multiple values in the return array. Review the database statement.");
				}
				else throw new Exception("There are multiple rows in the return array. Review the database statement.");
			}
			elseif(self::hasBitSet($q->options, self::_QUERY_RETURN_ARRAY))
			{
				if(is_array($res)) return $res;
				else throw new Exception("The result of the query did not return an array. Review the database statement.");
			}
			else throw new Exception('no return type given for query ' . $fn . '.');
		}
		else throw new Exception("You do not have the right permissions to execute query " . $fn . ".");
    }
}
?>
