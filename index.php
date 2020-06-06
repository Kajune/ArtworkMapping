<?php
	session_start();

	$php_array = <<< JSON_DOC
		[
			{"src": "img/demaria11.jpg", "title": "デ・マリア木彫⑪", "comment": "デ・マリア木彫⑪の説明・コメント", "id": 0},
			{"src": "img/demaria11.jpg", "title": "デ・マリア木彫⑪", "comment": "デ・マリア木彫⑪の説明・コメント", "id": 1},
			{"src": "img/demaria11.jpg", "title": "デ・マリア木彫⑪", "comment": "デ・マリア木彫⑪の説明・コメント", "id": 2},
			{"src": "img/monet_floor.jpg", "title": "モネ・ビアンコ", "comment": "モネ・ビアンコの説明・コメント", "id": 3},
			{"src": "img/monet_floor.jpg", "title": "モネ・ビアンコ", "comment": "モネ・ビアンコの説明・コメント", "id": 4},
		]
	JSON_DOC;
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

	<link rel="stylesheet" type="text/css" href="./style.css">

	<style type="text/css">
		.card-img-top {
			width: 100%;
			height: 15vw;
			object-fit: scale-down;
		}
	</style>
</head>
<body>
<div class="container header">
	<h1><span>美術品損傷</span><span>管理システム</span></h1>
	<br>
	<a type="button" class="btn-lg btn-primary" href="addArtwork">新しい美術品の登録</a>
	<br><br>

	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4" id="artwork-cardlist">
		<template id="card-template">
			<div class="col mb-4">
				<div class="card">
					<img src="img/demaria11.jpg" class="card-img-top d-none d-sm-block artwork-thumbnail">
					<div class="card-body">
						<h5 class="card-title artwork-title">美術品名</h5>
						<p class="card-text artwork-comment">説明・コメント</p>
						<a href="" type="button" class="btn-block btn-primary artwork-button">管理</a>
					</div>
				</div>
			</div>
		</template>
	</div>
</div>

<script type="text/javascript">
	var data = <?php echo $php_array; ?>;
	var template = document.getElementById('card-template');

	for (var i = 0; i < data.length; i++) {
		var clone = template.content.cloneNode(true);

		clone.querySelector('.artwork-thumbnail').src = data[i].src;
		clone.querySelector('.artwork-title').textContent = data[i].title;
		clone.querySelector('.artwork-comment').textContent = data[i].comment;
		clone.querySelector('.artwork-button').href = "./manage/?id=" + data[i].id;
		
		document.getElementById('artwork-cardlist').appendChild(clone);
	}
</script>
</body>
</html>