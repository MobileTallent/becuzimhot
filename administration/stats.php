<?php
/* (C) Websplosion LTD., 2001-2014

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");

class CAdminLogin extends CHtmlBlock
{

	function parseBlock(&$html)
	{
        global $l;
        $yearActive = intval(param('y', date('Y')));
        $monthActive = intval(param('m', date('m')));
        $time = mktime(0, 0, 0, $monthActive, 1, $yearActive);
        $now = mktime(0, 0, 0, date('m'), 1, date('y'));

        $html->setvar('yearActive', $yearActive);
        $html->setvar('monthActive', $monthActive);

        $columns = DB::column('SHOW FIELDS FROM stats');
        unset($columns[0]);
        unset($columns[1]);

        $columns=unsetDisabledStats($columns);

        $param = param('c', 'logins');
        if (!in_array($param, $columns)) {
            $param = 'logins';
        }
        $html->setvar('columnActive', $param);

        CStatsTools::parseChart($html, $param, $monthActive, $yearActive);

        foreach ($columns as $col) {
            if ($col == $param) {
                $html->setvar('active', 'active');
            } else {
                $html->setvar('active', '');
            }
            $html->setvar('column', $col);
            $html->setvar('column_name', lr($col));
            $html->parse('column', true);
        }


        $fDate = DB::result('SELECT date FROM stats ORDER BY date LIMIT 1');

        if ($fDate != '') {
            list($sYear, $sMonth, $sDay) = (strlen($fDate) == 8
                                             ? array(substr($fDate, 0, 4), substr($fDate, 4, 2), substr($fDate, 6, 2))
                                             : array(substr($fDate, 0, 4), substr($fDate, 5, 2), substr($fDate, 8, 2)));
            $nYear = date('Y');
            $nMonth = date('m');
            $ky=0;
            for ($y = $nYear; $y >= $sYear; $y--) {
              if($ky==0) {
                $html->setblockvar('month', '');
                $html->setvar('year', $y);

                $ysMonth = 1;
                $ynMonth = 12;
                if ($y == $sYear) {
                    $ysMonth = $sMonth;
                }
                if ($y == $nYear) {
                    $ynMonth = $nMonth;
                }
                for ($m = $ysMonth; $m <= $ynMonth; $m++) {
                    $html->setvar('month', $m);
                    $html->setvar('month_name', l(date('F', mktime(0, 0, 0, $m))));
                    if ($m != $ynMonth) {
                        $html->parse('month_nl', false);
                    } else {
                        $html->setblockvar("month_nl", "");
                    }
                    if ($y == $yearActive and $m == $monthActive) {
                        $html->parse('month_active', false);
                        $html->setblockvar("month_link", "");
                    } else {
                        $html->parse('month_link', false);
                        $html->setblockvar("month_active", "");
                    }
                    $html->parse('month', true);
                }
                $html->setblockvar("stats", "");
                $html->parse('year', false);
                if($y==$yearActive) { $ky=1; $y++;};
               } else {
                $ndays=date('t',$time);
                DB::query("select date, orientation, ".$param." from stats WHERE MONTH(date) = " . date('m', $time) . " AND YEAR(date) = " . date('Y', $time));
                $ors[] = array('id' => '0', 'title' => l('overall'));
                $ors = array_merge($ors, DB::all('SELECT * FROM const_orientation'));

               for($tt=1;$tt<=2;$tt++)
                {
                $html->setblockvar("orientation", "");
                $html->setblockvar("day_th", "");
                for($i=1+($tt-1)*intval($ndays/2)+($tt-1)*($ndays%2);$i<=intval($ndays/2*$tt)+(2-$tt)*($ndays%2);$i++)
                {
                    $class = ($i==intval($ndays/2*$tt)+(2-$tt)*($ndays%2))?"last":"";
                    $html->setvar("thclass",$class);
                    $html->setvar("day",$i);
                    $html->parse("day_th",true);
                }

                $fday=0;
                DB::query("select DAYOFMONTH(date) as day, orientation, ".$param." from stats WHERE MONTH(date) = " . date('m', $time) . " AND YEAR(date) = " . date('Y', $time));
                while ($row = DB::fetch_row()) {
                  if($fday==0) $fday=$row['day'];
                  $stats[$row['day']][$row['orientation']] = $row[$param];
                }
	        foreach ($ors as $orn => $or)
                {

                    DB::query("select date, orientation, ".$param." from stats WHERE MONTH(date) = " . date('m', $time) . " AND YEAR(date) = " . date('Y', $time)." AND ORIENTATION = ".$orn);
                    $html->setblockvar("day_td", "");

	            for($i=1+($tt-1)*intval($ndays/2)+($tt-1)*($ndays%2);$i<=$ndays/2*$tt+(2-$tt)*($ndays%2);$i++)
                    {
                        if((mktime(0, 0, 0, date('m'), date('d'), date('y'))>=mktime(0, 0, 0, $monthActive, $i, $yearActive))&&($i>=$fday))
                        {
                             $html->setvar("value", isset($stats[$i][$orn]) ? $stats[$i][$orn] : "---" );
                        } else {
                             $html->setvar("value","---");
                        }
                        $class = ($i==intval($ndays/2*$tt)+(2-$tt)*($ndays%2))?"last":"";
                        $html->setvar("tdclass",$class);
                        $html->parse("day_td",true);

                    }
                    if(($orn+1)%2==0)
                    {
                         $html->setvar("trclass","color");
                         $html->setvar("decl", '_l');
                         $html->setvar("decr", '_r');

                    } else {
                         $html->setvar("trclass","");
                         $html->setvar("decl", '');
                         $html->setvar("decr", '');

                    }
                    $html->setvar("orientation",l($or['title']));
	            $html->parse("orientation",true);
	        }

                $html->parse("table",true);
                }
                $html->setblockvar("year", "");
                $html->parse('stats', true);
                $ky=0;
               }
               $html->parse('block',true);

            }
        } else {
            $html->parse('nostats', true);
        }
        parent::parseBlock($html);
	}
}

$page = new CAdminLogin("", $g['tmpl']['dir_tmpl_administration'] . "stats.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
