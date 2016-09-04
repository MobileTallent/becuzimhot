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
        global $g;

        $html->setvar('year_title', date('Y'));
        $html->setvar('month_title', l(date('F')) . ' ' . date('Y'));
        $html->setvar('day_title', date('j') . ' ' . l(date('F')) . ' ' . date('Y'));
        $fDate = DB::result('SELECT date FROM stats ORDER BY date LIMIT 1');

        if ($fDate != '') {
            $fDate = (strlen($fDate) == 8
                        ? array(substr($fDate, 0, 4), substr($fDate, 4, 2), substr($fDate, 6, 2))
                        : array(substr($fDate, 0, 4), substr($fDate, 5, 2), substr($fDate, 8, 2)));
            $html->setvar('since_title', intval($fDate[2]) . ' '
                                       . l(date('F', mktime(0, 0, 0, intval($fDate[1])))) . ' ' . intval($fDate[0]));


            $columns = DB::column('SHOW FIELDS FROM stats');
            unset($columns[0]);
            unset($columns[1]);
            
            $columns=unsetDisabledStats($columns);   
            $cols = array();
            foreach ($columns as $col) {
                $cols[] = 'sum(' . $col . ') as ' . $col . '';
            }
            $cols = implode(', ', $cols);

            $sqlDay = "SELECT * FROM stats"
                 . " WHERE orientation = 0 AND date = '" . date('Y-m-d') . "'";
            $sqlMonth = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0 AND MONTH(date) = '" . date('m') . "' AND YEAR(date) = '" . date('Y') . "'";
            $sqlYear = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0 AND YEAR(date) = '" . date('Y') . "'";
            $sqlTotal = "SELECT $cols FROM stats"
                 . " WHERE orientation = 0";

            $day = DB::row($sqlDay);
            $month = DB::row($sqlMonth);
            $year = DB::row($sqlYear);
            $total = DB::row($sqlTotal);

            $i = 1;
            foreach ($columns as $col) {
                if ($i % 2 == 0) {
                    $html->setvar("class", 'color');
                    $html->setvar("decl", '_l');
                    $html->setvar("decr", '_r');
                } else {
                    $html->setvar("class", '');
                    $html->setvar("decl", '');
                    $html->setvar("decr", '');
                }
                $i++;

                $html->setvar('name', lr($col));
                $html->setvar('day', isset($day[$col]) ? $day[$col] : '0');
                $html->setvar('month', isset($month[$col]) ? $month[$col] : '0');
                $html->setvar('year', isset($year[$col]) ? $year[$col] : '0');
                $html->setvar('total', isset($total[$col]) ? $total[$col] : '0');

                $html->parse('row', true);
            }

            CStatsTools::parseChart($html);

            $html->parse('stats', true);
        } else {
            $html->parse('nostats', true);
        }

		parent::parseBlock($html);
	}
}

$page = new CAdminLogin("", $g['tmpl']['dir_tmpl_administration'] . "home.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

include("../_include/core/administration_close.php");

?>
