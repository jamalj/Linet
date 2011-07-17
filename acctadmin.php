﻿<?PHP
//M:חשבונות
/*
 | account administration module for Drorit accounting system
 | Written by Ori Idan Helicon technologies ltd.
 */
global $accountstbl, $transactionstbl;
global $AcctType;
global $RetModule;
global $arr6111;
global $lang, $dir;
$text='';
$haeder='';
if(!isset($prefix) || ($prefix == '')) {
	$l = _("This operation can not be executed without choosing a business first");
	print "<h1>$l</h1>\n";
	return;
}

$begindmy = isset($_COOKIE['begin']) ? $_COOKIE['begin'] : date("1-1-Y");
$enddmy = isset($_COOKIE['end']) ? $_COOKIE['end'] : date("d-m-Y");
$begindmy = isset($_GET['begin']) ? $_GET['begin'] : $begindmy;
$enddmy = isset($_GET['end']) ? $_GET['end'] : $enddmy;
$beginmysql = FormatDate($begindmy, "dmy", "mysql");
$endmysql = FormatDate($enddmy, "dmy", "mysql");

$option = isset($_GET['option']) ? $_GET['option'] : '';

/* Read 6111 data */
$arr6111 = array();
$lines = file("6111.txt");
foreach($lines as $line) {
	if($line[0] == '#')
		continue;
	if(strpos($line, ',')) {
		list($n, $s) = explode(',', $line);
		$arr6111[$n] = trim($s);
	}
}
	$result = mysql_query($query);

?>
<script type="text/javascript">
function VatChange() {
	var i = document.acct.src_tax.selectedIndex;

	if(i == 4) {
		document.acct.src_tax1.style.display = 'block';
	}
	else {
		document.acct.src_tax1.style.display = 'none';
	}
}

function Set6111() {
	var id = document.acct.id6111.value;

//	alert(id);
	switch(id) {
<?PHP
	foreach($arr6111 as $n => $s) {
		print "\t\tcase '$n':\n";
		print "\t\t\tdocument.acct.details6111.value = '$s';\n";
		print "\t\t\tbreak;\n";
	}
?>
		default:
			document.acct.details6111.value = '';
			break;
	}
}

