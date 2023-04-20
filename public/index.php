<?php

namespace openSB;
require_once dirname(__DIR__) . '/private/class/common.php';

// currently selects all uploaded videos, should turn it into all featured only
$videoData = $sql->query("SELECT $userfields $videofields, v.category_id FROM videos v JOIN users u ON v.author = u.id ORDER BY RAND() LIMIT 16");
// moved total subscribers to layout.php
if ($log) {
    $query = implode(', ', array_column($sql->fetchArray($sql->query("SELECT user FROM subscriptions WHERE id = ?", [$userdata['id']])), 'user'));
    if ($query != null) {
        $subscriptionVideos = $sql->query("SELECT $userfields $videofields FROM videos v JOIN users u ON v.author = u.id WHERE v.author IN($query) ORDER BY v.id DESC LIMIT 4");
    } else {
        $subscriptionVideos = null;
    }
    $totalViews = $sql->result("SELECT SUM(views) FROM videos WHERE author = ?", [$userdata['id']]);
    $creationDate = $sql->result("SELECT joined FROM users WHERE id = ?", [$userdata['id']]);
} else {
    $subscriptionVideos = null;
    $totalViews = 0;
    $creationDate = 0;
}
$twig = twigloader();

echo $twig->render('index.twig', [
    'videos' => $videoData,
    'subscriptionVideos' => $subscriptionVideos,
    'totalViews' => $totalViews,
    'creationDate' => $creationDate,
    'updated' => (isset($_GET['updated']) ? true : false),
]);
