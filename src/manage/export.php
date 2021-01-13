<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:../');
		exit;
	}

	$id = escapeshellcmd($_POST['id']);
	$command = "python3 exportExcel.py $id";
	exec($command, $output);

	echo json_encode(["result" => $output[0]]);
?>
