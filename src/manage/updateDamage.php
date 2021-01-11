<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"]) or !isset($_SESSION['editmode']) or !$_SESSION['editmode']) {
		header('Location:../');
		exit;
	}

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], 'artwork');

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$stmt = mysqli_prepare($sql, "UPDATE damage SET type = ?, comment = ?, adddate = ?, deldate = ?, color = ?, shape_id = ?, x = ?, y = ?, radius = ? WHERE `id` = ?");
	mysqli_stmt_bind_param($stmt, "sssssidddi", $_POST['type'], $_POST['comment'], $_POST['adddate'], $_POST['deldate'], 
		$_POST['color'], $_POST['shape_id'], $_POST['x'], $_POST['y'], $_POST['radius'], $_POST['id']);
	mysqli_stmt_execute($stmt);

	echo json_encode(['error' => mysqli_error($sql), 'deldate' => $_POST['deldate']]);
?>
