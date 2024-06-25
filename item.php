<?php
include 'site/config.php';

if (isset($_GET['id'])) {
	$itemId = mysqli_escape_string($conn, intval($_GET['id']));
	
		$itemQuery = mysqli_query($conn, "SELECT `title`, `description`, `created`, `updated`, `creatorId`, `thumbnail`, `type`, `sales`, `isOnSale`, `coins`, `bucks`, `isApproved`, `hash`, `is_pending_moderation`, `is_limited`,`is_limitede`,`limitede_stock`,`limitede_initial_stock`,`current_serial` FROM `items` WHERE `id` = $itemId");
	$item = mysqli_fetch_assoc($itemQuery);
	
	if(mysqli_num_rows($itemQuery) == 0) {
		exit(header("location: /shop.php"));
	}
	
	if($loggedIn) {
		$isAdminQuery = mysqli_query($conn, "SELECT 1 FROM `users` WHERE `id` = {$_SESSION['userId']} AND `isAdmin` = 1");
		$isAdmin = mysqli_num_rows($isAdminQuery) == 1;
	} else {$isAdmin = false;}
	
	if($isAdmin && isset($_GET["purgecomment"])) {
		$purgeCommentID = intval($_GET["purgecomment"]);
		mysqli_query($conn, "UPDATE `item_comments` SET `text`='[Content Removed]' WHERE `id`=$purgeCommentID");
		header("Location: /item?id=$itemId");
	}
	
	if((!$item['isApproved'] && (!$isAdmin))) {
		die();
	}
	
	if (!$item || ((!$item['isApproved'] && (!$isAdmin && ($loggedIn && $item['creatorId'] != $_SESSION['userId']))))) {
		//exit;
	}
	
	if ($loggedIn) {
		$ownedQuery = mysqli_query($conn, "SELECT 1 FROM `inventory` WHERE `itemId` = {$itemId} AND `userId` = {$_SESSION['userId']}");
		$isItemOwned = mysqli_num_rows($ownedQuery) != 0;
	}
	
	if($isAdmin) {
		if(isset($_POST['purge'])) {
			mysqli_query($conn, "INSERT INTO `admin_logs` (`userId`, `summary`, `timestamp`) VALUES ({$_SESSION['userId']}, 'purged item $itemId (ORIGINAL TITLE: {$item['title']}, ORIGINAL DESCRIPTION: {$item['description']})', CURRENT_TIME)");
			mysqli_query($conn, "UPDATE `items` SET `title` = '[Content Removed]', `description` = '[Content Removed]', `thumbnail` = '/storage/items/thumbnails/default.png', `creatorId`=0, `bucks`=0,`coins`=0,`isOnSale`=0 WHERE `id` = $itemId");
			header('Location: /item?id='.$itemId);
			exit;
		}
		
		if(isset($_POST['cancel_purge'])) {
			header('Location: /item?id='.$itemId);
			exit;
		}
	}
	
	if ($loggedIn && $item['isOnSale'] && !$isItemOwned) {
		if (isset($_POST['free']) && ($item['coins'] == 0 && $item['bucks'] == 0)) {
			mysqli_query($conn,"UPDATE `items` SET `current_serial`=`current_serial` + 1 WHERE `id`=$itemId");
				
				$serial = $item["current_serial"] + 1;
				
				if($item["is_limitede"] == 'yes') {
					mysqli_query($conn,"UPDATE `items` SET `limitede_stock` = `limitede_stock` - 1 WHERE `id`=$itemId");
				}
				
			mysqli_query($conn, "UPDATE `items` SET `sales` = `sales` + 1 WHERE `id` = $itemId");
			mysqli_query($conn, "INSERT INTO `transactions` (`itemId`, `currency`, `userId`, `ts`) VALUES ($itemId, 'Free', {$_SESSION['userId']}, CURRENT_TIME)");
			mysqli_query($conn, "INSERT INTO `inventory` (`itemId`, `userId`, `transactionId`, `SerialNum`) VALUES ($itemId, {$_SESSION['userId']}, 0, $serial)");
			
			header("Location: /item.php?id=$itemId&msg=1");
			exit;
		}
		
		if (isset($_POST['coins']) && $item['coins'] > 0) {
			$coinsQuery = mysqli_query($conn, "SELECT `coins` FROM `users` WHERE `id` = {$_SESSION['userId']}");
			$coins = mysqli_fetch_assoc($coinsQuery)['coins'];
			
			if ($coins >= $item['coins']) {
				mysqli_query($conn,"UPDATE `items` SET `current_serial`=`current_serial` + 1 WHERE `id`=$itemId");
				
				$serial = $item["current_serial"] + 1;
				
				if($item["is_limitede"] == 'yes') {
					mysqli_query($conn,"UPDATE `items` SET `limitede_stock` = `limitede_stock` - 1 WHERE `id`=$itemId");
				}
				
				mysqli_query($conn, "UPDATE `users` SET `coins` = `coins` - {$item['coins']} WHERE `id` = {$_SESSION['userId']}");
				mysqli_query($conn, "UPDATE `items` SET `sales` = `sales` + 1 WHERE `id` = $itemId");
				mysqli_query($conn, "INSERT INTO `transactions` (`itemId`, `currency`, `value`, `userId`, `ts`) VALUES ($itemId, 'Coins', {$item['coins']}, {$_SESSION['userId']}, CURRENT_TIME)");
				mysqli_query($conn, "INSERT INTO `inventory` (`itemId`, `userId`, `transactionId`, `serialNum`) VALUES ($itemId, {$_SESSION['userId']}, ".mysqli_insert_id($conn).", $serial)");
				mysqli_query($conn, "UPDATE `users` SET `coins` = `coins` + " . ceil($item['coins'] * .8) . " WHERE `id` = {$item['creatorId']}");
				
				header("Location: /item.php?id=$itemId&msg=1");
				exit;
			} else {
				header("Location: /item.php?id=$itemId&msg=2");
				exit;
			}
		}
		
		if (isset($_POST['bucks']) && $item['bucks'] > 0) {
			$bucksQuery = mysqli_query($conn, "SELECT `bucks` FROM `users` WHERE `id` = {$_SESSION['userId']}");
			$bucks = mysqli_fetch_assoc($bucksQuery)['bucks'];
			
			if ($bucks >= $item['bucks']) {
				mysqli_query($conn,"UPDATE `items` SET `current_serial`=`current_serial` + 1 WHERE `id`=$itemId");
				
				$serial = $item["current_serial"] + 1;
				
				if($item["is_limitede"] == 'yes') {
					mysqli_query($conn,"UPDATE `items` SET `limitede_stock` = `limitede_stock` - 1 WHERE `id`=$itemId ");
				}
				
				mysqli_query($conn, "UPDATE `users` SET `bucks` = `bucks` - {$item['bucks']} WHERE `id` = {$_SESSION['userId']}");
				mysqli_query($conn, "UPDATE `items` SET `sales` = `sales` + 1 WHERE `id` = $itemId");
				mysqli_query($conn, "INSERT INTO `transactions` (`itemId`, `currency`, `value`, `userId`, `ts`) VALUES ($itemId, 'Bucks', {$item['bucks']}, {$_SESSION['userId']}, CURRENT_TIME)");
				mysqli_query($conn, "INSERT INTO `inventory` (`itemId`, `userId`, `transactionId`, `serialNum`) VALUES ($itemId, {$_SESSION['userId']}, ".mysqli_insert_id($conn).", $serial)");
				mysqli_query($conn, "UPDATE `users` SET `bucks` = `bucks` + " . ceil($item['bucks'] * .8) . " WHERE `id` = {$item['creatorId']}");
				
				header("Location: /item.php?id=$itemId&msg=1");
				exit;
			} else {
				header("Location: /item.php?id=$itemId&msg=2");
				exit;
			}
		}
	}	else {
		if (isset($_POST['bucks']) || isset($_POST['coins']) || isset($_POST['free'])) {
			if ($isItemOwned) {
				header('Location: /item?id=' . $itemId . '&msg=6');
				exit;
			}
		}
	}
	
	if (isset($_POST['bucks']) || isset($_POST['coins']) || isset($_POST['free'])) {
		header('Location: /login/');
		exit;
	}
	
	if($loggedIn) {
		if($_POST["add_comment"] && $_POST["comment"]) {
			$LatestCommentStmt = mysqli_query($conn,"SELECT * FROM `item_comments` WHERE `poster_id` = ".intval($_SESSION["userId"])." AND `item_id`=".$itemId." ORDER BY `id` DESC LIMIT 1");
			$latestCommentRow = mysqli_fetch_assoc($LatestCommentStmt);
			
			$NOWDT = new DateTime();
			$LCRDT = new DateTime($latestCommentRow["post_timestamp"]);
		
			if(mysqli_num_rows($latestCommentStmt) == 0 || $NOWDT->getTimestamp() - $LCRDT->getTimestamp() > 60) {
			
			
				if(trim($_POST["comment"]) == "") {
					header("Location: /item?id=$itemId&msg=8"); exit;
				} else {
					mysqli_query($conn,"INSERT INTO `item_comments` VALUES (NULL,'".mysqli_escape_string($conn,$_POST["comment"])."',".intval($_SESSION["userId"]).",CURRENT_TIME,0,".intval($_GET["id"]).")");
					header("Location: /item?id=$itemId&msg=7");
					exit;
				}
			} else {
				exit(header("Location: /item?id=$itemId&cwait"));
			}
		}
	}
	
		
	$itemTypes = [
		1 => 'Shirt',
		2 => 'Face',
		3 => "Hat"
	];
	
	$created = date('Y-m-d H:i:s', strtotime($item['created']));
	
	$updated = date('Y-m-d H:i:s', strtotime($item['updated']));
	
	
	$title = htmlspecialchars($item["title"],ENT_QUOTES)." - Brixus";
	
	$renderItemModals = true;

	$creatorQuery = mysqli_query($conn, "SELECT `username` FROM `users` WHERE `id` = {$item['creatorId']}");
	$creator = mysqli_fetch_assoc($creatorQuery);
	
	if (isset($_GET['msg'])) {
		$msg = intval($_GET['msg']);
	} else {
		$msg = null;
	}
} else {
	exit;
}

