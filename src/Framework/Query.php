<?php

declare(strict_types = 1);

namespace Framework;

use \PDO;
use \PDOStatement;
use Framework\Exceptions\InternalException;


/** Class for which contains all database queries.
 * This class contains all the backend database calls. To use this, provide a valid instance of a session object and an valid database connection (PDO).
 */
final class Query extends Permissions
{
	const QUERY_RETURN_SINGLE_VALUE = 1 << 0;
	const QUERY_RETURN_SINGLE_ROW = 1 << 1;
	const QUERY_RETURN_ARRAY = 1 << 2;
	const QUERY_RETURN_NON = 1 << 3;

	private $dbh = null;
	private $stmt = array();
	private $methods;
	private $session;

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
			'getArticle' => new Q('SELECT articles.content as content FROM articles WHERE articles.a_id == ? AND articles.required_permissions == (articles.required_permissions & ?) LIMIT 1', 2, self::QUERY_RETURN_SINGLE_VALUE, self::PERM_NO),

			'getArticleMeta' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description, articles.date_created as date_created, articles.date_modified as date_modified, users.display_name as author, users.u_id as u_id FROM articles INNER JOIN users ON articles.u_id = users.u_id WHERE articles.a_id == ? AND articles.required_permissions == (articles.required_permissions & ?) LIMIT 1', 2, self::QUERY_RETURN_SINGLE_ROW, self::PERM_NO),

