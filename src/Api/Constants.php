<?php

declare(strict_types = 1);

namespace Api;

class Constants
{
	const _NS_API = 0x80;

	const _API_PREM_NO = (1 << 0) | self::_NS_API;
	const _API_PREM_REGISTERED_USER = (1 << 1) | self::_NS_API;
	const _API_PREM_ADMIN = (1 << 2) | self::_NS_API;
	const _API_PREM_ONLY_FRAMEWORK = (1 << 3) | self::_NS_API;
}
?>
