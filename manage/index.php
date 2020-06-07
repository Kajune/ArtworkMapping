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
	$artwork_tag = "指紋, バカ客, マヌケ";
	$artwork_img = "../img/artwork/demaria11.jpg";

	# idに紐づく損傷を取得する
	$damage_years = json_encode([2015, 2016, 2018, 2020]);

	# 図形のリスト
	$shape_list = json_encode([
		['id' => 0, 'name' => 'circle', 'src' => '../img/shape/shapes_01.png'], 
		['id' => 1, 'name' => 'square', 'src' => '../img/shape/shapes_02.png'], 
		['id' => 2, 'name' => 'cross', 'src' => '../img/shape/shapes_03.png'], 
		['id' => 3, 'name' => 'heart', 'src' => '../img/shape/shapes_04.png'],
		['id' => 4, 'name' => 'triangle', 'src' => '../img/shape/shapes_05.png'], 
		['id' => 5, 'name' => 'diamond', 'src' => '../img/shape/shapes_06.png'], 
		['id' => 6, 'name' => 'hexagon', 'src' => '../img/shape/shapes_07.png'], 
		['id' => 7, 'name' => 'pentagon', 'src' => '../img/shape/shapes_08.png'], ]);
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
		<div class="col-lg-6 col-xl-6 col-md-12 col-sm-12 col-xs-12 card">
			<div class="card-body">
				<div class="row">
					<h2 class="col-md-9 col-sm-12"><?php echo $artwork_name; ?></h2>
					<div class="col-md-3 col-sm-12"><a type="button" class="btn-lg btn-secondary" href="../" style="display: inline-block;">一覧に戻る</a></div>
				</div>

				<div class="row">
					<div class="col-sm-3 col-xs-12">
						<figure class="figure d-none d-sm-block">
							<figcaption class="figure-caption">全体像</figcaption>
							<div class="insideWrapper">
								<img src="<?php echo $artwork_img ?>" class="overedImage" style="max-width: 100%; height: auto;">
								<canvas class="coveringCanvas" width="300" height="300" id="thumb_canvas"></canvas>
							</div>
						</figure>

						<div class="d-flex justify-content-around form-row" id="year-list">
							<template id="year-checkbox">
								<div class="form-check form-control-lg col-sm-12 col-3" onchange="changeVisibleYear(event);">
									<input class="form-check-input" type="checkbox" value="" id="" checked>
									<label class="form-check-label" for=""></label>
								</div>
							</template>
						</div>
					</div>

					<div class="figure col-sm-9 col-xs-12" style="padding: 0px; margin: 0px;">
						<figcaption class="figure-caption">拡大図</figcaption>
						<canvas id="artwork_canvas" width="1000" height="1000" style="background-color:gray; width:100%; height: auto;"></canvas>
						<small>損傷を選択するには、損傷を中央の円内に収める</small>
					</div>
				</div>

				<br>
			</div>
		</div>

		<div class="col-lg-6 col-xl-6 col-md-12 col-sm-12 col-xs-12 card">
			<div class="row card-body justify-content-around">
				<div class="form-group d-flex btn-toolbar row justify-content-around col-12">
					<button class="btn btn-secondary col-md-3 col-sm-6" onclick="updateTag()">
						<span>現在位置に</span><span>新しい</span><span>損傷を</span><span>登録</span></button>

					<button class="btn btn-secondary col-md-3 col-sm-6" id="beginMoveDamageButton" onclick="beginMoveDamage()">
						<span>この損傷の</span><span>位置を</span><span>変更</span></button>

					<div id="endMoveDamageButtons" class="col-md-3 col-sm-6 btn-group-vertical" role="group">
						<button class="btn btn-primary" onclick="endMoveDamage()">完了</button>
						<button class="btn btn-secondary" onclick="cancelMoveDamage()">キャンセル</button>						
					</div>

					<button class="btn btn-warning col-md-3 col-sm-6" data-toggle="modal" data-target="#delete_damage">
						<span>この損傷を</span><span>削除</span></button>
				</div>

				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">種類</label>
					<div class="col-6">
						<input type="text" class="form-control" id="damage-type" placeholder="種類">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">コメント</label>
					<div class="col-9">
						<textarea type="text" class="form-control" id="damage-comment" placeholder="コメント"></textarea>
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">登録日</label>
					<div class="col-9">
						<input type="date" class="form-control" id="damage-date">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="color" class="col-3 col-form-label">色・形状</label>
					<div class="col-3">
						<input type="color" class="form-control" id="damage-color" style="margin:0px; border:0px;">
					</div>

					<div class="btn-group btn-group-toggle col-md-6 col-sm-12 text-left" data-toggle="buttons" 
					id="damage-shape-buttons" style="flex-wrap: wrap;">
						<template id="shape-option">
							<label class="btn btn-outline-secondary" onchange="changeShape(event);">
								<input type="radio" autocomplete="off">
								<img class="shape-img" src="" style="width:auto; height:3vh;">
							</label>
						</template>
					</div>
				</div>

				<div class="form-group row col-12">
					<div class="col-md-3 col-sm-12">
						<label for="referenceImageControl" class="col-form-label">参考画像</label>

						<div class="d-flex btn-toolbar">
							<button class="btn-sm btn-secondary col-md-12 col-sm-6" onclick="updateTag()">参考画像を追加</button>
							<button class="btn-sm btn-warning col-md-12 col-sm-6" data-toggle="modal" data-target="#delete_damage_image">現在の画像を削除</button>
						</div>
					</div>

					<div id="referenceImageControl" class="carousel slide col-md-6 col-sm-12" data-ride="carousel" data-interval="false">
						<div class="carousel-inner">
							<div class="carousel-item active">
								<img src="../img/damage/demaria11-damage1.jpg" style="width: 100%; height: auto;" data-toggle="modal" data-target="#image-modal">
								<div class="modal fade" id="image-modal">
									<div class="modal-dialog">
										<div class="modal-body">
											<img src="../img/damage/demaria11-damage1.jpg" style="width: 100%; height: auto;">
										</div>
									</div>
								</div>
							</div>

							<div class="carousel-item">
								<img src="../img/damage/demaria11-damage2.jpg" style="width: 100%; height: auto;" data-toggle="modal" data-target="#image-modal">
							</div>

							<div class="carousel-item">
								<img src="../img/damage/demaria11-damage3.jpg" style="width: 100%; height: auto;" data-toggle="modal" data-target="#image-modal">
							</div>
						</div>
						<a class="carousel-control-prev" href="#referenceImageControl" role="button" data-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="sr-only">前へ</span>
						</a>
						<a class="carousel-control-next" href="#referenceImageControl" role="button" data-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="sr-only">次へ</span>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-6 col-xl-6 col-md-12 col-sm-12 col-xs-12 card">
			<div class="row card-body">
				<div class="form-group d-flex col-12">
					<input type="text" class="form-control col-9" id="artwork_tag" placeholder="タグ(コンマ区切り)" value="<?php echo $artwork_tag; ?>">
					<button class="btn btn-secondary col-3" onclick="updateTag()">タグを更新</button>
				</div>

				<div class="form-group d-flex col-12">
					<textarea class="form-control col-9" id="artwork_comment"><?php echo $artwork_comment; ?></textarea>
					<button class="btn btn-secondary col-3" onclick="updateComment()">コメントを更新</button>
				</div>

				<div class="form-group d-flex col-12">
					<button class="btn btn-danger" data-toggle="modal" data-target="#delete_artwork">この美術品を削除</button>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- ダイアログ -->
