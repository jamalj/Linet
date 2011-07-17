<?PHP
/*
 | menu for drorit
 */

$MainMenu = array('main' => _("Main") . "|module=main", 
	'menu01' => _("Bussines details") . '|module=defs',
	'menu02' => _("Accounts") . '|module=acctadmin&amp;type=0',
	'menu03' => _("Business docs") . '|module=docnums',
	'menu04' => _("Contacts") . '|module=contact',
	'menu05' => _("Items") . '|module=items',
	'menu06' => _("Currency rates") . '|module=curadmin',
	'menu07' => _("Openning balances") . '|module=opbalance',
	'menu08' => _("Edit user") . '|module=login&amp;action=edituser',
	'menu09' => _("Add user") . '|module=login&amp;action=adduser',
	'top1' => _("Income") . '|',
	'menu11' => _("Invoice") . '|module=docsadmin&amp;targetdoc=3',
	'menu12' => _("Invoice receipt") . '|module=docsadmin&amp;targetdoc=3&amp;option=receipt',
	'menu13' => _("Receipt") . '|module=receipt',
	'menu14' => _("Bank deposits") . '|module=deposit&amp;type=2',
	'top2' => _("Outcome") . '|',
	'menu21' => _("Outcome") . '|module=outcome',
	'menu22' => _("Asstes") . '|module=outcome&amp;opt=asset',
	'menu23' => _("Payment") . '|module=payment',
	'menu24' => _("VAT payment") . '|module=payment&amp;opt=vat',
	'menu25' => _("Nat. Ins. payment") . '|module=payment&amp;opt=natins',
	//'menu26' => 'משכורת אורי|module=orisalary',
	'top3' => _("Bussiness docs") . '|',
	'menu31' => _("Proforma") . '|module=docsadmin&amp;targetdoc=1',
	'menu32' => _("Delivery doc.") . '|module=docsadmin&amp;targetdoc=2',
	'menu33' => _("Invoice") . '|module=docsadmin&amp;targetdoc=3',
	'menu34' => _("Credit inv.") . '|module=docsadmin&amp;targetdoc=4',
	'menu35' => _("Return doc.") . '|module=docsadmin&amp;targetdoc=5',
	'menu65' => _("Quote") . '|module=docsadmin&amp;targetdoc=7',
	'menu66' => _("Sales Order") . '|module=docsadmin&amp;targetdoc=8',
	'menu36' => _("Receipt") . '|module=receipt',
	'menu37' => _("Invoice receipt") . '|module=docsadmin&amp;targetdoc=3&amp;option=receipt',
	'menu38' => _("Print docs.") . '|module=showdocs',
	'top4' => _("Reports") . '|',
	'menu41' => _("Incomes outcomes") . '|module=tranrep',
	'menu42' => _("Customers owes") . '|module=owe',
	'menu43' => _("Profit & loss") . '|module=profloss',
	'menu44' => _("Monthly Prof. & loss") . '|module=mprofloss',
	'menu45' => _("VAT calculation") . '|module=vatrep',
	'menu46' => _("Balance") . '|module=balance',
	'top5' => _("Reconciliations") . '|',
	'menu51' => _("Bank docs entry") . '|module=bankbook',
	'menu52' => _("Bank recon.") . '|module=extmatch',
	'menu53' => _("Show recon.") . '|module=dispmatch',
	'menu54' => _("Accts. recon.") . '|module=intmatch',
	'top6' => _("Backups") . '|',
	'menu61' => _("Open docs") . '|module=openfrmt',
	'menu64' => _("PCN874") . '|module=pcn874',
	'menu62' => _("General backup") . '|module=backup',
	'menu63' => _("Backup restore") . '|module=backup&amp;step=restore',
	'top7' => _("Support") . '|module=support',
	'contactus' => _("Contact us") . '|module=contactus',
	'demo' => _("Demo") . '|module=demoreg',
	'main1' => _("Main") . '|id=main',
	'mainlogin' => _("Login") . '|action=login'
);

?>

