<?php
	switch ($_SERVER['QUERY_STRING']) {
	default:
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	}
