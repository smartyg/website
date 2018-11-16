<?php

declare(strict_types = 1);

namespace Api;

use Framework\Session;
use Framework\Theme;
use Framework\Meta;
use Framework\Query;
use Framework\Permissions;
use Framework\Userdata;
use \PDO;

/** Main class for which contains all api calls.
 * This class contains all api function calls. To use this class it must be linked to a valid instance of a \ref Session class
 */
final class Api extends Permissions
{
	private $query;

	/** Constructor to initialize the class.
	 * Initialize the Api class.
	 * @param $session		instance of the current \ref Session class which controls the rights of the active session.
	 */
	public function __construct(Session $session)
	{
		if($session->isValid()) $this->session = $session;
		else throw new Exception("No valid session is active, API not availible.");
		
		//$settings = $this->session->getSettings();
		$this->query = new Query($this->session, new PDO('sqlite:website.db'));
	}
	
	/**
	 * Cleanup this instance.
	 */
	public function __destruct()
	{
		unset($this->session);
	}
	
	public function getRelatedArticles(string $tags = "") : array
	{
		if($this->checkPerms())
		{
			
		}
	}
	
	public function getSubArticles(int $id = 0) : array
	{
		if($this->checkPerms())
		{
			$r = $this->query->getSubArticles($id, $this->session->getPermissions());
			foreach($r as $v)
			{
				$ret[] = $v['id'];
			}
			return $ret;
		}
	}
		
	public function getArticle(int $id) : array
	{
		if($this->checkPerms())
		{
			$r['id'] = $id;
			$r['content'] = $this->query->getArticle($id, $this->session->getPermissions());
			$r['meta'] = $this->getArticleMeta($id);
			$r['title'] = $r['meta']->title;
			return $r;
		}
	}
	
	public function getArticleMeta(int $id) : Meta
	{
		if($this->checkPerms())
		{
			$tags = $this->query->getArticleTags($id, $this->session->getPermissions());
			return new Meta($this->query->getArticleMeta($id, $this->session->getPermissions()));
		}
	}
	
	public function getSideArticles(int $id) : array
	{
		if($this->checkPerms())
		{
			$sides = $this->query->getSideArticles($id, $this->session->getPermissions());
			return explode(',', $sides);
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
		if($this->checkPerms(Constants::_API_PREM_ONLY_FRAMEWORK))
		{
			if(($check = $this->query->getPassword($username)) == null) return false;
			if(password_verify($password, $check) === true) return true;
			return false;
			
		}
		else throw new Exception("You can not call this function.");
	}

	public function getUserdata(string $username) : Userdata
	{
		if($this->checkPerms(Constants::_API_PREM_ONLY_FRAMEWORK))
		{
			if(is_array(($u = $this->query->getUserdata($username)))) return new Userdata($u);
			
		}
		else throw new Exception("You can not call this function.");
	}
	
	public function getUserdataById(int $id) : Userdata
	{
		if($this->checkPerms(Constants::_API_PREM_ONLY_FRAMEWORK))
		{
			if(is_array(($u = $this->query->getUserdataById($id)))) return new Userdata($u);
			
		}
		else throw new Exception("You can not call this function.");
	}
}

?>
