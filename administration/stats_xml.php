<?php

/* (C) Websplosion LTD., 2001-2014

  IMPORTANT: This is a commercial software product
  and any kind of using it must agree to the Websplosion's license agreement.
  It can be found at http://www.chameleonsocial.com/license.doc

  This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

header("Content-type: text/xml; charset=UTF-8");
header('Cache-Control: no-cache, must-revalidate');
// FIX for flash chart
echo pack("C3", 0xef, 0xbb, 0xbf);
//file_put_contents('post.txt', print_r($_POST, 1));
//file_put_contents('get.txt', print_r($_GET, 1));

$columns = DB::column('SHOW FIELDS FROM stats');
unset($columns[0]);
unset($columns[1]);

$param = explode('|', param('c'));
if (count($param) == 3) {
    $year = intval($param[1]);
    $month = intval($param[2]);
    $param = $param[0];
} else {
    $year = 0;
    $month = 0;
    $param = 'logins';
}

if ($year == 0) {
    $year = date('Y');
}
if ($month == 0) {
    $month = date('n');
}

if (!in_array($param, $columns)) {
    $param = 'logins';
}

$now = mktime(0, 0, 0, $month, 1, $year);


$colors = explode('|', '6a7889|8d9aab|c1e010|aaaaaa|ef8770');


$ors = array();
$ors[] = array('id' => '0', 'title' => 'Overall visitors');
$ors = array_merge($ors, DB::all('SELECT * FROM const_orientation'));


$stats = array();
$r = DB::query('SELECT *, DAYOFMONTH(date) as day FROM stats WHERE'
                . ' MONTH(date) = "' . date('m', $now) . '" AND YEAR(date) = "' . date('Y', $now) . '"');
while ($row = DB::fetch_row()) {
    $stats[$row['day']][$row['orientation']] = $row[$param];
}


$cats = array();
$csets = array();
$sets = array();

$today = (date('Ym') == date('Ym', $now) ? date('j') : date('t', $now));
$month = date('F', $now);
for ($day = 1; $day <= $today; $day++) {
    $cats[] = "<category name='" . $day . " " . l($month) . "' />";
    foreach ($ors as $orn => $or) {
        $csets[$orn][] = "<set value='" . (isset($stats[$day][$or['id']]) ? $stats[$day][$or['id']] : '0') . "' />";
    }
}

foreach ($ors as $orn => $or) {
    $color = $colors[$orn % 5];
    $sets[] = "<dataset seriesName='" . he(l($or['title'])) . "' color='$color' anchorBorderColor='$color' anchorBgColor='$color'>\n"
            . implode("\n", $csets[$orn]) . "\n"
            . "</dataset>";
}

echo "<graph caption='' subcaption='' hovercapbg='ffffff' hovercapborder='aaaaaa' formatNumberScale='0' decimalPrecision='0' showvalues='0' numdivlines='3' numVdivlines='0' rotateNames='1'>\n";
echo "<categories>\n" . implode("\n", $cats) . "</categories>\n";
echo implode("\n", $sets) . "\n";
echo "</graph>";

include("../_include/core/administration_close.php");
