<?php
$dsn = array(
	'host' => getenv('MYSQL_HOST'),
	'user' => getenv('ARTWORK_DB_ENV_MYSQL_USER'),
	'db' => getenv('ARTWORK_DB_ENV_MYSQL_DATABASE'),
	'pass' => getenv('ARTWORK_DB_ENV_MYSQL_PASSWORD'),
	'hash' => getenv('EDITMODE_HASH'),
);
?>