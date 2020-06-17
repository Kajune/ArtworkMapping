<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:../');
		exit;
	}

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], 'artwork');

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$stmt = mysqli_prepare($sql, "INSERT INTO damage(artwork_id, type, comment, date, color, shape_id, x, y, radius) VALUES (?, ?, '', CURDATE(), ?, ?, ?, ?, ?)");
	mysqli_stmt_bind_param($stmt, "issiddd", $_POST['artwork_id'], $_POST['type'], $_POST['color'], $_POST['shape_id'], $_POST['x'], $_POST['y'], $_POST['radius']);
	mysqli_stmt_execute($stmt);

	$last_id = mysqli_insert_id($sql);
	$result = mysqli_query($sql, "SELECT * from damage where `id` = $last_id");

	echo json_encode(['error' => mysqli_error($sql), 'result' => mysqli_fetch_assoc($result)]);
?>
