<?php
include 'site/config.php';

if (!$loggedIn) {
	header('Location: /login/');
	exit;
}

$itemId = mysqli_escape_string($conn, intval($_GET['id']));

$itemQuery = mysqli_query($conn, "SELECT `title`, `description`, `creatorId`, `coins`, `bucks`, `isOnSale`, `zoom`,`type`,`lookAtVector`,`is_limited` FROM `items` WHERE `id` = $itemId");

if (mysqli_num_rows($itemQuery) == 0) {
	header('Location: /');
	exit;
}

$item = mysqli_fetch_assoc($itemQuery);

$adminQuery = mysqli_query($conn, "SELECT `isAdmin` FROM `users` WHERE `id` = {$_SESSION['userId']}");
$admin = mysqli_fetch_assoc($adminQuery)['isAdmin'];

if ($item['creatorId'] != $_SESSION['userId'] && !$admin) {
	header('Location: /');
	exit;
}

if (isset($_POST['description']) && isset($_POST['coins']) && isset($_POST['bucks'])) {
	$coins = intval($_POST['coins']);
	$bucks = intval($_POST['bucks']);
	$title = mysqli_escape_string($conn, $_POST['title']);
	$description = mysqli_escape_string($conn, $_POST['description']);
	
	if (strlen($_POST['title']) <= 100 && strlen($_POST['description']) <= 1000 && trim($_POST['title']) != '' && ((!isset($_POST['isFree']) && ($coins != 0 || $bucks != 0)) || isset($_POST['isFree']))) {
		if (isset($_POST['isFree'])) {
			mysqli_query($conn, "UPDATE `items` SET `coins` = 0, `bucks` = 0 WHERE `id` = $itemId");
		} else {
			if (!isset($_POST['isFree'])) {
				mysqli_query($conn, "UPDATE `items` SET `coins` = $coins, `bucks` = $bucks WHERE `id` = $itemId");
			}
		}
		
		if (!isset($_POST['isOnSale'])) {
			mysqli_query($conn, "UPDATE `items` SET `isOnSale` = 0 WHERE `id` = $itemId");
		} else {
			mysqli_query($conn, "UPDATE `items` SET `isOnSale` = 1 WHERE `id` = $itemId");
		}
		
		mysqli_query($conn, "UPDATE `items` SET `title` = '$title' WHERE `id` = $itemId");
		mysqli_query($conn, "UPDATE `items` SET `description` = '$description' WHERE `id` = $itemId");
		//mysqli_query($conn, "UPDATE `items` SET `zoom` = $zoom WHERE `id` = $itemId");
		mysqli_query($conn, "UPDATE `items` SET `updated` = CURRENT_TIME WHERE `id` = $itemId");
		mysqli_query($conn, "UPDATE `items` SET `zoom`=".mysqli_escape_string($conn,$_POST["zoom"])." WHERE `id`=$itemId");
		mysqli_query($conn, "UPDATE `items` SET `lookAtVector`='".mysqli_escape_string($conn,$_POST["lookAtVector3"])."' WHERE `id`=$itemId");
		
		if(isset($_POST["isLimited"])) {
				$itemstock = (int) $_POST["itemstock"];
				mysqli_query($conn,"UPDATE `items` SET `is_limited`='yes' WHERE `id`=$itemId");
			} else {
				mysqli_query($conn,"UPDATE `items` SET `is_limited`='no' WHERE `id`=$itemId");
			}
			
		header("Location: /item.php?id=$itemId");
		exit;
	} else {
		if (strlen($_POST['title']) > 100) {
			header("Location: /editItem.php?id=$itemId&msg=1");
			exit;
		}
		
		if (strlen($_POST['description']) > 1000) {
			header("Location: /editItem.php?id=$itemId&msg=2");
			exit;
		}
		
		if (trim($_POST['title']) == '') {
			header("Location: /editItem.php?id=$itemId&msg=3");
			exit;
		}
		
		if (!isset($_POST['isFree']) && $coins <= 0 && $bucks <= 0) {
			header("Location: /editItem.php?id=$itemId&msg=4");
		}
	}
} else {
	//var_dump($_POST);
}

