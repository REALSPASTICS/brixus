<?
include("site/config.php");
header("Content-Type: image/png");

$FindItem = mysqli_query($conn, "SELECT * FROM `items` WHERE `id`=" . (int) $_GET["item_id"]);
$ItemRow = (object) mysqli_fetch_assoc($FindItem);

if ($ItemRow->{"is_pending_moderation"}) {
	exit(file_get_contents(__DIR__ . "/assets/ItemAwaitingApproval.png"));
}

if (!$ItemRow->{"isApproved"}) {
	exit(file_get_contents(__DIR__ . "/assets/ItemDisapproved.png"));
}

$get_item_thumb = mysqli_query($conn, "SELECT `thumbnail` FROM `items` WHERE `id` = ".intval($_GET["item_id"]));
$item_thumb = mysqli_fetch_assoc($get_item_thumb)["thumbnail"];
exit ( file_get_contents($item_thumb) ) ;
?>