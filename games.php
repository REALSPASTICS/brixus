<?php
include("site/config.php");
$title = "Games - Brixus";
include("site/header.php");
?>
<div style="width:200px;float:left;">
	<h1>Games</h1>
</div>
<div style="width:700px;float:right;text-align:right;">
	<form method="GET" style="vertical-align:middle;">
		<input type="text" name="search" placeholder="Search for a game title..." style="vertical-align:middle;">
		<select name="sort" style="vertical-align:middle;">
			<option value="ma">Most active</option>
			<option value="mv">Most visits</option>
			<option value="nf">Newest first</option>
			<option value="of">Oldest first</option>
		</select>
		<input type="submit" value="Search" style="vertical-align:middle;">
	</form>
</div>
<div style="clear:both;"></div>
<div class="sect"></div>
<span>No games found</span>
<?php
include("site/footer.php");
?>