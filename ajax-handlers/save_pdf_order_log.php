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
$Name = $_POST['Name'];
$Value = $_POST['Value'];
$Type = $_POST['Type'];
$IP_Address = $_POST['IP_Address'];

///Get latest quote
$PDFLogQuery = "SELECT * FROM pdf_order_log WHERE $PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' ORDER BY ID DESC";
$PDFLogResult = mysql_query($PDFLogQuery) or die('Query failed: ' . mysql_error());

if($PDFLogResult && mysql_num_rows($PDFLogResult))
	{						
	$PDFLog = mysql_fetch_assoc($PDFLogResult);
	
	if($Type=='text')
		{
		$SaveFieldQuery = "UPDATE pdf_order_log SET " . $Name . " = '" . $Value . "' WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
		}
	else
		{
		$SaveFieldQuery = "UPDATE pdf_order_log SET " . $Name . " = " . $Value . " WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
		}	
	mysql_query($SaveFieldQuery) or die('Query failed: ' . mysql_error());		
	
	}	 
?>