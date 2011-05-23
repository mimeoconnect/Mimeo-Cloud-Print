<?php
session_start ();

$Level = "";  
include "config.php";
include "system/class_mimeo_order_service.php";
include "system/mimeo-rest-client.php";
include "system/xml_string_to_array.php";

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

if(isset($_REQUEST['PDF_ID']))
	{
	$PDF_ID = $_REQUEST['PDF_ID'];
	}
else if(isset($_POST['PDF_ID']))
	{
	$PDF_ID = $_POST['PDF_ID'];
	}
else
	{
	// If there is no PDF we need to go back and find a source.
	header ("Location: file-source.php");	
	}
	
$IP_Address = $_SERVER['REMOTE_ADDR'];
//echo "ip address: " . $IP_Address . "<br />";

//Grab the PDF Order Information
$PDFQuery = "SELECT * FROM pdf_order WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' ORDER BY ID DESC";
//echo $PDFQuery;
$PDFResult = mysql_query($PDFQuery) or die('Query failed: ' . mysql_error());

if($PDFResult && mysql_num_rows($PDFResult))
	{						
	$PDF = mysql_fetch_assoc($PDFResult);	
	$Display_Title = trim($PDF['Title']);
	$Published_By = trim($PDF['Published_By']);
	$Quantity = $PDF['Quantity'];
	$Order_Email = $PDF['Order_Email'];
	
	$Delivery_Name = $PDF['Delivery_Name'];
	$Delivery_Phone = $PDF['Delivery_Phone'];
	$Delivery_Address1 = $PDF['Delivery_Address1'];
	$Delivery_Address2 = $PDF['Delivery_Address2'];
	$Delivery_City = $PDF['Delivery_City'];
	$Delivery_State = $PDF['Delivery_State'];
	$Delivery_Zip = $PDF['Delivery_Zip'];
	$Delivery_Country = $PDF['Delivery_Country'];
	if($Delivery_Country=='')
		{
		$Delivery_Country = 'US';
		}
	
	$Billing_Name = $PDF['Billing_Name'];
	$Billing_Phone = $PDF['Billing_Phone'];
	$Billing_Address1 = $PDF['Billing_Address1'];
	$Billing_Address2 = $PDF['Billing_Address2'];
	$Billing_City = $PDF['Billing_City'];
	$Billing_State = $PDF['Billing_State'];
	$Billing_Zip = $PDF['Billing_Zip'];
	$Billing_Country = $PDF['Billing_Country'];
	if($Billing_Country=='')
		{
		$Billing_Country = 'US';
		}
			
	$Card_Number = $PDF['Card_Number'];
	$Expiration_Month = $PDF['Expiration_Month'];
	$Expiration_Year = $PDF['Expiration_Year'];
	$CVC_Number = $PDF['CVC_Number'];
	
	$Delivery_Method = $PDF['Delivery_Method'];
	$Shipping_Option_ID = $PDF['Shipping_Option_ID'];
	}	

$Order_Approved = false;

if(isset($_POST['Card_Number']))
	{
	$Card_Number = $_POST['Card_Number'];
	$Expiration_Month = $_POST['Expiration_Month'];
	$Expiration_Year = $_POST['Expiration_Year'];
	$CVC_Number = $_POST['CVC_Number'];
	}
	
	/// Incoming Quantity
if(isset($_REQUEST['quantity']))
	{
	$Quantity = $_REQUEST['quantity'];
	}
else if(isset($_POST['quantity']))
	{
	$Quantity = $_POST['quantity'];
	}		
else if(isset($_SESSION['quantity']))
	{
	$Quantity = $_SESSION['quantity'];
	}		
else
	{
	$Quantity = 1;
	}	
	
//Let's record the Document_ID with appropriate settings

// In each of the builder pages (book or single sheet), when it gets Item Quote it sets Document_ID based upon selected settings
//  Here we need to look up settings based upon document_ID and set them...the reverse of building
//  We are doing bound and single sheet right now

