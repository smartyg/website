<?php

declare(strict_types = 1);

namespace Framework;

use \PDO;
use Api\Api;
use Framework\Exceptions\SessionException;

/** Main class for managing a user session.
 * This class is the core of the framework, it manage the active session and attached permissions.
 * An active session is needed in order for Api calls to succeeded. It's behaviour is determined by both constructor options and settings.
 */
final class Session
{
	const _SESSION_NEW = 0x1;
	const _SESSION_NO_NEW = 0x2;
	const _SESSION_SAVE_ID = __NAMESPACE__ . '\ID';

	private static $saved_vars = array('is_admin', 'started_time', 'current_article', 'user_id');
	private $api = null;
	private $buffer_started = false;
	private $is_admin = false;
	private $started_time = -1;
	private $is_valid = false;
	private $settings = null;
	private $use_buffer = true;
	private $current_article = 0;
	private $userdata = null;
	private $user_id;

	/** Constructor to initialize the class.
	 * Initialize the Session class.
	 * @param $type				allows _SESSION_NEW and _SESSION_NO_NEW
	 * @param $output_callback	define an additional output callback function
	 * @exception				throws a SessionException if failed to start a session.
	 */
	public function __construct(int $type = self::_SESSION_NEW, bool $use_buffer = true, callable $output_callback = null, string $config_file = Settings::_CONFIG_FILE)
	{
		if($output_callback == null && ini_get("lib.output_compression") == 0) $output_callback = "ob_gzhandler";
		$this->use_buffer = $use_buffer;
		if($this->use_buffer) ob_start($output_callback);
		session_start();

		if(!isset($_SESSION[self::_SESSION_SAVE_ID]) && $type == self::_SESSION_NO_NEW)
		{
			// As there is no previous session to reuse, as requested by the options, close the output buffer and destroy the session.
			session_destroy();
			if($this->use_buffer) ob_end_clean();
			// Throw a SessionException to indicate that we failed to start a session.
			throw new SessionException(SessionException::NO_SESSION_FOUND);
		}
		elseif(isset($_SESSION[self::_SESSION_SAVE_ID]))
		{
			// Previous session information was found, try to read it.
			$this->load($_SESSION[self::_SESSION_SAVE_ID]);
		}
		if($this->started_time < 0) $this->started_time = $_SERVER['REQUEST_TIME'];
		if(empty($this->user_id)) $this->user_id = 0;

		$this->settings = new Settings($config_file);

		// Set this variable before creating an Api instance, because the Api class will check if this instance is valid by a call to Api::isValid().
		$this->is_valid = true;

		// Get an instance of the Api class and pass this session as argument, so it will uses our premissions to execute calls. As we did not yet retreive the user data of the current user id, all permissions will still be zero.
		$this->api = new Api($this, $this->connectDB());

		// Now we have an Api, read the user data assoiciated with the current user ID, this includes reading the premissions.
		$this->userdata = $this->api->getUserdataById($this->user_id);
	}

	/**
	 * Destructor to save and cleanup the current session. All buffered output is discarded, in case the buffered output has to been written to the client call \ref Api::bufferFlush() first.
	 */
	public function __destruct()
	{
		// Unreference the Api, this will lead trigger the Api destructor and also erease it's reference to our session instance.
		unset($this->api);
		// Save the current session before all will go to waist.
		$this->save();
		// Stop and clean the output buffer.
		if($this->use_buffer) ob_end_clean();
	}

	/** Try to log in with given username and password.
	 * Try to log in. Username and password are checked against the storage backed. If succeeded then the new user permissions are active immediately.
	 * @param $username	The username geven by the user.
	 * @param $password	Password given by the user.
	 * @exception	Throws a \ref SessionException if logging in fails.
	 */
	public function login(string $username, string $password) : bool
	{
		if($this->api->checkPassword($username, $password))
		{
			$this->userdata = $this->api->getUserdata($username);
			return true;
		}
		else throw new SessionException(SessionException::LOGIN_FAILED);
	}

	/** Log off from a session.
	 * Loggs off and destroys all session data including used permissions.
	 */
	public function logoff() : void
	{
		session_destroy();
		$this->user_id = 0;
		$this->userdata = $this->api->getUserdataById($this->user_id);
		$this->started_time = $_SERVER['REQUEST_TIME'];
	}

	/** Get current article ID.
	 * Get the current article ID as stored in the session.
	 * @return	The article ID as stored in the current session.
	 */
	public function getArticleId() : int
	{
		return $this->current_article;	
	}

	/**
	 * Save the current content of the class instance to the $_SESSION variable.
	 */
	private function save() : void
	{
		$s = null;
		foreach(self::$saved_vars as $var)
		{
			$s[$var] = $this->{$var};
		}
		$_SESSION[self::_SESSION_SAVE_ID] = $s;
	}

	/**
	 * Load the associate array given as a paramater into this instance.
	 * @param $data		An associative array with at least fields that maches then names in Session::$saved_vars
	 */
	private function load(array $data) : void
	{
		foreach(self::$saved_vars as $var)
		{
			if(isset($data[$var])) $this->{$var} = $data[$var];
		}
	}

	/**
	 * Return the associative instance of the api for this session.
	 * @return	An instance of the Api class for this session.
	 */
	public function getApi() : object
	{
		return $this->api;
	}

	/** Cleans the current session output buffer.
	 * Cleans the current session output buffer. All currently buffered output is discarded and the buffer is empty again.
	 */
	public function bufferClean() : void
	{
		if($this->use_buffer) ob_clean();
	}

	/** Flush the current session output buffer.
	 * Flush the current session output buffer. All currently buffered output is end to the client and the buffer is empty again.
	 */
	public function bufferFlush() : void
	{
		if($this->use_buffer) ob_flush();
	}

	/** Check if this session is a fully initialized session.
	 * @return	Returns true is this is a valid session and false otherwise.
	 */
	public function isValid() : bool
	{
		return $this->is_valid;
	}

	/** Check if this session is an admin session.
	 * @return	Returns true is this is an admin session and false otherwise.
	 */
	public function isAdmin() : bool
	{
	/* TODO: link this to the user permissions */
		return ($this->is_valid && $this->is_admin);
	}

	/** Connect to backend database.
	 * This function reads the database settings from the settings file, connects to the database and return the connection handler.
	 * @return	A valid connection handler.
	 */
	private function connectDB() : PDO
	{
		$db = $this->settings->getSettingValue("db");
		if($db != null) return new PDO($db);
	}

	/** Get current sessions permissions.
	 * Get the permissions of the current active session.
	 * @return	An integer which holds all the active permissions bits.
	 */
	public function getPermissions() : int
	{
		if($this->userdata->isValid()) return $this->userdata->getPermissions();
	}
}

?>
