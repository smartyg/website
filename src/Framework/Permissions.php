<?php

declare(strict_types = 1);

namespace Framework;

use \Exception;
use \Framework\Session;

/** Main class for which contains all api calls.
 * This class contains all api function calls. To use this class it must be linked to a valid instance of a \ref Session class
 */
abstract class Permissions
{
	const PERM_NO = 1 << 0;
	const PERM_REGISTERED_USER = 1 << 1;
	const PERM_ADMIN = 1 << 2;
	const PERM_ONLY_FRAMEWORK = 1 << 3;

	abstract protected function getSession() : Session;
	
	protected static function hasBitSet(int $r, int $t) : bool
	{
		return (($r & $t) === $t);
	}

	final protected function checkPerms(int $req = 0) : bool
	{
		$r = false;
		$session = $this->getSession();

		if(!is_a($session, 'Framework\Session') || !$session->isValid()) throw new Exception("No valid session is active, API not availible.");
		else $r = true;
		
		if(self::hasBitSet($req, self::PERM_ADMIN))
			if(!$session->isAdmin()) return false;
		if(self::hasBitSet($req, self::PERM_ONLY_FRAMEWORK))
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
