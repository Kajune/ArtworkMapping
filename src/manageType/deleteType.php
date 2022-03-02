<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"]) or !isset($_SESSION['editmode']) or !$_SESSION['editmode']) {
		header('Location:../');
		exit;
	}

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$stmt = mysqli_prepare($sql, "DELETE FROM damage_type WHERE `id` = ?");
	mysqli_stmt_bind_param($stmt, "i", $_POST['id']);
	mysqli_stmt_execute($stmt);

	last_update_by_damage($sql, $_POST['id']);

	echo json_encode(['error' => mysqli_error($sql)]);
?>
