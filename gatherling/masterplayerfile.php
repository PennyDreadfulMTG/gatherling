<?php

use Gatherling\Models\Database;

header('Content-type: text/plain');
require_once 'lib.php';
$db = Database::getConnection();
$result = $db->query('SELECT name FROM players ORDER BY name');
$n = 10000001;
while ($row = $result->fetch_assoc()) {
    if (rtrim($row['name']) != '') {
        printf("%08d\tx\t%s\tUS\n", $n, $row['name']);
        $n++;
    }
}
$result->close();
