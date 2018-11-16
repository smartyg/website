<?php

declare(strict_types = 1);

namespace Framework;

use Api;

/** Main class for managing a user session.
 *
 */
final class Session
{
/* for PHP 7.4
	public static array $saved_vars = array('is_admin', 'started_time');
	private Api\Api $api = null;
	private bool $buffer_started = false;
	private bool $is_admin = false;
	private int $started_time = -1;
	private bool $is_valid = false;
	private Settings $settings = null;
*/
	public static $saved_vars = array('is_admin', 'started_time', 'current_article');
	private $api = null;
	private $buffer_started = false;
	private $is_admin = false;
	private $started_time = -1;
	private $is_valid = false;
	private $settings = null;
	private $use_buffer = true;
	private $current_article = 1;

	/** Constructor to initialize the class.
	 * Initialize the Session class.
	 * @param $type				allows _SESSION_NEW and _SESSION_NO_NEW
	 * @param $output_callback	define an additional output callback function
	 */
	public function __construct(int $type = Constants::_SESSION_NEW, bool $use_buffer = true, callable $output_callback = null)
	{
		if($output_callback == null && ini_get("lib.output_compression") == 0) $output_callback = "ob_gzhandler";
		$this->use_buffer = $use_buffer;
		if($this->use_buffer) ob_start($output_callback);
		session_start();

		if(!isset($_SESSION[Constants::_SESSION_SAVE_ID]) && $type == Constants::_SESSION_NO_NEW) throw new Exception("No previous session found");
		elseif(isset($_SESSION[Constants::_SESSION_SAVE_ID]))
		{
			$this->load($_SESSION[Constants::_SESSION_SAVE_ID]);
		}
		if($this->started_time < 0) $this->started_time = $_SERVER['REQUEST_TIME'];

		$this->settings = new Settings();

		// Set this variable before creating an Api instance, because the Api class will check if this instance is valid by a call to Api::isValid().
		$this->is_valid = true;

		$this->api = new Api\Api($this);
	}
	
	/**
	 * Destructor to save and cleanup the current session. All buffered output is discarded, in case the buffered output has to been written to the client call \ref Api::bufferFlush() first.
	 */
	public function __destruct()
	{
		unset($this->api);
		$this->save();
		if($this->use_buffer) ob_end_clean();
		/*
		{
			while(ob_get_level() > 0)
				ob_end_clean();
		}
		*/
	}
	
	public function getArticleId() : int
	{
		return $this->current_article;	
	}
	
	/**
	 * Save the current content of the class instance to the $_SESSION variable.
	 */
	private function save()
	{
		$s = null;
		foreach(self::$saved_vars as $var)
		{
			$s[$var] = $this->{$var};
		}
		$_SESSION[Constants::_SESSION_SAVE_ID] = $s;
	}
	
	/**
	 * Load the associate array given as a paramater into this instance.
	 * @param $data		An associative array with at least fields that maches then names in Session::$saved_vars
	 */
	private function load(array $data) : void
	{
		foreach(self::$saved_vars as $var)
		{
			$this->{$var} = $data[$var];
		}
	}
	
	/**
	 * Return the associative instance of the api for this session.
	 * @return	An instance of the Api class for this array.
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
		return ($this->is_valid && $this->is_admin);
	}
	
	/** execute a query on the database backend.
	 * @param $q	A Query object
	 */
	public function query(Query $q)
	{
		
	}
	
	private function connectDB()
	{
	}
}

?>
