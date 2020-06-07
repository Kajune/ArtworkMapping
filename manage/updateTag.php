<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:../');
		exit;
	}

	$php_array = ['id' => $_POST['id'],
		'tag' => $_POST['newtag']
	];

	echo json_encode($php_array);
?>