$currentUsrID = (int)$_SESSION["userId"];

if(isset($_POST["ExecuteOffer"]) && isset($_POST["OfferID"])) {
	$OfferID = intval($_POST["OfferID"]);
	$FindOffer = mysqli_query($conn,"SELECT * FROM `item_resales` WHERE `OfferID`=$OfferID AND `OfferValid`='yes'");
	if(mysqli_num_rows($FindOffer) == 0) {
		exit();
	} else {
		$offer = mysqli_fetch_assoc($FindOffer);
		if($offer["Currency"] == 'Coins') {
			$FindCurrentUsrCoins = mysqli_query($conn,"SELECT `coins` FROM `users` WHERE `id`=$currentUsrID");
			$CurrentUsrCoins = mysqli_fetch_assoc($FindCurrentUsrCoins)["coins"];
			if(intval($offer["CurrencyAmount"]) > intval($CurrentUsrCoins)) {
				header("Location: ./item.php?id=$itemId&oe");
				exit();
			}
		}
		if($offer["Currency"] == 'Bucks') {
			$FindCurrentUsrBucks = mysqli_query($conn,"SELECT `bucks` FROM `users` WHERE `id`=$currentUsrID");
			$CurrentUsrBucks = mysqli_fetch_assoc($FindCurrentUsrBucks)["bucks"];
			if(intval($offer["CurrencyAmount"]) > intval($CurrentUsrBucks)) {
				header("Location: ./item.php?id=$itemId&oe");
				exit();
			}
		}
		mysqli_query($conn,"UPDATE `users` SET `".strtolower($offer["Currency"])."`=`".strtolower($offer["Currency"])."`+".(ceil($offer[CurrencyAmount]*.8))." WHERE `id`=$offer[SellerUsrID]");
		mysqli_query($conn,"UPDATE `users` SET `".strtolower($offer["Currency"])."`=`".strtolower($offer["Currency"])."`-$offer[CurrencyAmount] WHERE `id`=$_SESSION[userId]");
		mysqli_query($conn,"UPDATE `inventory` SET `userId`=$_SESSION[userId] WHERE `id`=$offer[InventoryID]");
		mysqli_query($conn,"UPDATE `item_resales` SET `OfferValid`='no' WHERE `OfferID`=$OfferID");
		header("Location: $_SERVER[REQUEST_URI]");
		exit();
	}
}

