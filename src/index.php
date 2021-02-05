<?php
	session_start();

	require_once 'DSN.php';
	$sql = mysqli_connect($dsn['host'], $dsn['user'], $dsn['pass'], $dsn['db']);

	if (mysqli_connect_errno()) {
		echo mysqli_error($sql);
	}

	$args = array("id", "name", "tag", "comment", "img", "last_update");
	if ($result = mysqli_query($sql, "SELECT * from artwork where `deleted` = false")) {
		$tmp_array = mysqli_fetch_all($result);
		mysqli_free_result($result);
	} else {
		echo mysqli_error($sql);
	}

	$artwork_array = array();

	foreach ($tmp_array as $id1 => $value) {
		foreach ($args as $id2 => $arg) {
			$artwork_array[$id1][$arg] = htmlspecialchars_decode($value[$id2], ENT_QUOTES);
		}
	}
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
	<a type="button" class="btn-lg btn-secondary" href="mail">メール配信</a>
	<br><br>

	<hr>
		<div class="form-group">
			<input type="text" class="form-control" id="tag" placeholder="タグ(コンマ区切り)" onchange="checkNewTag(event);">
			<div id="tag-area"></div><small>クリックでタグを取り除く</small>
		</div>
	<hr>

	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4" id="artwork-cardlist">
	</div>

	<template id="card-template">
		<div class="col mb-4">
			<div class="card h-100">
				<img src="" class="card-img-top artwork-thumbnail">
				<div class="card-body">
					<h5 class="card-title artwork-name">美術品名</h5>
					<a href="" type="button" class="btn-block btn-primary go-manage">管理</a>
					<div class="badge-area justify-content-around"></div>
					<p class="card-text artwork-comment">説明・コメント</p>
					<p class="card-text"><small class="text-muted artwork-last-update"></small></p>
				</div>
			</div>
		</div>
	</template>

</div>

<script type="text/javascript">
	var tagList = [];

	function checkNewTag(event) {
		var tags = event.target.value.trim().replace(/\s+/g, "").split(',');
		for (var i = 0; i < tags.length; i++) {
			addTag(tags[i]);
		}

		event.target.value='';
	}

	function showTagList() {
		$('#tag-area').children().remove();
		for (var i = 0; i < tagList.length; i++) {
			var tag = document.createElement("button");
			tag.className = 'badge badge-pill badge-light text-wrap';
			tag.innerText = tagList[i];
			tag.onclick = function(event) {
				removeTag(event.target.innerText);
			};
			$('#tag-area').append(tag);
		}
	}

	function addTag(tagName) {
		tagName = tagName.replace(/\s+/g, "").trim();
		if (tagList.indexOf(tagName) == -1) {
			tagList.push(tagName);
			showTagList();
			updateItems();
		}
	}

	function removeTag(tagName) {
		var index = tagList.indexOf(tagName);
		if (index >= 0) {
			tagList.splice(index, 1);
		}
		showTagList();
		updateItems();
	}

	function updateItems() {
		var data = <?php echo json_encode($artwork_array); ?>;
		var template = document.getElementById('card-template');

		$('#artwork-cardlist').children().remove();

		for (var i = 0; i < data.length; i++) {
			var clone = template.content.cloneNode(true);

			var tags = data[i].tag.trim().replace(/\s+/g, "").split(',');

			if (tagList.length > 0) {
				var isOK = true;
				for (var j = 0; j < tagList.length; j++) {
					if (tags.indexOf(tagList[j]) < 0) {
						isOK = false;
						break;
					}
				}

				if (!isOK) {
					continue;
				}
			}

			for (var j = 0; j < tags.length; j++) {
				var tag = document.createElement("button");
				tag.className = 'badge badge-pill badge-light text-wrap';
				tag.innerText = tags[j];
				tag.onclick = function(event) {
					addTag(event.target.innerText);
				};
				clone.querySelector('.badge-area').appendChild(tag);
			}

			clone.querySelector('.artwork-thumbnail').src = 'img/artwork/' + data[i].img;
			clone.querySelector('.artwork-name').textContent = data[i].name;
			clone.querySelector('.artwork-comment').textContent = data[i].comment;
			clone.querySelector('.go-manage').href = "./manage/?id=" + data[i].id;
			clone.querySelector('.artwork-last-update').textContent = "Last update: " + data[i].last_update;
			
			$('#artwork-cardlist').append(clone);
		}
	}

	$(document).ready(function() {
		updateItems();
	});
</script>
</body>
</html>