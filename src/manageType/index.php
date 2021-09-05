<?php
	session_start();

	$_SESSION['editmode'] = false;
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

	<style type="text/css">
		.carousel-caption {
			position: absolute;
			top: 80%;
			transform: translateY(-100%);
		}
	</style>
</head>
<body>

<div class="container header">
	<h1>損傷種類管理</h1>
	<br>

	<div class="custom-control custom-switch">
		<input type="checkbox" class="custom-control-input" id="switchEdit" onchange="openEditModeDialog(event);">
		<label class="custom-control-label" for="switchEdit">編集モード</label>
	</div>

	<table class="table table-striped" id="type-table">
		<thead>
			<tr>
				<th scope="col">種類名称</th>
				<th scope="col">色</th>
				<th scope="col">操作</th>
			</tr>
		</thead>

		<div id="alert"></div>

		<template id="alert-success">
			<div class="alert alert-primary alert-dismissible fade show" role="alert">
				正常に適用されました
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

		<tbody id="type-table-body">
		</tbody>

		<template id="type-row">
			<tr>
				<td class="col-4">
					<div hidden class="type-id"></div>
					<input type="text" class="form-control editable-input type-name" readonly 
						oninput="updateType($(event.target).parent().parent())">
				</td>
				<td class="col-4">
					<div class="color-specified">
						<input type="color" class="type-color" onchange="updateType($(event.target).parent().parent().parent())">
						<button class="btn btn-sm btn-secondary editable-button" disabled 
							onclick="colorSpecify(false, $(event.target).parent().parent().parent())">色指定を外す</button>
					</div>
					<div class="color-not-specified" hidden>
						<button class="btn btn-sm btn-secondary editable-button" disabled
							onclick="colorSpecify(true, $(event.target).parent().parent().parent())">色を指定する</button>
					</div>
				</td>
				<td class="col-4">
					<button class="btn btn-sm btn-secondary editable-button" disabled
						onclick="openApplyDialog($(event.target).parent().parent())">適用</button>
					<button class="btn btn-sm btn-secondary editable-button" disabled
						onclick="openDeleteDialog($(event.target).parent().parent())">削除</button>
				</td>
			</tr>
		</template>

	</table>

	<div class="text-center col-lg-12">
		<button class="btn btn-lg btn-primary editable-button" disabled onclick="createDamageType()">新しい種類を追加</button>
		<a type="button" class="btn btn-lg btn-secondary" href="../">一覧に戻る</a>
	</div>
</div>


