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

<script type="text/javascript">
	function successAlert() {
		var template = document.getElementById('alert-success');
		var clone = template.content.cloneNode(true);
		document.getElementById('container').appendChild(clone);
	}
</script>

<?php
	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		echo '<script type="text/javascript">successAlert()</script>';
	}
?>

	<form method="POST">
		<div class="form-group row">
			<div class="col-lg-4">
				<label for="artwork-image">画像</label><br>
				<img src="" id="thumbnail" style="max-width: 15vw; height: 15vw;">
				<input type="file" name="artwork-image" accept="image/*" required onchange="imgChange(event)"><br>
			</div>

			<div class="col-lg-8">
				<label for="artwork-title">美術品名</label>
				<input type="text" class="form-control" name="artwork-title" placeholder="美術品名" required>

				<label for="artwork-comment">コメント</label>
				<textarea type="text" class="form-control" name="artwork-comment" placeholder="コメント"></textarea>
				<small>コメントは任意のタイミングで編集できます</small><br>

				<button type="submit" class="btn-lg btn-primary">登録する</button>
				<a type="button" class="btn-lg btn-secondary" href="../">一覧に戻る</a>
			</div>
		</div>
	</form>		
</div>

<script type="text/javascript">
	function imgChange(e){
		var reader = new FileReader();
		reader.onload = function (e) {
			document.getElementById("thumbnail").src = e.target.result;
		}
		reader.readAsDataURL(e.target.files[0]);
	}
</script>

</body>
</html>