function editshow() {
	var d = document.getElementById('editformdiv');
	
	if(d.style.display == 'none') {
		d.style.display = 'block';
		document.getElementById('b1').style.display = 'none';
	}
	else
		d.style.display = 'none';	
}
</script>
<?PHP
function Print6111id($def) {
	global $arr6111;
	
	$text.= "<select name=\"id6111\">\n";
	$l = _("Choose 6111 clause");
	$text.= "<option value=\"\">-- $l --</option>\n";
	foreach($arr6111 as $id => $str) {
		$d = ($def == $id) ? " selected" : "";
		$text.= "<option value=\"$id\"$d>$id $str</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function PrintVatPercent($def) {
	$p = array('100' => '100 %', '66.66' => '66 %', '25' => '25 %', '0' => '0 %', '--' => 'אחר');

//	print "def: $def<br>\n";
	if($def == '')
		$def = 100;
	$text.= "<select name=\"src_tax\" onchange=\"VatChange()\">\n";
	foreach($p as $k => $v) {
//		print "$k $def<br>\n";
		if($k == $def)
			$text.= "<option value=\"$k\" selected>$v</option>\n";
		else
			$text.= "<option value=\"$k\">$v</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function IncomeVatPercent($def) {
	$p = array('100' => '100 %', '0' => '0 %');
	
	if($def == '')
		$def = 100;
	$text.= "<select name=\"src_tax\">\n";
	foreach($p as $k => $v) {
//		print "$k $def<br>\n";
		if($k == $def)
			$text.= "<option value=\"$k\" selected>$v</option>\n";
		else
			$text.= "<option value=\"$k\">$v</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}

function GetAcctTotal($account, $begin, $end) {
	global $transactionstbl, $prefix;
	
	$query = "SELECT sum FROM $transactionstbl WHERE account='$account' AND date<='$end' AND prefix='$prefix'";
//	print "Query: $query<br>\n";
	$result = DoQuery($query, "GetAcctTotal");
	$total = 0.0;
	while($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$total += $line[0];	
	}
	return $total;
}

/* Check if all predefined accounts exist and create them if not */
require('acctcreate.inc.php');	


if($action == 'newacct') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}
	/* first calculate next account number for this company */
	$query = "SELECT MAX(num) FROM $accountstbl WHERE prefix='$prefix'";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$max = $line[0] + 1;
	if($max < 200)	/* start from 200 so we have place for more predefined accounts */
		$max = 201;

//	print_r($_POST);
	$type = $_POST['type'];
	
	$pay_terms = $_POST['pay_terms'];
	$p = strpos($pay_terms, '+');
	if($p === false) {
		$pay_terms = (integer)$pay_terms;
	}
	else {
		$pay_terms = (integer)$pay_terms * -1;
	}
	$id6111 = isset($_POST['id6111']) ? $_POST['id6111'] : '';
	$src_tax = isset($_POST['src_tax']) ? $_POST['src_tax'] : '';
	$src_date = isset($_POST['src_date']) ? FormatDate($_POST['src_date'], "dmy", "mysql") : '';
	$company = htmlspecialchars($_POST['company'], ENT_QUOTES);
	if($company == '') {
		$l = _("Account name must be specified");
		ErrorReport("$l");
		exit;
	}
	$contact = htmlspecialchars($_POST['contact'], ENT_QUOTES);
	$department = htmlspecialchars($_POST['department'], ENT_QUOTES);
	$vatnum = htmlspecialchars($_POST['vatnum'], ENT_QUOTES);
	$email = htmlspecialchars($_POST['email'], ENT_QUOTES);
	$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
	$dir_phone = htmlspecialchars($_POST['dir_phone'], ENT_QUOTES);
	$cellular = htmlspecialchars($_POST['cellular'], ENT_QUOTES);
	$fax = htmlspecialchars($_POST['fax'], ENT_QUOTES);
	$web = htmlspecialchars($_POST['web'], ENT_QUOTES);
	$address = htmlspecialchars($_POST['address'], ENT_QUOTES);
	$city = htmlspecialchars($_POST['city'], ENT_QUOTES);
	$zip = htmlspecialchars($_POST['zip'], ENT_QUOTES);
	$comments = htmlspecialchars($_POST['comments'], ENT_QUOTES);

	$query = "INSERT INTO $accountstbl (num, prefix, type, id6111, pay_terms, src_tax, src_date, ";
	$query .= "company, contact, department, vatnum, email, phone, ";
	$query .= "dir_phone, cellular, fax, web, address, city, zip, comments) \n";
	$query .= "VALUES ('$max', '$prefix', '$type', '$id6111', '$pay_terms', '$src_tax', '$src_date', '$company', '$contact', '$department', ";
	$query .= "'$vatnum', '$email', '$phone', ";
	$query .= "'$dir_phone', '$cellular', '$fax', '$web', '$address', '$city', '$zip', '$comments')";
//	print "<br>type: $type<br>\n";
//	print "Query: $query<br>\n";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
	
	if(isset($_GET['ret'])) {
		$RetModule = $_GET['ret'];
		if(isset($_GET['targetdoc'])) {
			$targetdoc = $_GET['targetdoc'];
			$RetModule .= "&targetdoc=$targetdoc";
		}
		$url = "index.php?module=$RetModule";
		print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=$url\">\n";
		return;
	}
}
if($action == 'updateacct') {
	if($name == 'demo') {
		$l = _("Demo user is not allowed to update data");
		print "<h1>$l</h1>\n";
		return;
	}

	$num = $_GET['num'];
	$type = $_POST['type'];
	$pay_terms = $_POST['pay_terms'];
	// print "pay_terms: $pay_terms<BR>\n";
	$p = strpos($pay_terms, '+');
	if($p === false) {
		// print "pay_terms1: $pay_terms<BR>\n";
		$pay_terms = (integer)$pay_terms;
	}
	else {
	//	print "pay_terms: $pay_terms<BR>\n";
		$pay_terms = (integer)$pay_terms * -1;
	//	exit;
	}

	$id6111 = isset($_POST['id6111']) ? $_POST['id6111'] : '';
	$src_tax = isset($_POST['src_tax']) ? $_POST['src_tax'] : '';
	if($src_tax == '--')
		$src_tax = $_POST['src_tax1'];
	$src_date = isset($_POST['src_date']) ? FormatDate($_POST['src_date'], "dmy", "mysql") : '';

//	$company = $_POST['company'];
	$company = GetPost('company');
	$contact = GetPost('contact');
	$department = GetPost('department');
	$vatnum = GetPost('vatnum');
	$email = GetPost('email');
	$phone = GetPost('phone');
	$dir_phone = GetPost('dir_phone');
	$cellular = GetPost('cellular');
	$fax = GetPost('fax');
	$web = GetPost('web');
	$address = GetPost('address');
	$city = GetPost('city');
	$zip = GetPost('zip');
	$comments = GetPost('comments');
	
	$query = "UPDATE $accountstbl SET\n";
	$query .= "type='$type',\n";
	$query .= "id6111='$id6111',\n";
	$query .= "pay_terms='$pay_terms',\n";
	$query .= "src_tax = '$src_tax', \n";
	$query .= "src_date = '$src_date', \n";
	$query .= "company='$company',\n";
	$query .= "contact='$contact',\n";
	$query .= "department='$department',\n";
	$query .= "vatnum='$vatnum',\n";
	$query .= "email='$email',\n";
	$query .= "phone='$phone',\n";
	$query .= "dir_phone='$dir_phone',\n";
	$query .= "fax='$fax',\n";
	$query .= "web='$web',\n";
	$query .= "address='$address',\n";
	$query .= "city='$city',\n";
	$query .= "zip='$zip',\n";
	$query .= "comments='$comments' \n";
	$query .= "WHERE num='$num' AND prefix='$prefix'\n";
	$line = __LINE__;
	$file = __FILE__;
	$result = DoQuery($query, "$file: $line");
}
if($action == 'editacct') {
	$num = (int)$_GET['num'];
	
	//print "<br>\n";
	//print "<div class=\"form righthalf1\" style=\"width:50%\"\n";
	$text=EditAcct($num, $type);
	createForm($text,$haeder,"righthalf1",450);
	//print "</div>\n";
	return;
}
if($action == 'delacct') {
	$num = (int)$_GET['num'];
	
	$file = __FILE__;
	$query = "SELECT * FROM $transactionstbl WHERE account='$num' AND prefix='$prefix'";
	$line = __LINE__;
	$result = DoQuery($query, "$file: $line");
	if(mysql_num_rows($result)) {
		$l = _("Account with transactions can not be deleted");
		print "<h1>$l</h1>";
	}
	else {
		$query = "DELETE FROM $accountstbl WHERE num='$num' AND prefix='$prefix'";
		$line = __LINE__;
		$result = DoQuery($query, "$file: $line");
	}
}
if($action == 'addacct') {
	$type = $_GET['type'];
	
	switch($type) {
		case SUPPLIER:
		case OUTCOME:
			$RetModule = "outcome";
			break;
		case CUSTOMER:
		case INCOME:
			$RetModule = "docsadmin";
			break;
	}
	$text=EditAcct(0, $type);
	createForm($text,$haeder,"righthalf1",450);
	return;
}

$RetModule = isset($_GET['ret']) ? $_GET['ret'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : $type;
$typename = $AcctType[$type];

print "<div class=\"accttype\">\n";
print "<br>\n";
print "<table cellspacing=\"5px\">\n";
for($i = 0; $i < 13; $i++) {
	print "<tr>\n";
	print "<td align=\"center\" width=\"145px\" height=\"37px\"";
	if($i != $type)
		$style = "background:url('botton.gif')";
	else
		$style = "background:url('pressed.gif')";
	$style .= ";background-repeat: no-repeat;background-position:top left;";
	print "style=\"$style\">\n";
	$t = stripslashes($AcctType[$i]);
	$url = "?module=acctadmin&amp;type=$i";
	if($option)
		$url .= "&amp;option=$option";
	print "<a href=\"$url\" style=\"color:white\">$t</a></td>\n";
	print "</tr>\n";
}
print "</table>\n";
print "<br>\n";
print "</div>\n";

// print "<br>";
// print "<div class=\"accttbl\">\n";
//print "<br>\n";
// print "<table width=\"75%\" style=\"margin-right:20px\"><tr><td width=\"50%\">";
if($option == '') {
	//print "<div class=\"form righthalf1\" style=\"width:50%\">\n";
	//print "<h3>$typename</h3>\n";
	$text=EditAcct(0, $type);
	//print "</div>\n"; 
	$haeder=$typename;
	createForm($text,$haeder,"righthalf1",450);
}

// print "<td>&nbsp;</td>\n";
// print "</tr><tr><td colspan=\"2\">\n";
print "<div class=\"form accttbl\">\n";
if($lang == 'he')
	print "<h2>חשבונות $typename קיימים</h2>\n";
else
	print "<h2>Existing $typename accounts</h2>\n";
// print "</div></div>\n";
print "<table dir=\"$dir\" border=\"0\" class=\"hovertbl\" style=\"margin-top:5px\">\n";
print "<tr class=\"tblhead\">\n";
$l = _("Internal number");
print "<td style=\"width:5em\">$l </td>\n";
// print "<td style=\"width:4.5em\">סוג חשבון </td>\n";
$l = _("Name");
print "<td style=\"width:10em\">$l</td>\n";
if(($type == INCOME) || ($type == OUTCOME) || ($type == ASSETS)) {
	$l = _("Recognized VAT");
	print "<td style=\"width:5.5em\">$l </td>\n";
	$l = _("6111 caluse");
	print "<td style=\"width:5.5em\">$l </td>\n";
	$l = _("6111 description");
	print "<td style=\"width:12em\">$l </td>\n";
}
if(($type == CUSTOMER) || ($type == SUPPLIER)) {
	$l = _("Address");
	print "<td style=\"width:6em\">$l</td>\n";
	$l = _("City");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Zip");
	print "<td style=\"width:4em\">$l</td>\n";
	$l = _("Phone");
	print "<td style=\"width:6em\">$l</td>\n";
/*	$l = _("Email");
	print "<td style=\"width:7em\">$l</td>\n"; */
}
// print "<TD>איש קשר</TD>\n";
$l = _("Acc. balance");
print "<td style=\"width:4em\">$l</td>\n";
if($option == '') {
	print "<td colspan=\"3\">&nbsp;<!-- Edit -->\n";
	print "&nbsp;<!-- Delete -->\n";
	print "&nbsp;<!-- Disp transactions --></td>\n";
}
print "</tr>\n";

$query = "SELECT * FROM $accountstbl WHERE type='$type' AND prefix='$prefix' ORDER BY company";
$result = DoQuery($query, "acctadmin.php");
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$num = $line['num'];
	$type = $line['type'];
	$name = $line['company'];
	$contact = $line['contact'];
	$address = $line['address'];
	$city = $line['city'];
	$zip = $line['zip'];
	$phone = $line['phone'];
//	$email = $line['email'];
	NewRow();
	print "<td>$num</td>\n";
	$typename = stripslashes($AcctType[$type]);
//	print "<td>$typename</td>\n";
	$name = stripslashes($name);
	print "<td><a href=\"?module=acctdisp&amp;account=$num&amp;begin=$begindmy&amp;end=$enddmy\">$name</a></td>\n";
	if(($type == INCOME) || ($type == OUTCOME) || ($type == ASSETS)) {
		$pvat = $line['src_tax'];
		if($pvat == '')
			$pvat = 100;
		print "<td dir=\"ltr\" align=\"right\">$pvat %</td>\n";
		$id6111 = $line['id6111'];
		$str6111 = $arr6111[$id6111];
		print "<td>$id6111</td>\n";
		print "<td>$str6111</td>\n";
	}
	if(($type == CUSTOMER) || ($type == SUPPLIER)) {
		print "<td>$address</td>\n";
		print "<td>$city</td>\n";
		print "<td>$zip</td>\n";
		print "<td>$phone</td>\n";
//		print "<td>$email</td>\n";
	}
	
//	print "<TD>$contact</TD>\n";
	$sum1 = GetAcctTotal($num, $beginmysql, $endmysql);
	$tstr = number_format($sum1);
	print "<td dir=\"ltr\" align=\"right\">$tstr</td>\n";
	$total += $sum1;
	if($option == '') {
		$l = _("Edit");
		$url = "?module=acctadmin&amp;action=editacct&amp;num=$num";
		print "<td><input type=\"button\" value=\"$l\" onClick=\"window.location.href='$url'\">&nbsp;\n";
		$l = _("Delete");
		$url = "?module=acctadmin&amp;action=delacct&amp;num=$num&amp;type=$type";
		print "<input type=\"button\" value=\"$l\" onClick=\"window.location.href='$url'\"></td>\n";
//	print "<a href=\"?module=acctdisp&account=$num&end=today\">תנועות לחשבון</a></td>\n";
	}
	print "</tr>\n";
}
print "\t<tr class=\"sumline\">\n";
$l = _("Total");
$tstr = number_format($total);
if(($type == CUSTOMER) || ($type == SUPPLIER)) {
	print "\t\t<td colspan=\"6\" align=\"left\"><b>$l: </b>&nbsp;&nbsp;</td>\n";
	print "\t\t<td dir=\"ltr\" align=\"right\">$tstr</td><td>&nbsp;</td>\n";
}
else if(($type == INCOME) || ($type == OUTCOME) || ($type == ASSETS)){
	print "\t\t<td colspan=\"5\" align=\"left\"><b>$l: </b>&nbsp;&nbsp;</td>\n";
	print "\t\t<td dir=\"ltr\" align=\"right\">$tstr</td><td>&nbsp;</td>\n";
}
else {
	print "\t\t<td colspan=\"2\" align=\"left\"><b>$l: </b>&nbsp;&nbsp;</td>\n";
	print "\t\t<td dir=\"ltr\" align=\"right\">$tstr</td><td>&nbsp;</td>\n";
}
	
print "\t</tr>\n</table>\n";
print "</div>\n";

// EditAcct(0, $type);

?>

