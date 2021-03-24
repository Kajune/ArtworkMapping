<?php
	session_start();

	if (!isset($_GET['id'])) {
		header('Location:../');
		exit;
	}

	$_SESSION['editmode'] = false;

	require_once '../DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$id = $_GET['id'];
	$id = mysqli_real_escape_string($sql, $id);

	$result = mysqli_query($sql, "SELECT name, comment, tag, img FROM artwork WHERE `deleted` = false AND `id` = $id");
	if ($result) {
		$row = $result->fetch_assoc();
		$artwork_name = $row['name'];
		$artwork_comment = $row['comment'];
		$artwork_tag = $row['tag'];
		$artwork_img = $row['img'];
	} else {
		header('Location:../');
	}

	$artwork_img = "../img/artwork/".$artwork_img;
	list($width, $height, $type, $attr) = getimagesize($artwork_img);

	# 図形のリスト
	$shape_list = [];
	if ($result = mysqli_query($sql, "SELECT * from shape")) {
		while ($row = $result->fetch_assoc()) {
			$row['src'] = '../img/shape/'.$row['src'];
			$shape_list[] = $row;
		}
		mysqli_free_result($result);
	} else {
		echo mysqli_error($sql);
	}

	$shape_list = json_encode($shape_list);

	# idに紐づく損傷を取得する
	$damage_list = [];
	$result = mysqli_query($sql, "SELECT * FROM damage WHERE `artwork_id` = $id");
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$damage_list[] = $row;
		}
		mysqli_free_result($result);
	}

	$damage_list = json_encode($damage_list);

	# idに紐づく損傷に紐づく画像
	$damage_image_list = [];
	$damage_image_update = [];
	$result = mysqli_query($sql, "SELECT damage_img.id, damage_img.damage_id, damage_img.img ".
		"FROM damage_img JOIN damage ON damage_img.damage_id = damage.id WHERE `artwork_id` = $id");
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$row['update'] =date("Y/m/d", filemtime('../img/damage/'. $row['img']));
			$damage_image_list[] = $row;
		}
		mysqli_free_result($result);
	}

	$damage_image_list = json_encode($damage_image_list);
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

	<style type="text/css">
		.carousel-caption {
			position: absolute;
			top: 80%;
			transform: translateY(-100%);
		}
	</style>
</head>
<body>

<div class="container-fluid main">
	<div class="row">
		<div class="col-lg-7 col-xl-7 col-md-12 col-sm-12 col-xs-12 card">
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

						<hr>