if(isset($_POST["RemoveOffer"]) && isset($_POST["OfferID"])) {
	$OfferID = intval($_POST["OfferID"]);
	$FindOffer = mysqli_query($conn,"SELECT * FROM `item_resales` WHERE `OfferID`=$OfferID AND `OfferValid`='yes'");
	if(mysqli_num_rows($FindOffer) == 0) {
		exit();
	} else {
		$offer = mysqli_fetch_assoc($FindOffer);
		if($_SESSION["userID"] == $offer["SellerUsrID"]) {
			mysqli_query($conn,"UPDATE `item_resales` SET `OfferValid`='no' WHERE `OfferID`=$OfferID");
			header("Location: $_SERVER[REQUEST_URI]");
			exit;
		}
	}
}

			$qualifiesForResale = $item["is_limited"] == 'yes' || ($item["is_limitede"] == 'yes' && (int)$item["limitede_stock"] == 0);
			if($qualifiesForResale) {

				$currentUsrID = (int)$_SESSION["userId"];
				$findCopiesSQL = "SELECT * FROM `inventory` WHERE `userID`=$currentUsrID AND `itemID`=$itemId";
				$findCopies = mysqli_query($conn,$findCopiesSQL);
				$numCopies = mysqli_num_rows($findCopies);
				if ($numCopies > 0) {
					$numresales = 0;
					$numcopies = 0;
					$findResaleOffersSQL = "SELECT * FROM `item_resales` WHERE `SellerUsrID`=$currentUsrID AND `OfferValid`='yes'";
					$findResaleOffers = mysqli_query($conn,$findResaleOffersSQL);
					while($offerRow = mysqli_fetch_assoc($findResaleOffers)) {
						$findItem = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `id`=$offerRow[InventoryID] AND `userId`=$_SESSION[userId]");
						if(mysqli_num_rows($findItem) > 0) {
							//ID
							$superduperID = mysqli_fetch_assoc($findItem)["itemId"];
							if($itemId == $superduperID) { $numresales++; }
						}
					}
					$findAllCopies = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `itemId`=$itemId AND `userId`=$_SESSION[userId]");
					while($invRow = mysqli_fetch_assoc($findAllCopies)) {
						if($invRow["itemId"]==$itemId) { $numcopies++; }
					}
					//logic error as fuck
					if($numresales < $numcopies) {
						//plz work i pray
if(isset($_POST["AddItemResaleOffer"])) {
	$currency = $_POST["currency"];
	$price = (int)$_POST["price"];
		$copy = (int)$_POST["copy"];
		if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM `inventory` WHERE `userId`=$_SESSION[userId] AND `serialNum`=$copy")) == 0) {
			exit;
		}

	
	if($currency != "Coins" && $currency != "Bucks") {
		exit;
	}
	
	if($price < 1) {
		header("Location: ./item.php?id=$itemId&ptl");
		exit;
	}
	
		$soisoisoi = "AND `serialNum`=$copy LIMIT 1";
	
	
	$findInventoryRow = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `userId`=$_SESSION[userId] AND `itemId`=$itemId $soisoisoi");
	if(mysqli_num_rows($findInventoryRow) == 0) {
		exit("a");
	} else {
		$inventoryRow = mysqli_fetch_assoc($findInventoryRow);
	}
	
	mysqli_query($conn,"INSERT INTO `item_resales` VALUES ($itemId,NULL,$_SESSION[userId],'$currency',$price,CURRENT_TIMESTAMP(),'yes',0,$inventoryRow[id])");
}
					}
				}
			}
		
