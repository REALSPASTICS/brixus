<?php
include("site/config.php");

$title = "Credits - Brixus";

include("site/header.php");
?>
<style>
.creditSection {
	margin: 10px 0;
}

.creditList {
	padding-left: 30px;
}

.creditListItem {
	margin: 5px 0;
}

h4 {
	padding-left:0!important;
}

.creditListSecondary {
	padding-left: 10px;
}
</style>
<div class="box" style="padding:50px">
	<h1>Credits</h1>
	<div class="seperator" style="margin-bottom:10px"></div>
	<div class="creditSection">
		<h3>Client Developers</h3>
		<div class="creditList">
			<div class="creditListItem"><a href="/user.php?id=78">syntaqs</a></div>
		</div>
	</div>
	<div class="creditSection">
		<h3>Site Developers</h3>
		<div class="creditList">
			<div class="creditListItem"><a href="/user.php?id=1">jaarva</a></div>
			<div class="creditListItem">smallbasketdude</div>
		</div>
	</div>
	<div class="creditSection">
		<h3>Asset Creators</h3>
		<div class="creditList">
			<div class="creditListItem"><a href="/user.php?id=138">Gathalo</a></div>
			<div class="creditListItem"><a href="/user.php?id=54">Trojan</a></div>
		</div>
	</div>
	<div class="creditSection">
		<h3>Administrators</h3>
		<div class="creditList">
			<div class="creditListItem"><a href="/user.php?id=3">Fetus</a></div>
			<div class="creditListItem"><a href="/user.php?id=2">treeified09</a></div>
		</div>
	</div>
	<div class="creditSection">
		<h3>Moderators</h3>
		<div class="creditList">
			<div class="creditListItem"><a href="/user.php?id=138">Gathalo</a></div>
			<div class="creditListItem"><a href="/user.php?id=27">The Master Of Pie</a></div>
			<div class="creditListItem"><a href="/user.php?id=46">trliy</a></div>
			<div class="creditListItem"><a href="/user.php?id=54">Trojan</a></div>
		</div>
	</div>
	<div class="creditSection">
		<h3>Code Libraries</h3>
		<div class="creditList">
			<h4>Website</h4>
			<div class="creditListSecondary">
				<div class="creditListItem"><a href="https://github.com/zunqco/obfuscator">PHP javascript obfuscator</a></div>
				<div class="creditListItem"><a href="https://github.com/mashiox/BBCode-Parser">BBCode-Parser</a></div>
				<div class="creditListItem"><a href="https://github.com/tedious/jshrink">JShrink</a></div>
			</div>
			<h4>Client</h4>
			<div class="creditListSecondary">
				<i>TBA</i>
			</div>
		</div>
	</div>
</div>


<?php include 'site/footer.php';