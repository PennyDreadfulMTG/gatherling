<?php

use Gatherling\Models\Database;

require_once 'lib.php';

$series = $_GET['series'];
$db = Database::getConnection();
$stmt = $db->prepare('SELECT logo, imgtype, imgsize FROM series WHERE name = ?');
$stmt->bind_param('s', $series);
$stmt->execute();
$stmt->bind_result($content, $type, $size);
$stmt->fetch();
$stmt->close();

// Send a transparent 1x1 png if there's no logo in the db rather than showing a broken image.
if (!$content) {
    $type = 'image/png';
    $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
}

header("Content-length: $size");
header("Content-type: $type");
echo $content;