$description = htmlspecialchars($item["description"],ENT_QUOTES);
$image = "https://www.brixus.net/meta_item_img?item_id=".intval($_GET["id"]);

include 'site/header.php';

if ($msg == 1) {
	echo '<div class="success">You have successfully bought ' . htmlspecialchars($item['title'],ENT_QUOTES) . '</div>
';
}

if ($msg == 2) {
	echo '<div class="error">Insufficient funds</div>
';
}

if ($msg == 4) {
	echo '<div class="success">Item details updated successfully</div>
';
}

if ($msg == 5) {
	echo '<div class="success">Item deleted from inventory</div>
';
}

if ($msg == 6) {
	echo '<div class="error">You already own this item</div>
';
}

if($msg == 7) {
	echo '<div class="success">Comment posted</div>';
}

if($msg == 8) {
	echo '<div class="error">Please enter a comment</div>';
}

if(!is_null($msg)) {
	

	

	
}

if(isset($_GET["cwait"])) {
	echo '<div class="error">Please wait before commenting on this item again</div>';
}

if(isset($_GET["oe"])) {
	echo("<div class='error'>You cannot afford to purchase this copy</div>");
}

if(isset($_GET["ptl"])) {
	echo("<div class='error'>Resale price must not be less than 0</div>");
}

if ($loggedIn) {
						$adminQuery = mysqli_query($conn, "SELECT `isAdmin`,`is_moderator` FROM `users` WHERE `id` = {$_SESSION['userId']}");
						$admin = mysqli_fetch_assoc($adminQuery)['isAdmin'];
						$mod = mysqli_fetch_assoc($adminQuery)["is_moderator"] == 1;
						
						$getPower = mysqli_query($conn, "SELECT `isAdmin`, `is_moderator` FROM `users` WHERE `id` = {$_SESSION['userId']}");
					$power = mysqli_fetch_assoc($getPower);
					}

