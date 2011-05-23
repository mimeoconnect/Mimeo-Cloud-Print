<?php 
session_start (); 
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Flipbook Javascript</title>
<style type="text/css">
.buttons {
	font-family: arial;
	font-size: 8pt;
	border-bottom:1px solid #282828; border-right:1px solid #282828; border-top:1px solid #8794A0; border-left:1px solid #8794A0;
	background-color: #E4E4E4;
}	
	
</style>
<script type="text/javascript" src="/js/bookflip.js"></script>
</head>
<body>

<?php
$Level = "";

// Mimeo REST Client
require_once "system/mimeo-rest-client.php";

// Utility for Converting XML String to Array
require_once "system/xml_string_to_array.php";

// This should be your Mimeo Sandbox Account Info
$root_url = "connect.sandbox.mimeo.com/2010/09/";
$user_name = "mimeo.restapi@applications.mimeo.com";
$password = "Mimeo123";

// Create a REST Client Object
$rest = new MimeoRESTclient($root_url,$user_name,$password);

//Replace with your Document
$AmazonS3_Document_URL = $_SESSION['Display_URL'];
//echo $AmazonS3_Document_URL;
//We have a URL.  What do we know about it?
$Source_URL_Array = parse_url($AmazonS3_Document_URL);

// Lets break up the incoming URL
$Host = $Source_URL_Array['host'];
//echo "Host: " . $Host . "<br />";
$Path = $Source_URL_Array['path'];
//echo "Path: " . $Path . "<br />";
$File_Array = explode("/",$Path);
$File_Name = $File_Array[sizeof($File_Array)-1];	
//echo "File Name: " . $File_Name . "<br />";	
$File_Name2 = $File_Array[sizeof($File_Array)-2];	
//echo "File Name 2: " . $File_Name2 . "<br />";	

$url = "ProofService/PrepareProof?documentSource=" . $AmazonS3_Document_URL;
//echo "URL: " . $url . "<br />";
$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();
//echo $output;
$XMLArray = xmlstr_to_array($output);

$Mimeo_Product_ID = $XMLArray['ProductId'];
//echo "Mimeo Product ID: " . $Mimeo_Product_ID . "<br />";

$Mimeo_Name = $XMLArray['Name'];
//echo "Mimeo Name: " . $Mimeo_Name . "<br />";

$Mimeo_Description = $XMLArray['Description'];
//echo "Mimeo Description: " . $Mimeo_Description . "<br />";

$Mimeo_IsRipped = $XMLArray['IsRipped'];
//echo "Mimeo IsRipped: " . $Mimeo_IsRipped . "<br />";

$Mimeo_PageCount = $XMLArray['PageCount'];
//echo "Mimeo Page Count: " . $Mimeo_PageCount . "<br />";

$Mimeo_HasErrors = $XMLArray['HasErrors'];
//echo "Mimeo HasErrors: " . $Mimeo_HasErrors . "<br />";

$Mimeo_MaxPageHeight = $XMLArray['MaxPageHeight'];
//echo "Mimeo MaxPageHeight: " . $Mimeo_MaxPageHeight . "<br />";

$Mimeo_MaxPageWidth = $XMLArray['MaxPageWidth'];
//echo "Mimeo MaxPageWidth: " . $Mimeo_MaxPageWidth . "<br />";

$Mimeo_IsBroken = $XMLArray['IsBroken'];
//echo "Mimeo IsBroken: " . $Mimeo_IsBroken . "<br />";

//Send Request to Mimeo Proof Service to Get Document Proof Information
$url = "ProofService/Proof/" . $Mimeo_Product_ID;
$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();

