<?PHP
/*
 | EmailDoc
 | Send document URL by email
 */
$step = isset($_GET['step']) ? $_GET['step'] : 0;
$doctype = isset($_GET['doctype']) ? $_GET['doctype'] : $doctype;
$docnum = isset($_GET['docnum']) ? $_GET['docnum'] : $docnum;
$account = isset($_GET['account']) ? $_GET['account'] : $account;

$doctype = isset($_POST['doctype']) ? $_POST['doctype'] : $doctype;
$docnum = isset($_POST['docnum']) ? $_POST['docnum'] : $docnum;
$account = isset($_POST['account']) ? $_POST['account'] : $account;

global $DocType;
global $accountstbl;

if($step == 1) {
	$url = GetURI();
	$email = $_POST['email'];
	$comment = nl2br($_POST['comment']);
	$doctypestr = $DocType[$doctype];
	if($doctype > DOC_RECEIPT)
		$doctypestr = "׳—׳©׳‘׳•׳ ׳™׳× ׳�׳¡ ׳§׳‘׳�׳”";
	$subject = "=?utf-8?B?" . base64_encode("$doctypestr ׳�׳×׳•׳›׳ ׳× ׳₪׳¨׳™׳�׳�׳ ׳¡") . "?=";
	$headers = "Content-type: text/html; charset=UTF-8\r\n";
	$headers .= "From: linet@linet.org.il\r\n";
	$body = "<div dir=\"rtl\">\n";
	$body .= "$comment<br>\n";
	$body .= "׳�׳”׳¦׳’׳× ׳”׳—׳©׳‘׳•׳ ׳™׳× ׳�׳—׳¥ ׳¢׳� ׳”׳§׳™׳©׳•׳¨ ׳”׳‘׳�: ";
	$body .= "<a href=\"$url/printdoc.php?doctype=$doctype&docnum=$docnum&prefix=$prefix\">";
	$body .= "$url/printdoc.php?doctype=$doctype&docnum=$docnum&prefix=$prefix</a><br>\n";
	$body .= "</div>\n";
	mail($email, $subject, $body, $headers);

	print "<h1>$doctypestr ׳�׳¡׳₪׳¨ $docnum</h1>";
	print "<h1>׳ ׳©׳�׳—׳” ׳�׳›׳×׳•׳‘׳× ׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™: $email</h1>\n";
	$step = 0;
}
else {
	$query = "SELECT email FROM $accountstbl WHERE num='$account'";
	$result = DoQuery($query, "emaildoc.php");
	$line = mysql_fetch_array($result, MYSQL_NUM);
	$email = $line[0];
	print "<br><h1>׳©׳�׳™׳—׳× ׳�׳¡׳�׳� ׳‘׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™</h1>\n";
	print "<div class=\"righthalf\">\n";
	$doctypestr = $DocType[$doctype];
	if($doctype > DOC_RECEIPT)
		$doctypestr = "׳—׳©׳‘׳•׳ ׳™׳× ׳�׳¡ ׳§׳‘׳�׳”";
	print "<h1>$doctypestr ׳�׳¡׳₪׳¨ $docnum</h1>\n";
	print "<form name=\"emailform\" action=\"?module=emaildoc&step=1\" method=\"post\">\n";
	print "<input type=\"hidden\" name=\"doctype\" value=\"$doctype\">\n";
	print "<input type=\"hidden\" name=\"docnum\" value=\"$docnum\">\n";
	print "<table border=\"0\"><tr>\n";
	print "<td>׳“׳•׳�׳¨ ׳�׳�׳§׳˜׳¨׳•׳ ׳™: </td>\n";
	print "<td><input type=\"text\" name=\"email\" value=\"$email\" dir=\"ltr\"></td>\n";
	print "</tr><tr>\n";
	print "<td valign=\"top\">׳”׳¢׳¨׳•׳×: </td>\n";
	print "<td><textarea name=\"comment\" rows=\"5\" cols=\"30\"></textarea></td>\n";
	print "</tr><tr>\n";
	print "<td colspan=\"2\" align=\"center\">\n";
	print "<input type=\"submit\" value=\"׳©׳�׳—\">\n";
	print "</td></tr>\n";
	print "</table>\n";
	print "</form>\n";
	print "</div>\n";
	
	return;
}
?>

