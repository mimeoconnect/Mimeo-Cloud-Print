<?php
$Level = "../";
include "../config.php";
include "../system/class_mimeo_order_service.php";
include "../system/mimeo-rest-client.php";
include "../system/xml_string_to_array.php";

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$PDF_ID = $_POST['PDF_ID'];
$Title = $_POST['Title'];
$IP_Address = $_POST['IP_Address'];
	
//Pull the last quote for this order
$UpdateQuery = "SELECT MAX(ID) AS ID FROM pdf_order WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
$CheckResult = mysql_query($UpdateQuery) or die('Query failed: ' . mysql_error());

if($CheckResult && mysql_num_rows($CheckResult))
	{						
	$CheckResult = mysql_fetch_assoc($CheckResult);	
	
	$SaveFieldQuery = "UPDATE pdf_order SET Title = '" . $Title . "' WHERE ID = " . $CheckResult['ID'];
	mysql_query($SaveFieldQuery) or die('Query failed: ' . mysql_error());	
	mysql_close();		
	
	}	
 
echo "Saved!";
	
?>