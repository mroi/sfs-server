<?php
require('command.php');

abstract class Assertion {
	const Root = 0;
}

function check($assertion) {
	$path = explode('?', $_SERVER['REQUEST_URI'])[0];
	switch ($assertion) {
	case Assertion::Root:
		if ($path != '/') fatalError(404);
		break;
	default:
		fatalError();
	}
}

function fatalError($code = 500) {
	switch ($code) {
	case 404:
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		break;
	default:
	case 500:
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
		break;
	case 501:
		header($_SERVER['SERVER_PROTOCOL'] . ' 501 Not Implemented');
		break;
	}
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
