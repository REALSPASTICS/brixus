<?php
include 'site/config.php';

if(!isset($_GET['id'])) {exit;}

$userId = intval($_GET['id']);
$type = intval($_GET['type']);
$page = intval($_GET['page']);

if($type != 0 && $type != 1 && $type != 2 && $type != 3) {exit;}

$itemNum = 0;
$numItemsPerPage = 8;

$allInventory = mysqli_query($conn, "SELECT * FROM `inventory` WHERE `userId` = $userId");
while($invitem1 = mysqli_fetch_assoc($allInventory)) {
	$itemQuery = mysqli_query($conn, "SELECT * FROM `items` WHERE `id` = {$invitem1['itemId']}");
	$item = mysqli_fetch_assoc($itemQuery);
	
	if($item['isApproved']) {if($type == 0) {$itemNum++;} else {if($item['type'] == $type) {$itemNum++;}}}
}

$pagesNum = ceil($itemNum/$numItemsPerPage);

if($pagesNum != 0 && ($page <= 0 || $page > $pagesNum)) {exit;}

$offset = ($page - 1) * $numItemsPerPage;

$inventoryQuery = mysqli_query($conn, "SELECT inv.*, item.* 
                                       FROM `inventory` AS inv 
                                       JOIN `items` AS item ON inv.itemId = item.id 
                                       WHERE inv.userId = $userId 
                                       AND item.isApproved = 1 
                                       AND (item.type = $type OR $type = 0) 
                                       ORDER BY inv.id DESC 
                                       LIMIT $offset,$numItemsPerPage");

if($itemNum != 0) { echo "<div>";

while($invitem = mysqli_fetch_assoc($inventoryQuery)) {
	$itemQuery = mysqli_query($conn, "SELECT * FROM `items` WHERE `id` = {$invitem['itemId']}");
	$item = mysqli_fetch_assoc($itemQuery);
	
	if($item['isApproved']) {if($type == 0 || $item['type'] == $type) {
		if($item["is_limitede"] == 'yes' || $item["is_limited"] == 'yes') {
			$lttext = '[#'.$invitem["SerialNum"].'] ';
		} else {
			$lttext = '';
		}
		echo '<div class="shopItem">
			<a href="/item?id='.$item['id'].'"><img src="'.$item['thumbnail'].'" title="'.htmlspecialchars($item['title'],ENT_QUOTES).'" class="itemThumb"></a>
			<a href="/item?id='.$item['id'].'" class="itemName"><span style="color:#9B2FBF!important">'.$lttext.'</span>'.htmlspecialchars($item['title'],ENT_QUOTES).'</a>
		</div>';
	}}
}

echo "</div>"; }

if($itemNum != 0) {
	echo '<div style="padding-top:10px;padding-bottom:10px;">';
	for($i = 1; $i <= $pagesNum; $i++) {echo '<span class="padded"><a onclick="loadInventory('.$type.', '.$i.')">'.$i.'</a></span>';}
	echo '</div>';
} else {
	echo '<span class="padded" style="text-align:center">No items found</span>';
}
?>