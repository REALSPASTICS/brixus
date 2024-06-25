<?php
include 'site/config.php';

if($loggedIn) {
	if(isset($_POST['token']) && isset($_POST['hash']) && isset($_POST['avatarData'])) {
		if($_POST['token'] == $_SESSION['SAVE_ITEM_THUMB_TOKEN']) {
			$cleanAvatarData = mysqli_escape_string($conn, $_POST['avatarData']);
			$cleanHash = mysqli_escape_string($conn, $_POST['hash']);
			mysqli_query($conn, "UPDATE `items` SET `thumbnail` = '$cleanAvatarData' WHERE `hash` = '$cleanHash'");
			exit;
		}
	}
}
?>