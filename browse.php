<?php
include 'site/config.php';

if (!isset($_GET['page']) || !isset($_GET['query']) || !isset($_GET["sort"])) {
	header('Location: /browse?page=1&query=&sort=lo');
	exit;
}

$itemsPerPage = 10;
$page = mysqli_escape_string($conn, intval($_GET['page'])) ?? 0;
$offset = ($page - 1) * $itemsPerPage;

$search = mysqli_escape_string($conn, $_GET['query']);

if($_GET["sort"] == "lo") {
	$sortvar = "`last_online` DESC, `id` DESC";
}

if($_GET["sort"] == "nf") {
	$sortvar = "`join_date` DESC";
}

if($_GET["sort"] == "of") {
	$sortvar = "`join_date` ASC";
}
	

$userQuery = mysqli_query($conn, "SELECT `username`, `blurb`, `avatar`, `last_online`, `id` FROM `users` WHERE `username` LIKE '%$search%' AND `id`>0 ORDER BY $sortvar LIMIT $offset, $itemsPerPage");


$userCountQuery = mysqli_query($conn, "SELECT COUNT(`id`) AS `count` FROM `users` WHERE `username` LIKE '%$search%'");
				$userCount = mysqli_fetch_assoc($userCountQuery)['count'];
				
				$pageCount = ceil($userCount / $itemsPerPage);
				
	if($page > $pageCount && $pageCount != 0) {
		//exit("not bossman");
		exit;
	}

$title = 'Browse - Brixus';

include 'site/header.php';


?>
		<div class="box">
			<div class="padded">
				<form method="GET" id="searchForm">
					<input type="hidden" name="page" value="1">
					<center>
						<input type="text" name="query" placeholder="Search for a user..." value="<?php echo htmlspecialchars(@$_GET['query'], ENT_QUOTES); ?>" style="width:250px" id="searchBox">
						<span>Sort:</span>
						<select name="sort">
							<option value="lo" <?php if($_GET["sort"] == "lo") { echo "selected"; } ?>>Last online</option>
							<option value="nf" <?php if($_GET["sort"] == "nf") { echo "selected"; } ?>>Newest first</option>
							<option value="of" <?php if($_GET["sort"] == "of") { echo "selected"; } ?>>Oldest first</option>
						</select>
						<input type="submit" value="Search" id="searchSubmit">
						<input type="button" onclick="window.location = '/browse'" value="Reset">
					</center>
				</form>
			</div>
		</div>
		<div class="box" style="margin-top:10px">
			<div class="padded">
				<?php
				if(mysqli_num_rows($userQuery) > 0) {
				?>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse">
					<tbody>
						<tr>
							<th width="15%">Avatar</th>
							<th width="70%">User</th>
							<th width="15%">Status</th>
						</tr>
						<tr height="10"></tr>
						<tr><td colspan="3"><div class="seperator"></div></td></tr>
						<tr height="10"></tr>
						<?php
						
						while ($user = mysqli_fetch_assoc($userQuery)) {
						?>
						<tr>
							<td width="15%">
								<a href="/user?id=<?php echo $user['id']; ?>"><img src="<?php echo $user['avatar']; ?>" title="<?php echo $user['username']; ?>" width="100%"></a>
							</td>
							<td width="70%">
								<center>
									<a href="/user?id=<?php echo $user['id']; ?>"><?php echo $user['username']; ?></a>
									<div style="color:">
										<?php echo nl2br(htmlspecialchars(substr($user['blurb'], 0, 250), ENT_QUOTES),false); if (strlen($user['blurb']) > 250) { echo '...'; } ?>
									</div>
								</center>
							</td>
							<td width="15%">
								<center><?php if (time() - strtotime($user['last_online']) < 900) { echo '<span style="color:#0a0;">Online</span>'; } else { echo '<span style="color:red">Offline</span>'; } ?></center>
							</td>
						</tr>
						<?php
						}
						?>
					</tbody>
				</table>
				<?php
				
				
				for ($i = 1; $i <= $pageCount; $i++) {
					echo "<a href='/browse?page=$i&query={$_GET['query']}&sort=$_GET[sort]' style='padding:0 3px'>$i</a>";
				}
				?>
				<?php
				} else {
					echo '<center>No results found</center>';
				}
				?>
			</div>
		</div>
<?php
include 'site/footer.php';
?>