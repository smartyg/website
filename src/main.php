<?php

declare(strict_types = 1);

error_reporting(E_ALL);

define("DEBUG", true);

require_once("./autoload.php");

use Framework\Session;
use Framework\Page;
use Framework\Exceptions\BaseException;

try
{
	$session = new Session(Session::_SESSION_NEW, false);
	$api = $session->getApi();
	
	$page = new Page($api->getTheme());
	try
	{
		$page->setArticle($api->getArticle($session->getArticleId())['content']);
		$page->setMeta($api->getArticleMeta($session->getArticleId()));
		$n_side = $api->getThemeNumberSides($page->getTheme());
		for($i = 0; $i < $n_side; $i++)
		{
			$page->setSide($i, $api->getSide($session->getArticleId(), $i));
		}
	}
	catch(Exception | Error $e)
	{
		$page->addMessage($e);
	}

	$session->bufferClean();
	$page->output();
	$session->bufferFlush();
}
catch(Exception | Error $e)
{
	//display 500 - internal server error with message
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	echo '<!DOCTYPE html><html><head><title>Internal Server Error</title><link rel="stylesheet" type="text/css" href="css/error.css"></head><body><h1>Internal Server Error</h1>';
	for($err = $e; !is_null($err); $err = $err->getPrevious())
	{
		echo '<div class="framework_message"><p class="framework_message_text">' . $err->getMessage() . '</p><p class="framework_message_code">(code: ' . $err->getCode() . ')</p>';
		if(DEBUG) echo '<p class="framework_message_detail">On file ' . $err->getFile() . ' on line ' . $err->getLine() . '</p>';
		echo '</div>';
	}
	echo '</body></html>';
}
?>
