<?php 
global $dir;
global $logintbl, $companiestbl;
global $prefix;

$action = isset($_GET['action']) ? $_GET['action'] : '';
$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];

$name1 = urldecode($name1);
$email = $name1;
$query = "SELECT fullname FROM $logintbl WHERE name='$name1'";
$l = __LINE__;
$result = DoQuery($query, "$l");
$line = mysql_fetch_array($result, MYSQL_NUM);
$fullname = stripslashes($line[0]);

$query = "SELECT companyname FROM $companiestbl WHERE prefix='$prefix'";
$result = DoQuery($query, __LINE__);
$line = mysql_fetch_array($result, MYSQL_NUM);
$company = $line[0];

$subject1 = _("Subject");
$fullname1 = _("Full name");
$email1 = _("Email");
$message1 = _("Message");
$submit = _("Submit");
$sendrep=_("I want to send a detialed automatic error report genrated by the system");
$l = _("Hello");
$contactform = <<<EOF
<h2>$l: $fullname</h2><br />\n
<form action="?module=bag&action=submit" method="post">
	<table class="formtbl" dir="$dir" width="100%">
		<tr>
			<td>$subject1: </td>
			<td><input type="text" name="subject" size=\"30\" value="$subject" /></td>
		</tr>
		<tr>
			<td><input type="checkbox" name="sendreport" value="true" /></td>
			<td>$sendrep</td>
		</tr>
		<tr>
			<td valign="top">$message1: </td>
			<td><textarea rows="10" style="width:90%" name="message">$message</textarea></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" value="$submit" class='btnaction' /></td>
		</tr>
	</table>
</form>
EOF;
//phpinfo () ;
if($action == 'submit') {
	//$name1 = isset($_GET['name']) ? $_GET['name'] : $_COOKIE['name'];
	$sendreport=isset($_POST['sendreport'])?$_POST['sendreport']:false;
	print $sendreport;
	if($sendreport){

//דפדפן לקוח וגרסה
//פרטים מphp.ini
		global $version;
		$text.="<hr />/**********Version************/<hr />\n";
		$text.=$version;
		$text.="<hr />/**********Error-msg**********/<hr />\n";
		$text.=$php_errormsg."\n";
		$text.="<hr />/*******PHP-EXTENSIONS********/<hr />\n";
		$ext=get_loaded_extensions ();
		$text.=var_export($ext,true)."\n";
		$text.="<hr />/************ENV**************/<hr />\n";
		$text.=var_export($_ENV,true)."\n";
		$text.="<hr />/***********POST**************/<hr />\n";
		$text.=var_export($_POST,true)."\n";
		$text.="<hr />/************GET**************/<hr />\n";
		$text.=var_export($_GET,true)."\n";
		$text.="<hr />/**********SERVER*************/<hr />\n";
		$text.=var_export($_SERVER,true)."\n";
		$text.="<hr />/**********COOKIE*************/<hr />\n";
		$text.=var_export($_COOKIE,true)."\n";
		$text.="<hr />/**********SESSION************/<hr />\n";
		$text.=var_export($_SESSION,true)."\n";
		$text.="<hr />/**********END REPORT*********/<hr />\n";
	}else{
		$text='';
	}
	$s = GetPost('subject');
	$message = GetPost('message');
	global $sendMailAddress;
	$email=$sendMailAddress;
	$to = "adam@speedcomp.co.il";
	//$from = "From: $email";
	$subject = "=?utf-8?B?" . base64_encode("[Linet] $s") . "?=";
	
	$body .= "<div dir=\"$dir\">\n";
	$l = _("From: ");
	$l1 = _("Regarding company");
	$body .= "$l$fullname <$email><br>$l1: $company<br>\n$message<br /><hr />";	
	$body.=$text;
	$body .= "</div>\n";
	$headers = "Content-type: text/html; charset=UTF-8\r\n";
	$headers .= "From: $email\r\n";
	

    
	$thanks =sendMail($email, $to, $subject, $body);
       
	$contactform= "<h1>$thanks</h1>\n";
//	$l = _("We will do our best to answer you shortly");
	//$contactform.= "<h1>$l</h1>\n";
	$l = _("Click here to continue");
	$contactform.= "<br><h2><a href=\"index.php\">$l</a></h2>\n";
	//return;
}


$haeder = _("send a Bug Report");
//print "<h3>$l</h3>\n";
//print "$contactform";
//print "</div>\n";
createForm($contactform,$haeder,'',600,'','',1,getHelp());
//print "<div class=\"lefthalf1\">\n";
//
//print 

//print "</div>\n";

//createForm($text, $haeder);
?>