//echo "here:" . $_SESSION['Order_Type'] . "<br />";
if(isset($_SESSION['Order_Type']) && $_SESSION['Order_Type']=='purchase')
	{

	// Update for bound documents
	if($_SESSION['Document_Type'] == "bound")
		{

		//  Gets a style, size and color for doucument
		$DocumentQuery = "SELECT Document_ID, Style, Size, Color FROM document WHERE Document_ID = '" . $_SESSION['Document_ID'] . "'";
		//echo $DocumentQuery;
		$DocumentResult = mysql_query($DocumentQuery) or die('Query failed: ' . mysql_error());
		
		if($DocumentResult && mysql_num_rows($DocumentResult))
			{						
			$Document = mysql_fetch_assoc($DocumentResult);	
			
			// Retrieve Style, Size and Color
			$Style = $Document['Style'];
			$Size = $Document['Size'];
			$Color = $Document['Color'];
			}

		// Get Item Quote
		//echo "Get Item quote!<br />";
		$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
		$ItemQuote = $MimeoOrderService->getItemQuote($PDF_ID,$Style,$Size,$Color,$Quantity,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);
		
		// Get Order Quote
		//echo "Get Shipping Options quote!<br />";
		$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
		$ItemQuote = $MimeoOrderService->getShippingOptions($PDF_ID,$root_url,$user_name,$password,$IP_Address);			
		
		// Set Shipping Option
		$DeliveryQuery = "SELECT Delivery_Method FROM pdf_order WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
		///echo $DocumentQuery;
		$DeliverResult = mysql_query($DeliveryQuery) or die('Query failed: ' . mysql_error());
		
		if($DeliverResult && mysql_num_rows($DeliverResult))
			{						
			$Delivery = mysql_fetch_assoc($DeliverResult);	
			
			// Retrieve Style, Size and Color
			$Delivery_Method = $Delivery['Delivery_Method'];
			
			// Get Shipping Option ID
			$ShipOptionQuery = "SELECT Shipping_Option_ID FROM pdf_order_shipping_options WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' AND Shipping_Option_Name = '" . $Delivery_Method . "'";
			///echo $DocumentQuery;
			$ShipOptionResult = mysql_query($ShipOptionQuery) or die('Query failed: ' . mysql_error());
			
			if($ShipOptionResult && mysql_num_rows($ShipOptionResult))
				{						
				$Ship_Opt = mysql_fetch_assoc($ShipOptionResult);	
				
				// Retrieve Style, Size and Color
				$Shipping_Option_ID = $Ship_Opt['Shipping_Option_ID'];
						
				$OrderLogQuery = "UPDATE pdf_order SET ";
				
				$OrderLogQuery .= "Shipping_Option_ID = '" . $Shipping_Option_ID . "'";
				
				$OrderLogQuery .= " WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
				//echo $OrderLogQuery . "<br />";
				mysql_query($OrderLogQuery) or die('Query failed: ' . mysql_error());					
						
				}			
			
			}		
		
		// Get Order Quote
		//echo "Get Order quote!<br />";
		$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
		$ItemQuote = $MimeoOrderService->getOrderQuote($PDF_ID,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);
		
		}		
	}	
	
// Now we eed to send to payment gateway, for right now we'll just approve.
$Order_Approved = true;

// Submit Order
$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
$Order = $MimeoOrderService->placeOrder($PDF_ID,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);

?>
<br /><br />
<p align="center"><img src="http://kinlane-productions.s3.amazonaws.com/mimeo/mimeo_connect_logo.jpg" /></p>
<p align="center" style="font-size: 14px;"><strong>Box.net Confirmation Page</strong></p>
<p align="center">Your Order #: <?php echo $Order;?></p>
<p align="center">An email has been sent to <?php echo $Order_Email;?>, with more information.</strong></p>
<p align="center"><a href="javascript:self.close();"><strong>Close Window</strong></a></p>