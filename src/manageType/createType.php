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

	$stmt = mysqli_prepare($sql, "INSERT INTO damage_type(name, color) VALUES ('種類名', NULL)");
	mysqli_stmt_execute($stmt);

	$last_id = mysqli_insert_id($sql);
	$result = mysqli_query($sql, "SELECT * from damage_type where `id` = $last_id");

	echo json_encode(['error' => mysqli_error($sql), 'result' => mysqli_fetch_assoc($result)]);
?>
