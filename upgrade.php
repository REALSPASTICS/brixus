<?php include("site/config.php"); ?>
<?php if(!$loggedIn) { exit(header("Location: /login/")); } ?>
<?php
$findPaymentID = mysqli_query($conn,"SELECT `paymentID` FROM `users` WHERE `id`=$_SESSION[userID]");
$paymentID = $_SESSION["userID"];
if(strlen($paymentID) == 0) {
function randtoken()
{
	$chars = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";
	
	
	
	$resulting = "";
	
	for($i = 0; $i < 20; $i++) {$resulting .= $chars[rand(0, strlen($chars)-1)];}
	
return $resulting	;
}
mysqli_query($conn,"UPDATE `users` SET `paymentID`='".randtoken()."' WHERE `id`=$_SESSION[userID]");
header("Location: $_SERVER[REQUEST_URI]");
exit;
}
?>
<?php $title = "Upgrades - Brixus"; ?>
<?php include("site/header.php"); ?>
<style>
p {
	padding-left:5px;
}
</style>
<style>
#MemBox {
	border-color:#4E93C3
}

<?php
$MemTiers = $_GeneralSettings["MemTiers"];
foreach($MemTiers as $i => $v) {
	$MemColor = $_GeneralSettings["MemColors"][$i];
	?>
	#MemBox #<?=$v?> a {
		color:<?=$MemColor?>!important
	}
	
	#MemBox #<?=$v?> .Title {
		color:<?=$MemColor?>!important
	}
	<?php
}
?>
</style>
<div class="box" id="MemBox">
	<style>
	#MemIMG {
		border-bottom:1px solid #4E93C3
	}
	</style>
	<img src="MemHd_2.png" title="Buy Membership" width="898" id="MemIMG">
	<?php
	foreach($MemTiers as $i => $v) {
		?>
		
		<div id="<?=$v?>">
			<h4 class="Title"><?=$v?></h4>
			<ul>
				<li><a onclick="<?=$v?>_1m()">$<?=$_GeneralSettings["MemPrices"][$i][0]?> - 1 Month</a></li>
				<li><a onclick="<?=$v?>_3m()">$<?=$_GeneralSettings["MemPrices"][$i][1]?> - 3 Months</a></li>
				<li><a onclick="<?=$v?>_6m()">$<?=$_GeneralSettings["MemPrices"][$i][2]?> - 6 Months</a></li>
				<li><a onclick="<?=$v?>_12m()">$<?=$_GeneralSettings["MemPrices"][$i][3]?> - 12 Month</a></li>
			</ul>
		</div>
		<script>
		function <?=$v?>_1m() { $("#<?=$v?>_1m").submit(); }
		function <?=$v?>_3m() { $("#<?=$v?>_3m").submit(); }
		function <?=$v?>_6m() { $("#<?=$v?>_6m").submit(); }
		function <?=$v?>_12m() { $("#<?=$v?>_12m").submit(); }
		</script>
		<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="<?=$v?>_1m">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="amount" value="<?=$_GeneralSettings["MemPrices"][$i][0]?>">
			<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/mem/notify/?pid=<?=$paymentID?>">
			<input type="hidden" name="return" value="https://www.brixus.net/payment/mem/return/">
			<input type="hidden" name="item_number" value="<?=$v?>Mem1M">
			<input type="hidden" name="item_name" value="1 Month of <?=$v?> Membership">
		</form>
		<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="<?=$v?>_3m">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="amount" value="<?=$_GeneralSettings["MemPrices"][$i][1]?>">
			<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/mem/notify/?pid=<?=$paymentID?>">
			<input type="hidden" name="return" value="https://www.brixus.net/payment/mem/return/">
			<input type="hidden" name="item_number" value="<?=$v?>Mem3M">
			<input type="hidden" name="item_name" value="3 Months of <?=$v?> Membership">
		</form>
		<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="<?=$v?>_6m">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="amount" value="<?=$_GeneralSettings["MemPrices"][$i][2]?>">
			<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/mem/notify/?pid=<?=$paymentID?>">
			<input type="hidden" name="return" value="https://www.brixus.net/payment/mem/return/">
			<input type="hidden" name="item_number" value="<?=$v?>Mem6M">
			<input type="hidden" name="item_name" value="6 Months of <?=$v?> Membership">
		</form>
		<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="<?=$v?>_12m">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="amount" value="<?=$_GeneralSettings["MemPrices"][$i][3]?>">
			<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/mem/notify/?pid=<?=$paymentID?>">
			<input type="hidden" name="return" value="https://www.brixus.net/payment/mem/return/">
			<input type="hidden" name="item_number" value="<?=$v?>Mem12M">
			<input type="hidden" name="item_name" value="12 Months of <?=$v?> Membership">
		</form>
		
		<?php
	}
	?>
	<h3>About</h3>
	<p>Premium membership gives you access to benefits inaccessible to users who use Brixus free.</p>
	<p>Please see the section below for details</p>
	<style>
	.ta th,.ta td {
		background: #FFF;
		text-align: center;
	}
	</style>
	<h3>Benefits</h3>
	<table width="878px" bgcolor="#AAAAAA" cellspacing="1" cellpadding="10" class="ta" style="margin-left:10px">
		<tr>
			<th width="20%">Benefit</th>
			<th width="20%">Free</th>
			<th width="20%">Ace</th>
			<th width="20%">Prime</th>
			<th width="20%">Grand</th>
		</tr>
		<tr>
			<th width="20%">Daily Coins</th>
			<td width="20%">10</td>
			<td width="20%">20</td>
			<td width="20%">50</td>
			<td width="20%">100</td>
		</tr>
		<tr>
			<th width="20%">Place Slots</th>
			<td width="20%">1</td>
			<td width="20%">5</td>
			<td width="20%">10</td>
			<td width="20%">20</td>
		</tr>
		<tr>
			<th width="20%">Badge</th>
			<td width="20%">No</td>
			<td width="60%" colspan="3">Yes</td>
		</tr>
		<tr>
			<th width="20%">Exclusive Item</th>
			<td width="20%">No</td>
			<td width="60%" colspan="3">Yes</td>
		</tr>
		<tr>
			<th width="20%">Create Groups</th>
			<td width="20%">Yes - 1</td>
			<td width="20%">Yes - 3</td>
			<td width="20%">Yes - 5</td>
			<td width="20%">Yes - 10</td>
		</tr>
	</table>
