<?php

namespace Command;

/* items remain for 32 days before being garbage-collected */
define('LIFETIME', 2764800);

function gc() {
	foreach (ls() as $name => $props) {
		if ($props['locked'] || $props['remaining'] > 0) continue;
		rm($props['secret']);
	}
}

function ls() {
	$ls = array();

	$items = new \DirectoryIterator(__DIR__);
	foreach ($items as $item) {
		if ($item->getFilename()[0] == '.') continue;
		if ($item->isLink() || !$item->isDir()) continue;

		$props = array();
		$props['secret'] = $item->getFilename();
		$props['locked'] = !$item->isWritable();
		$props['remaining'] = ($item->getCTime() + LIFETIME) - time();
		// TODO: add more properties as needed

		$name = resolve($props['secret']);
		$ls[$name] = $props;
	}

	return $ls;
}

function resolve($secret) {
	$dir = __DIR__ . '/' . $secret;
	$items = new \DirectoryIterator($dir);
	foreach ($items as $item) {
		// get the first file
		if (!$item->isFile()) continue;
		return $item->getFilename();
	}
	if (PHP_INT_MAX <= 2147483647) {
		// on 32bit PHP, isFile() gives wrong answers for files >2GB
		// find the first file with a dot in the name
		foreach ($items as $item) {
			$name = $item->getFilename();
			if (strpos($name, '.') > 0) return $name;
		}
	}
	return NULL;
}

function rm($secret) {
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
