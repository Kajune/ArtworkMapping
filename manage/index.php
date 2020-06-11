<?php
	session_start();

	if (!isset($_GET['id'])) {
		header('Location:../');
		exit;
	}

	$id = $_GET['id'];

	$sql = mysqli_connect('localhost', 'artworkadmin', 'akagisankawaii', 'artwork');

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$stmt = mysqli_prepare($sql, "SELECT name, comment, tag, img FROM artwork WHERE `deleted` = false AND `id` = ?");
	mysqli_stmt_bind_param($stmt, "i", $id);
	if (mysqli_stmt_execute($stmt) && 
		mysqli_stmt_bind_result($stmt, $artwork_name, $artwork_comment, $artwork_tag, $artwork_img) && 
		mysqli_stmt_fetch($stmt)) {
		mysqli_stmt_close($stmt);
	} else {
		header('Location:../');
	}

	$artwork_img = "../img/artwork/".$artwork_img;

	# 図形のリスト
	$shape_list = json_encode([
		['id' => 0, 'name' => 'circle', 'src' => '../img/shape/shapes_01.png'], 
		['id' => 1, 'name' => 'square', 'src' => '../img/shape/shapes_02.png'], 
		['id' => 2, 'name' => 'cross', 'src' => '../img/shape/shapes_03.png'], 
		['id' => 3, 'name' => 'heart', 'src' => '../img/shape/shapes_04.png'],
		['id' => 4, 'name' => 'triangle', 'src' => '../img/shape/shapes_05.png'], 
		['id' => 5, 'name' => 'diamond', 'src' => '../img/shape/shapes_06.png'], 
		['id' => 6, 'name' => 'hexagon', 'src' => '../img/shape/shapes_07.png'], 
		['id' => 7, 'name' => 'pentagon', 'src' => '../img/shape/shapes_08.png'], 
	]);

	# idに紐づく損傷を取得する
	$damage_list = json_encode([
		['id' => 0, 'type' => '指紋', 'comment' => 'マヌケが付けた指紋', 'date' => '2015-06-20', 'color' => '#ff0000', 'shape.id' => 0, 'x' => 235, 'y' => 68],
		['id' => 1, 'type' => 'カビ', 'comment' => 'カビですよろしくお願いします', 'date' => '2016-12-03', 'color' => '#00ff00', 'shape.id' => 1, 'x' => 463, 'y' => 1135],
		['id' => 2, 'type' => '汚職', 'comment' => '政治家のお食事券', 'date' => '2016-12-31', 'color' => '#000000', 'shape.id' => 2, 'x' => 0, 'y' => 0],
		['id' => 3, 'type' => '心の汚れ', 'comment' => '心が汚れている', 'date' => '2020-03-02', 'color' => '#0000ff', 'shape.id' => 3, 'x' => 730, 'y' => 87],
	]);

	# idに紐づく損傷に紐づく画像
	$damage_image_list = json_encode([
		['id' => 0, 'damage.id' => 0, 'src' => '../img/damage/demaria11-damage1.jpg'],
		['id' => 1, 'damage.id' => 2, 'src' => '../img/damage/demaria11-damage2.jpg'],
		['id' => 2, 'damage.id' => 2, 'src' => '../img/damage/demaria11-damage3.jpg'],
	]);
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
					<button class="btn btn-secondary col-md-3 col-sm-6" id="create-new-damage" onclick="updateTag()">
						<span>現在位置に</span><span>新しい</span><span>損傷を</span><span>登録</span></button>

