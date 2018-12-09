<?php

declare(strict_types = 1);

namespace Api;

use Api\Exceptions\ApiException;
use Framework\Session;
use Framework\Theme;
use Framework\Article;
use Framework\Meta;
use Framework\ShortArticle;
use Framework\Query;
use Framework\Permissions;
use Framework\Userdata;
use Framework\Utils;
use Framework\Exceptions\PermissionException;
use Framework\Exceptions\InternalException;
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
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$tags_str = $this->query->getArticleTags($id, $this->session->getPermissions());
				$tags = explode(',', $tags_str);
				$articles = $this->getRelatedArticlesByTagIds($tags);
				for($n = 0; $n < count($articles); $n++)
				{
                    if($articles[$n]->id == $id) unset($articles[$n]);
				}
				return array_values($articles);
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					return array();
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getRelatedArticlesByTagIds(array $ids) : array
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
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
				return Utils::Unique(Utils::SortArray($r, '\Framework\Article::compareId'), '\Framework\Article::compareId');
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					return array();
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getSubArticles(int $id = 0) : array
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$articles = $this->query->getSubArticles($id, $this->session->getPermissions());
				$r = array();
				foreach($articles as $article)
				{
					$r[] = new ShortArticle($article);
				}
				return $r;
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					return array();
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
		
	public function getArticle(int $id) : Article
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$i['id'] = $id; // safeguard to make sure pervious statement returned the right article ID
				$i['content'] = $this->query->getArticle($id, $this->session->getPermissions());
				$r = $this->getArticleMeta($id);
				return Article::extend($r, $i);
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					throw new ApiException(__METHOD__, null, ApiException::NO_ARTICLE);
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getArticleMeta(int $id) : Meta
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$meta = $this->query->getArticleMeta($id, $this->session->getPermissions());
				$tags = explode(',', $this->query->getArticleTags($id, $this->session->getPermissions()));
				foreach($tags as $key => $value)
				{
                    $tags[$key] = (int)$value;
				}
				$meta['tags'] = Utils::SortArray($tags, '\Framework\Utils::compareInt');
				return new Meta($meta);
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					throw new ApiException(__METHOD__, null, ApiException::NO_ARTICLE);
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	/* TODO */
	public function getSideArticles(int $id) : array
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			$sides = $this->query->getSideArticles($id, $this->session->getPermissions());
			$r = array();
			foreach($sides as $side)
			{
				$r[] = new ShortArticle($side);
			}
			return Utils::SortArray($r, '\Framework\Article::compareId');
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
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
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$value = $this->query->getSettingValue('theme');
				$class = "\Theme\\" . $value;
				if(class_exists($class, true) && is_subclass_of($class, '\Framework\Theme')) return new $class();
				else throw new InternalException("Theme class " . $class . " does not exists.");
			}
			catch(Error | Exception $e)
			{
				return new \Theme\DefaultTheme();
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getAllThemes() : array
	{
		$files = scandir('./Theme');
		$r = array();
		foreach($files as $file)
		{
			if(substr($file, -4) == '.php')
			{
				$class = substr($file, 0, -4);
				if(is_subclass_of('\Theme\\' . $class, '\Framework\Theme')) $r[] = $class;
			}
		}
		return $r;
	}
	
	public function getAdminTheme() : Theme
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				$value = $this->query->getSettingValue('admin_theme');
				$class = "\Theme\\" . $value;
				if(class_exists($class, true) && is_subclass_of($class, '\Framework\Theme') && is_subclass_of($class, '\Framework\iAdminTheme')) return new $class();
				else throw new Exception("Admin theme class " . $class . " does not exists.");
			}
			catch(Error | Exception $e)
			{
				return new \Theme\DefaultTheme();
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getAllAdminThemes() : array
	{
		$files = scandir('./Theme');
		$r = array();
		foreach($files as $file)
		{
			if(substr($file, -4) == '.php')
			{
				$class = substr($file, 0, -4);
				if(is_subclass_of('\Theme\\' . $class, '\Framework\Theme') && is_subclass_of('\Theme\\' . $class, '\Framework\iAdminTheme')) $r[] = $class;
			}
		}
		return $r;
	}
	
	public function hasAdminTheme(Theme $theme) : bool
	{
        $interface_name = 'Framework\iAdminTheme';
		$v = class_implements($theme);
		if(array_key_exists($interface_name, $v) && $v[$interface_name] == $interface_name) return true;
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
		$req_permissions = self::PERM_ONLY_FRAMEWORK;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				if(($check = $this->query->getPassword($username)) == null) return false;
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					return false;
				else
					throw new ApiException(__METHOD__, $e);
			}
			if(password_verify($password, $check) === true) return true;
			return false;
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	public function getUserdata(string $username) : Userdata
	{
		$req_permissions = self::PERM_ONLY_FRAMEWORK;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				if(is_array(($u = $this->query->getUserdata($username)))) return new Userdata($u);
				else throw new ApiException(__METHOD__, null, ApiException::NO_USER);
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					throw new ApiException(__METHOD__, null, ApiException::NO_USER);
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function getUserdataById(int $id) : Userdata
	{
		$req_permissions = self::PERM_ONLY_FRAMEWORK;
		if($this->checkPerms($req_permissions))
		{
			try
			{
				if(is_array(($u = $this->query->getUserdataById($id)))) return new Userdata($u);
				else throw new ApiException(__METHOD__, null, ApiException::NO_USER);
			}
			catch(InternalException $e)
			{
				if($e->getCode() == InternalException::NO_RECORDS_RETURNED)
					throw new ApiException(__METHOD__, null, ApiException::NO_SUCH_USER);
				else
					throw new ApiException(__METHOD__, $e);
			}
		}
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function login(string $username, string $password) : bool
	{
		$req_permissions = self::PERM_NO;
		if($this->checkPerms($req_permissions) && $this->session->isValid())
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
		else throw new PermissionException(__METHOD__, $req_permissions);
	}
	
	public function logoff() : bool
	{
		if($this->session->isValid()) $this->session->logoff();
		return true;
	}
}

?>
