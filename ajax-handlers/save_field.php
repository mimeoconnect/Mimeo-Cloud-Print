<?php
$Level = "../";
include "../config.php";
include "../system/class_mimeo_order_service.php";
include "../system/mimeo-rest-client.php";
include "../system/xml_string_to_array.php";

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

$ID_Name = $_POST['ID_Name'];

$ID = $_POST['ID'];
		
$Table = $_POST['Table'];
//echo "style: " . $Table . "<br />";

$Field_Type = $_POST['Field_Type'];
//echo "Field: " . $Field . "<br />";

$Field = $_POST['Field'];
//echo "Field: " . $Field . "<br />";

$Value = $_POST['Value'];
//echo "Value: " . $Value . "<br />";

$IP_Address = $_POST['IP_Address'];
//echo "Value: " . IP_Address . "<br />";

//We are going to insert this PDF into a database for safe keeping
$SaveFieldQuery = "SELECT * FROM " . $Table . " WHERE " . $ID_Name . " = " . $ID . " AND IP_ADDRESS = '" . $IP_Address . "'";
$CheckResult = mysql_query($SaveFieldQuery) or die('Query failed: ' . mysql_error());

if($CheckResult && mysql_num_rows($CheckResult))
	{						
	$CheckResult = mysql_fetch_assoc($CheckResult);	
	
	//Record this PDF Order
	if($Field_Type == 'text')
		{
		$SaveFieldQuery = "UPDATE " . $Table . " SET " . $Field . " = '" . $Value . "' WHERE " . $ID_Name . " = " . $ID;
		}
	else
		{
		$SaveFieldQuery = "UPDATE " . $Table . " SET " . $Field . " = " . $Value . " WHERE " . $ID_Name . " = " . $ID;
		}	
	mysql_query($SaveFieldQuery) or die('Query failed: ' . mysql_error());	
	mysql_close();		
	
	}	

echo $SaveFieldQuery;
	
?>