			'getSubArticles' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description, structure.`order` as \'order\' FROM articles INNER JOIN structure ON articles.a_id = structure.a_id WHERE structure.p_id == ? AND articles.required_permissions == (articles.required_permissions & ?) ORDER BY `structure`.`order` ASC, structure.a_id ASC', 2, self::QUERY_RETURN_ARRAY, self::PERM_NO),

			'getSubArticlesTop' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description, structure.`order` as \'order\' FROM articles INNER JOIN structure ON articles.a_id = structure.a_id WHERE structure.p_id IS NULL AND articles.required_permissions == (articles.required_permissions & ?) ORDER BY `structure`.`order` ASC, structure.a_id ASC', 1, self::QUERY_RETURN_ARRAY, self::PERM_NO),

			'getSideArticles' => new Q('SELECT GROUP_CONCAT(articlesSide.a_id) as sides FROM articles LEFT JOIN sidesMap ON articles.a_id = sidesMap.a_id LEFT JOIN articles as articlesSide ON sidesMap.s_id = articlesSide.a_id WHERE articles.a_id == ? AND articlesSide.required_permissions == (articlesSide.required_permissions & ?) GROUP BY articles.a_id', 2, self::QUERY_RETURN_SINGLE_VALUE, self::PERM_NO),

			'getArticleTags' => new Q('SELECT GROUP_CONCAT(tagsMap.t_id) as tags FROM tagsMap WHERE tagsMap.a_id == ? GROUP BY tagsMap.a_id', 1, self::QUERY_RETURN_SINGLE_VALUE, self::PERM_NO),

			'getArticlesByTag' => new Q('SELECT articles.a_id as id, articles.title as title, articles.description as description FROM tagsMap INNER JOIN articles ON tagsMap.a_id = articles.a_id WHERE tagsMap.t_id == ? AND articles.required_permissions == (articles.required_permissions & ?) ORDER BY articles.a_id ASC', 2, self::QUERY_RETURN_ARRAY, self::PERM_NO),

			'getNumberOfArticlesByTag' => new Q('SELECT tags.t_id as id, COUNT(articles.a_id) as number FROM tags LEFT JOIN tagsMap ON tags.t_id = tagsMap.t_id LEFT JOIN articles ON tagsMap.a_id = articles.a_id WHERE articles.required_permissions == (articles.required_permissions & ?) GROUP BY tags.t_id ORDER BY tags.t_id ASC', 1, self::QUERY_RETURN_ARRAY, self::PERM_NO),

			'getSettingValue' => new Q('SELECT value FROM settings WHERE name = ? LIMIT 1', 1, self::QUERY_RETURN_SINGLE_VALUE, self::PERM_NO),

			'getPassword' => new Q('SELECT password.password as password FROM password INNER JOIN users ON password.u_id = users.u_id WHERE email_address = ?', 1, self::QUERY_RETURN_SINGLE_VALUE, self::PERM_ONLY_FRAMEWORK),

			'getUserdata' => new Q('SELECT u_id, first_name, middle_name, last_name, display_name, email_address, permissions FROM users WHERE email_address = ?', 1, self::QUERY_RETURN_SINGLE_ROW, self::PERM_ONLY_FRAMEWORK),

			'getUserdataById' => new Q('SELECT u_id, first_name, middle_name, last_name, display_name, email_address, permissions FROM users WHERE u_id = ?', 1, self::QUERY_RETURN_SINGLE_ROW, self::PERM_ONLY_FRAMEWORK)
			);

		if($session->isValid())
		{
			$this->session = $session;
			$this->dbh = $dbh;
		}
		else throw new InternalException("No valid session is active, queries not availible.", InternalException::NO_VALID_SESSION);
	}
	
	protected function getSession() : Session
	{
        return $this->session;
	}

	/** Helper method for generic query execution.
	 * An overloading method which is used as a generic method to execute queries given in \ref this->methods.
	 */
	public function __call(string $fn, array $arguments = null)
    {
		if(!array_key_exists($fn, $this->methods)) throw new InternalException('Query ' . $fn . ' does not exists.', InternalException::QUERY_NOT_EXISTS);
		$q = $this->methods[$fn];
		if($this->checkPerms($q->permissions))
		{
			if(count($arguments) < $q->n) throw new InternalException("Not enough parameters given for query " . $fn . ".", InternalException::WRONG_ARGUMENT_COUNT);

			if(!array_key_exists($fn, $this->stmt) || $this->stmt[$fn] === null || $this->stmt[$fn] === false)
			{
				try
				{
					if(($this->stmt[$fn] = $this->dbh->prepare($q->query)) === false)
						throw new InternalException($fn . ": Cannot prepare SQL statement: " . $q->query . ".", InternalException::WRONG_SQL);
				}
				catch(PDOException $e)
				{
					throw new InternalException($fn . ": Cannot prepare SQL statement: " . $q->query, InternalException::WRONG_SQL, $e);
				}
			}

			$stmt = $this->stmt[$fn];
			for($n = 0; $n < $q->n; $n++)
			{
				if(!$stmt->bindValue($n + 1, $arguments[$n]))
					throw new InternalException($fn . ": failed to bind argument " . ($n + 1) . ".", InternalException::FAILED_ARGUMENT_BIND);
			}

			if(!$stmt->execute())
				throw new InternalException($fn . ": failed to execute query: " . $q->query, InternalException::FAILED_TO_EXECUTE);

			if(($res = $stmt->fetchAll(PDO::FETCH_ASSOC)) === false)
				throw new InternalException($fn . ": failed to execute query: " . $q->query, InternalException::FAILED_TO_FETCH);

			if(self::hasBitSet($q->options, self::QUERY_RETURN_NON))
			{
				if(count($res) == 0 || empty($res)) return true;
				else throw new InternalException($fn . ": The query returned data while it should not return any.", InternalException::WRONG_NUM_RECORDS_RETURNED);
			}
			elseif(self::hasBitSet($q->options, self::QUERY_RETURN_SINGLE_ROW))
			{
				if(count($res) == 1) return $res[0];
				elseif(count($res) > 1)
				{
					throw new InternalException($fn . ": There are multiple rows in the return array. Review the database statement.", InternalException::WRONG_NUM_RECORDS_RETURNED);
				}
				else
				{
					throw new InternalException($fn . ": There are no rows in the return array. Review the database statement.", InternalException::NO_RECORDS_RETURNED);
				}
			}
			elseif(self::hasBitSet($q->options, self::QUERY_RETURN_SINGLE_VALUE))
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
					else throw new InternalException($fn . ": There are multiple values in the return array. Review the database statement.", InternalException::WRONG_NUM_RECORDS_RETURNED);
				}
				elseif(count($res) > 1) throw new InternalException($fn . ": There are multiple rows in the return array. Review the database statement.", InternalException::WRONG_NUM_RECORDS_RETURNED);
				else throw new InternalException($fn . ": There are no rows in the return array. Review the database statement.", InternalException::NO_RECORDS_RETURNED);
			}
			elseif(self::hasBitSet($q->options, self::QUERY_RETURN_ARRAY))
			{
				if(is_array($res))
				{
                    if(count($res) > 0) return $res;
                    else throw new InternalException($fn . ": There are no rows in the return array. Review the database statement.", InternalException::NO_RECORDS_RETURNED);
                }
				else throw new InternalException($fn . ": The result of the query did not return an array. Review the database statement.", InternalException::WRONG_NUM_RECORDS_RETURNED);
			}
			else throw new InternalException('no return type given for query ' . $fn . '.', InternalException::CODE_ERROR);
		}
		else throw new InternalException("You do not have the right permissions to execute query " . $fn . ".", InternalException::WRONG_PERMISSION);
    }
}
?>
