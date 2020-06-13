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

	if(isset($_FILES) && isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])){
		$a = uniqid().'.jpg';
		if (move_uploaded_file($_FILES['file']['tmp_name'], '../img/damage/'.$a)) {
			$stmt = mysqli_prepare($sql, "INSERT INTO damage_img(damage_id, img) VALUES (?, ?)");
			mysqli_stmt_bind_param($stmt, "is", $_POST['damage_id'], $a);
			mysqli_stmt_execute($stmt);

			$last_id = mysqli_insert_id($sql);
			$result = mysqli_query($sql, "SELECT * from damage_img where `id` = $last_id");

			echo json_encode(['error' => mysqli_error($sql), 'result' => mysqli_fetch_assoc($result)]);
		} else {
			echo json_encode(['error' => 'File upload failed.']);
		}
	} else {
		echo json_encode(['error' => 'File upload failed.', 'result' => $_FILES['file']['tmp_name']]);
	}

?>
