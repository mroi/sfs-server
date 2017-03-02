<?php

namespace Command;

/* items remain for 32 days before being garbage-collected */
define('LIFETIME', 2764800);

function gc() {
	foreach (ls() as $name => $props) {
		if ($props['remaining'] > 0) continue;
		rm($props['secret']);
	}
}

function ls() {
	$ls = array();

	$items = new \DirectoryIterator(__DIR__);
	foreach ($items as $item) {
		if ($item->getBasename()[0] == '.') continue;
		if ($item->isLink() || !$item->isDir()) continue;

		$props = array();
		$props['secret'] = $item->getBasename();
		$props['remaining'] = ($item->getCTime() + LIFETIME) - time();
		// TODO: add more properties as needed

		$name = $item->getBasename();  // TODO: call resolve to get filename from secret
		$ls[$name] = $props;
	}

	return $ls;
}

function rm(string $secret) {
	$dir = __DIR__ . '/' . $secret;
	$dirs = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new \RecursiveIteratorIterator($dirs, \RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($files as $file) {
		if ($file->isDir())
			rmdir($file->getPathname());
		else
			unlink($file->getPathname());
	}
	rmdir($dir);
}
