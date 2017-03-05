<?php
require('command.php');

function l10n(string $text) {
	static $languages = NULL;
	static $translations = NULL;

	if (!isset($languages) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// break Accept-Language into languages and q factors
		preg_match_all('/(([a-z]{1,8})(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
		if (count($matches)) {
			// create an array with entries like 'en' => 0.5
			$languages = array_merge(array_combine($matches[1], $matches[5]),
			                         array_combine($matches[2], $matches[5]));
			foreach ($languages as $key => $value) {
				if ($value === '') $languages[$key] = 1;
			}
			// order by descending q factor and drop q factor
			arsort($languages, SORT_NUMERIC);
			$languages = array_keys($languages);
		}
	}
	if (isset($languages) && !isset($translations)) {
		require('l10n.php');
	}

	if (isset($languages) && isset($translations)) {
		foreach ($languages as $l) {
			if (isset($translations[$text][$l])) return $translations[$text][$l];
		}
	}

	return $text;
}

function html(string $body, $header = '') {
	$title = l10n("File Sharing");
	// TODO: header += bootstrap loading
	print("<!DOCTYPE html><html><head><title>${title}</title>${header}</head><body>${body}</body></html>");
}

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
		$message = 'The request is malformed.';
		break;
	case 404:
		$header = '404 Not Found';
		$message = 'The file does not exist.';
		break;
	default:
	case 500:
		$header = '500 Internal Server Error';
		$message = 'An internal error occurred.';
		break;
	case 501:
		$header = '501 Not Implemented';
		$message = 'The command is not implemented.';
		break;
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' ' . $header);
	html('<h1>' . l10n($message) . '</h1>');
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
