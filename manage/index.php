<?php
	session_start();

	if (!isset($_GET['id'])) {
		header('Location:../');
		exit;
	}

	$id = $_GET['id'];
	
	# 実際にはここでidをキーにしてSQLを発行しデータを取得する
	$artwork_name = "デ・マリア彫像⑪";
	$artwork_comment = "デ・マリア彫像のコメント";
	$artwork_img = "../img/demaria11.jpg";

	# idに紐づく損傷を取得する
	$damage_years = json_encode([2015, 2016, 2018, 2020]);
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="content-type" charset="utf-8">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<!-- 注意！Ajaxのためここだけslimじゃないものを使っている -->
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

	<title>美術品損傷管理システム</title>

	<link rel="stylesheet" type="text/css" href="../style.css">

	<script type="text/javascript">
		var id = <?php echo $id; ?>
	</script>
</head>
<body>
<div class="container-fluid main">
	<div class="row">
		<div class="col-lg-6 col-xl-6 col-md-12 col-sm-12 col-xs-12">
			<div class="row">
				<h2 class="col-md-9 col-sm-12"><?php echo $artwork_name; ?></h2>
				<div class="col-md-3 col-sm-12"><a type="button" class="btn-lg btn-secondary" href="../">一覧に戻る</a></div>
			</div>

			<div class="row">
				<div class="col-3">
					<figure class="figure">
						<figcaption class="figure-caption">全体像</figcaption>
						<div class="outsideWrapper">
							<div class="insideWrapper">
								<img src="<?php echo $artwork_img ?>" class="overedImage" style="max-width: 15vw; height: 15vw;">
								<canvas class="coveringCanvas" width="300" height="300" id="thumb_canvas"></canvas>
							</div>
						</div>
					</figure>

					<ul class="list-group list-group-flush" id="year-list">
						<template id="year-checkbox">
							<div class="form-check form-control-lg" onchange="changeVisibleYear(event);">
								<input class="form-check-input" type="checkbox" value="" id="" checked>
								<label class="form-check-label" for=""></label>
							</div>
						</template>
					</ul>
				</div>

				<div class="figure col-9">
					<figcaption class="figure-caption">拡大図</figcaption>
					<canvas id="artwork_canvas" width="1000" height="1000" style="background-color:gray; width:100%; height: auto;"></canvas>
				</div>
			</div>

			<br>
			<div class="row">
				<textarea class="form-control col-9" id="artwork_comment"><?php echo $artwork_comment ?></textarea>
				<button class="btn btn-secondary col-3" onclick="updateComment()">コメントを更新</button>
			</div>

			<br>
			<button class="btn btn-danger">この美術品を削除</button>
		</div>
	</div>

</div>

