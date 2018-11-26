<?php

declare(strict_types = 1);

namespace Framework;

/** Class to handle reading and parsing the settings (ini) file.
 * Class which handles the reading and returning of setting values as given by the provided ini file. for format of the ini file see http://php.net/manual/en/function.parse-ini-file.php.
 * To request a value, use the getSettingValue method. This method only works if being called from an instance of the \ref Session class.
 */
final class Settings
{
	const _CONFIG_FILE = './config.inc';

	private $filename;
	private $settings;

	/** Read and parse settings from the given ini file.
	 * Reads the file given in the first argument and parse it as in ini file. If it succeeds the reasulting settings will be saved in an private property which can be accessed by the \ref getSettingValue method.
	 * The format of the ini file the first argument points to should be in accordance with the example given here: http://php.net/manual/en/function.parse-ini-file.php.
	 * @param $filename	Filename of a file with settings (ini file).
	 * @exception		Throws and exception in case the file can not be found or in case it can not be parsed.
	 */
	public function __construct(string $filename)
	{
		// Check if file exists
		if(!file_exists(($this->filename = $filename))) throw new \Exception("Settings file '" . $filename . "' does not exists.");

		// check if file is readable and store the result in the settings property
		if(($this->settings = parse_ini_file($this->filename, false, INI_SCANNER_TYPED)) === false) throw new \Exception("File '" . $filename . "' does not seems to be a valid ini file.");
	}

	/** Get a setting value from the loaded settings.
	 *
	 * @param 		$name	The name of the setting of which the value is requested.
	 * @return		Returns the settings value with the type as closly matched as possible to the value of the ini file. In case the setting does not exists null is returned.
	 * @exception	Throws an exception if this function was called from somewhere else then inside an instance of the \ref Session class.
	 */
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
