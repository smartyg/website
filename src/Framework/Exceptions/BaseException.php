<?php

declare(strict_types = 1);

namespace Framework\Exceptions;

abstract class BaseException extends \Exception
{
	abstract public function msgCode() : int;
	abstract public function msgName() : string;
	abstract public function readableMessage() : string;
}

/*
error message

error
Cannot connect to database (code 1)
debug code


type = NOTICE | WARNING | ERROR
code
*/
?>
