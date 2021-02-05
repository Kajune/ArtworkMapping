<?php
	session_start();
	header('Expires:-1');
	header('Cache-Control:');
	header('Pragma:');

	mb_language("japanese");
	mb_internal_encoding("UTF-8");
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="content-type" charset="utf-8">

	<title>美術品損傷管理システム</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

	<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div class="container header" id="container">
	<h1>メール配信</h1>
	<br>

	<template id="alert-success">
		<div class="alert alert-primary alert-dismissible fade show" role="alert">
			正常に送信されました
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</template>

	<template id="alert-fail">
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<div class="fail-msg"></div>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</template>

<script type="text/javascript">
	function successAlert() {
		var template = document.getElementById('alert-success');
		var clone = template.content.cloneNode(true);
		document.getElementById('container').appendChild(clone);
	}

	function failAlert(msg) {
		var template = document.getElementById('alert-fail');
		var clone = template.content.cloneNode(true);
		clone.querySelector('.fail-msg').innerHTML = msg;
		document.getElementById('container').appendChild(clone);
	}
</script>

<?php
	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);
	
	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	if ($result = mysqli_query($sql, "SELECT id, name from artwork where `deleted` = false")) {
		$artworks = mysqli_fetch_all($result);
		mysqli_free_result($result);
	} else {
		echo mysqli_error($sql);
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$bad_flag = false;
		if (!isset($_POST['export-target-list']) or count($_POST['export-target-list']) == 0) {
			echo '<script type="text/javascript">failAlert("美術品が一つも選択されていません")</script>';
			$bad_flag = true;
		}
		
		if (!isset($_POST['mail-address-list']) or count($_POST['mail-address-list']) == 0) {
			echo '<script type="text/javascript">failAlert("メールアドレスが一つも選択されていません")</script>';
			$bad_flag = true;
		}

		if (!$bad_flag) {
			$file_list = [];

			$command = "cd ../manage && python3 exportExcel.py ";
			foreach ($_POST['export-target-list'] as &$target_id) {
				$id = escapeshellcmd($target_id);
				if (ctype_digit($id)) {
					$command = $command.$id.' ';
				}
			}
			exec($command, $file_list);

			if (count($file_list) == 0 || count($file_list) != count($_POST['export-target-list'])) {
				echo '<script type="text/javascript">failAlert("エクスポートに失敗しました。")</script>';
			} else {
				if (count($file_list) == 1) {
					$attach_file = $file_list[0];
				} else {
					$zip = new ZipArchive;

					$attach_file = '../manage/tmp/export_archive_'.date("Y-m-d H-i-s").'.zip';
					$zip->open($attach_file, ZipArchive::CREATE|ZipArchive::OVERWRITE);
					foreach ($file_list as $file) {
						$new_filename = substr($file,strrpos($file,'/') + 1);
						$zip->addFile('../manage/'.$file, $new_filename);
					}
					$zip->close();
				}

				echo $attach_file;
				$success = true;
				$header = 'From: test@artwork.local';
				foreach ($_POST['mail-address-list'] as $addr) {
					if (!mb_send_mail($addr, '美術品損傷管理システム', $_POST['mail-text'], $header)) {
						echo '<script type="text/javascript">failAlert("メール送信に失敗しました: '.$addr.'")</script>';
						$success = false;
					}
				}

				if ($success) {
					echo '<script type="text/javascript">successAlert()</script>';
				}
			}
		}
	}

	mysqli_close($sql);
