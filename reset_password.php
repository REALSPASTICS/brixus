<?php
include 'site/config.php';

if(!isset($_GET['token'])) {exit;}

$token = mysqli_escape_string($conn, $_GET['token']);

$tokenQuery = mysqli_query($conn, "SELECT * FROM `password_reset_tokens` WHERE `token` = '$token' AND `isUsed` = 0");
if(mysqli_num_rows($tokenQuery) == 0) {exit;}
$tokenAssoc = mysqli_fetch_assoc($tokenQuery);

if(isset($_POST['submit'])) {
	$newPassword = mysqli_escape_string($conn, $_POST['new_password']);
	$confirmPassword = mysqli_escape_string($conn, $_POST['confirm_password']);
	
	if($_POST['new_password'] != $_POST['confirm_password']) {$error = 'Passwords do not match!';}
	
	if(!isset($error)) {
		mysqli_query($conn, "UPDATE `password_reset_tokens` SET `isUsed` = 1 WHERE `token` = '$token'");
		$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
		mysqli_query($conn, "UPDATE `users` SET `password` = '$hashed' WHERE `id` = {$tokenAssoc['userId']}");
		header('Location: /');
		exit;
	}
	
}

$title = 'Reset Password - Brixus';

include 'site/header.php';
?>
		<div class="box">
			<h4>Reset Password</h4>
			<?php
			if(isset($error)) {echo '<span style="color:red">'.$error.'</span>';}
			if(isset($success)) {echo '<span style="color:green">'.$success.'</span>';}
			?>
			<div class="padded">
				<form method="POST">
					<input type="password" name="new_password" placeholder="New Password">
					<br>
					<input type="password" name="confirm_password" style="margin-top:4px" placeholder="Confirm Password">
					<br>
					<input type="submit" name="submit" style="margin-top:4px">
				</form>
			</div>
		</div>
<?php
include 'site/footer.php';
?>