<script type="text/javascript">
	var img = new Image();
	img.src = "<?php echo $artwork_img; ?>";
	var canvas = document.getElementById('artwork_canvas');
	var thumb_canvas = document.getElementById('thumb_canvas');
	var context = canvas.getContext('2d');
	var thumb_context = thumb_canvas.getContext('2d');

	var img_x = 0;
	var img_y = 0;
	var img_scale = 1;

	var mx = 0;
	var my = 0;

	var tx = 0;
	var ty = 0;

	img.onload = function(){drawImage(img_x, img_y, img_scale)};

	function drawImage(x, y, scale) {
		context.clearRect(0, 0, canvas.width, canvas.height);

		var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * scale;
		context.scale(real_scale, real_scale);
		context.translate((canvas.width / real_scale - img.width) / 2, (canvas.height / real_scale - img.height) / 2);
		context.translate(x * 0.5 * canvas.width / real_scale, -y * 0.5 * canvas.height / real_scale);
		context.drawImage(img, 0, 0);
		context.resetTransform();

		var left = Math.min(img.width, Math.max(0, img.width / 2 - ((x + 1) / 2) * canvas.width / real_scale));
		var right = Math.min(img.width, Math.max(0, img.width / 2 + ((1 - x) / 2) * canvas.width / real_scale));
		var top = Math.min(img.height, Math.max(0, img.height / 2 - ((1 - y) / 2) * canvas.height / real_scale));
		var bottom = Math.min(img.height, Math.max(0, img.height / 2 + ((1 + y) / 2) * canvas.height / real_scale));

		var thumb_scale_x = thumb_canvas.width / img.width;
		var thumb_scale_y = thumb_canvas.height / img.height;

		thumb_context.strokeStyle = 'yellow';
		thumb_context.clearRect(0, 0, thumb_canvas.width, thumb_canvas.height);
		thumb_context.strokeRect(left * thumb_scale_x, top * thumb_scale_y, (right - left) * thumb_scale_x, (bottom - top) * thumb_scale_y);
		thumb_context.resetTransform();
	}

	function onMouseMove(event) {
		if (event.which == 1) {
			var rect = canvas.getBoundingClientRect();
			img_x += (event.x - mx) / (rect.right - rect.left) * 2;
			img_y += (event.y - my) / (rect.top - rect.bottom) * 2;
			drawImage(img_x, img_y, img_scale);

		}

		mx = event.x;
		my = event.y;
	}

	function onMouseWheel(event) {
		var rect = canvas.getBoundingClientRect();
		var x = (event.x - rect.left) / (rect.right - rect.left);
		var y = (event.y - rect.bottom) / (rect.top - rect.bottom);
		x = x * 2 - 1
		y = y * 2 - 1

		scale_change = 0.8;

		if (event.wheelDeltaY > 0) {
			img_scale *= scale_change;
			img_x = (img_x - x) * scale_change + x;
			img_y = (img_y - y) * scale_change + y;
		} else {
			img_scale /= scale_change;
			img_x = (img_x - x) / scale_change + x;
			img_y = (img_y - y) / scale_change + y;
		}
		drawImage(img_x, img_y, img_scale);

		event.preventDefault();
	}

	function onTouchStart(event) {
		var x = 0, y = 0;
		if (event.touches && event.touches[0]) {
			x = event.touches[0].clientX;
			y = event.touches[0].clientY;
		} else if (event.originalEvent && event.originalEvent.changedTouches[0]) {
			x = event.originalEvent.changedTouches[0].clientX;
			y = event.originalEvent.changedTouches[0].clientY;
		} else if (event.clientX && event.clientY) {
			x = event.clientX;
			y = event.clientY;
		}

		tx = x;
		ty = y;
	}

	function onTouchMove(event) {
		var x = 0, y = 0;
		if (event.touches && event.touches[0]) {
			x = event.touches[0].clientX;
			y = event.touches[0].clientY;
		} else if (event.originalEvent && event.originalEvent.changedTouches[0]) {
			x = event.originalEvent.changedTouches[0].clientX;
			y = event.originalEvent.changedTouches[0].clientY;
		} else if (event.clientX && event.clientY) {
			x = event.clientX;
			y = event.clientY;
		}

		img_x += (x - tx) / canvas.width * 2;
		img_y -= (y - ty) / canvas.height * 2;
		drawImage(img_x, img_y, img_scale);

		tx = x;
		ty = y;
	}

	function updateComment() {
		var data = { 'id': id, 'newcomment': $('#artwork_comment').val() };

		$.ajax({
			type: "POST",
			url: './updateComment.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) { alert(data['id']); alert(data['comment']); });
	}

	function changeVisibleYear(e) {
		console.log(e.target.id + ':' + e.target.checked);
	}

	$(document).ready(function() {
		thisURL = './?id=' + <?php echo $id; ?>;

		var data = <?php echo $damage_years; ?>;
		var template = document.getElementById('year-checkbox');

		for (var i = 0; i < data.length; i++) {
			var clone = template.content.cloneNode(true);

			clone.querySelector('.form-check-input').id = 'visible-' + data[i];
			clone.querySelector('.form-check-label').htmlFor = 'visible-' + data[i];
			clone.querySelector('.form-check-label').textContent = data[i];

			document.getElementById('year-list').appendChild(clone);
		}

		canvas.addEventListener('mousemove', onMouseMove, false);
		canvas.addEventListener('mousewheel', onMouseWheel, false);
		canvas.addEventListener('touchstart', onTouchStart, false);
		canvas.addEventListener('touchmove', onTouchMove, false);
	});
</script>
</body>
</html>