<!--					<button class="btn btn-secondary col-md-3 col-sm-6" data-toggle="modal" id="edit-damage" data-target="#edit-damage-dialog">
						<span>この損傷を</span><span>編集</span></button>-->

					<button class="btn btn-secondary col-md-3 col-sm-6" id="beginMoveDamageButton" onclick="beginMoveDamage()">
						<span>この損傷の</span><span>位置を</span><span>変更</span></button>

					<div id="endMoveDamageButtons" class="col-md-3 col-sm-6 btn-group-vertical" role="group">
						<button class="btn btn-primary" onclick="endMoveDamage()">完了</button>
						<button class="btn btn-secondary" onclick="cancelMoveDamage()">キャンセル</button>						
					</div>

					<button class="btn btn-warning col-md-3 col-sm-6" data-toggle="modal" id="delete-damage" data-target="#delete-damage-dialog">
						<span>この損傷を</span><span>削除</span></button>
				</div>

				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">種類</label>
					<div class="col-6">
						<input type="text" class="form-control" id="damage-type" placeholder="種類">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-comment" class="col-3 col-form-label">コメント</label>
					<div class="col-9">
						<textarea type="text" class="form-control" id="damage-comment" placeholder="コメント"></textarea>
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-date" class="col-3 col-form-label">登録日</label>
					<div class="col-9">
						<input type="date" class="form-control" id="damage-date">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="color" class="col-3 col-form-label">色・形状</label>
					<div class="col-3">
						<input type="color" class="form-control" id="damage-color" value="#000000" style="margin:0px; border:0px;">
					</div>

					<div class="btn-group btn-group-toggle col-md-6 col-sm-12 text-left" data-toggle="buttons" 
					id="damage-shape-buttons" style="flex-wrap: wrap;">
						<template id="shape-option">
							<button class="btn btn-outline-secondary shape-button" onchange="changeShape(event);" disabled>
								<input type="radio" autocomplete="off" class="shape-button">
								<img class="shape-img" src="" style="width:auto; height:3vh;">
							</button>
						</template>
					</div>
				</div>

				<div class="form-group row col-12">
					<div class="col-md-3 col-sm-12">
						<label for="referenceImageControl" class="col-form-label">参考画像</label>

						<div class="d-flex btn-toolbar">
							<button class="btn btn-sm btn-secondary col-md-12 col-sm-6" id="add-damage-image">
								<label style="width:100%;">
									<input type="file" style="display:none" onchange="addDamageImage(event);">参考画像を追加
								</label>
							</button>
							<button class="btn btn-sm btn-warning col-md-12 col-sm-6" id="delete-damage-image" data-toggle="modal" data-target="#delete-damage-image-dialog">現在の画像を削除</button>
						</div>
					</div>

					<div id="referenceImageControl" class="carousel slide col-md-6 col-sm-12" data-ride="carousel" data-interval="false" 
						style="background-color: gray;">
						<div class="carousel-inner" id="damage-image-list">
							<template id="damage-image">
								<div class="carousel-item">
									<img src="" style="width: 100%; height: auto;" data-toggle="modal" data-target="" class="thumbnail">
									<div class="modal fade" id="">
										<div class="modal-dialog">
											<div class="modal-body"><img src="" style="width: 100%; height: auto;"></div>
										</div>
									</div>
								</div>
							</template>
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
					<button class="btn btn-secondary col-3" onclick="updateArtwork()">タグを更新</button>
				</div>

				<div class="form-group d-flex col-12">
					<textarea class="form-control col-9" id="artwork_comment"><?php echo $artwork_comment; ?></textarea>
					<button class="btn btn-secondary col-3" onclick="updateArtwork()">コメントを更新</button>
				</div>

				<div class="form-group d-flex col-12">
					<button class="btn btn-danger" data-toggle="modal" data-target="#delete-artwork-dialog">この美術品を削除</button>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- ダイアログ -->
<div class="modal fade" id="delete-damage-dialog" tabindex="-1" role="dialog" aria-labelledby="label_delete_damage" aria-hidden="true">
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

<div class="modal fade" id="delete-damage-image-dialog" tabindex="-1" role="dialog" aria-labelledby="label_delete_damage_image" aria-hidden="true">
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

<div class="modal fade" id="delete-artwork-dialog" tabindex="-1" role="dialog" aria-labelledby="label_delete_artwork" aria-hidden="true">
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
				<button type="button" class="btn btn-danger" onclick="updateArtwork(true);">はい</button>
			</div>
		</div>
	</div>
</div>

<!--
<div class="modal fade" id="edit-damage-dialog" tabindex="-1" role="dialog" aria-labelledby="label_edit_damage" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_edit_damage">この損傷を編集</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">種類</label>
					<div class="col-6">
						<input type="text" class="form-control" id="damage-type" placeholder="種類">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-comment" class="col-3 col-form-label">コメント</label>
					<div class="col-9">
						<textarea type="text" class="form-control" id="damage-comment" placeholder="コメント"></textarea>
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-date" class="col-3 col-form-label">登録日</label>
					<div class="col-9">
						<input type="date" class="form-control" id="damage-date">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="color" class="col-3 col-form-label">色・形状</label>
					<div class="col-3">
						<input type="color" class="form-control" id="damage-color" value="#000000" style="margin:0px; border:0px;">
					</div>

					<div class="btn-group btn-group-toggle col-md-6 col-sm-12 text-left" data-toggle="buttons" 
					id="damage-shape-buttons" style="flex-wrap: wrap;">
						<template id="shape-option">
							<button class="btn btn-outline-secondary shape-button" onchange="changeShape(event);" disabled>
								<input type="radio" autocomplete="off" class="shape-button">
								<img class="shape-img" src="" style="width:auto; height:3vh;">
							</button>
						</template>
					</div>
				</div>

				<div class="form-group row col-12">
					<div class="col-md-3 col-sm-12">
						<label for="referenceImageControl" class="col-form-label">参考画像</label>

						<div class="d-flex btn-toolbar">
							<button class="btn btn-sm btn-secondary col-md-12 col-sm-6" id="add-damage-image" onclick="updateTag()">参考画像を追加</button>
							<button class="btn btn-sm btn-warning col-md-12 col-sm-6" id="delete-damage-image" data-toggle="modal" data-target="#delete-damage-image-dialog">現在の画像を削除</button>
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
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
				<button type="button" class="btn btn-primary">編集を適用</button>
			</div>
		</div>
	</div>
