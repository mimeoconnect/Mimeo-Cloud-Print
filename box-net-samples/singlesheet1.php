<?php

$Level = "../";

//  Code for this is available at: https://github.com/mimeoconnect/Mimeo-Connect-Cloud-Print-API---REST-Client
require_once "../system/mimeo-rest-client.php";

$root_url = "connect.sandbox.mimeo.com/2010/09/";
$user_name = "mimeo.restapi@applications.mimeo.com";
$password = "Mimeo123";

$rest = new MimeoRESTClient($root_url,$user_name,$password);

$template = "custom";

// Set the PDF URL incoming from Box.net
$PDF_URL = $_REQUEST['url'];

//  This is an existing document template - Sample - Sheet Sheet - 1 Panel - Premium White Paper
//  A custom template can be used or other sample templates can be requested
$documentTemplateId = "e5122322-c5b0-4de9-8e7a-d535fb397b0d";

$url = "OrderService/NewProduct?template=" . $template . "&documentTemplateId=" . $documentTemplateId;

//  Create New Document Template
$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();

//Store Order Template for Manipulation
$xml = new SimpleXMLElement($output);
$OrderXML = new SimpleXMLElement($output);

// Product Info
echo "<br /><br /><strong>Product</strong><br /><br />";
echo "<strong>Information</strong><br />";
echo "Template Type: " . $xml->Product->Template . "<br />";
echo "DocumentTemplateId: " . $xml->Product->DocumentTemplateId . "<br />";
echo "DocumentTemplateName: " . $xml->Product->DocumentTemplateName . "<br />";

///Get the Content for this Product
echo "<br /><br /><strong>Content</strong><br />";
$ProductContent = $xml->Product->Content;
foreach ($ProductContent->DocumentSection as $DocumentSection) {
  echo "Source: " . $DocumentSection->Source . "<br />";
  echo "Range: " . $DocumentSection->Range . "<br /><br />";
}

//  This is a one page single sheet.  So only 1 PDF is needed with a single range.
$xml->Product->Content->DocumentSection[0]->Source = $PDF_URL;
$xml->Product->Content->DocumentSection[0]->Range = "1";

// Set Shipping Information
$xml->Addresses->RecipientAddress->CompanyName = "Mimeo";
$xml->Addresses->RecipientAddress->Name = "Kin Lane";
$xml->Addresses->RecipientAddress->FirstName = "Kin";
$xml->Addresses->RecipientAddress->LastName = "Lane";
$xml->Addresses->RecipientAddress->CareOf = "Mimeo";
$xml->Addresses->RecipientAddress->Street = "460 Park Avenue South";
$xml->Addresses->RecipientAddress->ApartmentOrSuite = " ";
$xml->Addresses->RecipientAddress->City = "New York";
$xml->Addresses->RecipientAddress->StateOrProvince = "NY";
$xml->Addresses->RecipientAddress->PostalCode = "10016";
$xml->Addresses->RecipientAddress->Country = "US";
$xml->Addresses->RecipientAddress->TelephoneNumber = "555-555-5555";
$xml->Addresses->RecipientAddress->Email = "kin.lane@mimeo.com";
$xml->Addresses->RecipientAddress->IsResidential = "false";

///Set the order quantity
$xml->Details->OrderQuantity = "1";

//Set the XML to Send Back
$SendXML = $xml->asXML();

// Send Order XML back to get shipping options.
$rest = new MimeoRESTClient($root_url,$user_name,$password);
$url = "OrderService/GetShippingOptions";
$rest->createRequest($url,"POST","");
$rest->setHeader("Content-Type","application/xml");
$rest->setBody($SendXML);
$rest->sendRequest();
$shipopts = $rest->getResponseBody();

//Store Order Template for Manipulation
$xml = new SimpleXMLElement($shipopts);

$ShippingChoice = "";

foreach ($xml->Details->ShippingOptions->ShippingOption as $ShippingOption) {

	//I'm just going to set the shipping choice to each one, we'll endup with last
	$ShippingOptionID = $ShippingOption->Id;
	$ShippingChoice = $ShippingOption->Id;
	$ShippingName = $ShippingOption->Name;
	$ShippingCharge = $ShippingOption->Charge;
	$ShippingDeliveryDate = $ShippingOption->DeliveryDate;
	
	}
	
///Set the order ShippingChoice to whatever the last choice was
$xml->Details->ShippingChoice = $ShippingChoice;

$xml->Details->ShippingOptions = "";

//Set the XML to Send Back
$SendXML = $xml->asXML();

// Send order XML back to get quote for this order
$rest = new MimeoRESTClient($root_url,$user_name,$password);
$url = "OrderService/GetQuote";
$rest->createRequest($url,"POST","");
$rest->setHeader("Content-Type","application/xml");
$rest->setBody($SendXML);
$rest->sendRequest();
$orderquote = $rest->getResponseBody();

//Store Order Template for Manipulation
$xml = new SimpleXMLElement($orderquote);

// Display Order Quote Information
echo "ShippingOptions: " . $xml->Details->ShippingOptions . "<br />";
echo "ShippingChoice: " . $xml->Details->ShippingChoice . "<br />";
echo "ProductPrice: " . $xml->Details->ProductPrice . "<br />";
echo "ShippingPrice: " . $xml->Details->ShippingPrice . "<br />";
echo "HandlingPrice: " . $xml->Details->HandlingPrice . "<br />";
echo "TaxPrice: " . $xml->Details->TaxPrice . "<br />";
echo "TotalPrice: " . $xml->Details->TotalPrice . "<br />";
echo "OrderId: " . $xml->Details->OrderId . "<br />";
echo "OrderQuantity: " . $xml->Details->OrderQuantity . "<br />";

//Set the XML to Send Back
$SendXML = $xml->asXML();

// Sending XML back to place order
$rest = new MimeoRESTClient($root_url,$user_name,$password);
$url = "OrderService/PlaceOrder";
$rest->createRequest($url,"POST","");
$rest->setHeader("Content-Type","application/xml");
$rest->setBody($SendXML);
$rest->sendRequest();
$orderinfo = $rest->getResponseBody();
$xml = new SimpleXMLElement($orderinfo);

//Set Order ID
$Order_ID = $xml->OrderFriendlyId;	

// Done - Order Placed for Box.net document
?>





