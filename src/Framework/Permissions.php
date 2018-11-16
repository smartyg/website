<?php

declare(strict_types = 1);

namespace Framework;

use \Exception;

/** Main class for which contains all api calls.
 * This class contains all api function calls. To use this class it must be linked to a valid instance of a \ref Session class
 */
abstract class Permissions
{
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
		
		if(self::hasBitSet($req, Constants::_API_PREM_ADMIN))
			if(!$this->session->isAdmin()) $r = false;
		//if($req & _API_PREM_WRITE && !$this->session->isAdmin()) $r = false;
		return $r;
	}
	
	
}

?>
