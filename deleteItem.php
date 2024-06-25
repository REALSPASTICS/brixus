<?php
include 'site/config.php';

if (!$loggedIn) {
	header('Location: /login/');
	exit;
}

$item_id = (int) $_GET['id'];

$get_item = mysqli_query($conn, "SELECT * FROM `items` WHERE `id` = $item_id");

$item = mysqli_fetch_assoc($get_item);

if (mysqli_num_rows($get_item) == 0) {
	//header('Location: /');
	//exit;
}

if(!$get_item){
	exit;
}

$get_inv = mysqli_query($conn, "SELECT 1 FROM `inventory` WHERE `itemId` = $item_id AND `userId` = {$_SESSION['userId']}");

$inv_exists = mysqli_num_rows($get_inv);

if (!$inv_exists) {
	//header('Location: /');
	//exit;
}

if(!$get_inv){
	exit;
}

if (isset($_POST['delete'])) {
	mysqli_query($conn, "DELETE FROM `inventory` WHERE `itemId` = $item_id AND `userId` = {$_SESSION['userId']}");
	header("Location: /item?id=$item_id&msg=5");
	exit;
}

$title = 'Delete Item - Brixus';

include 'site/header.php';
?>
<?php if ($item['creatorId'] == $_SESSION['userId']) { ?>
<div class="box"></div>
<?php } else { ?>
		<style>
		form { display: inline-block; }
		.box { padding: 10px; }
		form { padding-top: 10px; }
		img {  padding-bottom: 10px; }
		input[name='delete'] { padding-left: 16px; background: url('/assets/icons/bin_closed.png') red no-repeat !important; }
		input#cancel { padding-left: 16px; background: url('/assets/icons/cancel.png') #72AFFF no-repeat !important; }
		</style>
		<div class="box">
			<img src="<?=$item['thumbnail']?>" style="width: 200px; height: 200px;" title="<?php echo $item['title']; ?>">
			<div>Are you sure you want to delete <?=$item['title'];?> from your inventory?</div>
			<form method="post"><input type="submit" name="delete" style="background:red;color:#fff" value="Delete"></form>
			<form method="get" action="/item.php"><input type="hidden" name="id" value="<?=$item_id;?>"><input type="submit" value="Cancel" id="cancel"></form>
		</div>
<?php } ?>
<?php
include 'site/footer.php';
?>