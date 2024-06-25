<?php
include 'site/config.php';

if(!$loggedIn) {
	header("Location: /login/");
	exit;
}

$moderationQuery = mysqli_query($conn, "SELECT * FROM `moderation` WHERE `userId` = {$_SESSION['userId']} AND `isActive` = 1 ORDER BY `id` DESC LIMIT 1");
if(mysqli_num_rows($moderationQuery) == 0) {exit;}
$moderation = mysqli_fetch_assoc($moderationQuery);

if(!$moderation) {exit;}

if(isset($_POST['reactivate'])) { echo "ya"; }

if($moderation['isBanned'] || $moderation['isWarned']) {
	if(strtotime($moderation['until']) < time()) {
		if(isset($_POST['reactivate'])) {
			mysqli_query($conn, "UPDATE `moderation` SET `isActive` = 0 WHERE `id` = ".intval($moderation["id"]));
			header('Location: /');
			exit;
		}
	}
}

$title = 'Account Status - Brixus';

$ismodpage = true;

include 'site/header.php';
?>
		<div class="box">
			<h3><?php if($moderation['isBanned']) {echo 'Banned';} if($moderation['isWarned']) {echo 'Warning';} if($moderation['isSuspended']) {echo 'Suspension';} ?></h3>
			<?php
			if($moderation['isSuspended']) {echo '<h5>Your account has been suspended for violating our Terms of Service.</h5>';}
			if($moderation['isBanned']) {echo '<h5>Your account has been banned for violating our Terms of Service.</h5>';}
			if($moderation['isWarned']) {echo '<h5>Your account has been warned for violating our Terms of Service.</h5>';}
			?>
			<div class="padded" style="margin-top:10px">
				<span>Moderator Note:</span>
				<div style="background:#FFF;border:1px solid #000;margin-bottom:10px">
					<?php
					echo nl2br($moderation['modNote']);
					?>
				</div>
				<?php
				if($moderation['isBanned']) {echo '<div>You may reactivate your account on '.date('Y/m/d',strtotime($moderation['until'])).'.</div>';}
				
				if($moderation['isBanned'] || $moderation['isWarned']) {
					if(strtotime($moderation['until']) < time()) {
						echo '<div>Your account may be reactivated.</div>
						<form method="POST" action="account_status.php" style="margin-top:5px">
							<input type="submit" name="reactivate" value="Reactivate">
						</form>';
					}
				}
				?>
			</div>
		</div>
<?php
include 'site/footer.php';
?>