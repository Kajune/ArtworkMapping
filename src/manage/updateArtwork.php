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

	$id = $_POST['id'];
	$tag = $_POST['artwork-tag'];
	$comment = $_POST['artwork-comment'];
	$deleted = mysqli_real_escape_string($sql, $_POST['artwork-deleted']);

	$stmt = mysqli_prepare($sql, "UPDATE artwork SET tag = ?, comment = ?, deleted = $deleted WHERE `id` = ?");
	mysqli_stmt_bind_param($stmt, "ssi", $tag, $comment, $id);
	mysqli_stmt_execute($stmt);

	echo json_encode(['error' => mysqli_error($sql)]);
?>
