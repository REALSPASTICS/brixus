<?php
include 'site/config.php';

	if($loggedIn) {
	$isAdminQuery = mysqli_query($conn, "SELECT 1 FROM `users` WHERE `id` = {$_SESSION['userId']} AND `isAdmin` = 1");
	$isAdmin = mysqli_num_rows($isAdminQuery) > 0;
} else {
	$isAdmin = false;
}

// thanks to... whoever did the checks

if (!isset($_GET['type'])) {
    header('Location: /shop.php?page=' . (isset($_GET['page']) ? (int) $_GET['page'] : '1') . '&type=0&sort=' . (isset($_GET['sort']) ? (int) $_GET['sort'] : '1') . '&search=');
    exit;
}

else if (!isset($_GET['page'])) {
    header('Location: /shop.php?page=1&type=' . (isset($_GET['type']) ? (int) $_GET['type'] : '0') . '&sort=' . (isset($_GET['sort']) ? (int) $_GET['sort'] : '1') . '&search=');
    exit;
}

else if (!isset($_GET['sort'])) {
    header('Location: /shop.php?page=' . (isset($_GET['page']) ? (int) $_GET['page'] : '1') . '&type=' . (isset($_GET['type']) ? (int) $_GET['type'] : '0') . '&sort=1&search=');
    exit;
} else {
	$itemsPerPage = 12;
	$page = mysqli_escape_string($conn, intval($_GET['page'])) ?? 0;
	$offset = ($page - 1) * $itemsPerPage;

	$type = mysqli_escape_string($conn, intval($_GET['type']));
	$search = mysqli_escape_string($conn, $_GET['search']);
	$sort = mysqli_escape_string($conn, intval($_GET['sort']));
	
		if ($sort > 5 || $sort < 1) {exit;
	} else {
		if ($sort == 1) {$sortThing = "ORDER BY `id` DESC";}
		if ($sort == 2) {$sortThing = "ORDER BY `id` ASC";}
		if ($sort == 3) {$sortThing = "ORDER BY `sales` DESC, `id` DESC";}
		if ($sort == 4) {$sortThing = "ORDER BY `sales` ASC, `id` DESC";}
		if($sort == 5) {$sortThing = "ORDER BY RAND()";}
	}
	
	if (!isset($sortThing)) {
		$sortThing = '';
	}
	
	if ($type < 0 || $type > 3) {
		exit;
	} else {
		if ($type != 0) {
			$typeThingy = "`type` = $type AND";
		} else {
			$typeThingy = '';
		}
	}
	
	if(!isset($_GET["showunavailable"])) {
		$availablething = " AND (`isOnSale`=1 OR `is_limited`='yes')";
} else {
	$availablething = "";
}
	
	
	
	
	
	$itemNumQuery = mysqli_query(
		$conn,
		"SELECT 1 FROM `items`
		WHERE $typeThingy (`title` LIKE '%$search%' OR `description` LIKE '%$search%') AND `isApproved` = 1 $availablething"
	);
			
	
	$itemNum = mysqli_num_rows($itemNumQuery);
	
	$pageNum = ceil($itemNum/$itemsPerPage);
	
	if ($page <= 0 || ($pageNum != 0 && $page > $pageNum)) {
		header ( 'Location: /shop.php?page=1&type=0&sort=1&search=' );
		exit;
	}

	
	

}

$title = 'Shop - Brixus';

