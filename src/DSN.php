<?php
$dsn = array(
	'host' => getenv('MYSQL_HOST'),
	'user' => getenv('MYSQL_USER'),
	'db' => getenv('MYSQL_DATABASE'),
	'pass' => getenv('MYSQL_PASSWORD'),
	'hash' => getenv('EDITMODE_HASH'),
);

function last_update($sql, $id){
	$stmt = mysqli_prepare($sql, "UPDATE artwork SET last_update = NOW() WHERE `id` = ?");
	mysqli_stmt_bind_param($stmt, "i", $id);
	mysqli_stmt_execute($stmt);
}

function last_update_by_damage($sql, $damage_id){
	$stmt = mysqli_prepare($sql, "UPDATE artwork, damage SET artwork.last_update = NOW() WHERE artwork.id = damage.artwork_id AND damage.id = ?");
	mysqli_stmt_bind_param($stmt, "i", $damage_id);
	mysqli_stmt_execute($stmt);
}

?>