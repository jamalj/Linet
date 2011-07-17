﻿<?PHP
//M:דו"ח רווח והפסד
/*
 | Profit & Loss report for Freelance accounting system
 | Written by Ori Idan July 2009
 */
if(!isset($module)) {
	header('Content-type: text/html;charset=UTF-8');

	include('config.inc.php');
	include('func.inc.php');

	$link = mysql_connect($host, $user, $pswd) or die("Could not connect to host $host");
	mysql_select_db($database) or die("Could not select database: $database");


	$prefix = isset($_GET['prefix']) ? $_GET['prefix'] : $_COOKIE['prefix'];
	$reptitle = _("Profit and loss report");
	include('printhead.inc.php');
	print $header;
	
}
else {
	/* open window script */
	print "<script type=\"text/javascript\">\n";
	print "function PrintWin(url) {\n";
//	print "\talert(url);\n";
	print "\twindow.open(url, 'PrintWin', 'width=800,height=600,scrollbar=yes');\n";
	print "}\n";
	print "</script>\n";
}

global $prefix, $accountstbl, $companiestbl, $transactionstbl, $tranreptbl;

if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}


function GetAcctType($acct) {
	global $prefix, $accountstbl;

	$query = "SELECT type FROM $accountstbl WHERE num='$acct' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAcctType");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAccountName($val) {
	global $accountstbl;
	global $prefix;

	$query = "SELECT company FROM $accountstbl WHERE num='$val' AND prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	return $line[0];
}

function GetAcctTotal($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
//	print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return round($total, 0);
}

function GetGroupTotal($grp, $begin, $end) {
	global $accountstbl, $prefix;
	
	$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
//		if($num < 100)
//			continue;	/* system accounts are not counted as income or outcome */
//		print "Get total for: $num, ";
		$sub_total = GetAcctTotal($num, $begin, $end);
//		print "$sub_total<br>\n";
		$total += $sub_total;
	}
	return round($total, 0);
}

/* if(!isset($module)) {
	$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
	$result = DoQuery($query, "GetAccountName");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$str = $line[0];
	print "<h1>$str</h1>\n";	
} */

$step = isset($_GET['step']) ? $_GET['step'] : 0;

$reptitle = _("Profit and loss report");
if($step != 0) {
	print "<br><h1>$reptitle</h1>\n";
}