<!-- ダイアログ -->
<div class="modal fade" id="editmode-dialog" tabindex="-1" role="dialog" aria-labelledby="label_editmode" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_editmode">編集モード</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#switchEdit').prop('checked', false);">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				パスワードを入力してください<br>
				<input type="password" id="password" class="form-control">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="switchEditMode()" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="apply-type-dialog" tabindex="-1" role="dialog" aria-labelledby="label_apply_type" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_apply_type">この種類のルールを適用</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				本当にこの種類のルールを全ての損傷に適用しますか？
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
				<button type="button" class="btn btn-warning" id="apply-type-button" data-dismiss="modal">はい</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="delete-type-dialog" tabindex="-1" role="dialog" aria-labelledby="label_delete_type" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="label_delete_type">この種類を削除</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				本当にこの種類を削除しますか？
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
				<button type="button" class="btn btn-warning" id="delete-type-button" data-dismiss="modal">はい</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function successAlert() {
		let template = $('#alert-success').contents();
		let clone = template.clone();
		$('#alert').append(clone);
	}

	function failAlert(msg) {
		let template = $('#alert-fail').contents();
		let clone = template.clone();
		clone.find('.fail-msg').html(msg);
		$('#alert').append(clone);
	}

	function updateTable() {
		$.ajax({
			type: "GET",
			url: './fetchType.php',
			dataType: 'json',
		}).done(function(data){
			let type_list_global = data['data'];

			$('#type-table-body').children().remove();

			let template = $('#type-row').contents();
			for (const damage_type of type_list_global) {
				let clone = template.clone();

				clone.find('.type-id').text(damage_type['id']);
				clone.find('.type-name').val(damage_type['name']);
				if (damage_type['color']) {
					clone.find('.type-color').val(damage_type['color']);
					clone.find('.color-specified').attr('hidden', false);
					clone.find('.color-not-specified').attr('hidden', true);
				} else {
					clone.find('.color-specified').attr('hidden', true);
					clone.find('.color-not-specified').attr('hidden', false);
				}

				$('#type-table-body').append(clone);
			}
		}).fail(function(data){
			console.log(data);
		});
	}

	function colorSpecify(color_enabled, row) {
		if (color_enabled) {
			row.find('.color-specified').attr('hidden', false);
			row.find('.color-not-specified').attr('hidden', true);
		} else {
			row.find('.color-specified').attr('hidden', true);
			row.find('.color-not-specified').attr('hidden', false);
		}
		updateType(row);
	}

	function createDamageType() {
		$.ajax({
			type: "GET",
			url: './createType.php',
			dataType: 'json',
		}).done(function(data){
			let template = $('#type-row').contents();
			let clone = template.clone();

			if (data['result']) {
				clone.find('.type-id').text(data['result']['id']);
				clone.find('.type-name').val(data['result']['name']);
				if (data['result']['color']) {
					clone.find('.type-color').val(data['result']['color']);
					clone.find('.color-specified').attr('hidden', false);
					clone.find('.color-not-specified').attr('hidden', true);
				} else {
					clone.find('.color-specified').attr('hidden', true);
					clone.find('.color-not-specified').attr('hidden', false);
				}

				$('#type-table-body').append(clone);
			}

			enableEdit(true);
		}).fail(function(data){
			console.log(data);
		});
	}

	function updateType(row) {
		let data = { 
			'id': row.find('.type-id').text(), 
			'name': row.find('.type-name').val(),
		};

		if (!row.find('.color-specified').attr('hidden')) {
			data['color'] = row.find('.type-color').val();
		}

		$.ajax({
			type: "POST",
			url: './updateType.php',
			dataType: 'json',
			data: data,
		}).done(function(data){
//			console.log(data);
//			location.reload(true);
//			updateTable();
		}).fail(function(data){
			console.log(data);
		});
	}

	function applyType(row) {
		let data = {
			'id': row.find('.type-id').text(), 
		};

		$.ajax({
			type: "POST",
			url: './applyType.php',
			dataType: 'json',
			data: data,
		}).done(function(data){
			successAlert();
		}).fail(function(data){
			console.log(data);
			failAlert('ルールの適用に失敗しました。コンソールを確認してください');
		});
	}

	function deleteType(row) {
		let data = { 
			'id': row.find('.type-id').text(), 
		};

		$.ajax({
			type: "POST",
			url: './deleteType.php',
			dataType: 'json',
			data: data,
		}).done(function(data){
			row.remove();
		}).fail(function(data){
			console.log(data);
		});
	}

	function openApplyDialog(row) {
		$('#apply-type-button').on('click', function() {
			applyType(row);
		});
		$('#apply-type-dialog').modal('show');
	}

	function openDeleteDialog(row) {
		$('#delete-type-button').on('click', function() {
			deleteType(row);
		});
		$('#delete-type-dialog').modal('show');
	}

	function openEditModeDialog(e) {
		if (e.target.checked) {
			$('#editmode-dialog').modal('show');
		} else {
			switchEditMode();
		}
	}

	function enableEdit(enabled) {
		if (enabled) {
			$('.editable-button').attr('disabled', false);
			$('.editable-input').attr('readonly', false);
		} else {
			$('.editable-button').attr('disabled', true);
			$('.editable-input').attr('readonly', true);
		}
	}

	function switchEditMode() {
		if ($('#switchEdit').prop('checked')) {
			pass = $('#password').val();
			$('#password').val('');
			$.ajax({
				type: "POST",
				url: '../manage/changeEditMode.php',
				dataType: 'json',
				data: {'editmode': true, 'pass': pass},
			}).done(function (data, textStatus, xhr) {
				if (data['result']) {
					enableEdit(true);
				} else {
					alert('パスワードが違います');
					$('#switchEdit').prop('checked', false);
				}
			});
		} else {
			$.ajax({
				type: "POST",
				url: '../manage/changeEditMode.php',
				dataType: 'json',
				data: {'editmode': false},
			}).done(function (data, textStatus, xhr) {
				enableEdit(false);
			});
		}
	}

	//
	// 初期化
	//
	$(window).on('load', function() {
		updateTable();

		$('#editmode-dialog').on('shown.bs.modal', function () {
			$('#password').focus();
		});
	});
</script>
</body>
</html>