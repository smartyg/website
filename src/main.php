<?php

declare(strict_types = 1);

require_once("./autoload.php");

use Framework\Session;
use Framework\Page;

try
{
	$session = new Session();
	$api = $session->getApi();
	
	$page = new Page($api->getTheme());
	$page->setArticle($api->getArticle($session->getArticleId())['content']);
	$page->setMeta($api->getArticleMeta($session->getArticleId()));
	$n_side = $api->getThemeNumberSides($page->getTheme());
	for($i = 0; $i < $n_side; $i++)
	{
		$page->setSide($i, $api->getSide($session->getArticleId(), $i));
	}

	$session->bufferClean();

	$page->output();
	$session->bufferFlush();
}
catch(exception $e)
{
	//$page->setWarning();
}
?>
