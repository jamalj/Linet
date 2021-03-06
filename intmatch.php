<?PHP
/*
 | Accounts transactions match handling script for Drorit Free accounting software
 | Written by Ori Idan Helicon technologies Ltd. 2004
 |
 | This program is a free software licensed under the GPL 
 */
if(!isset($prefix) || ($prefix == '')) {
	ErrorReport( _("This operation can not be executed without choosing a business first"));
	//print "<h1>$l</h1>\n";
	return;
}
$text='';
global $accountstbl, $transactionstbl;
global $namecache;
global $TranType;
global $dir;
global $correlationtbl,$curuser;
function PrintAccountSelect() {
	global $accountstbl, $prefix;

	$type1 = CUSTOMER;
	$type2 = SUPPLIER;
	$text='';
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type1' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	$text.= "<select id=\"account\" name=\"account\">\n";
	$l = _("Select account");
	$text.= "<option value=\"0\">-- $l --</option>\n";
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$query = "SELECT num,company FROM $accountstbl WHERE type='$type2' AND prefix='$prefix' ORDER BY company ASC";
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$num = $line['num'];
		$name = stripslashes($line['company']);
		$text.= "<option value=\"$num\">$name</option>\n";
	}
	$text.= "</select>\n";
	return $text;
}//*/