<!--						<h4>登録年</h4>
						<div class="d-flex justify-content-around form-row" id="year-list">
							<template id="year-checkbox">
								<button class="badge badge-pill badge-light text-wrap">
									<div class="form-check form-control-sm col-sm-12 col-3" onchange="changeVisibleYear(event);">
										<input class="form-check-input" type="checkbox" value="" id="" checked>
										<label class="form-check-label text-nowrap" for="" style="font-size: 80%;"></label>
									</div>
								</button>
							</template>
						</div>-->

						<h4>表示日</h4>
						<div>
							<input type="date" class="form-control" id="show-date" onchange="changeVisibleDate(event)">
						</div>

						<hr>
						<h4>種類</h4>
						<div class="d-flex justify-content-around form-row" id="type-list">
							<template id="type-checkbox">
								<button class="badge badge-pill badge-light text-wrap">
									<div class="form-check form-control-sm col-sm-12 col-3" onchange="changeVisibleType(event);">
										<input class="form-check-input" type="checkbox" value="" id="" checked>
										<label class="form-check-label text-nowrap" for="" style="font-size: 80%;"></label>
									</div>
								</button>
							</template>
						</div>
						<hr>
					</div>

					<div class="figure col-sm-9 col-xs-12" style="padding: 0px; margin: 0px;">
						<figcaption class="figure-caption">拡大図</figcaption>
						<canvas id="artwork_canvas" width="1000" height="1000" style="background-color:gray; width:100%; height: auto;"></canvas>
						<button class="btn btn-secondary float-left" onclick="scale(false)">+</button>
						<button class="btn btn-secondary float-left" onclick="scale(true)">-</button>
						<button class="btn btn-secondary float-left" onclick="rotate(true)">↻</button>
						<small>損傷を選択するには、損傷を中央の円内に収める</small>
					</div>
				</div>

				<br>
			</div>
		</div>

		<div class="col-lg-5 col-xl-5 col-md-12 col-sm-12 col-xs-12 card">
			<div class="row card-body justify-content-around">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="switchEdit" onchange="switchEditMode(event)">
					<label class="custom-control-label" for="switchEdit">編集モード</label>
				</div>

				<div class="editable-item d-none">
					<div class="form-group d-flex btn-toolbar row justify-content-around col-12">
						<button class="btn btn-secondary col-md-3 col-sm-6" id="create-new-damage" onclick="createDamage()">
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
				</div>

				<div class="form-group row col-12">
					<label for="damage-type" class="col-3 col-form-label">種類</label>
					<div class="col-9">
						<input type="text" class="form-control editable-input" id="damage-type" placeholder="種類" onchange="changeType(event)" readonly="true">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-comment" class="col-3 col-form-label">コメント</label>
					<div class="col-9">
						<textarea type="text" rows="4" class="form-control editable-input" id="damage-comment" placeholder="コメント" onchange="changeComment(event)" readonly="true"></textarea>
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-adddate" class="col-3 col-form-label">登録日</label>
					<div class="col-9">
						<input type="date" class="form-control editable-input" id="damage-adddate" onchange="changeAddDate(event)" readonly="true">
					</div>
				</div>

				<div class="form-group row col-12">
					<label for="damage-deldate" class="col-3 col-form-label">削除日</label>
					<div class="col-9">
						<input type="date" class="form-control editable-input" id="damage-deldate" onchange="changeDelDate(event)" readonly="true">
					</div>
				</div>

				<div class="editable-item d-none">
					<div class="form-group row col-12">
						<label for="color" class="col-3 col-form-label">色・形状</label>
						<div class="col-3">
							<input type="color" class="form-control" id="damage-color" value="#000000" 
							style="margin:0px; border:0px;" onchange="changeColor(event)">
						</div>

						<div class="btn-group btn-group-toggle col-md-6 col-sm-12 text-left" data-toggle="buttons" 
						id="damage-shape-buttons" style="flex-wrap: wrap;">
							<template id="shape-option">
								<button class="btn btn-outline-secondary shape-button" onchange="changeShape(event);" disabled>
									<input type="radio" autocomplete="off" class="shape-button">
									<img class="shape-img" src="" style="width:auto; height:2.5vh;">
								</button>
							</template>
							<label for="damage-radius">サイズ</label>
							<input type="range" class="custom-range" id="damage-radius" min="0" max="<?php echo $width * 0.1; ?>" value="0" 
								onmousemove="changeRadius(event)" ontouchmove="changeRadius(event)" onchange="changeRadius(event,true)">
						</div>
					</div>
				</div>

				<div class="form-group row col-12">
					<div class="col-md-4 col-sm-12">
						<label for="referenceImageControl" class="col-form-label">参考画像</label>

						<div class="editable-item d-none">
							<div class="d-flex btn-toolbar">
								<button class="btn btn-sm btn-secondary col-md-12 col-sm-6" id="add-damage-image">
									<label style="width:100%;">
										<input type="file" id="damage-image-uploader" style="display:none" onchange="addDamageImage(event);">参考画像を追加
										<div class="progress">
											<div class="progress-bar" role="progressbar" id="damageImageUploadProgress" style="width: 0%" aria-valuenow="0" 
												aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</label>
								</button>
								<button class="btn btn-sm btn-warning col-md-12 col-sm-6" id="delete-damage-image" data-toggle="modal" data-target="#delete-damage-image-dialog" disabled>現在の画像を削除</button>
							</div>
						</div>
					</div>

					<div id="referenceImageControl" class="carousel slide col-md-6 col-sm-12" data-ride="carousel" data-interval="false">
						<div class="carousel-inner" id="damage-image-list" style="background-color: gray; height: 15vh;">
							<template id="damage-image">
								<div class="carousel-item" name="" style="max-width: 100%; height: 100%;">
									<img src="" data-toggle="modal" data-target="" class="thumbnail" style="max-width: 100%; max-height: 100%;">
									<div class="modal fade" id="">
										<div class="modal-dialog">
											<div class="modal-body"><img src="" style="width: 100%; height: auto;"></div>
										</div>
									</div>
									<div class="carousel-caption">
										<label class="damage-image-index" data-toggle="modal" data-target="" style="text-shadow:2px 2px 2px #000000;"></label>
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

		<div class="editable-item d-none">
			<div class="col-lg-7 col-xl-7 col-md-12 col-sm-12 col-xs-12 card">
				<div class="row card-body">
					<div class="form-group d-flex col-12">
						<input type="text" class="form-control col-9" id="artwork_tag" placeholder="タグ(コンマ区切り)" value="<?php echo $artwork_tag; ?>">
						<button class="btn btn-secondary col-3" onclick="updateTag()">タグを更新</button>
					</div>
					<div class="form-group d-flex col-12">
						<label id="tag-update-msg"></label>
					</div>

					<div class="form-group d-flex col-12">
						<textarea class="form-control col-9" id="artwork_comment"><?php echo $artwork_comment; ?></textarea>
						<button class="btn btn-secondary col-3" onclick="updateComment()">コメントを更新</button>
					</div>
					<div class="form-group d-flex col-12">
						<label id="comment-update-msg"></label>
					</div>

					<div class="form-group d-flex col-12">
						<button class="btn btn-success" onclick="export_to_excel()" id="export-btn">Excelにエクスポート<br>
							<small hidden="1" id="export-wait-message">しばらくお待ち下さい</small></button>
						<button class="btn btn-danger" data-toggle="modal" data-target="#delete-artwork-dialog">この美術品を削除</button>
					</div>
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
				<button type="button" class="btn btn-warning" onclick="deleteDamage()" data-dismiss="modal">はい</button>
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
				<button type="button" class="btn btn-warning" onclick="deleteDamageImage()" data-dismiss="modal">はい</button>
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
	var img_angle = 0;

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
	var moving_damage = null;
	var type_list = [];
	var year_list = [];
	for (const damage of damage_list) {
		if (type_list.indexOf(damage['type']) == -1) {
			type_list.push(damage['type']);
		}
		damage['adddate'] = new Date(damage['adddate']);
		damage['deldate'] = damage['deldate'] ? new Date(damage['deldate']) : null;

//		var year = damage['adddate'].getFullYear();
//		if (year_list.indexOf(year) == -1) {
//			year_list.push(year);
//		}
		damage['visible'] = true;
	}
	type_list.sort();
	year_list.sort();

	//
	// 損傷の参考画像の読み込み
	//
	var damage_image_list = <?php echo $damage_image_list; ?>;

	function dateToISO(date) {
		if (!date || date.toString() === "Invalid Date") {
			return '0000-00-00';
		}
		var yyyy = date.getFullYear();
		var mm = ("0"+(date.getMonth()+1)).slice(-2);
		var dd = ("0"+date.getDate()).slice(-2);
		return yyyy+'-'+mm+'-'+dd;
	}

	var defaultDamageValue = {
		'type' : '',
		'color': $('#damage-color').val(),
		'shape_id' : shape_list[0]['id'],
		'radius': 0,
	};

	//
	// 描画関係
	//

	function enableEditing(enable) {
//		$('#create-new-damage').prop("disabled", enable);
		$('#beginMoveDamageButton').prop("disabled", !enable);
		$('#edit-damage').prop("disabled", !enable);
		$('#delete-damage').prop("disabled", !enable);
		$('#damage-type').prop("disabled", !enable);
		$('#damage-comment').prop("disabled", !enable);
		$('#damage-adddate').prop("disabled", !enable);
		$('#damage-deldate').prop("disabled", !enable);
		$('#damage-color').prop("disabled", !enable);
		$('#damage-radius').prop("disabled", !enable);
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
			$('#damage-radius').val(0.0);
			
			$('#damage-image-list').children().not('template').remove();
		} else {
			$('#damage-type').val(selected_damage['type']);
			$('#damage-comment').val(selected_damage['comment']);
			$('#damage-adddate').val(dateToISO(selected_damage['adddate']));
			$('#damage-deldate').val(dateToISO(selected_damage['deldate']));
			$('#damage-color').val(selected_damage['color']);
			$('#damage-radius').val(selected_damage['radius']);

			var shape_button = $('input.shape-button');
			shape_button.prop('checked', false);
			shape_button.parent().removeClass('active');				
	
			shape_button = $('input.shape-button[name="' + selected_damage['shape_id'] + '"]');
			shape_button.prop('checked', true);
			shape_button.parent().addClass('active');

			$('#damage-image-list').children().not('template').remove();

			var template = $('#damage-image').contents();
			var first = true;
			var count = 0;
			for (const damage_image of damage_image_list) {
				if (damage_image['damage_id'] != selected_damage['id']) {
					continue;
				}

				var clone = template.clone();
				clone.find('img').attr('src', '../img/damage/' + damage_image['img']);
				clone.find('img.thumbnail').attr('data-target', '#damage-image' + damage_image['id']);
				clone.find('label').attr('data-target', '#damage-image' + damage_image['id']);
				clone.find('.modal').attr('id', 'damage-image' + damage_image['id']);
				clone.find('.damage-image-index').html((count + 1) + '枚目<br>' + damage_image['update']);
				clone.attr('name', '' + damage_image['id']);
				if (first) {
					clone.addClass('active');
					first = false;
				}
				$('#damage-image-list').append(clone);

				count++;
			}

			if (first) {
				$('#delete-damage-image').prop("disabled", true);
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
		if (minDistance > radius || moving_damage) {
			selected_damage = null;
		}
		if (last_selected_damage != selected_damage) {
			enableEditing(selected_damage);
		}
	}

	function drawMainImage(x, y, scale, real_scale, angle) {
		context.clearRect(0, 0, canvas.width, canvas.height);

		context.scale(real_scale, real_scale);
		context.translate((canvas.width / real_scale - img.width) / 2, (canvas.height / real_scale - img.height) / 2);
		context.translate(x * 0.5 * canvas.width / real_scale, -y * 0.5 * canvas.height / real_scale);

		context.translate(img.width / 2, img.height / 2);
		context.rotate(angle * Math.PI / 180);
		context.translate(-img.width / 2, -img.height / 2);

		context.drawImage(img, 0, 0);

		context.translate(img.width / 2, img.height / 2);
		context.rotate(-angle * Math.PI / 180);
		context.translate(-img.width / 2, -img.height / 2);

		for (const damage of damage_list) {
			if (!damage['visible']) {
				continue;
			}
			
			if (damage['radius'] > 0) {
				var shape_img_tmp = shape_imgs[damage['shape_id']];

				var rad_canvas = document.createElement("canvas");
				rad_canvas.width = damage['radius'] * 2;
				rad_canvas.height = damage['radius'] * 2;
				var rad_context = rad_canvas.getContext('2d');

				rad_context.drawImage(shape_img_tmp, 0, 0, shape_img_tmp.width, shape_img_tmp.height, 
									0, 0, rad_canvas.width, rad_canvas.height);
				rad_context.fillStyle = damage['color'];
				rad_context.globalCompositeOperation = "source-atop";
				rad_context.fillRect(0, 0, rad_canvas.width, rad_canvas.height);
				rad_context.globalCompositeOperation = "source-over";

				var x = damage['x'] - damage['radius'];
				var y = damage['y'] - damage['radius'];
				var w = damage['radius'] * 2;
				var h = damage['radius'] * 2;

				context.globalAlpha = 0.25;
				context.drawImage(rad_canvas, 0, 0, rad_canvas.width, rad_canvas.height, x, y, w, h);
				context.globalAlpha = 1.0;
			}
		}


		var mem_canvas = document.createElement("canvas");
		mem_canvas.width = marker_size;
		mem_canvas.height = marker_size;
		var mem_context = mem_canvas.getContext('2d');
		var scaled_marker_size = marker_size / real_scale;

		for (const damage of damage_list) {
			if (!damage['visible']) {
				continue;
			}
			mem_context.clearRect(0, 0, mem_canvas.width, mem_canvas.height);

			var shape_img_tmp = shape_imgs[damage['shape_id']];

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

			mem_context.globalCompositeOperation = "destination-out";
			mem_context.drawImage(shape_img_tmp, 0, 0, shape_img_tmp.width, shape_img_tmp.height, 
				mem_canvas.width * margin * 2, mem_canvas.height * margin * 2, 
				mem_canvas.width * (1 - margin * 4), mem_canvas.height * (1 - margin * 4));

			mem_context.globalCompositeOperation = "source-over";

			if (selected_damage === damage || moving_damage === damage) {
				mem_context.lineWidth = marker_size * margin;
				mem_context.strokeStyle = 'white';
				mem_context.beginPath();
				mem_context.arc(mem_canvas.width / 2, mem_canvas.height / 2, marker_size / 2, 0, Math.PI * 2);
				mem_context.stroke();
			}

			var x = damage['x'] - scaled_marker_size / 2;
			var y = damage['y'] - scaled_marker_size / 2;
			var w = scaled_marker_size;
			var h = scaled_marker_size;

			if (moving_damage === damage) {
				x = -img_x * canvas.width / real_scale / 2 + img.width / 2 - w / 2;
				y = img_y * canvas.height / real_scale / 2 + img.height / 2 - h / 2;
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

	function updateCanvas(x, y, scale, angle) {
		var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * scale;
		checkSelection(x, y, real_scale);
		drawMainImage(x, y, scale, real_scale, angle);
		drawReticle();
		drawSubImage(x, y, real_scale);
	}

	//
	// 拡大図操作関係
	//
	function scale(up=true) {
		var scale_change = 0.8;

		if (up) {
			img_scale *= scale_change;
		} else {
			img_scale /= scale_change;
		}
		if (img_scale < 1) {
			scale_change /= img_scale;
			img_scale = 1;
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function rotate(right=true) {
		if (right) {
			img_angle += 90;
		} else {
			img_angle -= 90;
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
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
			updateCanvas(img_x, img_y, img_scale, img_angle);
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

		updateCanvas(img_x, img_y, img_scale, img_angle);

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

		updateCanvas(img_x, img_y, img_scale, img_angle);

		tx = x;
		ty = y;

		event.preventDefault();
	}

	//
	// 美術品の情報更新
	//

	function updateTag() {
		$('#tag-update-msg').text('タグが更新されました');
		updateArtwork(false);
		setTimeout(function(){$('#tag-update-msg').text('');}, 5000);
	}

	function updateComment() {
		$('#comment-update-msg').text('コメントが更新されました');
		updateArtwork(false);
		setTimeout(function(){$('#comment-update-msg').text('');}, 5000);
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
		}).done(function (data, textStatus, xhr) { if (del) { location.href = "../"; } });
	}

	function export_to_excel() {
		var data = { 'id': id };
		$('#export-wait-message').attr('hidden', false);
		$('#export-btn').attr('disabled', true);

		$.ajax({
			type: "POST",
			url: './export.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) {
			window.location.href = data['result'];
			$('#export-wait-message').attr('hidden', true);
			$('#export-btn').attr('disabled', false);
		}).fail(function (data, textStatus, xhr) {
			console.log(textStatus);
			$('#export-wait-message').attr('hidden', true);
			$('#export-btn').attr('disabled', false);
			alert('Failed to Excel export. See console log.');
		});
	}

	//
	// 損傷編集関係
	//

	function switchEditMode(e) {
		editable = e.target.checked;

		if (editable) {
			pass = window.prompt("パスワードを入力してください", "");
			$.ajax({
				type: "POST",
				url: './changeEditMode.php',
				dataType: 'json',
				data: {'editmode': true, 'pass': pass},
			}).done(function (data, textStatus, xhr) {
				if (data['result']) {
					$('.editable-item').removeClass('d-none');
					$('.editable-input').attr('readonly', false);
				} else {
					alert('パスワードが違います');
					e.target.checked = false;
				}
			});
		} else {
			$.ajax({
				type: "POST",
				url: './changeEditMode.php',
				dataType: 'json',
				data: {'editmode': false},
			}).done(function (data, textStatus, xhr) {
				$('.editable-item').addClass('d-none');
				$('.editable-input').attr('readonly', true);
			});
		}
	}

	function createDamage() {
		var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * img_scale;
		var centerX = -img_x * canvas.width / real_scale / 2 + img.width / 2;
		var centerY = img_y * canvas.height / real_scale / 2 + img.height / 2;

		var data = { 'artwork_id': id,
			'type' : defaultDamageValue['type'],
			'color' : defaultDamageValue['color'],
			'shape_id' : defaultDamageValue['shape_id'],
			'x' : centerX, 
			'y' : centerY, 
			'radius' : defaultDamageValue['radius'],
		};

		$.ajax({
			type: "POST",
			url: './createDamage.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) {
			var damage = data['result'];
			damage['adddate'] = new Date(damage['adddate']);
			damage['deldate'] = new Date(damage['deldate']);
//			var year = damage['adddate'].getFullYear();
//			var index = year_list.indexOf(year);
//			if (index == -1) {
//				year_list.push(year);
//				addNewYear(year);
//				damage['visible'] = true;
//			} else {
//				damage['visible'] = $('#visible-' + year).prop('checked');
//			}

			damage['visible'] = true;

			if (damage['deldate'].toString() === "Invalid Date") {
				damage['deldate'] = null;
			}

			damage_list.push(damage);
			updateCanvas(img_x, img_y, img_scale, img_angle);
		});
	}

	function deleteDamage() {
		if (!selected_damage) {
			return;
		}

		var data = { 'id': selected_damage['id'] };

		damage_list = damage_list.filter(d => d !== selected_damage);

		$.ajax({
			type: "POST",
			url: './deleteDamage.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) {
			updateTypeCheckbox();
//			updateYearCheckbox();
			updateCanvas(img_x, img_y, img_scale, img_angle);
			console.log(data);
		});
	}

	function changeType(e) {
		if (selected_damage) {
			selected_damage['type'] = e.target.value;
			updateDamage(selected_damage);
			updateTypeCheckbox();
		}
		defaultDamageValue['type'] = e.target.value;
	}

	function changeComment(e) {
		if (selected_damage) {
			selected_damage['comment'] = e.target.value;
			updateDamage(selected_damage);
		}
	}

	function changeAddDate(e) {
		if (selected_damage) {
			selected_damage['adddate'] = new Date(e.target.value);
			updateDamage(selected_damage);
//			updateYearCheckbox();
		}
	}

	function changeDelDate(e) {
		if (selected_damage) {
			selected_damage['deldate'] = new Date(e.target.value);
			if (selected_damage['deldate'].toString() === "Invalid Date") {
				selected_damage['deldate'] = null;
			}
			updateDamage(selected_damage);
			updateVisibleDamageByDate();
//			updateYearCheckbox();
		}
	}

	function changeColor(e) {
		if (selected_damage) {
			selected_damage['color'] = e.target.value;
			updateDamage(selected_damage);
			updateCanvas(img_x, img_y, img_scale, img_angle);
		}
		defaultDamageValue['color'] = e.target.value;
	}

	function changeRadius(e, upload=false) {
		if (selected_damage) {
			selected_damage['radius'] = e.target.value;
			if (upload) {
				updateDamage(selected_damage);
			}
			updateCanvas(img_x, img_y, img_scale, img_angle);
		}
		defaultDamageValue['radius'] = e.target.value;
	}

	function changeShape(e) {
		if (selected_damage) {
			selected_damage['shape_id'] = e.target.name;
			updateDamage(selected_damage);
			updateCanvas(img_x, img_y, img_scale, img_angle);
		}
		defaultDamageValue['shape_id'] = e.target.name;
	}

	function updateDamage(damage) {
		var data = {
			'type' : damage['type'],
			'comment': damage['comment'],
			'adddate': dateToISO(damage['adddate']),
			'deldate': dateToISO(damage['deldate']),
			'color' : damage['color'],
			'shape_id' : damage['shape_id'],
			'x' : damage['x'], 
			'y' : damage['y'], 
			'id' : damage['id'],
			'radius': damage['radius'],
		};

		$.ajax({
			type: "POST",
			url: './updateDamage.php',
			dataType: 'json',
			data: data,
		}).done(function (data, textStatus, xhr) {
		}).fail(function (data, textStatus, xhr) {
			console.log(textStatus);
			alert('Failed to update damage. See console log.');
		});
	}

	function beginMoveDamage() {
		if (!selected_damage) {
			return;
		}
		$('#beginMoveDamageButton').hide();		
		$('#endMoveDamageButtons').show();
		moving_damage = selected_damage;
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function endMoveDamage() {
		$('#endMoveDamageButtons').hide();
		$('#beginMoveDamageButton').show();

		if (moving_damage) {
			var real_scale = Math.min(canvas.width / img.width, canvas.height / img.height) * img_scale;
			var centerX = -img_x * canvas.width / real_scale / 2 + img.width / 2;
			var centerY = img_y * canvas.height / real_scale / 2 + img.height / 2;

			moving_damage['x'] = centerX;
			moving_damage['y'] = centerY;

			updateDamage(moving_damage);
			moving_damage = null;
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function cancelMoveDamage() {
		$('#endMoveDamageButtons').hide();
		$('#beginMoveDamageButton').show();
		moving_damage = null;
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function addDamageImage(e) {
		if (!selected_damage) {
			return;
		}

		var formData = new FormData();
		formData.append('file', e.target.files[0]);
		formData.append('damage_id', selected_damage['id']);

		$.ajax({
			type: "POST",
			url: './addDamageImage.php',
			processData: false, 
			contentType: false,
			cache: false,
			dataType: 'json',
			data: formData,
			enctype: 'multipart/form-data',
			async: true,
			xhr : function(){
				var XHR = $.ajaxSettings.xhr();
				$('#damageImageUploadProgress').parent().show();
				if(XHR.upload){
					XHR.upload.addEventListener('progress',function(e){
						var progress = parseInt(e.loaded / e.total * 100);
						$('#damageImageUploadProgress').prop('aria-valuenow', progress);
						$('#damageImageUploadProgress').css('width', progress + '%');
					}, false);
				}
				return XHR;
			}
		}).done(function (data, textStatus, xhr) {
			damage_image_list.push(data['result']);
			enableEditing(true);
			$('#damageImageUploadProgress').parent().hide();
			$('#damage-image-uploader').val('');
		});
	}

	function deleteDamageImage() {
		if (!selected_damage) {
			return;
		}

		var delete_id = $('#damage-image-list').find('.active').attr('name');
		if (typeof delete_id !== 'undefined') {
			damage_image_list = damage_image_list.filter(d => d['id'] !== delete_id);

			$.ajax({
				type: "POST",
				url: './deleteDamageImage.php',
				dataType: 'json',
				data: { 'id': delete_id },
			}).done(function (data, textStatus, xhr) {
				enableEditing(true);
			});
		}
	}

	//
	// 左側の表示年度関係
	//
	/*
	function changeVisibleYear(e) {
		for (const damage of damage_list) {
			if (damage['adddate'].getFullYear() == e.target.name) {
				damage['visible'] = e.target.checked &&
					(damage['type'] == '' || $('#visible-' + btoa(encodeURIComponent(damage['type']))).prop('checked'));
			}
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}*/

	function updateVisibleDamageByDate() {
		date = new Date($('#show-date').val());
		for (const damage of damage_list) {
			if (date.toString() == 'Invalid date') {
				damage['visible'] = true;
				continue;
			}
			damage['visible'] = false;
			if (damage['adddate'] > date) {
				continue;
			}
			if (damage['deldate'] < date) {
				continue;
			}
			damage['visible'] = true;
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function changeVisibleDate(e) {
		updateVisibleDamageByDate();
	}

	function changeVisibleType(e) {
		for (const damage of damage_list) {
			if (damage['type'] == e.target.name) {
				damage['visible'] = e.target.checked;
//					&& $('#visible-' + damage['adddate'].getFullYear()).prop('checked');
			}
		}
		updateCanvas(img_x, img_y, img_scale, img_angle);
	}

	function addNewType(id) {
		var clone = $('#type-checkbox').contents().clone(true);

		clone.find('.form-check-input').prop('id', 'visible-' + btoa(encodeURIComponent(id)));
		clone.find('.form-check-input').prop('name', id);
		clone.find('.form-check-label').prop('htmlFor', 'visible-' + btoa(encodeURIComponent(id)));
		clone.find('.form-check-label').text(id);

		$('#type-list').append(clone);
	}

	/*
	function addNewYear(id) {
		var clone = $('#year-checkbox').contents().clone(true);

		clone.find('.form-check-input').prop('id', 'visible-' + id);
		clone.find('.form-check-input').prop('name', id);
		clone.find('.form-check-label').prop('htmlFor', 'visible-' + id);
		clone.find('.form-check-label').text(id);

		$('#year-list').append(clone);
	}*/

	function updateTypeCheckbox() {
		type_list = [];
		for (const damage of damage_list) {
			if (type_list.indexOf(damage['type']) == -1 && damage['type'] !== '') {
				type_list.push(damage['type']);
			}
		}
		type_list.sort();

		$('#type-list').find('button').remove();
		for (var i = 0; i < type_list.length; i++) {
			addNewType(type_list[i]);	
		}
	}

	/*
	function updateYearCheckbox() {
		year_list = [];
		for (const damage of damage_list) {
			var year = damage['adddate'].getFullYear();
			if (year_list.indexOf(year) == -1) {
				year_list.push(year);
			}
		}
		year_list.sort();

		$('#year-list').find('button').remove();
		for (var i = 0; i < year_list.length; i++) {
			addNewYear(year_list[i]);
		}
	}*/

	//
	// 初期化
	//
	$(window).on('load', function() {
		updateTypeCheckbox();
//		updateYearCheckbox();

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

		updateCanvas(img_x, img_y, img_scale, img_angle);
		enableEditing(selected_damage);

		$('#damageImageUploadProgress').parent().hide();

//		$('#show-date').val(dateToISO(new Date()));
		updateVisibleDamageByDate();
	});
</script>
</body>
</html>