?>

	<form method="POST" class="form-group row" id="form" enctype="multipart/form-data">
		<div class="col-lg-4">
			<h4>エクスポート対象</h4>
			<div class="text-left">
				<button type="button" class="btn btn-secondary" onclick="selectAll('export-target')">全て選択・選択解除</button>
				<select class="custom-select" multiple size=10 id="export-target-list" name="export-target-list[]">
				</select>
				<small>複数選択する場合はCtrlを押しながらクリック</small>
			</div>
			<br>
		</div>

		<div class="col-lg-4">
			<h4>送信先アドレス</h4>
			<div class="text-left">
				<button type="button" class="btn btn-secondary" onclick="selectAll('mail-address')">全て選択・選択解除</button>
				<select class="custom-select" multiple size=6 id="mail-address-list" name="mail-address-list[]">
				</select>
				<small>複数選択する場合はCtrlを押しながらクリック</small>
				<div class="input-group">
					<input type="text" class="form-control" placeholder="追加するメールアドレス" id="add-address">
					<div class="input-group-append"><button type="button" class="btn btn-primary" onclick="addAddress()">追加</button></div>
				</div>
				<small style="color: red;" id="addAddress-msg"></small>
			</div>
			<br>
		</div>

		<div class="col-lg-4">
			<h4>文面</h4>
			<textarea type="text" class="form-control" id="mail-text" name="mail-text" placeholder="送信する文面" rows=10></textarea>
			<div class="text-right">
				<small style="color: red;" id="updateText-msg"></small>
				<button type="button" class="btn btn-primary" onclick="updateText()">テンプレートとして登録</button>
			</div>
			<br>
		</div>

		<div class="text-center col-lg-12">
			<button type="submit" class="btn btn-lg btn-primary" id="submit">送信する</button>
			<a type="button" class="btn btn-lg btn-secondary" href="../">一覧に戻る</a>
		</div>
	</form>		
</div>

<script type="text/javascript">
	var artworks = <?php echo json_encode($artworks); ?>;
	var mail_address_list = [];

	function fetchMailAddressList() {
		$.ajax({
			type: 'GET',
			dataType: 'json',
			scriptCharset: 'UTF-8',
			url: 'mail-address-list.php'
		}).done(function(data, status, xhr) {
			mail_address_list = data;
			$('#mail-address-list').empty();
			mail_address_list.forEach(addr =>
				$('#mail-address-list').append('<option name="mail-address" value=' + addr + '>' + addr + '</option>')
			);
		});
	}

	function fetchMailText() {
		$.ajax({
			type: 'GET',
			dataType: 'text',
			scriptCharset: 'UTF-8',
			url: 'mail-text.php'
		}).done(function(data, status, xhr) {
			$('#mail-text').text(data);
		});
	}

	function addAddress() {
		let addr = $('#add-address').val();
		setTimeout(function(){$('#addAddress-msg').text('');}, 3000);

		if (addr === '') {
			$('#addAddress-msg').text('メールアドレスが空欄です');
			return;
		}

		let reg = /^[A-Za-z0-9]{1}[A-Za-z0-9_.-]*@{1}[A-Za-z0-9_.-]{1,}\.[A-Za-z0-9]{1,}$/;
		if (!reg.test(addr)) {
			$('#addAddress-msg').text('メールアドレスの形式が不正です');
			return;
		}

		$.ajax({
			type: 'POST',
			dataType: 'text',
			scriptCharset: 'UTF-8',
			url: 'mail-address-list.php',
			data: {'addr': addr },
		}).done(function(data, status, xhr) {
			fetchMailAddressList();
			$('#add-address').val('');
			$('#addAddress-msg').text('登録しました');
		}).fail(function(response) {
			$('#addAddress-msg').text('登録に失敗しました。コンソールを確認してください');
			console.log(response);
		})
	}

	function updateText() {
		setTimeout(function(){$('#updateText-msg').text('');}, 3000);

		$.ajax({
			type: 'POST',
			dataType: 'text',
			scriptCharset: 'UTF-8',
			url: 'mail-text.php',
			data: {'text': $('#mail-text').val() },
		}).done(function(data, status, xhr) {
			$('#mail-text').text(data);
			$('#updateText-msg').text('登録しました');
		}).fail(function(response) {
			$('#updateText-msg').text('登録に失敗しました。コンソールを確認してください');
			console.log(response);
		})
	}

	function selectAll(name) {
		let flag = true;
		$('option[name=' + name + ']').each(function(index, target) {
			if ($(target).prop('selected')) {
				flag = false;
			}
		});

		$('option[name=' + name + ']').each(function(index, target) {
			$(target).prop('selected', flag);
		});
	}

	$(function() {
		artworks.forEach(artwork => 
			$('#export-target-list').append('<option name="export-target" value=' + artwork[0] + '>' + artwork[1] + '</option>')
		);

		fetchMailAddressList();
		fetchMailText();
	});
</script>

</body>
</html>