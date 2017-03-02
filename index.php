<?php
require('command.php');

abstract class Assertion {
	const Root = 0;
}

function check($assertion) {
	$path = explode('?', $_SERVER['REQUEST_URI'])[0];
	switch ($assertion) {
	case Assertion::Root:
		if ($path != '/') fatalError(400);
		break;
	default:
		fatalError();
	}
}

function fatalError($code = 500) {
	sleep(1);  // rate limit simple brute force attacks
	switch ($code) {
	case 400:
		$header = '400 Bad Request';
		break;
	case 404:
		$header = '404 Not Found';
		break;
	default:
	case 500:
		$header = '500 Internal Server Error';
		break;
	case 501:
		$header = '501 Not Implemented';
		break;
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' ' . $header);
	exit();
}

/* dispatch query string commands to their implementations */
try {
	switch ($_SERVER['QUERY_STRING']) {
	case 'gc':
		check(Assertion::Root);
		Command\gc();
		break;
	default:
		fatalError(501);
	}
}
catch (Exception $e) {
	fatalError();
}
