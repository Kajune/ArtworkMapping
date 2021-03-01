<?php
	header('Content-Type: application/force-download');
	header("Content-disposition: attachment; filename=\"" . basename($_GET['url']) . "\""); 
	readfile($_GET['url']); 
?>
