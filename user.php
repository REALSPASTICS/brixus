<?php
include 'site/config.php';

if (!isset($_GET['id'])) {
    exit;
}

$userId = intval($_GET['id']);

$userQuery = mysqli_query($conn, "SELECT `username`, `avatar`, `blurb`, `join_date`, `last_online`, `power`,`InventoryVisible` FROM `users` WHERE `id` = {$userId}");

$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    $title = "Error - Brixus";
} else {

$joined = date('Y-m-d', strtotime($user['join_date']));

if($loggedIn) {
	$isAdminQuery = mysqli_query($conn, "SELECT 1 FROM `users` WHERE `id` = {$_SESSION['userId']} AND  (`isAdmin` = 1 OR `is_moderator` = 1)");
	$isAdmin = mysqli_num_rows($isAdminQuery) > 0;
} else {
	$isAdmin = false;
}

if($isAdmin) {
	$powerQuery = mysqli_query($conn, "SELECT `power` FROM `users` WHERE `id` = {$_SESSION['userId']}");
	$ownPower = mysqli_fetch_assoc($powerQuery)['power'];
}

if($isAdmin && $ownPower > $user['power']) {
	if(isset($_GET['purgeblurb'])) {
		mysqli_query($conn, "UPDATE `users` SET `blurb` = '[Content Removed]' WHERE `id` = $userId");
		mysqli_query($conn, "INSERT INTO `admin_logs` VALUES({$_SESSION['userId']}, 'purged user $userId blurb from ".mysqli_escape_string($conn, $user['blurb'])."', CURRENT_TIME)");
		header('Location: /user?id='.$userId);
		exit;
	}
	
	if(isset($_GET['purgename'])) {
		mysqli_query($conn, "UPDATE `users` SET `username` = '[Deleted $userId]' WHERE `id` = $userId");
		mysqli_query($conn, "INSERT INTO `admin_logs` VALUES({$_SESSION['userId']}, 'purged user $userId username from ".mysqli_escape_string($conn, $user['username'])."', CURRENT_TIME)");
		header('Location: /user?id='.$userId);
		exit;
	}
}

$get_punishments = mysqli_query($conn, "SELECT 1 FROM `moderation` WHERE `isSuspended` = 1 AND `isActive` = 1 AND `userId` = $userId");
$num_pun = mysqli_num_rows($get_punishments);

$title = $user['username'];
$title .= ' - Brixus';

	$description = htmlspecialchars($user["blurb"],ENT_QUOTES);
	$image = "https://alpha.brixus.net/meta_user_img?userID=".intval($_GET["id"]);
	
	$userID=$userId;
}

if($_SESSION["userId"] == (int)$_GET["id"] && isset($_GET["personal_view"])) {$submenuenabled = true;}

