<?php
$dsn = array(
	'host' => getenv('MYSQL_HOST'),
	'user' => getenv('MYSQL_USER'),
	'db' => getenv('MYSQL_DATABASE'),
	'pass' => getenv('MYSQL_PASSWORD'),
	'hash' => getenv('EDITMODE_HASH'),
);
?>