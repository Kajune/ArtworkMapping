<?php
	session_start();
	header('Expires:-1');
	header('Cache-Control:');
	header('Pragma:');
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="content-type" charset="utf-8">

	<title>美術品損傷管理システム</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

	<link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

<div class="container header" id="container">
	<h1><span>新しい<span>美術品の</span></span><span>追加</span></h1>
	<br>

	<template id="alert-success">
		<div class="alert alert-primary alert-dismissible fade show" role="alert">
			正常に追加されました
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
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], 'artwork');
	
	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	if ($result = mysqli_query($sql, "SELECT name from artwork where `deleted` = false")) {
		$names = mysqli_fetch_all($result);
		mysqli_free_result($result);
	} else {
		echo mysqli_error($sql);
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$name = $_POST['artwork-name'];
		$tag = $_POST['artwork-tag'];
		$comment = $_POST['artwork-comment'];

		$bad_flag = false;
		foreach ($names as $ename) {
			if (strcmp($name, $ename[0]) == 0) {
				echo '<script type="text/javascript">failAlert("既に存在している美術品名です。")</script>';
				$bad_flag = true;
				break;
			}
		}

		if (!$bad_flag) {
			if(isset($_FILES) && isset($_FILES['artwork-image']) && is_uploaded_file($_FILES['artwork-image']['tmp_name'])){
				$a = uniqid().'.jpg';
				if (move_uploaded_file($_FILES['artwork-image']['tmp_name'], '../img/artwork/'.$a)) {
					$name = htmlspecialchars($name);
					$tag = htmlspecialchars($tag);
					$comment = htmlspecialchars($comment);

					$stmt = mysqli_prepare($sql, "INSERT INTO artwork (name, tag, comment, img, last_update) VALUES (?,?,?,?, CURDATE())");
					mysqli_stmt_bind_param($stmt, "ssss", $name, $tag, $comment, $a);
					if (mysqli_stmt_execute($stmt)) {
						echo '<script type="text/javascript">successAlert()</script>';
					} else {
						echo mysqli_error($sql);
					}
				} else {
					echo '<script type="text/javascript">failAlert("ファイルのアップロードに失敗しました。")</script>';
				}
			} else {
				echo '<script type="text/javascript">failAlert("ファイルのアップロードに失敗しました。")</script>';
			}
		}
	}

	mysqli_close($sql);
?>

	<form method="POST" class="form-group row" id="form" enctype="multipart/form-data">
		<div class="col-lg-4">
			<label for="artwork-image">画像</label><br>
			<img src="" id="thumbnail" style="max-width: 15vw; height: auto;">
			<input type="file" name="artwork-image" accept="image/*" id="artwork-image" required onchange="imgChange(event)"><br>
		</div>

		<div class="col-lg-8">
			<label for="artwork-name">美術品名</label>
			<input type="text" class="form-control" name="artwork-name" placeholder="美術品名" id="artwork-name" required onchange="nameChange(event);">
			<small style="color: red;" id="duplicate_error" hidden>既に存在している美術品名です。</small>
			<br>

			<label for="artwork-tag">タグ</label>
			<input type="text" class="form-control" name="artwork-tag" placeholder="タグ(コンマ区切り)">
			<br>

			<label for="artwork-comment">コメント</label>
			<textarea type="text" class="form-control" name="artwork-comment" placeholder="コメント"></textarea>
			<small>タグ・コメントは任意のタイミングで編集できます</small><br>
			<br>

			<button type="submit" class="btn btn-lg btn-primary" id="submit">登録する</button>
			<a type="button" class="btn btn-lg btn-secondary" href="../">一覧に戻る</a>
		</div>
	</form>		
</div>

<script type="text/javascript">
	var existing_names = <?php echo json_encode($names); ?>;

	function imgChange(e) {
		var reader = new FileReader();
		reader.onload = function (e) {
			document.getElementById("thumbnail").src = e.target.result;
		}
		reader.readAsDataURL(e.target.files[0]);
	}

	function nameChange(e) {
		for (const ename of existing_names) {
			if (ename[0] === e.target.value.trim()) {
				$('#submit').attr('disabled', true);
				$('#duplicate_error').attr('hidden', false);
				return;
			}
		}
		$('#submit').attr('disabled', false);
		$('#duplicate_error').attr('hidden', true);
	}
</script>

</body>
</html>