</div>
-->

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
	const radius = 50;
	const marker_size = 30;

	//
	// マーカーの読み込み
	//
	var shape_list = <?php echo $shape_list; ?>;
	var shape_imgs = {};
	for (const shape of shape_list) {
		var shape_img = new Image();
		shape_img.src = shape['src'];
		shape_imgs[shape['id']] = shape_img;
	}

	//
	// 損傷の読み込み
	//
	var damage_list = <?php echo $damage_list; ?>;
	var selected_damage = null;
	var year_list = [];
	for (const damage of damage_list) {
		damage['date'] = new Date(damage['date']);
		var year = damage['date'].getFullYear();
		if (year_list.indexOf(year) == -1) {
			year_list.push(year);
		}
		damage['visible'] = true;
	}

	//
	// 損傷の参考画像の読み込み
	//
	var damage_image_list = <?php echo $damage_image_list; ?>;

	function dateToISO(date) {
		var yyyy = date.getFullYear();
		var mm = ("0"+(date.getMonth()+1)).slice(-2);
		var dd = ("0"+date.getDate()).slice(-2);
		return yyyy+'-'+mm+'-'+dd;
	}

	function enableEditing(enable) {
//		$('#create-new-damage').prop("disabled", enable);
		$('#beginMoveDamageButton').prop("disabled", !enable);
		$('#edit-damage').prop("disabled", !enable);
		$('#delete-damage').prop("disabled", !enable);
		$('#damage-type').prop("disabled", !enable);
		$('#damage-comment').prop("disabled", !enable);
		$('#damage-date').prop("disabled", !enable);
		$('#damage-color').prop("disabled", !enable);
		$('button.shape-button').prop("disabled", !enable);
		$('#add-damage-image').prop("disabled", !enable);
		$('#delete-damage-image').prop("disabled", !enable);

		if (!enable || !selected_damage) {
			$('#damage-type').val('');
			$('#damage-comment').val('');

			var today = new Date();
			today.setDate(today.getDate());
			$('#damage-date').val(dateToISO(today));
			$('#damage-color').val('#000000');

			$('#damage-image-list').children().not('template').remove();
		} else {
			$('#damage-type').val(selected_damage['type']);
			$('#damage-comment').val(selected_damage['comment']);
			$('#damage-date').val(dateToISO(selected_damage['date']));
			$('#damage-color').val(selected_damage['color']);

			var shape_button = $('input.shape-button');
			shape_button.prop('checked', false);
			shape_button.parent().removeClass('active');				
	
			shape_button = $('input.shape-button[name="' + selected_damage['shape.id'] + '"]');
			shape_button.prop('checked', true);
			shape_button.parent().addClass('active');

			$('#damage-image-list').children().not('template').remove();

			var template = $('#damage-image').contents();
			var first = true;
			for (const damage_image of damage_image_list) {
				if (damage_image['damage.id'] != selected_damage['id']) {
					continue;
				}

				var clone = template.clone();
				clone.find('img').attr('src', damage_image['src']);
				clone.find('img.thumbnail').attr('data-target', '#damage-image' + damage_image['id']);
				clone.find('.modal').attr('id', 'damage-image' + damage_image['id']);
				if (first) {
					clone.addClass('active');
					first = false;
				}
				$('#damage-image-list').append(clone);
			}
		}
	}

	function checkSelection(x, y, real_scale) {
		var last_selected_damage = selected_damage;

		var centerX = -x * canvas.width / real_scale / 2 + img.width / 2;
		var centerY = y * canvas.height / real_scale / 2 + img.height / 2;
		var minDistance = radius * 2;
		for (const damage of damage_list) {
			if (!damage['visible']) {
				continue;
			}
			var distance = Math.hypot(centerX - damage['x'], centerY - damage['y']) * real_scale;
			if (distance < minDistance) {
				minDistance = distance;
				selected_damage = damage;
			}
		}
		if (minDistance > radius) {
			selected_damage = null;
		}
		if (last_selected_damage != selected_damage) {
			enableEditing(selected_damage);
		}
	}

	function drawMainImage(x, y, scale, real_scale) {
		context.clearRect(0, 0, canvas.width, canvas.height);

		context.scale(real_scale, real_scale);
		context.translate((canvas.width / real_scale - img.width) / 2, (canvas.height / real_scale - img.height) / 2);
		context.translate(x * 0.5 * canvas.width / real_scale, -y * 0.5 * canvas.height / real_scale);
		context.drawImage(img, 0, 0);

		mem_canvas = document.createElement("canvas");
		mem_canvas.width = marker_size;
		mem_canvas.height = marker_size;
		var mem_context = mem_canvas.getContext('2d');

		var scaled_marker_size = marker_size / real_scale;
		for (const damage of damage_list) {
			if (!damage['visible']) {
				continue;
			}
			mem_context.clearRect(0, 0, mem_canvas.width, mem_canvas.height);

			var shape_img_tmp = shape_imgs[damage['shape.id']];
			var x = damage['x'] - scaled_marker_size / 2;
			var y = damage['y'] - scaled_marker_size / 2;
			var w = scaled_marker_size;
			var h = scaled_marker_size;

			const margin = 0.1;
			mem_context.drawImage(shape_img_tmp, 0, 0, shape_img_tmp.width, shape_img_tmp.height, 
				mem_canvas.width * margin, mem_canvas.height * margin, 
				mem_canvas.width * (1 - margin * 2), mem_canvas.height * (1 - margin * 2));
			mem_context.fillStyle = damage['color'];
			mem_context.globalCompositeOperation = "source-atop";
			mem_context.fillRect(0, 0, mem_canvas.width, mem_canvas.height);

			mem_context.globalCompositeOperation = "destination-over";
			mem_context.drawImage(shape_img_tmp, 0, 0, shape_img_tmp.width, shape_img_tmp.height, 
				0, 0, mem_canvas.width, mem_canvas.height);
			mem_context.globalCompositeOperation = "source-over";

			if (selected_damage === damage) {
				mem_context.lineWidth = marker_size * margin;
				mem_context.strokeStyle = 'white';
				mem_context.beginPath();
				mem_context.arc(mem_canvas.width / 2, mem_canvas.height / 2, marker_size / 2, 0, Math.PI * 2);
				mem_context.stroke();
			}

			context.drawImage(mem_canvas, 0, 0, mem_canvas.width, mem_canvas.height, x, y, w, h);
		}
		context.resetTransform();
	}

	function drawReticle() {
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
	}

	function drawSubImage(x, y, real_scale) {
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

	function updateCanvas(x, y, scale) {
		var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * scale;
		checkSelection(x, y, real_scale);
		drawMainImage(x, y, scale, real_scale);
		drawReticle();
		drawSubImage(x, y, real_scale);
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
			updateCanvas(img_x, img_y, img_scale);
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
		img_x *= scale_change;
		img_y *= scale_change;

		updateCanvas(img_x, img_y, img_scale);

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
			img_x *= scale_change;
			img_y *= scale_change;

			finger_distance = new_finger_distance;
		}

		updateCanvas(img_x, img_y, img_scale);

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

	function updateArtwork(del=false) {
		var data = { 'id': id, 
			'artwork-tag': $('#artwork_tag').val(),
			'artwork-comment': $('#artwork_comment').val(),
			'artwork-deleted': del };

		$.ajax({
			type: "POST",
			url: './updateArtwork.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) { if (del) { location.href = "../"; } });;
	}

	function changeVisibleYear(e) {
		for (const damage of damage_list) {
			if (damage['date'].getFullYear() == e.target.name) {
				damage['visible'] = e.target.checked;
			}
		}
		updateCanvas(img_x, img_y, img_scale);
	}

	function changeShape(e) {
		console.log(e.target);
	}

	function addDamageImage(e) {
		if (e.target.files.length == 0) {
			return;
		}
		var reader = new FileReader();
		reader.onload = function (e) {
			alert(e.target.result);
		}
		reader.readAsDataURL(e.target.files[0]);
	}

	$(document).ready(function() {
		var template = document.getElementById('year-checkbox');

		for (var i = 0; i < year_list.length; i++) {
			var clone = template.content.cloneNode(true);

			clone.querySelector('.form-check-input').id = 'visible-' + year_list[i];
			clone.querySelector('.form-check-input').name = year_list[i];
			clone.querySelector('.form-check-label').htmlFor = 'visible-' + year_list[i];
			clone.querySelector('.form-check-label').textContent = year_list[i];

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

		$('#endMoveDamageButtons').hide();

		template = document.getElementById('shape-option');

		for (var i = 0; i < shape_list.length; i++) {
			var clone = template.content.cloneNode(true);

			clone.querySelector('.shape-img').src = shape_list[i]['src'];
			clone.querySelector('input').name = shape_list[i]['id'];
			if (i == 0) {
				clone.querySelector('button').className += ' active';
				clone.querySelector('input').checked = true;
			}

			$('#damage-shape-buttons').append(clone);
		}
	});

	$(window).on('load', function() {
		updateCanvas(img_x, img_y, img_scale, radius);
		enableEditing(selected_damage);
	});
</script>
</body>
</html>