?>
<script type="text/javascript">
function CalcDebitSum() {
	var vals = document.getElementsByClassName('debit');
	var sum = document.getElementsByClassName('debit_sum');
	var t = document.form1.debit_total;
	
	size = vals.length;
	total = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				total += parseFloat(sum[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			total = parseFloat(sum.value);
	}
	t.value = total;
}

function CalcCreditSum() {
	var vals = document.getElementsByClassName('credit');
	var sum = document.getElementsByClassName('credit_sum');
	var t = document.form1.credit_total;
	
	size = vals.length;
	total = parseFloat("0.0");
	t.value = '';
	if(size) {
		for(i = 0; i < size; i++) {
			if(vals[i].checked) {
				total += parseFloat(sum[i].value);
			}
		}
	}
	else {
		if(vals.checked)
			total = parseFloat(sum.value);
	}
	t.value = total;
}

function goForm(){
	$(function() {
		$( "#dialog:ui-dialog" ).dialog( "destroy" );
		$( "#dialog-confirm" ).dialog({
			resizable: false,height:200,width:300,//modal: true,
			buttons: {
				"Ok": function() {
					$( this ).dialog( "close" );
				},
				"no thanks": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	});
}

function go(){
	var bil=true;//CalcSum();
	if(parseFloat($('#credit_total').val())!=parseFloat($('#debit_total').val())){
		var sum=(-1)*(parseFloat($('#credit_total').val())-parseFloat($('#debit_total').val()));
		var account =parseFloat($('#account').val());
		//alert('we r not balanced!');
		var dialog = $('<div dir="rtl" id="dialogdiv"></div>').appendTo('body');
		dialog.load("?action=lister&form=voucher&sum="+sum+"&acc="+account, {}, 
		        function (responseText, textStatus, XMLHttpRequest) {
		        	var agreed = false; 
		            dialog.dialog({resizable: false,height:500,width:780,hide: 'clip',title: ''});
		            dialog.bind('dialogclose', function(event) {
			            var acc=$('#account').val();
		            	window.location.href='?module=intmatch&step=1&account='+acc;
		            });
		        }
		    );
		bil=false;
	}
	if(bil)
		document.form1.submit();
}
$(document).ready(function(){
	$("#form").validate({
		   submitHandler: function(form) {
			   go();
		   }
	   });
});

</script>

<?PHP
$haeder = _("Accounts reconciliations");
//print "<h3>$l</h3>\n";

$step = isset($_GET['step']) ? $_GET['step'] : 0;

if($step == 2) {
	$debit = GetPoster('debit');
	$credit = GetPoster('credit');
	$account = GetPoster('account');
	
	$debit_str = '';
	$total = 0.0;

	if(is_array($debit)) {
	//	$debit = array_unique($debit);
		foreach($debit as $val) {
			/* $val is transaction number in debit side */
			list($num,$id) = explode(":", $val);
			$query = "SELECT sum FROM $transactionstbl WHERE num='$val' AND id='$id' AND account='$account' AND prefix='$prefix'";
			$result = DoQuery($query,__FILE__.": ".__LINE__);
			
			while($line = mysql_fetch_array($result, MYSQL_NUM)) {
				$sum = $line[0];
				$total += $sum;
				//print "sum: $sum<br />\n";
				if(!empty($debit_str))
					$debit_str .= ',';
				$debit_str .= $val;
			}
		}
	}
	//print "total: $total<br />\n";
	$credit_str = '';
	if(is_array($credit)) {
	//	$credit = array_unique($credit);
		foreach($credit as $val) {
			/* $val is transaction number in debit side */
			list($num,$id) = explode(":", $val);
			$query = "SELECT sum FROM $transactionstbl WHERE num='$val' AND id='$id' AND account='$account' AND prefix='$prefix'";
			//print $query."<br />\n";
			$result = DoQuery($query,__FILE__.": ".__LINE__);
			
			while($line = mysql_fetch_array($result, MYSQL_NUM)) {
				$sum = $line[0];
				//print "sum: $sum<br />\n";
				$total += $sum;
				if(!empty($credit_str))
					$credit_str .= ',';
				$credit_str .= $val;
			}
		}
	}
	//print "total: $total<br />\n";
//	print "debit_str: $debit_str<BR>\n";
//	print "credit_str: $credit_str<BR>\n";
	if(($total > 0.01) || ($total < -0.01)) {
		$l = _("Unbalanced reconciliation");
		ErrorReport($l);
		exit;
	}
	/* balanced match so put debit_str for all credit side transactions and credit_str for all debit side */
//	print_r($credit);
//	print_r($debit);
//	print "<BR>debit: $debit_str<BR>credit: $credit_str<BR>\n";
	$cor_num=maxSql(array('prefix'=>$prefix), "num", $correlationtbl);
	$uid=$curuser->id;
	$query = "INSERT INTO $correlationtbl VALUES ('$prefix', '$cor_num', '$debit_str', '$credit_str', '".OPEN."', '$uid');";
	DoQuery($query, __FILE__.":".__LINE__);
	foreach($credit as $val) {
		list($num,$id) = explode(":", $val);
		$query = "UPDATE $transactionstbl SET cor_num='$cor_num' ";
		$query .= "WHERE num='$num' AND id='$id' AND account='$account' AND prefix='$prefix'";
//		print "Query: $query<BR>\n";
		$result = DoQuery($query,__FILE__.": ".__LINE__);
		
	}
	foreach($debit as $val) {
		list($num,$id) = explode(":", $val);
		$query = "UPDATE $transactionstbl SET cor_num='$cor_num' ";
		$query .= "WHERE num='$num' AND id='$id' AND account='$account' AND prefix='$prefix'";
		$result = DoQuery($query,__FILE__.": ".__LINE__);
		
	}
	$step = 1;
}
if($step == 1) {
	$account = GetPoster('account');
	
	if($account == 0) {
		$l = _("No account chosen");
		ErrorReport($l);
		exit;
	}
	//adam:?
	//print "</div>\n";	/* end of righthalf */
	
	//print "<div class=\"form innercontent\">\n";
	$l = _("Account");
	$text.= "<h2>$l: \n";
	$text.=  GetAccountName($account);
	$text.=  "</h2>\n";
	
	$text.=  "<form id=\"form\" name=\"form1\" action=\"?module=intmatch&amp;step=2\" method=\"post\">\n";
	$text.=  "<input type=\"hidden\" id=\"account\" name=\"account\" value=\"$account\" />\n";
	$text.=  "<table><tr>\n";
	$l = _("Debit transactions");
	$text.=  "<td align=\"right\"><h2>$l</h2></td>\n";
	$text.=  "<td style=\"background:white\">&nbsp;&nbsp;</td>\n";
	$l = _("Credit transactions");
	$text.=  "<td align=\"right\"><h2>$l</h2></td>\n";
	$text.=  "</tr><tr><td valign=\"top\">\n";
	$text.=  "<table class=\"formy\" border=\"1\"><tr>\n";
	$text.=  "<th>&nbsp;</th>\n";
	$l = _("Tran. type");
	$text.=  "<th>$l</th>\n";
	$l = _("Date");
	$text.=  "<th>$l</th>\n";
	$l = _("Ref. num");
	$text.=  "<th>$l</th>\n";
	$l = _("Sum");
	$text.=  "<th>$l</th>\n";
	$text.=  "</tr>\n";
	
	/* Now the actual work of printing transactions in debit side */
	$query = "SELECT * FROM $transactionstbl WHERE account='$account' AND sum<0 AND prefix='$prefix'";
	$result = DoQuery($query, __LINE__);
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cor = $line['cor_num'];
		if(($cor != '') && ($cor != 0))
			continue;
		$num = $line['num'];
		$id = $line['id'];
		$type_str = $TranType[$line['type']];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum1'];
		$sum = $line['sum'];
		$sum *= -1.0;
		$text.=  "<tr>\n";
		$text.=  "<td><input type=\"checkbox\" class=\"debit\" name=\"debit[]\" value=\"$num:$id\" onchange=\"CalcDebitSum()\"></td>\n";
		$text.=  "<td>$type_str</td>\n";
		$text.=  "<td>$date</td>\n";
		$text.=  "<td>$refnum</td>\n";
		$text.=  "<td>$sum</td><input type=\"hidden\" class=\"debit_sum name=\"debit_sum[]\" value=\"$sum\"></TD>\n";
		$text.=  "</tr>\n";
	}
	$text.=  "<tr><td colspan=\"4\">&nbsp;</td>\n";
	$text.=  "<td><input type=\"text\" id=\"debit_total\" name=\"debit_total\" value=\"0\" size=\"5\" readonly></td></tr>\n";
	$text.=  "</table>\n";
	$text.=  "<td style=\"background:white\">&nbsp;&nbsp;</td>\n";
	$text.=  "</td><td valign=\"top\">\n";
	$text.=  "<table  class=\"formy\" border=\"1\"><tr>\n";
	$text.=  "<th>&nbsp;</th>\n";
	$l = _("Tran. type");
	$text.=  "<th>$l</th>\n";
	$l = _("Date");
	$text.=  "<th>$l</th>\n";
	$l = _("Ref. num");
	$text.=  "<th>$l</th>\n";
	$l = _("Sum");
	$text.=  "<th>$l</th>\n";
	$text.=  "</tr>\n";
	
	/* Now the actual work of printing transactions in credit side */
	$query = "SELECT * FROM $transactionstbl WHERE account='$account' AND sum>0 AND prefix='$prefix'";
	$result = DoQuery($query,__FILE__.": ".__LINE__);

	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$cor = $line['cor_num'];
		if(($cor != '') && ($cor != 0))
			continue;
		$num = $line['num'];
		$id = $line['id'];
		$type_str = $TranType[$line['type']];
		$date = FormatDate($line['date'], "mysql", "dmy");
		$refnum = $line['refnum1'];
		$sum = $line['sum'];
		$text.=  "<tr>\n";
		$text.=  "<td><input type=\"checkbox\" class=\"credit\" name=\"credit[]\" value=\"$num:$id\" onchange=\"CalcCreditSum()\" /></td>\n";
		$text.=  "<td>$type_str</td>\n";
		$text.=  "<td>$date</td>\n";
		$text.=  "<td>$refnum</td>\n";
		$text.=  "<td>$sum<input type=\"hidden\" class=\"credit_sum\" name=\"credit_sum[]\" value=\"$sum\" /></td>\n";
		$text.=  "</tr>\n";
	}
	$text.=  "<tr><td colspan=\"4\">&nbsp;</td>\n";
	$text.=  "<td><input type=\"text\" id=\"credit_total\" name=\"credit_total\" value=\"0\" size=\"5\" readonly /></td></tr>\n";
	$text.=  "</table>\n";
	$text.=  "</td></tr>\n";
	$l = _("Reconciliate");
	$text.=  "<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" value=\"$l\" class='btnaction' /></td></tr>\n";
	$text.=  "</table>\n";
	$text.=  "</form>\n";
	//print "</div>\n";
	//print "<div class=\"form righthalf1\">\n";
}


$text.= '<form name="form2" action="?module=intmatch&amp;step=1" method="post"><table border="0" width="100%" class="formtbl">';

$l = _("Select account");
$text.= "<tr><td>$l: </td>\n";
$text.= '<td>';
$text.=  PrintAccountSelect(); 
$text.= '</td></tr><tr><td>&nbsp;</td></tr>';
$l = _("Select");
$text.=  "<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"$l\" class='btnaction' /></td></tr>\n";

$text.= '</table></form>';
createForm($text,$haeder,'',750,'','',1,getHelp());

?>
