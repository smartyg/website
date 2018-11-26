<?php

declare(strict_types = 1);

namespace Framework;

use \Exception;

/** Main class for which contains all api calls.
 * This class contains all api function calls. To use this class it must be linked to a valid instance of a \ref Session class
 */
abstract class Permissions
{
	const _PREM_NO = 1 << 0;
	const _PREM_REGISTERED_USER = 1 << 1;
	const _PREM_ADMIN = 1 << 2;
	const _PREM_ONLY_FRAMEWORK = 1 << 3;

	/* for PHP 7.4
	private Session $session;
	*/
	protected $session;
	
	protected static function hasBitSet(int $r, int $t) : bool
	{
		return (($r & $t) === $t);
	}

	final protected function checkPerms(int $req = 0) : bool
	{
		$r = false;
		if(!isset($this->session) || !$this->session->isValid()) throw new Exception("No valid session is active, API not availible.");
		else $r = true;
		
		if(self::hasBitSet($req, self::_PREM_ADMIN))
			if(!$this->session->isAdmin()) return false;
		if(self::hasBitSet($req, self::_PREM_ONLY_FRAMEWORK))
		{
			// check that the n-th function before is part of the Framework namespace.
			$call = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

			if(array_key_exists(1, $call) && array_key_exists('class', $call[1]) && $call[1]['class'] == 'Framework\Query')
			{
				if(!(array_key_exists(3, $call) && array_key_exists('class', $call[3]) && $call[3]['class'] == 'Framework\Session')) return false;
			}
			if(array_key_exists(1, $call) && array_key_exists('class', $call[1]) && $call[1]['class'] == 'Framework\Api')
			{
				if(!(array_key_exists(2, $call) && array_key_exists('class', $call[2]) && $call[2]['class'] == 'Framework\Session')) return false;
			}
		}
		return $r;
	}
	
	
}

?>