</div>
<div style="height:10px"></div>
<style>
#GetBucksBox {
	border-color:#477B44
}

#GetBucksBox a {
	color:#477B44!important
}
</style>
<div class="box" id="GetBucksBox">
	<style>
	#GetBucksIMG {
		border-bottom:1px solid #477B44
	}
	</style>
	<img src="GetBucksHd.png" title="Get Bucks" width="898" id="GetBucksIMG">
	<ul>
		<li><a onclick="_30b()">$2.99 - 30 Bucks</a></li>
		<li><a onclick="_50b()">$4.99 - 50 Bucks</a></li>
		<li><a onclick="_100b()">$6.99 - 100 Bucks</a></li>
	</ul>
	<h3>About</h3>
	<p>Bucks are one of the virtual currencies used on Brixus.</p>
	<p>You can use these Bucks to buy accessories for your avatar, create groups, buy gamepasses, and much more.</p>
</div>
<script>
function _30b() { $("#30B").submit(); }
function _50b() { $("#50B").submit(); }
function _100b() { $("#100B").submit(); }
</script>
<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="30B">
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="amount" value="2.99">
	<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/b/notify/?pid=<?=$paymentID?>">
	<input type="hidden" name="return" value="https://www.brixus.net/payment/b/return/">
	<input type="hidden" name="item_number" value="30B">
	<input type="hidden" name="item_name" value="30 Bucks">
</form>
<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="50B">
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="amount" value="4.99">
	<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/b/notify/?pid=<?=$paymentID?>">
	<input type="hidden" name="return" value="https://www.brixus.net/payment/b/return/">
	<input type="hidden" name="item_number" value="50B">
	<input type="hidden" name="item_name" value="50 Bucks">
</form>
<form name="_xclick" action="<?=$_SiteSettings["PayPal"]["URL"]?>" method="post" id="100B">
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="<?=$_SiteSettings["PayPal"]["Email"]?>">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="amount" value="6.99">
	<input type="hidden" name="notify_url" value="https://www.brixus.net/payment/b/notify/?pid=<?=$paymentID?>">
	<input type="hidden" name="return" value="https://www.brixus.net/payment/b/return/">
	<input type="hidden" name="item_number" value="100B">
	<input type="hidden" name="item_name" value="100 Bucks">
</form>
<?php include("site/footer.php"); ?>