include 'site/header.php';
?>
<style></style>
<?php
if(!$user){
?>
		<div class="error">The requested user does not exist</div>
<?php
}
if($num_pun>0){
?>
		<div class="error">The requested user's profile cannot be viewed</div>
<?php
}
?>
<?php
if($isAdmin || (!$isAdmin && $user && $num_pun < 1)){
?>
        <style>
            .online {
                color: #0a0;
            }

            .offline {
                color: red;
            }
        </style>
		<style>
		.memPane {
			border:1px solid #000;
			border-width: 0 0 1px 0;
			padding: 5px;
			background:#FFF;
		}
		
		.memPane:hover {
			background:#DDD;
		}
		
		.memImg,.memText {
			vertical-align:middle;
		}
		</style>
		<?php
		//friends
		$friendsQuery = mysqli_query($conn, "SELECT 1 FROM `friends` WHERE (`recipientId` = $userId OR `senderId` = $userId) AND `isActive` = 1");
		$friendsNum = mysqli_num_rows($friendsQuery);
		
		//num threads
		$postQuery = mysqli_query($conn, "SELECT 1 FROM `forum_threads` WHERE `poster_id` = $userId");
		$forumPosts = mysqli_num_rows($postQuery);
		
		//num replies
		
		$findreplies=mysqli_query($conn,"SELECT * FROM `forum_replies` WHERE `poster_id`=$userId");
		$replies=mysqli_num_rows($findreplies);
		
		//num posts
		$forumposts=$forumPosts+$replies;
		?>
        <div style="float:left;width:445px;">
			<div class="box" style="padding:10px;">
            <div style="text-align:center;">
                <div style="padding-bottom:5px;padding-left:0;padding-right:0;padding-top:0;margin-top:-5px;font-size:20px;"><?php echo $user['username']; ?></div>
				<?php if($isAdmin && $ownPower > $user['power']) {echo '<span class="padded"><a href="/user?id='.$userId.'&purgename" class="label">Purge</a></span><br>';} ?>
<?php
if (time() - strtotime($user['last_online']) > 900) {
?>
                <span class="offline">Offline</span>
<?php
} else {
?>
                <span class="online">Online</span>
<?php
}
?>
				<div style="margin-top:10px;text-align:center;">
					<span><a href="https://www.brixus.net/user?id=<?=$userID?>">https://www.brixus.net/user?id=<?=$userID?></a></span>
				</div>
            </div>
            <div style="clear:both;height:10px"></div>
			<center><div style="width:50%;display:table-cell;vertical-align:middle;">
					<img src="<?php echo $user['avatar']; ?>" title="<?php echo $user['username']; ?>" alt="<?php echo $user['username']; ?>" width="100%">
				</div><div style="width:50%;display:table-cell;vertical-align:middle;font-size:12px;">
					<style>
					.profile_view_link { margin: 10px auto; }
					</style>
					<?php
					if ($userId == $_SESSION['userId'] && isset($_GET["personal_view"])) {
					?>
					<div class="profile_view_link"><a href="/messages/">Inbox</a></div>
					<div class="profile_view_link"><a href="/avatar/">Change Avatar</a></div>
					<div class="profile_view_link"><a href="/settings/">Edit Settings</a></div>
					<div class="profile_view_link"><a href="/user?id=<?=$userID?>">View Public Profile</a></div>
					<div class="profile_view_link"><a href="/create_place">Create New Place</a></div>
					<div class="profile_view_link"><a href="/currency/">Currency</a></div>
					<div class="profile_view_link"><a href="/upgrade">Upgrades</a></div>
					<div class="profile_view_link"><a href="/invite">Invite</a></div>
					<?php
					} else {
						if($userId != $_SESSION["userId"]) {
					?>
					<?php
					$isFriendRequestPendingToViewingUserQuery = mysqli_query($conn, "SELECT 1 FROM `friendRequests` WHERE `recipientId` = {$_SESSION['userId']} AND `senderId` = $userId AND `isInvalid` = 0");
					$isFriendRequestPendingToViewingUser = mysqli_num_rows($isFriendRequestPendingToViewingUserQuery) != 0;
					
					$isFriendRequestPendingToProfileUserQuery = mysqli_query($conn, "SELECT 1 FROM `friendRequests` WHERE `recipientId` = $userId AND `senderId` = {$_SESSION['userId']} AND `isInvalid` = 0");
					$isFriendRequestPendingToProfileUser = mysqli_num_rows($isFriendRequestPendingToProfileUserQuery) != 0;
					
					$areFriendsQuery = mysqli_query($conn, "SELECT 1 FROM `friends` WHERE `isActive` = 1 AND ((`recipientId` = $userId AND `senderId` = {$_SESSION['userId']}) OR (`recipientId` = {$_SESSION['userId']} AND `senderId` = $userId))");
					$areFriends = mysqli_num_rows($areFriendsQuery) != 0;
					
					
					if (!$isFriendRequestPendingToViewingUser && !$isFriendRequestPendingToProfileUser) {
						if (!$areFriends) {
					?>
					<div class="profile_view_link"><a href="/friends/request?id=<?php echo $userId; ?>">Add Friend</a></div>
					<?php
						} else {
					?>
					<div class="profile_view_link"><a href="/friends/">Remove Friend</a></div>
					<?php
						}
					}
					
					
					?>
					<?php
					if ($isFriendRequestPendingToViewingUser) {
					?>
					<div class="profile_view_link"><a href="/friends/">Accept Friend Request</a></div>
					<div class="profile_view_link"><a href="/friends/">Decline Friend Request</a></div>
					<?php
					}
					?>
					<?php
					if ($isFriendRequestPendingToProfileUser) {
					?>
					<div class="profile_view_link"><a href="/friends/cancel_request?id=<?php echo $userId; ?>">Cancel Friend Request</a></div>
					<?php
					}
					?>
					<div class="profile_view_link"><a href="/messages/compose?id=<?php echo $userId; ?>">Send Message</a></div>
					<div class="profile_view_link"><a href="/currency/gift?username=<?=$user['username']?>">Gift Currency</a></div>
					<?php
					}
					?>
					<div style="text-align:center;margin:10px auto;">
					<?php echo nl2br(htmlentities($user['blurb']), false); ?>
					<?php
					if($isAdmin && $ownPower > $user['power']) {echo '<div><a href="/user?id='.$userId.'&purgeblurb" class="label">Purge</a></div>';}
					?>
					</div>
					<?php
					  
					}
					?>
				</div></center>
			 <div style="clear:both;height:10px"></div>
			<div style="text-align:center;">
<?php
if ($loggedIn) {
    if ($userId != $_SESSION['userId']) {
?>
				
				<div style="margin-top:11px">
					<?php
					if($isAdmin) {
						echo '<span class="padded"><a href="/admin/user?id='.$userId.'" class="label">Admin View</a></span>';
						if($ownPower > $user['power']) {echo '
							<span class="padded"><a href="/admin/suspend?id='.$userId.'" class="label">Suspend</a></span>
							<span class="padded"><a href="/admin/ban?id='.$userId.'" class="label">Ban</a></span>
						<span class="padded"><a href="/admin/warn?id='.$userId.'" class="label">Warn</a></span>
							<span class="padded"><a href="/testrender?id='.$userId.'" class="label">Render</a></span>';}
						echo ' ';
					}
                    if($loggedIn) {echo '<span class="padded"><a href="/report/?userId='.$userId.'" class="label">Report</a></span>';}
					?>
                </div>
<?php
    } else {
?>
<?php
    }
}
?>
            </div>
        </div>
		<?php
			$shitSQL = "SELECT * FROM `membership` WHERE `user_id`=".$userId." AND `is_active`='yes'";
			$findMemberships = mysqli_query($conn, $shitSQL);
			$allMems = NULL;
			$numMems = mysqli_num_rows($findMemberships);
			if($numMems > 0) {
				echo "<div class='box' style='margin-top:10px'>
				<h4 style='border-bottom:1px solid #000;'>Membership:</h4>";
			}
			while($memRow = mysqli_fetch_assoc($findMemberships)) {
				echo '<div class="memPane">
					<img src="/assets/badges/prem.png" height="14" class="memImg">
					<span class="memText" style="color:'.$_GeneralSettings["MemColors"][$memRow["tier"]].'">'.$_GeneralSettings["MemTiers"][$memRow["tier"]].' Membership until '.$memRow["until"].'</span>
				</div>';
			}
			if($numMems > 0) {
				echo "</div>";
			}
		?>
		<div style="height:10px"></div>
		<div style="width:445px;float:left">
            <div class="box">
                <div style="text-align:center" class="heading">Featured Place</div>
				<div style="padding:5px">
					<?php
					if($userId == $_SESSION['userId'] && isset($_GET["personal_view"])) {
						echo '<div style="text-align:center">You don\'t have a featured place</div>';
					} else {
						echo '<div style="text-align:center">'.$user['username'].' doesn\'t have a featured place</div>';
					}
					?>
				</div>
            </div>
		</div>
        </div>
        <div style="float:right;width:445px;">
			<div class="box" style="text-align:center">
                <div style="text-align:center" class="heading">Statistics</div>
                <div style="padding:20px">
                    <div style="margin:2px">
                        <span style="font-weight:bold">Place Visits:</span>
                        <span>0</span>
                    </div>
                    <div style="margin:2px">
                        <span style="font-weight:bold">Friends:</span>
                        <span><?php echo number_format($friendsNum); ?></span>
                    </div>
                    <div style="margin:2px">
                        <span style="font-weight:bold">Forum Posts:</span>
                        <span><?php echo number_format($forumposts); ?></span>
                    </div>
                    <div style="margin:2px">
                        <span style="font-weight:bold">Joined:</span>
                        <span><?php echo $joined; ?></span>
                    </div>
                </div>
            </div>
			<div style="height:10px"></div>
			<div class="box">
                <div style="text-align:center" class="heading">Badges</div>
                <div style="padding:5px;width:433px"><center>
                    <?php
					$badgeQuery = mysqli_query($conn, "SELECT * FROM `badge` WHERE `userId` = $userId");
					while($badge = mysqli_fetch_assoc($badgeQuery)) {
						$badgeInfoQuery = mysqli_query($conn, "SELECT * FROM `badgeInfo` WHERE `id` = {$badge['badgeId']}");
						$badgeInfo = mysqli_fetch_assoc($badgeInfoQuery);
						
						echo '<div style="display:inline-block;width:119px;text-align:center;padding:5px">
                        <a href="/badges.php"><img src="data:image/png;base64,'.base64_encode(file_get_contents($badgeInfo['image'])).'" title="'.$badgeInfo['name'].'" alt="'.$badgeInfo['name'].'" style="border:1px solid #000;background:#FFF;display:block;width:117px"></a>
                        <div style="height:10px"></div>
                        <a href="/badges.php">'.$badgeInfo['name'].'</a>
                    </div>';
					}
					
					if(mysqli_num_rows($badgeQuery) == 0) {
						if($userID == $_SESSION["userId"] && isset($_GET["personal_view"])) {echo '<div style="text-align:center">You don\'t have any badges</div>';}
						if($userID != $_SESSION["userId"]) {echo '<div style="text-align:center">'.$user['username'].' doesn\'t have any badges</div>';}
					}
					?>
                </center></div>
            </div>
			<div class="box" style="margin:10px 0 0 0">
                <div style="text-align:center" class="heading">Friends</div>
				<div style="padding:5px;width:433px;">
					<?php
					$friendQuery = mysqli_query($conn, "SELECT `senderId`, `recipientId` FROM `friends` WHERE `isActive` = 1 AND (`recipientId` = $userId OR `senderId` = $userId) ORDER BY `id` DESC LIMIT 8");
					while($friend = mysqli_fetch_assoc($friendQuery)) {
						if($friend['recipientId'] == $userId) {$friendRelId = $friend['senderId'];}
						if($friend['senderId'] == $userId) {$friendRelId = $friend['recipientId'];}
						$friendDetailsQuery = mysqli_query($conn, "SELECT `username`, `avatar`, `id` FROM `users` WHERE `id` = $friendRelId");
						$friendDetails = mysqli_fetch_assoc($friendDetailsQuery);
						echo '<div style="display:inline-block;width:85px;text-align:center;padding:10px;vertical-align:top;">
                        <a href="/user?id='.$friendRelId.'"><img src="'.$friendDetails['avatar'].'" title="'.$friendDetails['username'].'" alt="'.$friendDetails['username'].'" style="display:block;width:85px"></a>
                        <div style="height:10px"></div>
                        <a href="/user?id='.$friendRelId.'">'.$friendDetails['username'].'</a>
                    </div>';
					}
					?>
					<?php
if(mysqli_num_rows($friendQuery) > 0) {echo '<center><a href="/friends/?id='.$userId.'"><input type="button" value="View All"></a></center>';} else {
	if($userId == $_SESSION["userId"] && isset($_GET["personal_view"])) {echo '<div style="text-align:center">You don\'t have any friends</div>';}
	else {echo '<div style="text-align:center">'.$user['username'].' doesn\'t have any friends</div>';}
}
				?>
				</div>
            </div>
        </div>
		<div style="clear:both;height:10px"></div>
		<div class="box">
		<?php
		if($user["InventoryVisible"] == 'no') {
			?>
			<div style="padding:10px 0 0 0;text-align:center;">This user's inventory is private</div>
			<?php
		} else {
		?>
			<h4 style="text-align:center;">Inventory</h4>
				<center style="margin-bottom:10px">
					<input type="button" style="width:10%;max-width:100%;padding:5px;" onclick="loadInventory(0,1)" value="All">
					<input type="button" style="width:10%;max-width:100%;padding:5px;" onclick="loadInventory(1,1)" value="Shirts">
					<input type="button" style="width:10%;max-width:100%;padding:5px;" onclick="loadInventory(2,1)" value="Faces">
					<input type="button" style="width:10%;max-width:100%;padding:5px;" onclick="loadInventory(3,1)" value="Hats">
				</center>
				<div style="width:688px;margin:auto;">
					<center>
						<div id="inventory">
						</div>
					</center>
				</div>
				<div style="width:798px;clear:both"></div>
			</div>
			<?php
		if($user["InventoryVisible"] == 'yes') {
			?>
			<script type="text/javascript">
			function loadInventory(type, page) {
				$('#inventory').load('/profile_inventory?id='+<?php echo $userId; ?>+'&type='+type+'&page='+page);
			}
			
			body.onload = loadInventory(0, 1);
			</script>
			<?php
		}
		?>
		<?php
		}
		?>
		</div>
<?php
}
?>
<?php
include 'site/footer.php';
?>