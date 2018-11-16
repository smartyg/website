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
			'getArticle' => new Q('SELECT content FROM articles WHERE id == ?', 1, Constants::_QUERY_RETURN_SINGLE_VALUE, Constants::_API_PREM_NO),
			'getArticleMeta' => new Q('SELECT * FROM articles WHERE id == ?', 1, Constants::_QUERY_RETURN_SINGLE_ROW, Constants::_API_PREM_NO)
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

			if(!array_key_exists($fn, $this->stmt) || $this->stmt[$fn] === null) $this->stmt[$fn] = $this->dbh->prepare($q->query);

			$stmt = $this->stmt[$fn];
			for($n = 0; $n < $q->n; $n++)
			{
				$stmt->bindValue($n + 1, $arguments[$n]);
			}

			$stmt->execute();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if(self::hasBitSet($q->options, Constants::_QUERY_RETURN_SINGLE_ROW))
			{
				if(count($res) == 1) return $res[0];
				else throw new Exception("There are multiple rows in the return array. Review the database statement.");
			}
			elseif(self::hasBitSet($q->options, Constants::_QUERY_RETURN_SINGLE_VALUE))
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
			elseif(self::hasBitSet($q->options, Constants::_QUERY_RETURN_ARRAY))
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
