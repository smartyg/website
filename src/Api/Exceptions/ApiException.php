<?php

declare(strict_types = 1);

namespace Api\Exceptions;

use Framework\Exceptions\ExternalException;

class ApiException extends ExternalException
{
    const NO_ARTICLE = 1;
    const NO_SUCH_USER = 2;

	public function __construct(string $fn, InternalException $e = null, int $code = 0)
	{
		parent::__construct("You don't have the premission to execute function '" . $fn . "'.", $code, $e);

		//$this->detailedMessage("...");
	}
}

?>
