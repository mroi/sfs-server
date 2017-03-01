<?php
require('command.php');

function fatalError($code) {
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
switch ($_SERVER['QUERY_STRING']) {
default:
	fatalError(501);
}
