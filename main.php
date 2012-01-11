<?PHP
/*
 | Linet accounting system main page
 */
global $prefix, $dir, $lang;
global $logintbl, $companiestbl;
global $permissionstbl;
global $transactionstbl;
global $accountstbl;
global $stattbl, $modnames;
global $itemstbl;
global $superuser;
global $curuser;

$name=$curuser->name;
if($lang == 'he')
	$align = 'right';
else
	$align = 'left';

$action = isset($_GET['action']) ? $_GET['action'] : '';
if($action == 'delcomp') {
	
	
	$p = $_GET['company'];
//	print "Transactions <br>\n";
	delCompany($p);
	
}
$text='';
if(!isset($prefix) || ($prefix == '')) {	/* Display list of companies */
	
	$query = "SELECT company FROM $permissionstbl WHERE name='$name'";
	//print "Query: $query<br>\n";
	$result = DoQuery($query, "main.php");
	$n = mysql_num_rows($result);
	//if($n == 1) {
	//	$line = mysql_fetch_array($result, MYSQL_NUM);
		//$prefix = $line[0];
		//$company = $line[0];
		//print "<meta http-equiv=\"refresh\" content=\"0;url=?company=$line[0]\" /> ";
		//exit;
		//print "prefix: $prefix<br>\n";
	//} 
	if($n == 0) {
		//print "<br />\n";
		$l = _("No companies for this user");
		ErrorReport($l);
		return;
	}
	if($n >= 1) {
		//$line = mysql_fetch_array($result, MYSQL_NUM);
		

		$haeder = _("Choose business to work on");
		$compname=_("Company Name");
		$actions=_("Actions");
		$text.= "<table class=\"formy\"><tr><th>$compname</th><th>$actions</th></tr>\n";
		while($line = mysql_fetch_array($result, MYSQL_NUM)) {
			if($line[0] == '*') {
				$query = "SELECT prefix FROM $companiestbl";
				$result = DoQuery($query, "main.php");
				$n = mysql_num_rows($result);
				continue;
				// print "n: $n<br>\n";
			}
			$s = $line[0];
			// print "prefix: $s<br>\n";
			$query = "SELECT companyname FROM $companiestbl WHERE prefix='$s'";
			$r = DoQuery($query, "compass.php");
			$line = mysql_fetch_array($r, MYSQL_NUM);
			$n = $line[0];
			$cookietime = time() + 60*60*24*30;
			$url = "index.php?company=$s";
			//setcookie('company', $s, $cookietime);
			$text.= "<tr><td><a href=\"$url\">$n</a></td><td>&nbsp;\n";
			if($superuser) {
				//$l = _("Delete");
				//$text.= "<a class=\"btnremove\" href=\"?module=main&amp;action=delcomp&amp;company=$s\"></a>";
				$text.= "<a class=\"btnremove\" href=\"javascript:delComp('$s');\"></a>";
				$text.= "<a class=\"btnedit\" href=\"?module=defs&amp;company=$s\"></a>";
			}
			$text.= "</td></tr>\n";
		}
		$text.= "</table>\n";
		if($superuser) {
			$l = _("Add new business");
//			print "<br><br><a href=\"?module=defs\">׳³ג€�׳³ג€™׳³ג€�׳³ֲ¨׳³ֳ— ׳³ג€”׳³ג€˜׳³ֲ¨׳³ג€� ׳³ג€”׳³ג€�׳³ֲ©׳³ג€�</a><br>\n";
			$text.= "<br /><br /><a href=\"?module=defs\">$l</a><br />\n";
		}
		$text.="<script type=\"text/javascript\">
        function delComp(company){
 			var bla= window.confirm(this.title || '"._("Are you sure you want to delete this company?")."');
       		if(bla==true){
       			window.location ='?module=main&action=delcomp&company='+company;
       		}
       	}
    </script>";
		createForm($text, $haeder,'',750,'','img/icon_defs1.png',0,'?module=redirect&amp;dest=defy');
		return;
	}
}

function GetAcctTotal($acct, $begin, $end) {
	global $transactionstbl, $prefix;
	
	if($begin != 0)
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date>='$begin' AND date<='$end' AND prefix='$prefix'";
	else 
		$query = "SELECT sum FROM $transactionstbl WHERE account='$acct' AND date<='$end' AND prefix='$prefix'";
	//print "query: $query<br>\n";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];
	}
	return $total;
}

function GetGroupTotal($grp, $begin, $end) {
	global $accountstbl, $prefix;

	$query = "SELECT num FROM $accountstbl WHERE prefix='$prefix' AND type='$grp'";
	$result = DoQuery($query, "compass.php");
	$total = 0.0;
	list($y, $m, $d) = explode('-', $begin);
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$num = $line[0];
		//print ";$num;";
		$sub_total = GetAcctTotal($num, $begin, $end);
		//print ";$sub_total;";
		$total += $sub_total;
	}
	return $total;
}

/****************** dates form ***************************/
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : date("1-1-Y");
$enddmy = isset($_GET['end']) ? $_GET['end'] : date("d-m-Y");


$haeder = _("Business details (NIS)");
//print "<br />$l\n";
$begin = FormatDate($begindmy, "dmy", "mysql");
$end = FormatDate($enddmy, "dmy", "mysql");
$income = GetGroupTotal(INCOME, $begin, $end);
$outcome = GetGroupTotal(OUTCOME, $begin, $end);
$text='';