include 'site/header.php';
?>
<style>
.legendSect {
	margin-top:10px;
}
.legendSect .
</style>
<?php
if(isset($_GET["showunavailable"])) {
	$viewshowavailable = "true";
} else {
	$viewshowavailable = "false";
}
?>
		<div style="width:200px;float:left;padding-top:10px;">
			<div>
				<div style="float:left;width:70%;vertical-align:middle;">
					<?php
					switch($type) {
						case 0:
							$typeText = "All";
							break;
						case 1:
							$typeText = "Shirts";
							break;
						case 2:
							$typeText = "Faces";
							break;
						case 3:
							$typeText = "Hats";
							break;
						default:
							$typeText = "joe mama";
							break;
					}
					
					switch((int)$sort) {
						case 1:
							$sortText = "Newest";
							break;
						case 2:
							$sortText = "Oldest";
							break;
						case 3:
							$sortText = "Most Sales";
							break;
						case 4:
							$sortText = "Least Sales";
							break;
						case 5:
							$sortText = "Random";
							break;
					}
					?>
					<strong style="vertical-align:middle;font-size:19px;">Shop</strong>
					<br>
					<span style="vertical-align:top;font-size:13px;color:#666;"><?=$typeText?>: <?=$sortText;?></span>
				</div>
				<div style="float:right;width:30%;vertical-align:middle;">
					<a href="./shop/create/index" style="color:#FFF;float:right;"><input type="button" value="Create" class="green"></a>
					<div style="clear:both"></div>
				</div>
			</div>
			<div style="clear:both;height:10px;"></div>
			<div class="box" style="padding:5px;border-bottom-width:0!important;">
				<div style="border-bottom-width:0;padding:5px;font-weight:bold;">Browse:</div>
			</div>
			<div class="verticalTabContainer" style="border:1px solid #000;border-bottom:0;">
				<div class="tabitem" onclick="swapView(1, 0, <?php echo (int) $_GET['sort']; ?>, '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>', <?=$viewshowavailable?>)">All</div>
				<div class="tabitem" onclick="swapView(1, 1, <?php echo (int) $_GET['sort']; ?>, '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>', <?=$viewshowavailable?>)">Shirts</div>
				<div class="tabitem" onclick="swapView(1, 2, <?php echo (int) $_GET['sort']; ?>, '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>', <?=$viewshowavailable?>)">Faces</div>
				<div class="tabitem" onclick="swapView(1, 3, <?php echo (int) $_GET['sort']; ?>, '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>', <?=$viewshowavailable?>)">Hats</div>
			</div>
			<div class="box" style="margin-top:10px">	
				<div style="margin:10px;margin-bottom:0">
				<style>
				#clr{display:none;}
				</style>
