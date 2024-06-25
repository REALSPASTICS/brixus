<?php
include 'site/config.php';

$usersQuery = mysqli_query($conn, "SELECT * FROM `users` ORDER BY `coins` DESC, `bucks` DESC, `id` DESC");
?>
<h1>no sharies plzz</h1>
<ul>
<?php
$COUNT = 0;
while($user = mysqli_fetch_assoc($usersQuery)) {
	$COUNT++;
	echo '<li>
	<span><strong>'.$COUNT.'. '.$user['username'].'</strong></span>
	<br>
	<span>coins: '.$user['coins'].'</span>
	<br>
	<span>bucks: '.$user['bucks'].'</span>
</li>';
}
?>
</ul>