$style="style=\"background-color: #f2f2f2;padding: 10px;text-align:center; border: 1px solid #dedede;margin-top: 1px;\"";
$text.= "<form  $style action=\"\" name=\"main\" method=\"get\">\n";
$text.= "<input type=\"hidden\" name=\"module\" value=\"main\" />\n";
//print "<br /><table dir=\"$dir\" style=\"height:100%\"><tr>\n";
$l = _("Begin date");
$style="style=\"padding-right: 10px;width: 120px;text-align:right;color: #186ABF;   font-weight: bold;\"";
$text.= "<table style=\"\"><tr><td $style>$l: <br />\n";
$text.= "<input class=\"date\" type=\"text\" id=\"begin\" name=\"begin\" value=\"$begindmy\" size=\"7\" />\n";
//color="#f7f7f7"color="#f2f2f2"color="#dedede"
$text.= "</td><td $style>\n";
$l = _("End date");
$text.= "$l: <br />\n";
$text.= "<input class=\"date\" type=\"text\" id=\"end\" name=\"end\" value=\"$enddmy\" size=\"7\" />\n";
//$text.='<script type="text/javascript">	addDatePicker("#begin","'.$begindmy.'");addDatePicker("#end","'.$enddmy.'");</script>';
//print "&nbsp;&nbsp;<input type=\"submit\" value=\"׳³ג€˜׳³ֲ¦׳³ֲ¢\" />\n";
$l=_('Go');
$text.= "</td><td $style><a href='javascript:document.main.submit();' class='btnsmall'>$l</a></td></tr></table>";
//print "</tr></table>\n";
$text.= "</form>\n<br /><br /><br /><br /><br />";


// style=\"font-weight:normal;font-size:14px;\"
$text.= "<div style=\"width:200px;display:inline-block;\"><table class=\"sums\">\n";
//NewRow();
$n = number_format($income);
$l = _("Total income");
$text.= "<tr><td><a href=\"?module=acctadmin&amp;type=3&amp;option=rep\">$l</a></td>";
$text.= "<td>$n</td></tr>\n";
//NewRow();
$o = $outcome * -1.0;
$n = number_format($o);
$l = _("Total outcome");
$text.= "<tr><td><a href=\"?module=acctadmin&amp;type=2&amp;option=rep\">$l</a></td>";
$text.= "<td>$n</td></tr>\n";
//NewRow();
$profit = $income + $outcome;
$url = "?module=profloss&amp;step=1&amp;begindate=$begindmy&amp;enddate=$enddmy";
if($profit >= 0.0) {
	$l = _("Total profit");
	$text.= "<tr><td><a href=\"$url\">$l</a></td>";
}
else {
	$l = _("Total loss");
	$text.= "<tr><td><a href=\"$url\">$l</a></td>";
}
$n = number_format(abs($profit));
$text.= "<td>$n</td>\n";
$text.= "</tr>"; 
//NewRow();
$text.="<tr>";
$t = GetGroupTotal(CUSTOMER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
$l = _("Total customers");
$text.= "<td><a href=\"?module=acctadmin&amp;type=0&amp;option=rep\">$l</a></td><td>$n</td>\n";
$text.= "</tr>\n";
$text.='<tr>';
$t = GetGroupTotal(SUPPLIER, $begin, $end);
if($t < 0.0)
	$t *= -1.0;
$n = number_format($t);
$l = _("Total suppliers");
$text.= "<td><a href=\"?module=acctadmin&amp;type=1&amp;option=rep\">$l</a></td><td style=\"color:black;font-weight:normal;font-size:14px;\">$n</td>\n";
$text.= "</tr></table>\n</div>";

//finaly some action
 $text.='<!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.js"></script><![endif]-->
 <script language="javascript" type="text/javascript" src="js/jquery.jqplot.min.js"></script>
 <script type="text/javascript" src="js/jqplot.barRenderer.min.js"></script>
 <script type="text/javascript" src="js/jqplot.categoryAxisRenderer.min.js"></script>
 <script type="text/javascript" src="js/jqplot.pointLabels.min.js"></script>
 <link rel="stylesheet" type="text/css" href="js/jquery.jqplot.min.css" />
 
 <div id="chartdiv" style="height:180px;width:200px; display: inline-block;"></div> ';
$text.='<script language="javascript" type="text/javascript">
  var s1 = ['.$income.','.-1*$outcome.'];
     var ticks = [\''._("Income").'\',\''._("Outcome").'\'];
     
    var plot1 = $.jqplot(\'chartdiv\', [s1], {
         seriesDefaults:{
            renderer:$.jqplot.BarRenderer,
            rendererOptions:{ varyBarColor : true },
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: ticks
            },
            yaxis:{
              tickOptions:{ formatString:\'%d ₪\'},
              autoscale:true,
            }
        },
        seriesColors: [ "#eee", "#ccc"],
        highlighter: { show: false }
    });
</script>';
//adam:CreateProfitGraph($income, $outcome, $profit);
//$text.= "<br />\n";
//$text.= "<img src=\"tmp/profit.png\" alt=\"graph\" style=\"margin-right:10px;display: inline-block;\" />\n";
createForm($text,$haeder,"maindiv",460,500,'img/icon_detiales.png',null,'?module=redirect&amp;dest=main');

$haeder = _("Events according to date");

require('calendar.php');
createForm($text,$haeder,"caldiv",280,500,'img/icon_cel.png');
?>

