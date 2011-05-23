<?php

require_once "../libraries/mimeo-rest-client.php";
require_once "../libraries/xml_string_to_array.php";

//Sandbox
$root_url = "connect.sandbox.mimeo.com/2010/09/";
$user_name = "mimeo.restapi@applications.mimeo.com";
$password = "Mimeo123";

//$root_url = "connect.mimeo.com/2010/09/";
//$user_name = "kin.lane@mimeo.com";
//$password = "m1m30!23";

$rest = new MimeoRESTclient($root_url,$user_name,$password);

$documentSource = "http://documents.scribd.com.s3.amazonaws.com/docs/2kjfqelznknvkv5.pdf?t=1284562355";

$url = "ProofService/PrepareProof?documentSource=" . $documentSource;

$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();

$XMLArray = xmlstr_to_array($output);

?>
<textarea rows="10" cols="55"><?php echo $output;?></textarea>
<p><strong>I pull a Document from Scribd and Send to ProofService/PrepareProof</strong></p>
<?php

$Product_ID = $XMLArray['ProductId'];
echo "Product ID: " . $Product_ID . "<br />";

$Name = $XMLArray['Name'];
echo "Name: " . $Name . "<br />";

$Description = $XMLArray['Description'];
echo "Description: " . $Description . "<br />";

$IsRipped = $XMLArray['IsRipped'];
echo "IsRipped: " . $IsRipped . "<br />";

$PageCount = $XMLArray['PageCount'];
echo "Page Count: " . $PageCount . "<br />";

$HasErrors = $XMLArray['HasErrors'];
echo "HasErrors: " . $HasErrors . "<br />";

$MaxPageHeight = $XMLArray['MaxPageHeight'];
echo "MaxPageHeight: " . $MaxPageHeight . "<br />";

$MaxPageWidth = $XMLArray['MaxPageWidth'];
echo "MaxPageWidth: " . $MaxPageWidth . "<br />";

$IsBroken = $XMLArray['IsBroken'];
echo "IsBroken: " . $IsBroken . "<br />";
?>
<p><strong>Then I send that ID to ProofService/Proof/</strong></p>
<?php
$documentId = "702e6c5b-35d8-4ffb-8d3c-cf779d9a13eb";
$url = "ProofService/Proof/" . $Product_ID;

$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();
$XMLArray = xmlstr_to_array($output);
?>
<p><strong>Displayed Proofed Images for PDF</strong></p>
<?php 
for ($Page = 0; $Page < $PageCount; $Page++)
	{
	?>
	<?php
	$Small_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['SmallImage'];
	//This is temporary fix.  I was getting image paths back without version in URL.
	$Small_Image_URL = str_replace("http://connect.sandbox.mimeo.com/ProofService/","http://connect.sandbox.mimeo.com/2010/09/ProofService/",$Small_Image_URL);
	$Small_Image_URL = str_replace("http://connect.mimeo.com/ProofService/","https://connect.mimeo.com/2010/09/ProofService/",$Small_Image_URL);
	?><img src="<?php echo $Small_Image_URL;?>" /><?php
	
	$Large_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['LargeImage'];
	//This is temporary fix.  I was getting image paths back without version in URL.  
	$Large_Image_URL = str_replace("http://connect.sandbox.mimeo.com/ProofService/","http://connect.sandbox.mimeo.com/2010/09/ProofService/",$Large_Image_URL);
	$Large_Image_URL = str_replace("http://connect.mimeo.com/ProofService/","https://connect.mimeo.com/2010/09/ProofService/",$Large_Image_URL);	
	?><img src="<?php echo $Large_Image_URL;?>" /><br /><br />
	<?php
	}
?>

