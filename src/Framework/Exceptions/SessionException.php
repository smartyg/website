<?php

declare(strict_types = 1);

namespace Framework\Exceptions;

class SessionException extends ExternalException
{
	const LOGIN_FAILED = 1;
	const NO_SESSION_FOUND = 2;

	function __construct(int $code)
	{
		parent::__construct("", $code);
	}
}

?>
