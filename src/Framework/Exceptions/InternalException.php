<?php

declare(strict_types = 1);

namespace Framework\Exceptions;

class InternalException extends BaseException
{
	const RECORD_NOT_EXISTS = 1;
	const WRONG_ARGUMENT_COUNT = 2;
	const FAILED_ARGUMENT_BIND = 3;
	const QUERY_NOT_EXISTS = 4;
	const NO_VALID_SESSION = 5;
	const WRONG_SQL = 6;
	const FAILED_TO_EXECUTE = 7;
	const FAILED_TO_FETCH = 8;
	const WRONG_NUM_RECORDS_RETURNED = 9;
	const WRONG_PERMISSION = 10;
	const ARTICLES_NO_MATCH = 11;
}

?>
