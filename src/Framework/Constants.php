<?php

declare(strict_types = 1);

namespace Framework;

class Constants extends \Api\Constants
{
	const _NS_FRAMEWORK = 0xA0;

	const _SESSION_NEW = 0x1 | self::_NS_FRAMEWORK;
	const _SESSION_NO_NEW = 0x2 | self::_NS_FRAMEWORK;
	const _SESSION_SAVE_ID = __NAMESPACE__ . '\ID';//(string)(0x3 | _NS_FRAMEWORK);

	const _QUERY_RETURN_SINGLE_VALUE = 1 << 0 | self::_NS_FRAMEWORK;
	const _QUERY_RETURN_SINGLE_ROW = 1 << 1 | self::_NS_FRAMEWORK;
	const _QUERY_RETURN_ARRAY = 1 << 2 | self::_NS_FRAMEWORK;
}
?>
