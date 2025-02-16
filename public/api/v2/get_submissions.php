<?php

namespace openSB\APIv2;
chdir('../../');
$rawOutputRequired = true;
require_once dirname(__DIR__) . '/../../private/class/common.php';

header('Content-Type: application/json');

$limit = (isset($_GET['limit']) ? $_GET['limit'] : 10);
$offset = (isset($_GET['offset']) ? $_GET['offset'] : 0);

$sumbissionCount = $sql->result("SELECT COUNT(1) FROM videos v JOIN users u ON v.author = u.id");
$submissions = $sql->query("SELECT $userfields v.* FROM videos v JOIN users u ON v.author = u.id ORDER BY v.id DESC LIMIT ? OFFSET ?", [$limit, $offset]);

$apiOutput = [];
foreach ($submissions as $submission) {
    $apiOutput[] =
        [
            'id' => $submission['video_id'],
            'title' => $submission['title'],
            'description' => $submission['description'],
            'time' => $submission['time'],
            'views' => $submission['views'],
            'file' => \openSB\Videos::getVideoFile($submission),
            'tags' => $submission['tags'],
            'author' => [
                'id' => $submission['u_id'],
                'name' => $submission['u_name'],
                'color' => $submission['u_customcolor'],
            ]
        ];
}

echo json_encode(array('submissions' => $apiOutput, 'count' => $sumbissionCount));
