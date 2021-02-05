<?php
	session_start();

	if (empty($_SERVER["HTTP_REFERER"])) {
		header('Location:./');
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		file_put_contents(__DIR__.'/mail-address-list.txt', PHP_EOL.$_POST['addr'], FILE_APPEND);
	}

	$mail_address_list =  explode("\n", file_get_contents(__DIR__.'/mail-address-list.txt'));
	echo json_encode($mail_address_list);
?>
