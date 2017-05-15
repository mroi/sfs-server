<?php
require('command.php');

function l10n($text) {
	static $languages = NULL;
	static $translations = NULL;

	if (!isset($languages) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// break Accept-Language into languages and q values
		preg_match_all('/(([a-z]{1,8})(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
		if (count($matches)) {
			// first create an array with short entries like 'en' => 0.5
			$languages = array_combine($matches[2], $matches[5]);
			// lower their q values a little
			foreach ($languages as $key => $value) {
				if ($value === '') $languages[$key] = 1;
				$languages[$key] -= 0.01;
			}
			// add more specific entries like 'en-us'
			// explicit short entries will overwrite the ones from above
			$languages = array_merge($languages, array_combine($matches[1], $matches[5]));
			// fixup q values again
			foreach ($languages as $key => $value) {
				if ($value === '') $languages[$key] = 1;
			}
			// order by descending q value
			arsort($languages, SORT_NUMERIC);
			// now we do not need the q values any more
			$languages = array_keys($languages);
		} else {
			$languages = array();
		}
	}
	if (count($languages) && !isset($translations)) {
		require('l10n.php');
	}

	if (count($languages) && count($translations)) {
		foreach ($languages as $l) {
			if (isset($translations[$text][$l])) return $translations[$text][$l];
		}
	}

	return $text;
}

function html($body, $header = '') {
	$title = l10n('File Sharing');
	$header .= '<link rel="stylesheet" href="/bootstrap.css">';
	$header .= '<style>body { margin-top:2em } .alert :first-child { margin-top:0 }</style>';
	$body .= '<footer class="text-center"><small class="text-muted">';
	$body .= sprintf(l10n('The <a %s>source code of this application</a> is available under the terms of the <a %s>AGPLv3&nbsp;license</a>.'),
		'target="_blank" href="https://github.com/mroi/sfs-server"', 'target="_blank" href="http://www.gnu.org/licenses/agpl-3.0.html"');
	$body .= '</small></footer>';
	print("<!DOCTYPE html><html><head><title>${title}</title>${header}</head><body class=container>${body}</body></html>");
}

abstract class Request {
	public static $path;
	public static $secret;
	public static $command;
}

abstract class Assertion {
	const Root = 0;
	const Secret = 1;
}

function check($assertion) {
	$path = explode('?', $_SERVER['REQUEST_URI'])[0];
	switch ($assertion) {
	case Assertion::Root:
		if ($path != '/') fatalError(400);
		Request::$path = $path;
		break;
	case Assertion::Secret:
		if (!preg_match('/^\/[A-Za-z0-9]+\/?$/', $path)) fatalError(400);
		$secret = trim($path, '/');
		if (!is_dir(__DIR__ . '/' . $secret)) fatalError(404);
		Request::$path = $path;
		Request::$secret = $secret;
		break;
	default:
		fatalError();
	}
}

function fatalError($code = 500) {
	sleep(1);  // rate limit simple brute force attacks
	switch ($code) {
	case 400:
		$style = 'warning';
		$header = '400 Bad Request';
		$message = 'Malformed Request';
		$description = 'You have sent an invalid request that cannot work.';
		break;
	case 404:
		$style = 'info';
		$header = '404 Not Found';
		$message = 'File Not Found';
		$description = 'The requested file does not exist or it has been deleted.';
		break;
	default:
	case 500:
		$style = 'danger';
		$header = '500 Internal Server Error';
		$message = 'Internal Error';
		$description = 'An unknown error occurred.';
		break;
	case 501:
		$style = 'warning';
		$header = '501 Not Implemented';
		$message = 'Unknown Command';
		$description = 'The command you sent is not implemented.';
		break;
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' ' . $header);
	html("<div class='alert alert-${style}'><h1 class=h4>" . l10n($message) . '</h1><p>' . l10n($description) . '</p></div>');
	exit();
}


/* dispatch query string commands to their implementations */
try {
	Request::$command = $_SERVER['QUERY_STRING'];
	switch (Request::$command) {
	case '':
		// handle short links
		$script = '<script>'
			. 'var secret = window.location.pathname;'
			. 'if (window.location.hash.length) secret = window.location.hash;'
			. 'secret = secret.replace(/[^A-Za-z0-9]/g, "");'
			. 'window.location.href = "https://" + window.location.host + "/" + secret + "/?resolve";'
			. '</script>';
		$message = 'JavaScript Required';
		$description = 'Short links need JavaScript enabled in your browser.';
		$noscript = '<noscript><div class="alert alert-warning"><h1 class=h4>' . l10n($message) . '</h1><p>' . l10n($description) . '</p></div></noscript>';
		html($noscript, $script);
		break;
	case 'gc':
		check(Assertion::Root);
		Command\gc();
		break;
	case 'resolve':
		check(Assertion::Secret);
		$name = Command\resolve(Request::$secret);
		if (!$name) fatalError(404);
		header('Location: /' . Request::$secret . '/' . rawurlencode($name));
		break;
	default:
		fatalError(501);
	}
}
catch (Exception $e) {
	fatalError();
}
