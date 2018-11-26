<?php

declare(strict_types = 1);

namespace Framework;

/**
 * Class for storing simple queries in the \ref Query class.
 */
final class Q
{
	public $query;
	public $n;
	public $options;
	public $permissions;
	
	function __construct(string $query, int $n, int $options = 0, int $permissions = Permissions::_PREM_NO)
	{
		$this->query = $query;
		$this->n = $n;
		$this->options = $options;
		$this->permissions = $permissions;
	}
}

?>
