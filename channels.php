<?php
require('lib/common.php');

$min = ((isset($_GET['page']) ? $_GET['page'] : 1) - 1) * 20;
$max = $min + 20;

// currently selects all registered users (channels)
$userData = query("SELECT username, lastview FROM users ORDER BY lastview DESC");

$pageCount = ceil(fetch("SELECT COUNT(*) FROM users")['COUNT(*)'] / 20);
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/channels.php?page=';

$twig = twigloader();

echo $twig->render('channels.twig', [
	'users' => $userData,
	'currentPage' => (isset($_GET['page']) ? $_GET['page'] : 1),
	'pageCount' => $pageCount,
	'url' => $url
]);