$title = "Edit ".htmlspecialchars($item['title'],ENT_QUOTES)." - Brixus";

include 'site/header.php';

if (isset($_GET['msg'])) {
	$msg = intval($_GET['msg']);
} else {
	$msg = 0;
}

if ($msg == 1) {
	echo '<div class="error">Title must be within 2 and 50 characters</div>
';
}

if ($msg == 2) {
	echo '<div class="error">Description must be within 1000 characters</div>
';
}

if ($msg == 3) {
	echo '<div class="error">Title must not be empty</div>
';
}

if ($msg == 4) {
	echo '<div class="error">Invalid prices</div>
';
}
?>
		<div class="box">
			<h3 class="heading">Edit <?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?><?php if ($item['creatorId'] != $_SESSION['userId'] && $admin) {echo ' (admin)';} ?></h3>
			<form method="POST" class="padded">
				<?php
				if($item["type"] == 3){
				?>
				<div style="padding-bottom:10px">
					<h5>zoom:</h5>
					<input type="text" name="zoom" value="<?=$item["zoom"]?>">
					<h5>lookAtVector3:</h5>
					<input type="text" name="lookAtVector3" value="<?=$item["lookAtVector"]?>">
				</div>
				<?php
				}
				?>
				<span>Title:</span>
				<input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>" style="margin-bottom:4px">
				<br>
				<span>Description:</span>
				<textarea rows="10" style="width:863px" name="description" placeholder="Description" style="margin-bottom:4px"><?php echo htmlspecialchars($item['description']); ?></textarea>
				<input type="checkbox" name="isOnSale" <?php if ($item['isOnSale']) {echo 'checked';} ?>>
				<span>On Sale</span>
				<br>
				<input type="checkbox" name="isFree" id="isFreeCheckbox" <?php if ($item['coins'] == 0 && $item['bucks'] == 0) {echo 'checked';} ?>>
				<span>Free</span>
				<div id="currencyPanel" style="display:<?php if ($item['coins'] == 0 && $item['bucks'] == 0) {echo 'none';} else {echo 'block';} ?>">
					<img src="/assets/icons/coins.png" title="Coins">
					<input type="number" name="coins" placeholder="0" id="coinsInput" <?php echo "value='"; if ($item['coins'] > 0) {echo $item['coins'];} else {echo '';} echo "'"; ?>><span class="label">*</span>
					<br>
					<img src="/assets/icons/bucks.png" title="Bucks">
					<input type="number" name="bucks" placeholder="0" id="bucksInput" <?php echo "value='"; if ($item['bucks'] > 0) {echo $item['bucks'];} else {echo '';} echo "'"; ?>><span class="label">*</span>
					<div class="label" style="padding-top:5px">*20% marketplace tax applies</div>
				</div>
				<div id="islimitedpanel">
					<?php
					if($item["type"] == 3) {
						?>
					<input type="checkbox" name="isLimited">
					<span>Is Limited</span>
					<?php
					}
					?>
				</div>
				<input type="submit" name="update" value="Update" style="display:block;margin-top:5px">
			</form>
		</div>
		<script type="text/javascript">
			$('#isFreeCheckbox').change(function() {
				
				
				if ($('#isFreeCheckbox').is(':checked')) {
					setTimeout(function() {
						
						$('#coinsInput').val('');
						$('#bucksInput').val('');
					},300);
				} else {
						$('#coinsInput').val('<?php if ($item['coins'] != 0) {echo $item['coins'];} else {echo '';} ?>');
						$('#bucksInput').val('<?php if ($item['bucks'] != 0) {echo $item['bucks'];} else {echo '';} ?>');
				}
				
				$('#currencyPanel').slideToggle(300);
			});
		</script>
		<style>
			#currencyPanel {
				padding: 10px;
			}
		</style>
<?php
include 'site/footer.php';
?>