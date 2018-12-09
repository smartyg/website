<?php

declare(strict_types = 1);

namespace Api;

use Framework\Session;
use Framework\Theme;
use Framework\Article;
use Framework\Meta;
use Framework\ShortArticle;
use Framework\Query;
use Framework\Permissions;
use Framework\Userdata;
use Framework\Utils;
use \PDO;

/** Main class for which contains all api calls.
 * This class contains all api function calls. To use this class it must be linked to a valid instance of a \ref Session class and given a backend database handler.
 * Before a Api call is executed the current permissions will be checked to see if this api call is allowed. If this fails a PermissionException is thrown.
 */
final class Api extends Permissions
{
	private $query;
	private $session;

	/** Constructor to initialize the class.
	 * Initialize the Api class.
	 * @param $session	Instance of the current \ref Session class which controls the permissions of the active session.
	 * @param $dbh		Instance of a PDO database handler.
	 * @exception		Throws a \ref SessionException in case no valid session was given.
	 */
	public function __construct(Session $session, PDO $dbh)
	{
		// Check if we were given a valid session, otherwise throw a session exception.
		if($session->isValid()) $this->session = $session;
		else throw new SessionException(SessionException::NO_VALID_SESSION);
		
		// Create an instance of the Query class to handle all the backend database queries.
		$this->query = new Query($this->session, $dbh);
	}
	
	/**
	 * Cleanup this instance.
	 */
	public function __destruct()
	{
		// Unset the link to the session instance, so garbage collector can do it's work properly.
		unset($this->session);
		// Also remove the link to the Query instance for the garbage collector.
		unset($this->query);
	}

	protected function getSession() : Session
	{
        return $this->session;
	}

	public function getRelatedArticlesByArticleId(int $id) : array
	{
		if($this->checkPerms())
		{
			$tags_str = $this->query->getArticleTags($id, $this->session->getPermissions());
			$tags = explode(',', $tags_str);
			return $this->getRelatedArticlesByTagIds($tags);
		}
		else throw new Exception("Not the right permissions.");
	}
	
	public function getRelatedArticlesByTagIds(array $ids) : array
	{
		if($this->checkPerms())
		{
			$r = array();
			foreach($ids as $tag)
			{
				$articles = $this->query->getArticlesByTag($tag, $this->session->getPermissions());
				foreach($articles as $article)
				{
					$r[] = new ShortArticle($article);
				}
			}
			return Utils::SortArray($r, '\Framework\Article::compareId');
		}
		else throw new Exception("Not the right permissions.");
	}
	
	public function getSubArticles(int $id = 0) : array
	{
		if($this->checkPerms())
		{
			$articles = $this->query->getSubArticles($id, $this->session->getPermissions());
			$r = array();
			foreach($articles as $article)
			{
				$r[] = new ShortArticle($article);
			}
			return $r;
		}
	}
		
	public function getArticle(int $id) : Article
	{
		if($this->checkPerms())
		{
			$r = $this->getArticleMeta($id);
			$i['content'] = $this->query->getArticle($id, $this->session->getPermissions());

			return Article::extend($r, $i);
		}
	}
	
	public function getArticleMeta(int $id) : Meta
	{
		if($this->checkPerms())
		{
			$meta = $this->query->getArticleMeta($id, $this->session->getPermissions());
			$tags = explode(',', $this->query->getArticleTags($id, $this->session->getPermissions()));
			$meta['tags'] = Utils::SortArray($tags, '\Framework\Utils::compareInt');
			return new Meta($meta);
		}
	}
	
	public function getSideArticles(int $id) : array
	{
		if($this->checkPerms())
		{
			$sides = $this->query->getSideArticles($id, $this->session->getPermissions());
			$r = array();
			foreach($sides as $side)
			{
				$r[] = new ShortArticle($side);
			}
			return Utils::SortArray($r, '\Framework\Article::compareId');
		}
	}
	
	public function getActivePlugins() : array
	{
	}
	
	public function getAllPlugins() : array
	{
	}
	
	public function getSessionData() : array
	{
	}
	
	public function getTheme() : Theme
	{
		$value = $this->query->getSettingValue('theme');
		$class = "\Theme\\" . $value;

		if(class_exists($class, true) && is_subclass_of($class, '\Framework\Theme')) return new $class();
		else throw new Exception("Theme class " . $class . " does not exists.");
	}
	
	public function getAllThemes() : array
	{
	}
	
	public function getAdminTheme() : Theme
	{
		$value = $this->query->getSettingValue('admin_theme');
		$class = "\Theme\\" . $value;

		if(class_exists($class, true) && is_subclass_of($class, '\Framework\Theme') && is_subclass_of($class, '\Framework\iAdminTheme')) return new $class();
		else throw new Exception("Admin theme class " . $class . " does not exists.");
	}
	
	public function getAllAdminThemes() : array
	{
	}
	
	public function hasAdminTheme(Theme $theme) : bool
	{
		$v = class_implements($theme);
		if(array_key_exists('iAdminTheme', $v) && $v['iAdminTheme'] == 'iAdminTheme') return true;
		return false;
	}
	
	public function getThemeNumberSides(Theme $theme) : int
	{
		return 0;
	}
	
	public function getThemeSettings(Theme $theme) : array
	{
	}
	
	public function setThemeSettings(Theme $theme, array $settings) : void
	{
	}
	
	public function addArticle(string $article, Meta $meta) : int
	{
	}
	
	public function removeArticle(int $id) : bool
	{
	}
	
	public function changeArticle(int $id, string $article, Meta $meta) : bool
	{
	}
	
	public function checkPassword(string $username, string $password) : bool
	{
		if($this->checkPerms(self::PREM_ONLY_FRAMEWORK))
		{
			if(($check = $this->query->getPassword($username)) == null) return false;
			if(password_verify($password, $check) === true) return true;
			return false;
			
		}
		else throw new Exception("You can not call this function.");
	}

	public function getUserdata(string $username) : Userdata
	{
		if($this->checkPerms(self::PREM_ONLY_FRAMEWORK))
		{
			if(is_array(($u = $this->query->getUserdata($username)))) return new Userdata($u);
			
		}
		else throw new Exception("You can not call this function.");
	}
	
	public function getUserdataById(int $id) : Userdata
	{
		if($this->checkPerms(self::PREM_ONLY_FRAMEWORK))
		{
			if(is_array(($u = $this->query->getUserdataById($id)))) return new Userdata($u);
			
		}
		else throw new Exception("You can not call this function.");
	}
	
	public function login(string $username, string $password) : bool
	{
		if($this->checkPerms() && $this->session->isValid())
		{
			try
			{
				$this->session->logon($username, $password);
			}
			catch(LoginException $e)
			{
				return false;
			}
			return true;
		}
	}
	
	public function logoff() : bool
	{
		if($this->session->isValid()) $this->session->logoff();
		return true;
	}
}

?>
