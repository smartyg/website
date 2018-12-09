<?php

declare(strict_types = 1);

namespace Framework\Exceptions;

class PermissionException extends ExternalException
{
	public function __construct(string $fn, int $req_permissions)
	{
		parent::__construct("You don't have the premission to execute function '" . $fn . ".");
	}
}
?>
