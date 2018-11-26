<?php

declare(strict_types = 1);

namespace Framework;

final class Settings
{
	const _CONFIG_FILE = './config.inc';

	private $filename;
	private $settings;

	public function __construct(string $filename)
	{
		// Check if file exists
		if(!file_exists(($this->filename = $filename))) throw new \Exception("Settings file '" . $filename . "' does not exists.");

		// check if file is readable and store the result in the settings property
		if(($this->settings = parse_ini_file($this->filename, false, INI_SCANNER_TYPED)) === false) throw new \Exception("File '" . $filename . "' does not seems to be a valid ini file.");
	}

	public function getSettingValue(string $name)
	{
		// In case reading the ini file failed and the previous exception got ignored
		if($this->settings === false) throw new \Exception("File '" . $this->filename . "' does not seems to be a valid ini file.");

		// Only replay if been called from a Session instance, otherwise throw and exception stating that this is not allowed.
		$call = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		if(!(array_key_exists(1, $call) && array_key_exists('class', $call[1]) && $call[1]['class'] == 'Framework\Session')) throw new \Exception("This method can only be called from a Session instance.");
		
		// Check if parameter $name exists in the settings and return it's value, otherwise return null.
		if(array_key_exists($name, $this->settings)) return $this->settings[$name];
		else return null;
	}
}

?>
