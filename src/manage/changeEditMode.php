<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:../');
		exit;
	}

	require_once '../DSN.php';

	if (isset($_POST['editmode']) and $_POST['editmode']) {
		$salt = 'thisissalt';

		if (isset($_POST['pass']) and 
			hash('sha256', $_POST['pass'].$salt) == $dsn['hash']) {
			$result = true;
		} else {
			$result = false;
		}
		$_SESSION['editmode'] = $result;
	} else {
		$result = true;
		$_SESSION['editmode'] = false;
	}

	echo json_encode(['result' => $result]);
?>
