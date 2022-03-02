<?php
	session_start();

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	# 種類のリスト
	$type_list = [];
	if ($result = mysqli_query($sql, "SELECT * from damage_type")) {
		while ($row = $result->fetch_assoc()) {
			$type_list[] = $row;
		}
		mysqli_free_result($result);
	} else {
		echo mysqli_error($sql);
	}

	echo json_encode(['data' => $type_list, 'error' => mysqli_error($sql)]);
?>