<div class="modal fade" id="delete_damage" tabindex="-1" role="dialog" aria-labelledby="label_delete_damage" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_delete_damage">この損傷を削除</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				本当にこの損傷を削除しますか？
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
				<button type="button" class="btn btn-warning">はい</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="delete_damage_image" tabindex="-1" role="dialog" aria-labelledby="label_delete_damage_image" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_delete_damage_image">この参考画像を削除</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				本当にこの参考画像を削除しますか？
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
				<button type="button" class="btn btn-warning">はい</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="delete_artwork" tabindex="-1" role="dialog" aria-labelledby="label_delete_artwork" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_delete_artwork">この美術品を削除</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				本当にこの美術品を削除しますか？<br>
				この美術品に紐づく損傷もすべて削除されます。
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
				<button type="button" class="btn btn-danger">はい</button>
			</div>
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

	var clicked = false;

	var mx = 0;
	var my = 0;

	var tx = 0;
	var ty = 0;

	var finger_distance = 0;

	var shape_imgs = [];

	const radius = 30;

	function drawImage(x, y, scale) {
		context.clearRect(0, 0, canvas.width, canvas.height);

		var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * scale;
		context.scale(real_scale, real_scale);
		context.translate((canvas.width / real_scale - img.width) / 2, (canvas.height / real_scale - img.height) / 2);
		context.translate(x * 0.5 * canvas.width / real_scale, -y * 0.5 * canvas.height / real_scale);
		context.drawImage(img, 0, 0);
		context.resetTransform();

		context.lineWidth = 5;
		context.strokeStyle = 'yellow';
		context.beginPath();
		context.moveTo(0, canvas.height / 2);
		context.lineTo(canvas.width / 2 - radius, canvas.height / 2);

		context.moveTo(canvas.width, canvas.height / 2);
		context.lineTo(canvas.width / 2 + radius, canvas.height / 2);

		context.moveTo(canvas.width / 2, 0);
		context.lineTo(canvas.width / 2, canvas.height / 2 - radius);

		context.moveTo(canvas.width / 2, canvas.height);
		context.lineTo(canvas.width / 2, canvas.height / 2 + radius);
		context.stroke();

		context.beginPath();
		context.arc(canvas.width / 2, canvas.height / 2, radius, 0, 2 * Math.PI);
		context.stroke();

		var left = Math.min(img.width, Math.max(0, img.width / 2 - ((x + 1) / 2) * canvas.width / real_scale));
		var right = Math.min(img.width, Math.max(0, img.width / 2 + ((1 - x) / 2) * canvas.width / real_scale));
		var top = Math.min(img.height, Math.max(0, img.height / 2 - ((1 - y) / 2) * canvas.height / real_scale));
		var bottom = Math.min(img.height, Math.max(0, img.height / 2 + ((1 + y) / 2) * canvas.height / real_scale));

		var thumb_scale_x = thumb_canvas.width / img.width;
		var thumb_scale_y = thumb_canvas.height / img.height;

		thumb_context.lineWidth = 5;
		thumb_context.strokeStyle = 'yellow';
		thumb_context.clearRect(0, 0, thumb_canvas.width, thumb_canvas.height);
		thumb_context.strokeRect(left * thumb_scale_x, top * thumb_scale_y, (right - left) * thumb_scale_x, (bottom - top) * thumb_scale_y);
		thumb_context.resetTransform();
	}

	function onMouseDown(event) {
		if (event.button === 0) {
			clicked = true;
		}
	}

	function onMouseUp(event) {
		if (event.button === 0) {
			clicked = false;
		}
	}

	function onMouseLeave(event) {
		clicked = false;
	}

	function onMouseMove(event) {
		if (clicked) {
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

		var delta = (typeof event.wheelDeltaY !== 'undefined') ? event.wheelDeltaY : event.deltaY;

		var scale_change = 0.8;

		if (delta < 0) {
			scale_change = 1 / scale_change;
		}

		img_scale *= scale_change;
		if (img_scale < 1) {
			scale_change /= img_scale;
			img_scale = 1;
		}
		img_x = (img_x - x) * scale_change + x;
		img_y = (img_y - y) * scale_change + y;

		drawImage(img_x, img_y, img_scale);

		event.preventDefault();
	}

	function getFingerPos(event){
		var x = 0, y = 0;
		if (event.touches) {
			x = event.touches[0].clientX;
			y = event.touches[0].clientY;
		} else if (event.originalEvent && event.originalEvent.changedTouches[0]) {
			x = event.originalEvent.changedTouches[0].clientX;
			y = event.originalEvent.changedTouches[0].clientY;
		} else if (event.clientX && event.clientY) {
			x = event.clientX;
			y = event.clientY;
		}
		return {x: x, y: y}
	}

	function onTouchStart(event) {
		var {x, y} = getFingerPos(event);
		tx = x;
		ty = y;

		if (event.touches.length >= 2) {
			var rect = canvas.getBoundingClientRect();

			finger_distance = Math.hypot(
				(event.touches[0].clientX - event.touches[1].clientX) / (rect.right - rect.left) * 2,
				(event.touches[0].clientY - event.touches[1].clientY) / (rect.top - rect.bottom) * 2);
		}
	}

	function onTouchMove(event) {
		var {x, y} = getFingerPos(event);

		var rect = canvas.getBoundingClientRect();

		if (event.touches.length == 1) {
			img_x += (x - tx) / (rect.right - rect.left) * 2;
			img_y += (y - ty) / (rect.top - rect.bottom) * 2;
		} else if (event.touches.length >= 2) {
			var x1 = (event.touches[0].clientX - rect.left) / (rect.right - rect.left) * 2 - 1;
			var y1 = (event.touches[0].clientY - rect.bottom) / (rect.top - rect.bottom) * 2 - 1;
			var x2 = (event.touches[1].clientX - rect.left) / (rect.right - rect.left) * 2 - 1;
			var y2 = (event.touches[1].clientY - rect.bottom) / (rect.top - rect.bottom) * 2 - 1;

			var new_finger_distance = Math.hypot(x2 - x1, y2 - y1);

			var midX = (x1 + x2) / 2;
			var midY = (y1 + y2) / 2;

			var scale_change = new_finger_distance / finger_distance;
			img_scale *= scale_change;
			if (img_scale < 1) {
				scale_change /= img_scale;
				img_scale = 1;
			}
			img_x = (img_x - midX) * scale_change + midX;
			img_y = (img_y - midY) * scale_change + midY;

			finger_distance = new_finger_distance;
		}

		drawImage(img_x, img_y, img_scale);

		tx = x;
		ty = y;

		event.preventDefault();
	}

	function beginMoveDamage() {
		$('#beginMoveDamageButton').hide();		
		$('#endMoveDamageButtons').show();
	}

	function endMoveDamage() {
		$('#endMoveDamageButtons').hide();
		$('#beginMoveDamageButton').show();
	}

	function cancelMoveDamage() {
		$('#endMoveDamageButtons').hide();
		$('#beginMoveDamageButton').show();
	}

	function updateTag() {
		var data = { 'id': id, 'newtag': $('#artwork_tag').val() };

		$.ajax({
			type: "POST",
			url: './updateTag.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) { alert(data['id']); alert(data['tag']); });
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

	function changeShape(e){
		console.log(e.target);
	}

	img.onload = function(){ drawImage(img_x, img_y, img_scale, radius); };

	$(document).ready(function() {
		var thisURL = './?id=' + <?php echo $id; ?>;

		var data = <?php echo $damage_years; ?>;
		var template = document.getElementById('year-checkbox');

		for (var i = 0; i < data.length; i++) {
			var clone = template.content.cloneNode(true);

			clone.querySelector('.form-check-label').textContent = data[i];
			clone.querySelector('.form-check-input').id = 'visible-' + data[i];
			clone.querySelector('.form-check-label').htmlFor = 'visible-' + data[i];

			document.getElementById('year-list').appendChild(clone);
		}

		canvas.addEventListener('mousedown', onMouseDown, false);
		canvas.addEventListener('mouseup', onMouseUp, false);
		canvas.addEventListener('mouseleave', onMouseLeave, false);
		canvas.addEventListener('mousemove', onMouseMove, false);
		canvas.addEventListener('mousewheel', onMouseWheel, false);
		canvas.addEventListener('wheel', onMouseWheel, false);
		canvas.addEventListener('touchstart', onTouchStart, false);
		canvas.addEventListener('touchmove', onTouchMove, false);

		var today = new Date();
		today.setDate(today.getDate());
		var yyyy = today.getFullYear();
		var mm = ("0"+(today.getMonth()+1)).slice(-2);
		var dd = ("0"+today.getDate()).slice(-2);
		document.getElementById("damage-date").value=yyyy+'-'+mm+'-'+dd;

		$('#endMoveDamageButtons').hide();

		template = document.getElementById('shape-option');

		var shape_list = <?php echo $shape_list; ?>;
		for (var i = 0; i < shape_list.length; i++) {
			var img = new Image();
			img.src = shape_list[i].src;
			shape_imgs.push(img);

			var clone = template.content.cloneNode(true);

			clone.querySelector('.shape-img').src = img.src;
			clone.querySelector('input').name = shape_list[i].id;
			if (i == 0) {
				clone.querySelector('label').className += ' active';
				clone.querySelector('input').checked = true;
			}

			$('#damage-shape-buttons').append(clone);
		}
	});
</script>
</body>
</html>