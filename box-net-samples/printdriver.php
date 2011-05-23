<?php

$Level = "../";

//  Code for this is available at: https://github.com/mimeoconnect/Mimeo-Connect-Cloud-Print-API---REST-Client
require_once "../system/mimeo-rest-client.php";

$root_url = "connect.sandbox.mimeo.com/2010/09/";
$user_name = "[Mimeo Account Email]";
$password = "[Mimeo Account Password]";

// Set the PDF URL incoming from Box.net
$PDF_URL = $_REQUEST['url'];

// Get File Contents
$PDF_Content = fopen($PDF_URL, "w");

// Other Post Content
$Post_Content = "";

$rest = new MimeoRESTClient($root_url,$user_name,$password);
$url = "StorageService/[folder name]/";
$rest->createRequest($url,"POST",$Post_Content,$PDF_Content);
$rest->sendRequest();
$StorageResponse = $rest->getResponseBody();

//echo $StorageResponse;

// Done - Order Placed for Box.net document
?>





