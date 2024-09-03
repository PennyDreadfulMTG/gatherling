<?php

use Gatherling\Models\Database;

require_once 'lib.php';

header('Content-Type: text/calendar');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//pauperkrew/gatherling//EN\r\n";
echo "X-WR-CALNAME;VALUE=TEXT:Gatherling Tournament Schedule\r\n";
echo "X-WR-CALDESC;VALUE=TEXT:Magic Player Run Events on Magic: The Gathering Online\r\n";
echo "X-WR-RELCALID:480fC555:F784:4C19:9D38:A65F931880AB\r\n";
echo "X-WR-TIMEZONE:US/Eastern\r\n";
echo "BEGIN:VTIMEZONE\r\n";
echo "TZID:US/Eastern\r\n";
echo "X-LIC-LOCATION:US/Eastern\r\n";
echo "BEGIN:STANDARD\r\n";
echo "TZOFFSETFROM:-0400\r\n";
echo "TZOFFSETTO:-0500\r\n";
echo "TZNAME:EST\r\n";
echo "DTSTART:19700308T020000\r\n";
echo "END:STANDARD\r\n";
echo "BEGIN:DAYLIGHT\r\n";
echo "TZOFFSETFROM:-0500\r\n";
echo "TZOFFSETTO:-0400\r\n";
echo "TZNAME:EDT\r\n";
echo "DTSTART:19701101T020000\r\n";
echo "END:DAYLIGHT\r\n";
echo "END:VTIMEZONE\r\n";

function printEventIcal($eventstart, $eventname, $eventlink = '')
{
    $timeStartFormatted = date('Ymd\THis', $eventstart);
    // All events will last for 5 hours for now.
    // TODO: Make this scale based on number of rounds.
    $timeEndFormatted = date('Ymd\THis', $eventstart + (60 * 60 * 5));
    echo "BEGIN:VEVENT\r\n";
    echo "DTSTART:{$timeStartFormatted}\r\n";
    echo "DTEND:{$timeEndFormatted}\r\n";
    echo "SUMMARY:{$eventname}\r\n";
    if (strcmp($eventlink, '') != 0) {
        echo "URL:{$eventlink}\r\n";
    }
    echo "END:VEVENT\r\n";
}

// The last 50 ones.

$db = Database::getConnection();

$result = $db->query('SELECT UNIX_TIMESTAMP(DATE_SUB(start, INTERVAL 30 MINUTE)) as d, format, series, name, threadurl FROM events WHERE start < NOW() ORDER BY start DESC LIMIT 50');

if (!$result) {
    throw new Exception($db->error, 1);
}
while ($row = $result->fetch_assoc()) {
    printEventIcal($row['d'], $row['name'], $row['threadurl']);
}

$result->close();

// And all of the ones that haven't happened yet.

$result = $db->query('SELECT UNIX_TIMESTAMP(start) as d, format, series, name, threadurl FROM events WHERE start > NOW() ORDER BY start ASC');

if (!$result) {
    throw new Exception($db->error, 1);
}
while ($row = $result->fetch_assoc()) {
    printEventIcal($row['d'], $row['name'], $row['threadurl']);
}

?>
END:VCALENDAR
