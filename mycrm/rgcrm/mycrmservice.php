<?php
/*+*******************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ********************************************************************************/
if(isset($_REQUEST['service']))
{
	if($_REQUEST['service'] == "customerportal")
	{
		include("soap/customerportal.php");
	}
	elseif($_REQUEST['service'] == "firefox")
	{
		include("soap/firefoxtoolbar.php");
	}
	elseif($_REQUEST['service'] == "wordplugin")
	{
		include("soap/wordplugin.php");
	}
	elseif($_REQUEST['service'] == "thunderbird")
	{
		include("soap/thunderbirdplugin.php");
	}
	else
	{
		echo "No Service Configured for ". strip_tags($_REQUEST[service]);
	}
}
else
{
	echo "<h1>mycrmCRM Soap Services</h1>";
	echo "<li>mycrmCRM Outlook Plugin EndPoint URL -- Click <a href='mycrmservice.php?service=outlook'>here</a></li>";
	echo "<li>mycrmCRM Word Plugin EndPoint URL -- Click <a href='mycrmservice.php?service=wordplugin'>here</a></li>";
	echo "<li>mycrmCRM ThunderBird Extenstion EndPoint URL -- Click <a href='mycrmservice.php?service=thunderbird'>here</a></li>";
	echo "<li>mycrmCRM Customer Portal EndPoint URL -- Click <a href='mycrmservice.php?service=customerportal'>here</a></li>";
	echo "<li>mycrmCRM WebForm EndPoint URL -- Click <a href='mycrmservice.php?service=webforms'>here</a></li>";
	echo "<li>mycrmCRM FireFox Extension EndPoint URL -- Click <a href='mycrmservice.php?service=firefox'>here</a></li>";
}


?>
