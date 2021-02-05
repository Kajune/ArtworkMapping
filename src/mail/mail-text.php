<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:./');
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		file_put_contents(__DIR__.'/mail-text.txt', $_POST['text']);
	}

	$text = file_get_contents(__DIR__.'/mail-text.txt');
	echo $text;
?>
