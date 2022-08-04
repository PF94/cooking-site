<?php

namespace squareBracket;

// fixme: change video-related variables to more generic variables

use Exception;

require dirname(__DIR__) . '/private/class/common.php';

$id = ($_GET['v'] ?? null);
$ip = ($_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']));

$videoData = fetch("SELECT $userfields v.* FROM videos v JOIN users u ON v.author = u.id WHERE v.video_id = ?", [$id]);

if (!$videoData) error('404', __("The video you were looking for cannot be found."));

$query = '';
$count = 0;
$commentData = query("SELECT $userfields c.comment_id, c.id, c.comment, c.author, c.date, c.deleted, (SELECT COUNT(reply_to) FROM comments WHERE reply_to = c.comment_id) AS replycount FROM comments c JOIN users u ON c.author = u.id WHERE c.id = ? ORDER BY c.date DESC", [$id]);

$relatedVideosData = query("SELECT $userfields $videofields FROM videos v JOIN users u ON v.author = u.id WHERE NOT v.video_id = ? ORDER BY RAND() LIMIT 6", [$id]);

$totalLikes = result("SELECT COUNT(rating) FROM rating WHERE video=? AND rating=1", [$videoData['id']]);
$totalDislikes = result("SELECT COUNT(rating) FROM rating WHERE video=? AND rating=0", [$videoData['id']]);
$combinedRatings = $totalDislikes + $totalLikes;

$allRatings = calculateRatio($totalDislikes, $totalLikes, $combinedRatings);

$allVideos = result("SELECT COUNT(id) FROM videos WHERE author=?", [$videoData['u_id']]);

if (isset($userdata['name'])) {
    $rating = result("SELECT rating FROM rating WHERE video=? AND user=?", [$videoData['id'], $userdata['id']]);
    $subscribed = result("SELECT COUNT(user) FROM subscriptions WHERE id=? AND user=?", [$userdata['id'], $videoData['author']]);
} else {
    $rating = 2;
    $subscribed = 0;
}
if (fetch("SELECT COUNT(video_id) FROM views WHERE video_id=? AND user=?", [$videoData['video_id'], crypt($ip, $ip)])['COUNT(video_id)'] < 1) {
    query("INSERT INTO views (video_id, user) VALUES (?,?)",
        [$videoData['video_id'], crypt($ip, $ip)]);
}

$subCount = fetch("SELECT COUNT(user) FROM subscriptions WHERE user=?", [$videoData['author']])['COUNT(user)'];
$commentCount = fetch("SELECT COUNT(id) FROM comments WHERE id=?", [$videoData['video_id']])['COUNT(id)']; //broken,, fix -gr 11/3/2021
$viewCount = fetch("SELECT COUNT(video_id) FROM views WHERE video_id=?", [$videoData['video_id']])['COUNT(video_id)'];

// scrapped randley layout had dumb code regarding if the video was "modern" (converted to mp4) or "legacy"
// (converted to dash), so we need to do this. actually i could do this in twig but whatever. -grkb 7/10/2022
if ($videoData['post_type'] == 0 or $videoData['post_type'] == 1) {
    $postType = "video";
} elseif ($videoData['post_type'] == 2) {
    $postType = "artwork";
} else {
    throw new Exception("Post type is not supported.");
}

$previousRecentView = result("SELECT most_recent_view from videos WHERE video_id = ?", [$id]);
$currentTime = time();

query("UPDATE videos SET most_recent_view = ? WHERE video_id = ?", [$currentTime, $id]);

$twig = twigloader();
echo $twig->render('watch.twig', [
    'video' => $videoData,
    'related_videos' => $relatedVideosData,
    'comments' => $commentData,
    'total_likes' => $totalLikes,
    'total_dislikes' => $totalDislikes,
    'total_rating' => $combinedRatings,
    'rating' => $rating,
    'subscribed' => $subscribed,
    'subCount' => $subCount,
    'comCount' => $commentCount,
    'viewCount' => $viewCount,
    'videoRatio' => $allRatings,
    'recentView' => $previousRecentView,
    'allVideos' => $allVideos,
    'postType' => $postType,
]);