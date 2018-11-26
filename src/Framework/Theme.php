<?php

declare(strict_types = 1);

namespace Framework;

use Framework\Exceptions\BaseException;

/** Base theme class.
 * All themes should inherit this class and must be part of the Theme namespace.
 */
abstract class Theme
{
	abstract public function getNumberSides() : int;
	abstract public function getJS() : array;
	abstract public function getStylesheets() : array;
	abstract public function output(string $article, string $navigation, array $sides = null) : string;

	private $messages = array();
	private $messagesPrinted = true;

	final function __construct()
	{
	}

	final public function addMessage(Throwable $e) : void
	{
		for($err = $e; !is_null($err); $err = $err->getPrevious())
		{
			if(is_a($err, 'Framework\Exceptions\BaseException') || is_a($err, 'Framework\Exceptions\BaseError'))
				$this->messages[] = '<div class="framework_message"><p class="framework_message_name">' . $err->msgName() . '</p><p class="framework_message_code">(code: ' . $err->msgCode() . ')</p><p class="framework_message_text">' . $err->readableMessage() . '</p></div>';
			else
				$this->messages[] = '<div class="framework_message"><p class="framework_message_text">' . $err->getMessage() . '</p><p class="framework_message_code">(code: ' . $err->getCode() . ')</p></div>';
		}
		$this->messagePrinted = false;
	}
	
	final public function printMessages() : void
	{
		echo '<div id="framework_messages">' . implode('', $this->messages) . '</div>';
		$this->messagePrinted = true;
	}
	
	final public function messagesPrinted() : bool
	{
		return $this->messagesPrinted;
	}

	static public function isValid() : bool
	{
		return true;
	}
}

?>