?>
		<?php
		if($isAdmin) {
			echo '<script type="text/javascript">
			var isShown = false;
			
			function toggleHash() {
				if(!isShown) {
					$("#hashLink").text("hide hash");
					$("#hash").show();
					isShown = true;
				} else {
					$("#hashLink").text("show hash");
					$("#hash").hide();
					isShown = false;
				}
			}
		</script>';
		}
		
		/*
		if($item['is_pending_moderation']) {
			echo '<div class="box" style="margin-bottom:10px">
			<h4><img src="/assets/icons/exclamation.png"> This item is pending moderation</h4>
		</div>';
		} else {
		
			if(!$item['isApproved']) {
				echo '<div class="box" style="margin-bottom:10px">
					<h4><img src="/assets/icons/exclamation.png"> This item is disapproved</h4>
				</div>';
			}
		}
		*/
		
		if ($loggedIn) {
						$adminQuery = mysqli_query($conn, "SELECT `isAdmin`,`is_moderator` FROM `users` WHERE `id` = {$_SESSION['userId']}");
						$admin = mysqli_fetch_assoc($adminQuery)['isAdmin'];
						$mod = mysqli_fetch_assoc($adminQuery)["is_moderator"] == 1;
					}
		
		if($power["isAdmin"] || $power["is_moderator"]) {
			if(isset($_GET['purge']) && (!isset($_POST['purge']) && !isset($_POST['cancel_purge']))) {
				echo '<div class="box" style="margin-bottom:10px">
					<h4>Are you sure you want to purge this item?</h4>
					<form method="POST" action="/item?id='.$itemId.'?purge">
						<input type="submit" name="purge" value="Purge" class="green">
						<input type="submit" name="cancel_purge" value="Cancel" class="red">
					</form>
				</div>';
			}
		}
		?>
		<div class="box" style="width:800px;margin:auto;">
			<h3 class="heading" style="text-align:center;"><?php echo htmlspecialchars($item['title'],ENT_QUOTES); ?><?php if($isAdmin) {echo ' <span style="font-size:12px"><span id="hash" style="display:none">'.$item['hash'].' </span>[<a href="javascript:toggleHash()" id="hashLink">show hash</a>]</span>';} ?></h3>
			<div style="float:left;width:320px;">
				<div class="nested" style="display:block;width:300px;height:300px;border:1px solid #000;margin:10px;">
					<?
					if(!isset($_GET["render"]) || (isset($_GET["render"]) && !$loggedIn)){
					?>
					<img src="<?=$item["thumbnail"]?>" title="<?php echo htmlspecialchars($item["title"],ENT_QUOTES); ?>" width="300">
					<?php
					
					?>
					<?
					}else{
					?>
					<iframe src="/render_item?id=<?php echo $itemId; ?>" frameborder="0" width="300" height="300" title="<?php echo $item["title"]; ?>"></iframe>
					<?
					}
					?>
				</div>
			</div>
			<div style="float:right;width:350px;padding:10px">
				<div class="box nested" style="padding:5px">
					<h5 style="padding-top:0;font-weight:normal;letter-spacing:1px">Brixus <?php echo $itemTypes[$item['type']]; ?></h5>
					<?php if ($item['isOnSale']) { ?>
					<?php
					if($item["is_limitede"] == 'yes') {
						?>
							<div style="font-size:14px;padding:5px ;display:block">
						<span style="color:#9B2FBF;font-size:14px;">Limited</span> <span style="color:#D53FFF;font-size:14px;">Stock</span>
					</div>
							<span style="color:red"><?=number_format($item["limitede_stock"])?> of <?=number_format($item["limitede_initial_stock"])?> remaining</span>
					<?php
						if($item["limitede_stock"] == 0){
					if ($item['coins'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/coins.png' style='vertical-align:middle' title='".number_format($item['coins'])." Coins'>
						<span style='color:#E8B119' style='vertical-align:middle'>WAS ".number_format($item['coins'])."</span>
						</div>";
					}
					?>
					<?php
					if ($item['bucks'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/bucks.png' style='vertical-align:middle' title='".number_format($item['bucks'])." Bucks'>
						<span style='color:#5EA25A' style='vertical-align:middle'>WAS ".number_format($item['bucks'])."</span>
						</div>";
					}
					?>
					<?php
					if ($item['coins'] == 0 && $item['bucks'] == 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/tag_blue.png' style='vertical-align:middle' title='Free'>
						<span style='color:#4477FF' style='vertical-align:middle'>WAS Free</span>
						</div>";
					}
						}
					?>
							<?php
					}
						if($item["limitede_stock"] != 0 || $item["limitede_stock"] < 0 || $item["is_limitede"] == 'no') {
							?>
					<form method="POST" action="/item?id=<?php echo $itemId; ?>">
					<?php
					if ($item['coins'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/coins.png' style='vertical-align:middle' title='".number_format($item['coin'])." Coins'>
						<span style='color:#E8B119' style='vertical-align:middle'>".number_format($item['coins'])."</span>
						<div style='padding:2px'></div>
						<input type='submit' name='coins' value='Buy with Coins' style='background:#E8B119'>
						</div>";
					}
					?>
					<?php
					if ($item['bucks'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/bucks.png' style='vertical-align:middle' title='".number_format($item['bucks'])." Bucks'>
						<span style='color:#5EA25A' style='vertical-align:middle'>".number_format($item['bucks'])."</span>
						<div style='padding:2px'></div>
						<input type='submit' name='bucks' value='Buy with Bucks' style='background:#5EA25A'>
						</div>";
					}
					?>
					<?php
					if ($item['coins'] == 0 && $item['bucks'] == 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/tag_blue.png' style='vertical-align:middle' title='Free'>
						<span style='color:#4477FF' style='vertical-align:middle'>Free</span>
						<div style='padding:2px'></div>
						<input type='submit' name='free' value='Grab One' style='background:#4477FF'>
						</div>";
					}
					?>
					</form>
					<?php
						} else {
							?>
							<?php
					}
					?>
					<?php } else { ?>
					<?php if($item["is_limited"] == 'no') { ?>
					<span style="color:#777;padding:5px;font-size:14px;">Off Sale</span>
					<?php } else { ?>
					<span style="color:#9B2FBF;padding:5px;font-size:14px;">Limited</span>
					<?php
					if ($item['coins'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/coins.png' style='vertical-align:middle' title='".number_format($item['coins'])." Coins'>
						<span style='color:#E8B119' style='vertical-align:middle'>WAS ".number_format($item['coins'])."</span>
						</div>";
					}
					?>
					<?php
					if ($item['bucks'] > 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/bucks.png' style='vertical-align:middle' title='".number_format($item['bucks'])." Bucks'>
						<span style='color:#5EA25A' style='vertical-align:middle'>WAS ".number_format($item['bucks'])."</span>
						</div>";
					}
					?>
					<?php
					if ($item['coins'] == 0 && $item['bucks'] == 0) {
						echo "<div style='padding:5px'>
						<img src='/assets/icons/tag_blue.png' style='vertical-align:middle' title='Free'>
						<span style='color:#4477FF' style='vertical-align:middle'>WAS Free</span>
						</div>";
					}
					?>
					<?php } ?>
					<?php } ?>
					<?php
					if ($loggedIn) {
						$adminQuery = mysqli_query($conn, "SELECT `isAdmin`,`is_moderator` FROM `users` WHERE `id` = {$_SESSION['userId']}");
						$admin = mysqli_fetch_assoc($adminQuery)['isAdmin'];
						$mod = mysqli_fetch_assoc($adminQuery)["is_moderator"] == 1;
					}
					
					
					if ($loggedIn && $item['creatorId'] == $_SESSION['userId']) {
						echo "<div style='padding:5px'><a href='/editItem?id=$itemId'><input type='button' value='Edit Item'></a></div>
						";
					}
					
					if ($loggedIn && $isItemOwned) {
						//echo "<div style='padding:5px'><a href='/deleteItem?id=$itemId'><input type='button' value='Delete from Inventory' style='background:red'></a></div>
						//";
					}
					
					if ($item['description'] != '') {
						echo "
					<div style='height:5px'></div>
					<div style='padding:5px;padding-left:0;'>
						" . nl2br(htmlspecialchars($item['description'], ENT_QUOTES)) . "
					</div>
					";
					}
					
					
					?>
					<div style="margin:5px 0" class="label"><strong>Created:</strong> <?php echo $created; ?></div>
					<div style="margin:5px 0" class="label"><strong>Updated:</strong> <?php echo $updated; ?></div>
					<div style="margin:5px 0" class="label"><strong>Sales:</strong> <?php echo number_format($item['sales']); ?></div>
					<?php if($item["creatorId"] > 0) { ?>
					<div style="margin:5px 0" class="label"><strong>Creator:</strong> <a href="/user?id=<?php echo $item['creatorId']; ?>"><?php echo $creator['username']; ?></a></div>
					<?php } ?>
					<?php
					if($loggedIn) {

						$adminQuery = mysqli_query($conn, "SELECT `isAdmin`,`is_moderator` FROM `users` WHERE `id` = {$_SESSION['userId']}");
						$admin = mysqli_fetch_assoc($adminQuery)['isAdmin'];
						$mod = mysqli_fetch_assoc($adminQuery)["is_moderator"];
						if($power["isAdmin"] || $power["is_moderator"] || $item["creatorId"] == $_SESSION["userID"]) {
							echo '<span class="padded"><a href="/item?id='.$itemId.'&render" class="label">Redraw</a></span>';
						}
						if($power["isAdmin"] || $power["is_moderator"]) {
							
						echo '
							<span class="padded"><a href="/item?id='.$itemId.'&purge" class="label">Purge</a></span>
							<span class="padded"><a href="/admin/disapprove_item?id='.$itemId.'" class="label">Disapprove</a></span>
							<span class="padded"><a href="/admin/approve_item?id='.$itemId.'" class="label">Approve</a></span>
							';
						}
					}
										
					
					if ($loggedIn && $item['creatorId'] != $_SESSION['userId']) {
						echo '<span class="padded"><a href="/report/?itemId='.$itemId.'" class="label">Report</a></span>
					<div style="clear:both"></div>
					'; 
					} else {
						
					}
					
					?>
				</div>
			</div>
			<div style="clear:both"></div>
			<?php
			$qualifiesForResale = $item["is_limited"] == 'yes' || ($item["is_limitede"] == 'yes' && (int)$item["limitede_stock"] == 0);
			if($qualifiesForResale) {
				?>
				<?php
				$currentUsrID = (int)$_SESSION["userId"];
				$findCopiesSQL = "SELECT * FROM `inventory` WHERE `userID`=$currentUsrID AND `itemID`=$itemId";
				$findCopies = mysqli_query($conn,$findCopiesSQL);
				$numCopies = mysqli_num_rows($findCopies);
				if ($numCopies > 0) {
					$numresales = 0;
					$numcopies = 0;
					$findResaleOffersSQL = "SELECT * FROM `item_resales` WHERE `SellerUsrID`=$currentUsrID AND `OfferValid`='yes'";
					$findResaleOffers = mysqli_query($conn,$findResaleOffersSQL);
					while($offerRow = mysqli_fetch_assoc($findResaleOffers)) {
						$findItem = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `id`=$offerRow[InventoryID] AND `userId`=$_SESSION[userId]");
						if(mysqli_num_rows($findItem) > 0) {
							//ID
							$superduperID = mysqli_fetch_assoc($findItem)["itemId"];
							if($itemId == $superduperID) { $numresales++; }
						}
					}
					$findAllCopies = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `itemId`=$itemId AND `userId`=$_SESSION[userId]");
					while($invRow = mysqli_fetch_assoc($findAllCopies)) {
						if($invRow["itemId"]==$itemId) { $numcopies++; }
					}
					//logic error as fuck
					if($numresales < $numcopies) {
						
					?>
				<div class="heading" style="border-top:1px solid #000;padding:5px;margin:10px 0;">Sell a Copy</div>
				
					
				<form method="POST" class="padded">
					<span>Select Copy:</span>
					<select name="copy">
						<?php
						$copiesAssoc = function() { return "joe mama"; };
						while ($copyRow = mysqli_fetch_assoc($findCopies)) {
							if(mysqli_num_rows(mysqli_query($conn,"SELECT 1 FROM `item_resales` WHERE `OfferValid`='yes' AND `InventoryID`=$copyRow[id]")) == 0) {
							?>
							<option value="<?=$copyRow["SerialNum"]?>">#<?=$copyRow["SerialNum"]?></option>
							<?php
							}
						}
						?>
					</select>
					<div style="height:5px"></div>
					<span>Currency:</span>
					<select name="currency">
						<option value="Bucks">Bucks</option>
						<option value="Coins">Coins</option>
					</select>
					<div style="height:5px"></div>
					<span>Price:</span>
					<input type="number" name="price" min="1" value="1">
					<div style="height:10px"></div>
					<input type="hidden" name="AddItemResaleOffer" value="AddItemResaleOffer">
					<input type="submit">
				</form>
				<?php
				}
				?>
				
				<?php } ?>
				
				<?php
				// Find all offers for this item 
				$offerConditions = "`ItemID`=$itemId AND `OfferValid`='yes'";
				$findAllOffers = mysqli_query($conn,"SELECT * FROM `item_resales` WHERE $offerConditions");
				// Are there any offers for this item? 
				$numOffers = mysqli_num_rows($findAllOffers);
				
				if ($numOffers > 0) {
					?>
				<div class="heading" style="border-top:1px solid #000;padding:5px;margin:10px 0;">Sellers</div>
				<?php
				
				while ($currentOffer = mysqli_fetch_assoc($findAllOffers)) {
					$findSellerQ = mysqli_query($conn,"SELECT `username`,`avatar` FROM `users` WHERE `id`=$currentOffer[SellerUsrID]");
					$ThisSeller = mysqli_fetch_assoc($findSellerQ);
					$findInvRQ = mysqli_query($conn,"SELECT * FROM `inventory` WHERE `id`=$currentOffer[InventoryID]");
					$lalaid = mysqli_fetch_assoc($findInvRQ);
					$findItemQ = mysqli_query($conn,"SELECT * FROM `items` WHERE `id`=$lalaid");
					$lalaitem = mysqli_fetch_assoc($findItemQ);
					?>
					<div class="seperator" style="margin:10px 0"></div>
			<div style="width:780px;margin:0 auto 10px auto;">
				<a href="/user?id=<? echo $currentOffer["SellerUsrID"]; ?>" width="100"><img src="<? echo $ThisSeller["avatar"]; ?>" title="<? echo $ThisSeller["username"]; ?>" width="100"></a><div style="display:inline-block;width:680px;vertical-align:top;">
					<a href="/user?id=<? echo $currentOffer["SellerUsrID"]; ?>"><? echo $ThisSeller["username"]; ?></a>
					<span class="label"> - [#<?=$lalaid["SerialNum"]?>]</span>
					<div style="margin-top:5px;min-height:61px;">
						<?php
						if($currentOffer["Currency"] == 'Bucks') {$currcl = "#5EA25A";$currimg = "/assets/icons/bucks.png";}
						if($currentOffer["Currency"] == 'Coins') {$currcl = "#E8B119";$currimg = "/assets/icons/coins.png";}
						$offertext = number_format((int)$currentOffer["CurrencyAmount"])." $currentOffer[Currency]";
						echo "<span style='color:$currcl'><img src='$currimg' title='$currentOffer[Currency]' style='vertical-align:middle'> <span style='vertical-align:middle'>$offertext</span></span>";
						echo "<form method='POST' action=''>";
						echo "<input type='hidden' name='OfferID' value='$currentOffer[OfferID]'>";
						if($currentOffer["SellerUsrID"] == $_SESSION["userId"]) { echo "<input type='submit' name='RemoveOffer' value='Remove' class='red'>"; }
						if($currentOffer["SellerUsrID"] != $_SESSION["userId"]) { echo "<input type='submit' name='ExecuteOffer' value='Purchase'>"; }
						echo "</form>";
						//yours sincerely, jaarva.
						?>
					</div>
					<div style="vertical-align:bottom;width:680px;">
						<span style="font-size:11px;"><i>Posted <?=$currentOffer['OfferCreated']?></i></span>
					</div>
				</div>
			</div>
					<?php
				}
				
	
				}
				?>
				<?php
			}
			?>
			<div class="heading" style="border-top:1px solid #000;padding:5px;margin:10px 0;">Comments</div>
			<form method="POST">
				<textarea name="comment" placeholder="Your Comment" style="width:765px;display:block;margin:0 10px 10px 10px;" rows="5"></textarea>
				<input type="submit" name="add_comment" value="Post Comment" style="margin-left:10px;">
			</form>
			<?
			$get_comments = mysqli_query($conn, "SELECT * FROM `item_comments` WHERE `item_id` = ".intval($_GET["id"])." AND `is_deleted` = 0 ORDER BY `id` DESC");
			while($comment = mysqli_fetch_assoc($get_comments)) {
				$get_poster = mysqli_query($conn, "SELECT `username`, `avatar` FROM `users` WHERE `id` = ".intval($comment["poster_id"]));
				$poster = mysqli_fetch_assoc($get_poster);
			?>
			<div class="seperator" style="margin:10px 0"></div>
			<div style="width:780px;margin:0 auto 10px auto;">
				<a href="/user?id=<? echo $comment["poster_id"]; ?>" width="100"><img src="<? echo $poster["avatar"]; ?>" title="<? echo $poster["username"]; ?>" width="100"></a><div style="display:inline-block;width:680px;vertical-align:top;">
					<span class="label"><a href="/user?id=<? echo $comment["poster_id"]; ?>"><? echo $poster["username"]; ?></a> - <? echo date("Y-m-d H:i:s",strtotime($comment["post_timestamp"])); ?></span>
					<div style="margin-top:5px;min-height:61px;">
						<? echo nl2br(htmlspecialchars($comment["text"],ENT_QUOTES)); ?>
					</div>
					<div style="vertical-align:bottom;width:680px;">
						<?php
						if($power["isAdmin"] || $power["is_moderator"]) {
							echo '<a href="/item?id='.$itemId.'&purgecomment='.$comment["id"].'" class="label">Purge</a>';
							echo ' ';
						}
						?>
						<a href="/report/?item_comment_id=<? echo $comment["id"]; ?>" class="label">Report</a>
					</div>
				</div>
			</div>
			<?
			}
			
			if(mysqli_num_rows($get_comments) == 0) { echo '<div class="seperator" style="margin:10px 0"></div><div style="display:block;text-align:center;">No comments on this item</div>'; }
			?>
		</div>
<?php
include 'site/footer.php';
?>