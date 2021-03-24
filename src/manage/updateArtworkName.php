<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:../');
		exit;
	}

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);
	
	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$id = $_POST['id'];
	$name = $_POST['name'];

	$stmt = mysqli_prepare($sql, "UPDATE artwork SET name = ?, last_update = CURDATE() WHERE `id` = ?");
	mysqli_stmt_bind_param($stmt, "si", $name, $id);
	mysqli_stmt_execute($stmt);

	echo json_encode(['error' => mysqli_error($sql), 'name' => $name]);
?>