<input type="text" id="search" <?php if ($_GET['search'] != '') {echo 'value="' . htmlspecialchars($_GET['search'], ENT_QUOTES) . '"';} ?>
style="max-width:100%" placeholder="Search for an item...">
<div style="height:5px"></div>
<a onclick="swapView(1, <?php echo (int) $_GET['type']; ?>, <?php echo (int) $_GET['sort']; ?>, $('#search').val())"
title="Search"
class="label"><i class='fa fa-search'></i> Search</a>
<div style="height:10px"></div>
											<span style="padding-left:5px">Sort By:</span>
					<select id="sort" onchange="swapView(1, <?php echo (int) $_GET['type']; ?>, $('#sort').val(), '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>',<?=$viewshowavailable?>)">
						<option value="1" <?php if (intval($_GET['sort']) == 1) { echo 'selected'; } ?>>Newest</option>
						<option value="2" <?php if (intval($_GET['sort']) == 2) { echo 'selected'; } ?>>Oldest</option>
						<option value="3" <?php if (intval($_GET['sort']) == 3) { echo 'selected'; } ?>>Most Sales</option>
						<option value="4" <?php if (intval($_GET['sort']) == 4) { echo 'selected'; } ?>>Least Sales</option>
						<option value="5" <? if(intval($_GET["sort"]) == 5) { echo "selected"; } ?>>Random</option>
					</select>
					<?
						if(intval($_GET["sort"])==5){echo"<div style='height:5px'></div><a onclick='window.location.reload();' class='label'>
						<i class='fa fa-refresh'></i> Refresh
						</a>";}
					?>
					<div style="height:10px"></div>
<input <?php
if(isset($_GET["showunavailable"])) {echo 'checked';}
?>	type="checkbox" id="showunavailable" onclick="swapView(1, <?php echo (int) $_GET['type']; ?>, $('#sort').val(), '<?php echo htmlspecialchars($_GET['search'], ENT_QUOTES); ?>',$(this).prop('checked'))" style=
"
margin-left:0;">
					<span>Show unavailable items</span>
				</div>
			</div>
			<div class="box padded" style="margin-top:10px">
				<h4>Legend</h4>
				<div class="legendSect">
					<div class='shopPrice free' style="padding:0">Free</div>
					<span>Items with this label may be obtained without spending any currency.</span>
				</div>
				<div class="legendSect">
					<div style='color:#9B2FBF;padding: 0 ;display:block;font-size:12px;'>Limited</div>
					<span>Items with this label have previously been for sale, although have since been made Limited and may only be obtained through buying from a reseller who owns the item.</span>
				</div>
				<div class="legendSect">
					<div style="font-size:12px;padding:0 ;display:block">
						<span style='color:#9B2FBF;font-size:12px;'>Limited</span> <span style='color:#D53FFF;font-size:12px;'>Stock</span>
					</div>
					<span>Items with this label have a limited stock. After the stock runs out, users who have purchased the item may resell it.</span>
				</div>
			</div>
		</div>
		<div style="width:690px;float:right;">
				<?php
				$itemQuery =
				mysqli_query(
					$conn,
					"SELECT `title`, `creatorId`, `bucks`, `coins`, `isOnSale`, `id`, `thumbnail`, `is_limited`,
			`is_limitede`,
			`limitede_stock` FROM `items` WHERE $typeThingy (`title` LIKE '%$search%' OR `description` LIKE '%$search%') AND `isApproved` = 1 $availablething $sortThing LIMIT $offset, $itemsPerPage"
				);
				
				$displayed = false;
				
				while ($item = mysqli_fetch_assoc($itemQuery)) {
					$displayed = true;
					
					$creatorQuery = mysqli_query($conn, "SELECT `username` FROM `users` WHERE `id` = {$item['creatorId']}");
					$creator = mysqli_fetch_assoc($creatorQuery);
					
					if($item["is_limitede"] == 'yes') {
						if(intval($item["limitede_stock"]) == 0) {
							$stockCol = "#888";
						} else {
							$stockCol = "#E00";
						}
					}
					
					echo "<div class='shopItem'>
						<a href='/item.php?id={$item['id']}'><img src='$item[thumbnail]' title='".htmlspecialchars($item['title'],ENT_QUOTES)."' class='itemThumb'></a>
						<a href='/item.php?id={$item['id']}' class='itemName'>".htmlspecialchars($item["title"],ENT_QUOTES)."</a>
						".($item["creatorId"] > 0 ? "<span class='itemCreator label'>Creator: <a href='/user.php?id={$item['creatorId']}'>{$creator['username']}</a></span>" : "")."
						"
						.
						(!$item['isOnSale'] && $item["is_limited"] == 'no' ? "<span class='shopOffSaleIndicator'>Off Sale</span>" : '')
						.
						(!$item["isOnSale"] && $item["is_limited"] == 'yes' ? "<span style='color:#9B2FBF;padding: 0 5px;display:block;font-size:12px;'>Limited</span>" : '')
						.
						($item["is_limitede"] == 'yes' ? "<div style='font-size:12px;padding-bottom:5px;padding:0 5px 5px 5px;display:block'>
							<span style='color:#9B2FBF;font-size:12px;'>Limited</span> <span style='color:#D53FFF;font-size:12px;'>Stock</span>
							<div style='color:$stockCol;font-size:12px;display:block'>".number_format($item["limitede_stock"])." remaining"."</div>
						</div>" : "")
						.
						($item['isOnSale'] && $item['coins'] > 0 ? "<span class='shopPrice coins' title='".number_format($item['coins'])." Coins'><img src='/assets/icons/coins.png' style='vertical-align:bottom' width='14'> ".number_format($item['coins'])."</span>" : '')
						.
						($item['isOnSale'] && $item['bucks'] > 0 ? "<span class='shopPrice bucks' title='".number_format($item['bucks'])." Bucks'><img src='/assets/icons/money.png' style='vertical-align:bottom' width='14'> ".number_format($item['bucks'])."</span>" : '')
						.
						($item['isOnSale'] && $item['bucks'] == 0 && $item['coins'] == 0 ? "<span class='shopPrice free'>Free</span>" : '')
						.
						"
					</div>";
				}
				
				if (!$displayed) {
					echo "<center>No items found</center>";
				} else {
					echo '<div>';
					for ($i = 1; $i <= $pageNum; $i++) {
						
						echo '<a onclick="swapView(' . $i . ','.$type.',' . (isset($_GET['sort']) ? (int) $_GET['sort'] : '1') . ',\'' . (isset($_GET['search']) ? urlencode($_GET['search']) : '') . '\','.(isset($_GET["showunavailable"]) ? "true" : "false").')" style="padding:3px">'.$i.'</a>';

					}
					echo '</div>';
				}
				?>
</div>
		<div style="clear:both"></div>
		<script type="text/javascript">
			function swapView(page, type, sort, search,showunavailable) {
				if(showunavailable) {
					showunavailable = "&showunavailable";
				} else {
					showunavailable = "";
				}
				window.location = '/shop.php?page=' + page + '&type=' + type + '&sort=' + sort + '&search=' + search + showunavailable;
			}
		</script>
<?php
include 'site/footer.php';
?>