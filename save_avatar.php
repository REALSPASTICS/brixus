<?php
include 'site/config.php';

if($loggedIn) {
	if(isset($_POST['token']) && isset($_POST['avatarData']) && isset($_POST["renderID"])) {
		if($_POST['token'] == $_SESSION['SAVE_AVATAR_TOKEN']) {
			$cleanAvatarData = mysqli_escape_string($conn, $_POST['avatarData']);
			mysqli_query($conn, "UPDATE `users` SET `avatar` = '$cleanAvatarData' WHERE `id` = ".intval($_POST["renderID"]));
			exit;
		}
	}
}
?>