if($step == 0) {	/* Get date range */
	$edate = date("d-m-Y");
	list($d, $m, $y) = explode('-', $edate);
	$bdate = "1-1-$y";
	print "<br>\n";
	print "<div class=\"form righthalf1\">\n";
	print "<h3>$reptitle</h3>\n";
	print "<form name=\"dtrange\" action=\"\" method=\"get\">\n";
	print "<input type=\"hidden\" name=\"module\" value=\"profloss\">\n";
	print "<input type=\"hidden\" name=\"step\" value=\"1\">\n";
	print "<table cellpadding=\"5px\" cellspacing=\"5px\" class=\"formtbl\" width=\"100%\"><tr>\n";
	$l = _("From date");
	print "<td>בחר תאריך תחילה: </td>\n";
	print "<td><input type=\"text\" id=\"begindate\" name=\"begindate\" value=\"$bdate\" size=\"7\">\n";
?>
<script type="text/javascript">
		addDatePicker("#begindate","<?print "$bdate"; ?>");
</script>
<?PHP
	print "</td>\n";
	$l = _("To date");
	print "<td>$l: </td>\n";
	print "<td><input type=\"text\" id=\"enddate\" name=\"enddate\" value=\"$edate\" size=\"7\">\n";
?>
<script type="text/javascript">
		addDatePicker("#enddate","<?print "$edate"; ?>");
</script>
<?PHP
	print "</td>\n";
	print "</tr><tr><td colspan=\"4\">\n";
	$l = _("Show percent");
	print "&nbsp;<input type=\"checkbox\" name=\"percent\" checked>$l\n";
	$l = _("6111 clause");
	print "&nbsp;<input type=\"checkbox\" name=\"d6111\" checked>$l\n";
	print "</td><tr>\n";
	$l = _("Execute");
	print "<td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"$l\"></td>\n";
	print "</tr></table>\n";
	print "</form>\n";
	print "</div>\n";
	print "<div class=\"lefthalf1\">\n";
	ShowText('profloss');
	print "</div>\n";
}
if($step == 2) {
	$filename = "tmp/profloss.csv";
	$fd = fopen($filename, "w");
}
if($step >= 1) {
	$percent = isset($_GET['percent']) ? $_GET['percent'] : 0;
	$d6111 = isset($_GET['d6111']) ? $_GET['d6111'] : 0;
	$begindate = $_GET['begindate'];
	$enddate = $_GET['enddate'];
	$l = _("For period");
	print "<h2>$l: $begindate - $enddate</h2>\n";

	$bdate = FormatDate($begindate, "dmy", "mysql");
	$edate = FormatDate($enddate, "dmy", "mysql");
	if($step == 1) {
		if(!isset($module)) {
			print "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">\n";
			print "<table border=\"0\" cellpadding=\"3px\" class=\"printtbl\" align=\"center\">\n";
		}
		else
			print "<table border=\"0\" style=\"margin-right:2%\" cellpadding=\"3px\" class=\"hovertbl\">\n";
		if(!isset($module))
			print "<tr class=\"tblheadprt\" align=\"right\" style=\"border-top:1px solid\">\n";
		else
			print "<tr class=\"tblhead\" align=\"right\" style=\"border-top:1px solid;border-bottom:1px solid\">\n";
		$l = _("Account");
		print "<td style=\"width:15em\" align=\"right\" >$l</td>\n";
		$l = _("Sum NIS");
		print "<td style=\"width:7em\" align=\"right\" dir=\"ltr\">$l&nbsp;</td>\n";
		if($percent) {
			$l = _("Percent");
			print "<td align=\"right\" style=\"width:5em\">$l&nbsp;&nbsp;</td>\n";
		}
		if($d6111) {
			$l = _("6111 clause");
			print "<td align=\"right\">$l</td>\n";
		}
		if(!isset($module))
			print "</tr><tr class=\"tblheadprt\">\n";
		else
			print "</tr><tr class=\"tblhead\">\n";
		$l = _("Income");
		print "<td colspan=\"4\" align=\"right\"><u>$l</u></td>\n";
		print "</tr>\n";
	}
	else {
		$l1 = _("Account");
		$l2 = _("Sum");
		fwrite($fd, "$l1,l2");
		if($percent) {
			$l = _("Percent");
			fwrite($fd, ",$l");
		}
		if($d6111) {
			$l = _("6111 clause");
			fwrite($fd, ",$l");
		}
		fwrite($fd, "\n");
		$l = _("Income");
		fwrite($fd, "$l\n");
	}
	$t = INCOME;
	$total_income = GetGroupTotal($t, $bdate, $edate);

	/* Calculate total cost of sales 
	$open_stock = round(GetAcctTotal(OPEN_STOCK, $bdate, $edate), 2);
	if($open_stock < 0)
		$open_stock *= -1.0;
	$close_stock = round(GetAcctTotal(CLOSE_STOCK, $bdate, $edate), 2);
	if($close_stock < 0)
		$close_stock *= -1.0;
	$buy_stock = round(GetAcctTotal(BUY_STOCK, $bdate, $edate), 2);
	if($buy_stock < 0)
		$buy_stock *= -1.0;
	$sale_cost = $open_stock + $buy_stock - $close_stock;
	$total_income += $sale_cost;
*/

//	$total_income = round($total_income, 2);
	$query = "SELECT num,company,id6111 FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
	$result = DoQuery($query, "profloss.php");
	$tp = 0;
	$e = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$acct = $line['company'];
		$id6111 = $line['id6111'];
		$total = GetAcctTotal($num, $bdate, $edate);
		if($total == 0)
			continue;
		if($total_income > 0)
			$p = ($total * 100) / $total_income;
		$p = round($p, 2);
		$tp += $p;
		if($step == 1) {
			NewRow();
			$url = "/?module=acctdisp&amp;account=$num&amp;begin=$begindate&amp;end=$enddate";
			$tstr = number_format($total);
			if(isset($module))
				print "<td><a href=\"$url\">$acct</a></td><td style=\"text-align:right;direction:ltr\">$tstr</td>\n";
			else
				print "<td>$acct</td><td style=\"text-align:right;direction:ltr\">$tstr</td>\n";
			if($percent)
				print "<td>% $p</td>";
			if($d6111)
				print "<td>$id6111</td>\n";
			print "</tr>\n";
		}
		else {
			fwrite($fd, "$acct,$total");
			if($percent)
				fwrite($fd, ",$p");
			if($id6111)
				fwrite($fd, ",$id6111");
			fwrite($fd, "\n");
		}
	}
	$total_income += $sale_cost;
	if($step == 1) {
		$tstr = number_format($total_income);
		if(!isset($module))
			print "<tr class=\"sumlineprt\" align=\"right\">\n";
		else
			print "<tr class=\"sumline\" align=\"right\">\n";
		$l = _("Total");
		print "<td align=\"right\"><b>$l</b></td><td align=\"right\">$tstr</td>\n";
		if($percent)
			print "<td>% 100</td>\n";
		if($d6111)
			print "<td>&nbsp;</td>\n";
		print "</tr>\n";
	}
	else {
		$l = _("Total");
		fwrite($fd, "$l,$total_income");
		if($percent)
			fwrite($fd, ",100");
		fwrite($fd, "\n");
	}
	/* display cost of sales 
	if($sale_cost > 0) {
		if($step == 1) {
			if(!isset($module))
				print "<tr class=\"tblheadprt\" align=\"right\">\n";
			else
				print "<tr class=\"tblhead\" align=\"right\">\n";
			print "<td colspan=\"4\" align=\"right\"><u>עלות המכר</u></td></tr>\n";
			$tstr = number_format($open_stock);
			print "<tr><td align=\"right\">מלאי פתיחה</td><td align=\"right\">$tstr</td></tr>\n";
			$tstr = number_format($buy_stock);
			print "<tr class=\"otherline\" align=\"right\"><td >קניות</td><td>$tstr</td>";
			if($percent)
				print "<td>&nbsp;</td>";
			if($d6111)
				print "<td>&nbsp;</td>\n";
			print "</tr>\n";
			$tstr = number_format($close_stock);
			print "<tr align=\"right\"><td>מלאי סופי</td><td>$tstr</td>\n";
			if($percent)
				print "<td>&nbsp;</td>";
			print "</tr>\n";
		}
		else {
			fwrite($fd, "עלות המכר\n");
			fwrite($fd, "מלאי פתיחה,$open_stock\n");
			fwrite($fd, "קניות,$buy_stock\n");
			fwrite($fd, "מלאי סופי,$close_stock\n");
		}
		$p = ($sale_cost * 100) / $total_income;
		$p = round($p, 2);
		if($step == 1) {
			$tstr = number_format($sale_cost);
			print "<tr class=\"otherline\" align=\"right\"><td><b>סה\"כ עלות המכר</b></td><td>$tstr</td>";
			if($percent)
				print "<td>% $p</td>";
			if($d6111)
				print "<td>&nbsp;</td>\n";
			print "</tr>\n";
		}
		else
			fwrite($fd, "סה\"כ עלות המכר,$sale_cost\n");
		$t = $total_income - $sale_cost;
		$p = round(t * 100 / $total_income, 2);
		if($step == 1) {
			$tstr = number_format($t);
			if(!isset($module))
				print "<tr class=\"sumlineprt\" align=\"right\">\n";
			else
				print "<tr class=\"sumline\" align=\"right\">\n";
			print "<td><b>רווח גולמי</b></td><td>$tstr</td>\n";
			if($percent)
				print "<td>% $p</td>";
			if($d6111)
				print "<td>&nbsp;</td>\n";
			print "</tr>\n";
		}
		else {
			fwrite($fd, "רווח גולמי,$t");
			if($percent)
				fwrite($fd, ",$p");
			fwrite($fd, "\n");
		}
	} */
	if($step == 1) {
		if(!isset($module))
			print "<tr class=\"tblheadprt\" align=\"right\">\n";
		else
			print "<tr class=\"tblhead\" align=\"right\">\n";
		$l = _("Outcome");
		print "<td colspan=\"4\"><u>$l</u></td>\n";
	}
	else {
		$l = _("Outcome");
		fwrite($fd, "$l\n");
	}
	$t = OUTCOME;
	$total_outcome = GetGroupTotal($t, $bdate, $edate) * -1.0;
	$query = "SELECT num,company,id6111 FROM $accountstbl WHERE prefix='$prefix' AND type='$t'";
	$result = DoQuery($query, "profloss.php");
	$tp = 0;
	$e = 0;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$acct = $line['company'];
		$id6111 = $line['id6111'];
		$total = GetAcctTotal($num, $bdate, $edate) * -1.0;
		if($total == 0)
			continue;
		if($total_outcome > 0)
			$p = ($total * 100) / $total_outcome;
		$p = round($p, 2);
		$tp += $p;
		if($step == 1) {
			NewRow();
			$url = "/?module=acctdisp&amp;account=$num&amp;begin=$begindate&amp;end=$enddate";
			$tstr = number_format($total);
			if(isset($module))
				print "<td><a href=\"$url\">$acct</a></td><td style=\"text-align:right;direction:ltr\">$tstr</td>\n";
			else
				print "<td>$acct</td><td style=\"text-align:right;direction:ltr\">$tstr</td>\n";
			if($percent)
				print "<td>% $p</td>";
			if($d6111)
				print "<td>$id6111</td>\n";
			print "</tr>\n";
		}
		else {
			fwrite($fd, "$acct,$total");
			if($percent)
				fwrite($fd, ",$p");
			if($d6111)
				fwrite($fd, ",$id6111");
			fwrite($fd, "\n");
		}
	}
	if($step == 1) {
		$tstr = number_format($total_outcome);
		if(!isset($module))
			print "<tr class=\"sumlineprt\" align=\"right\">\n";
		else
			print "<tr class=\"sumline\" align=\"right\">\n";
		$l = _("Total");
		print "<td><b>$l</b></td><td>$tstr</td>\n";
		if($percent)
			print "<td>% $tp</td>";
		if($d6111)
			print "<td>&nbsp;</td>\n";
		print "</tr>\n";
		if(!isset($module))
			print "<tr class=\"sumlineprt\" align=\"right\">\n";
		else
			print "<tr class=\"sumline\" align=\"right\" >\n";
		print "<td>\n";
	}
	else {
		$l = _("Total");
		fwrite($fd, "$l,$total_outcome");
		if($percent)
			fwrite($fd, ",$tp");
		fwrite($fd, "\n");
	}
	$total = $total_income - $total_outcome;
	if($total > 0) {
		$l = _("Profit");
		if($step == 1)
			print "<b>$l</b>\n";
		else
			fwrite($fd, "$l,");
	}
	else {
		$l = _("Loss");
		if($step == 1)
			print "<b>$l</b>\n";
		else
			fwrite($fd, "$l,");
	}
	if($step == 1) {
		$tstr = number_format($total);
		print "</td><td dir=\"ltr\" align=\"right\">$tstr</td>\n";
		if($percent)
			print "<td>&nbsp;</td>";
		if($d6111)
			print "<td>&nbsp;</td>\n";
		print "</tr>\n";
		print "</table>\n";
	}
	else
		fwrite($fd, "$total\n");
	
	if(isset($module) && ($step == 1)) {
		$url = "profloss.php?print=1&amp;step=1&amp;begindate=$begindate&amp;enddate=$enddate";
		if($percent)
			$url .= "&amp;percent=on";
		if($d6111)
			$url .= "&amp;d6111=on";
		$url .= "&amp;prefix=$prefix";
		print "<div class=\"repbottom\">\n";
		$l = _("Print");
		print "<input type=\"button\" value=\"$l\" onclick=\"PrintWin('$url')\">\n";
		print "&nbsp;&nbsp;";
		$url = "?module-profloss&amp;step=2&amp;begindate=$begindate&amp;enddate=$enddate";
		if($percent)
			$url .= "&amp;percent=on";
		$l = _("File export");
		print "<input type=\"button\" value=\"$l\" onclick=\"window.location.href='$url'\">\n";
		print "</div>\n";
	}
	else if($step == 2) {
		fclose($fd);
		Conv1255($filename);
		$l = _("File export");
		print "<h2>$l: ";
		$url = "/download.php?file=$filename&amp;name=profloss.csv";
		print "<a href=\"$filename\">profloss.csv</a></h2>\n";
		$l = _("Right click and choose 'save as...'");
		print "<h2>$l</h2>\n";
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"window.open('$url', 'Download')\", 1000);\n";
		print "</script>\n";

	}

}

?>