$XMLArray = xmlstr_to_array($output);
?>
<div align="center">	

	<div id="myBook" style="display:none;">	
	
		<div name="Home" style="background:#055123 url(/images/assets/standard_cover.png);color:#ffffff;">
			<div align=center>
			<h1><?php echo $_SESSION['Display_Title'];?></h1>
			</div>
		</div>
		
		<?php
		for ($Page = 0; $Page < $Mimeo_PageCount; $Page++)
			{
			//$Small_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['SmallImage'];
			//$Small_Image_URL = str_replace("http://connect.sandbox.mimeo.com/","",$Small_Image_URL);
			//$Small_Image_URL = str_replace("http://connect.mimeo.com/","",$Small_Image_URL);			

			//$Small_Image_Name = str_replace("ProofService/Proof/","",$Small_Image_URL);
			//$Small_Image_Name = str_replace("/","-",$Small_Image_Name);

			//$rest->createRequest(trim($Small_Image_URL),"GET","");
			//$rest->sendRequest();
			//$image = $rest->getResponseBody();	
	    	//$Save_Filename = 'proof-images/' . $Small_Image_Name . ".jpg";
			
	        //$fh = fopen($Save_Filename, "w");
	        //if($fh==false)
	        //    die("unable to create file");
	        //fputs($fh,$image,strlen($image));
	        //fclose ($fh);				       
		
			$Large_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['LargeImage'];
			$Large_Image_URL = str_replace("http://connect.sandbox.mimeo.com/","",$Large_Image_URL);
			$Large_Image_URL = str_replace("http://connect.mimeo.com/","",$Large_Image_URL);			
			
			$Large_Image_Name = str_replace("ProofService/Proof/","",$Large_Image_URL);
			$Large_Image_Name = str_replace("/","-",$Large_Image_Name);			
						
			$rest->createRequest($Large_Image_URL,"GET","");
			$rest->sendRequest();
			$image = $rest->getResponseBody();		

	    	$Save_Filename = 'proof-images/' . $Large_Image_Name . ".jpg";
			//echo "Saving " . $Save_Filename . "<br />";
			
	        $fh = fopen($Save_Filename, "w");
	        if($fh==false)
	            die("unable to create file");
	        fputs($fh,$image,strlen($image));
	        fclose ($fh);	

	        
	        //echo $Save_Filename;
			?>
	
			<div name="Page <?php echo $Page;?>" style="background:#FFFFFF; color:#000000; overflow: scroll;">
  				<img src="http://nimbus.laneworks.net/<?php echo $Save_Filename;?>" width="500" />
			</div>
	
			<?php
			}
		?>
	
	</div>

	<input type=button class="buttons" value=prev onclick="goprev();">
	<input type=button class="buttons" value=next onclick="gonext();"> 
	Page: <select id="flipSelect" style="display:none;"></select> 

</div>

<script type="text/javascript">

/****************************************************************************
//** Software License Agreement (BSD License)
//** Book Flip slideshow script- Copyright 2008, Will Jones (coastworx.com)
//** This Script is wholly developed and owned by CoastWorx.com 
//** Copywrite: http://wwww.coastworx.com/
//** You are free to use this script so long as this coptwrite notice 
//** stays intact in its entirety
//** You are NOT Permitted to claim this script as your own or
//** use this script for commercial purposes without the express
//** permission of CoastWorx Technologies!
//** Full credit to Scott Schiller (schillmania.com) for soundManager
//***************************************************************************/

var myPageW=500; //page width
var myPageH=550; //page height

var pageBorderWidth=1; //adjust border width
var pageBorderColor="#000000"; //border color
var pageBorderStyle="solid"; //border style eg. solid, dashed or dotted.

var pSpeed=20; //page speed, 20 is best for Opera browser. Less is faster
var pSound=false; //change to true for flip sound effect, if true, install SoundManager from schillmania.com

var showBinder=false; //change to false for no binder
var binderImage = "parchmentring2.gif"; //location of center binder
var binderWidth = 20; //width of center binder image
var numPixelsToMove = 20;//number of pixels to move per frame, more is faster but less smooth

//You must create the place holder for the dropdown on this page with an id attribute, eg. <select id="flipSelect" style="display:none;"></select>
var selectNavigation=true; //this builds a drop down list of pages for auto navigation.
allowPageClick=true; //allow clicking page directly
doIncrementalAutoFlip = false; //set this to true if you want to flip to selected page incrementally(ie.see each page turn)

ini();

</script>	
	
</body>
</html>