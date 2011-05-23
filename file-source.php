<?php 
session_start ();
$Level = ""; 
include "config.php";
include "system/class_mimeo_order_service.php";
include "system/mimeo-rest-client.php";
include "system/xml_string_to_array.php";

if(isset($_FILES["uploadfile"]))
{
//echo $_FILES["uploadfile"]["type"];
if ($_FILES["uploadfile"]["type"] == "application/pdf")
  {
  if ($_FILES["uploadfile"]["error"] > 0)
    {
   // echo "Error: " . $_FILES["uploadfile"]["error"] . "<br />";
    }
  else
    {
   // echo "Upload: " . $_FILES["uploadfile"]["name"] . "<br />";
   // echo "Type: " . $_FILES["uploadfile"]["type"] . "<br />";
   // echo "Size: " . ($_FILES["uploadfile"]["size"] / 1024) . " Kb<br />";
   // echo "Stored in: " . $_FILES["uploadfile"]["tmp_name"];
   
	if(isset($_POST['type']))
		{
		$type = $_POST['type'];
		}	 
	if(isset($_POST['quantity']))
		{
		$quantity = $_POST['quantity'];
		}			
	if(isset($_POST['docid']))
		{
		$docid = $_POST['docid'];
		}	
	if(isset($_POST['Order_Type']))
		{
		$Order_Type = $_POST['Order_Type'];
		}				

    if (file_exists("upload/" . $_FILES["uploadfile"]["name"]))
      {
      //echo $_FILES["uploadfile"]["name"] . " already exists. ";
      unlink($RooteSiteFolder . "/upload/" . $_FILES["uploadfile"]["name"]);
      
      move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"upload/" . $_FILES["uploadfile"]["name"]);
      $PDF_URL = "http://" . $RootSiteURL . "/upload/" . $_FILES["uploadfile"]["name"];
      
      //echo $PDF_URL . "<br />";
            
      }
    else
      {
      
      move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"upload/" . $_FILES["uploadfile"]["name"]);
      $PDF_URL = $RootSiteURL . "/upload/" . $_FILES["uploadfile"]["name"];
      
      //echo $PDF_URL . "<br />";
      
      }    
      
    $Send_URL = "url=" . urlencode($PDF_URL);  
      
	if(isset($type))
		{
		//echo $type . "<br />";
		$Send_URL .= "&type=" . urlencode($type);
		}	  
		
	if(isset($quantity))
		{
		//echo $quantity . "<br />";
		$Send_URL .= "&quantity=" . urlencode($quantity);
		}	  

	if(isset($docid))
		{
		//echo $docid . "<br />";
		$Send_URL .= "&docid=" . urlencode($docid);
		}
		
    if(isset($Order_Type))
		{
		//echo $docid . "<br />";
		$Send_URL .= "&Order_Type=" . urlencode($Order_Type);
		}		
      
      // Send to process
     //echo "Sending to: http://" . $RootSiteURL . "/index.php?" . $Send_URL . "<br />";
     header ("Location: " .  "http://" . $RootSiteURL . "/index.php?" . $Send_URL);
    
    }
  }
else
  {
  echo "Invalid file";
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="overflow: hidden; background: transparent;">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Mimeo Cloud Printer</title>
<meta name="Description" content=""/>
<meta name="Keywords" content=""/>

<link href="/css/site.css" rel="stylesheet" type="text/css" />

</head>
<body>

<div id="step3" style="border: 2px solid #000;">
	<div>		
		<div align="center">
			<center></center>
			<table cellpadding="25" sellscpaing="25" align="center" border="0" width="100%">
				<tr>
					<td align="center" width="50%" valign="top">
			
						<table cellpadding="10" sellscpaing="10" align="center" border="0" width="100%">
							<tr>
								<td align="left" colspan="4">
									<ol>
										<li id="delivery-address" style="width: 500px;">
											<h2 style="width: 30px;"></h2>
											<div class="section-header" style="width: 500px; padding-top: 3px;">Cloud Platform(s)</div>
										</li>			
									</ol>
								</td>
							</tr>				
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/scribd-logo.jpg" width="100" />
								</td>
								<td align="center">
								
									<form action="index.php">
									<input name="url" id="url" type="text" class="textinput" size="40" value="" />
									
								</td>		
								<td align="center" width="100">
								
									<!-- Begin Build File Using Scribd Source Button -->
									<button type="submit" class="blueblackrounded">
										<span>Build Document From File</span>
									</button>
									<!-- End Build File Using Scribd Source Button -->
									</form>
									
								</td>
							</tr>	
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/amazon_aws_logo.jpg" width="100" />
								</td>
								<td align="center">
									<form action="index.php">
									<input name="url" id="url" type="text" class="textinput" size="40" value="" />
								</td>		
								<td align="center" width="100">
								
									<!-- Begin Build File Using PDF URL Source Button -->
									<button type="submit" class="blueblackrounded">
										<span>Build Document From File</span>
									</button>
									<!-- End Build File Using PDF URL Source Button -->
									</form>	
												
								</td>
							</tr>	
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/box_logo.png" width="50" />
								</td>
								<td align="center">
									<form action="index.php">
									<input name="url" id="url" type="text" class="textinput" size="40" value="" />
								</td>		
								<td align="center" width="100">
								
									<!-- Begin Build File Using PDF URL Source Button -->
									<button type="submit" class="blueblackrounded">
										<span>Build Document From File</span>
									</button>
									<!-- End Build File Using PDF URL Source Button -->
									</form>		
												
								</td>
							</tr>			
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/dropbox_logo.jpg" width="100" />
								</td>
								<td align="center">
									<form action="index.php">
									<input name="url" id="url" type="text" class="textinput" size="40" value="" />
								</td>		
								<td align="center" width="100">
								
									<!-- Begin Build File Using PDF URL Source Button -->
									<button type="submit" class="blueblackrounded">
										<span>Build Document From File</span>
									</button>
									<!-- End Build File Using PDF URL Source Button -->
									</form>		
												
								</td>
							</tr>					
							<tr>
								<td align="left" colspan="4"><br /></td>
							</tr>						
							<tr>				
								<td align="left" colspan="4">
									<ol>
										<li id="billing-address" style="height: 25px;">
											<h2 style="width: 30px;"></h2>
											<div class="section-header" style="width: 500px; padding-top: 3px;">Email</div>
										</li>			
									</ol>
								</td>
							</tr>	
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/email-into-folder.jpg" width="100" />
								</td>						
								<td>
									<p style="padding-left: 50px; padding-top: 15px;"><strong>print@mimeo.com</strong></p>
								</td>
								<td align="center" width="100">
								
									<!-- Begin Build File Using PDF URL Source Button -->
									<button type="button" class="blueblackrounded" onclick="location.href='email.php'">
										<span>Process Emails</span>
									</button>
									<!-- End Build File Using PDF URL Source Button -->
									</form>		
												
								</td>						
							</tr>	
							<tr>
								<td align="left" colspan="4"><br /></td>
							</tr>						
							<tr>				
								<td align="left" colspan="4">
									<ol>
										<li id="payment" style="height: 25px;">
											<h2 style="width: 30px;"></h2>
											<div class="section-header" style="width: 500px; padding-top: 3px;">Twitter</div>
										</li>			
									</ol>
								</td>
							</tr>					
							<tr>
								<td style="width: 50px;"></td>
								<td align="center">
									<img src="/images/twitter_logo.jpg" width="100" />
								</td>						
								<td>
									<p style="padding-left: 50px; padding-top: 15px;"><strong>use hashtag #printtomimeo</strong></p>
								</td>
								<td align="center" width="100">
								
									<!-- Begin Build File Using PDF URL Source Button -->
									<button type="button" class="blueblackrounded" onclick="location.href='twitter.php'">
										<span>Process Tweets</span>
									</button>
									<!-- End Build File Using PDF URL Source Button -->
									</form>		
												
								</td>							
							</tr>	
						</table>
					</td>
					<td align="center" width="50%" valign="top">
					
						<table cellpadding="5" cellspacing="4" align="center" border="0" width="100%">
					
							<tr>				
								<td align="left">
									<ol style="float: left;">
										<li id=delivery-option style="height: 35px; text-align:left; width: 600px;">
											<h2 style="width: 30px;"></h2>
											<div class="section-header" style="width: 600px; padding-top: 0px;">Widgets, Embeds and Buttons</div>
										</li>			
									</ol>
								</td>
							</tr>					
							<tr>
								<td>	
					
									<table cellpadding="10" cellspacing="10" align="center" border="0" width="100%">
								
										<tr>				
											<td align="left">								
												<strong>Order Custom Document w/ Build and Purchase</strong>
											</td>
										</tr>
										<tr>				
											<td align="left">	
											
												<table cellpadding="2" cellspacing="2" align="center" border="0" width="100%">
											
													<tr>
														<td align="center" width="20%">
															<!-- This widget goes straight to index, then builder -->
															<form action="index.php" method="post">		
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="25" />															
															<input type="hidden" name="Order_Type" value="build" />														
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="1" class="textinput" />																
															<input name="url" id="url" type="text" class="textinput" size="4" value="" />
														</td>		
														<td align="center" width="15%">
															<button type="submit" class="blueblackrounded">
																<span>Submit Link</span>
															</button>
															</form>			
														</td>
														<td align="center" width="22%">
															<!-- This widget uploads a pdf to this page, then goes on to index, builder -->															
															<form action="file-source.php" method="post" enctype="multipart/form-data">
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="5" />															
															<input type="hidden" name="Order_Type" value="build" />
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />								
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="1" class="textinput" />																							
															<input name="uploadfile" id="uploadfile" type="file" class="textinput" size="1" value="" />
														</td>		
														<td align="center" width="15%">
															<button type="submit" class="blueblackrounded">
																<span>Upload PDF</span>
															</button>
															</form>			
														</td>		
														<td align="center" width="28%">
															<!-- This button goes straight to index, then builder -->
															<form action="index.php" method="post">
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="5" />															
															<input type="hidden" name="Order_Type" value="build" />
															<!-- document - quantity -->
															<input type="hidden" name="quantity" value="[quantity goes here]" />																
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />					
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="2" class="textinput" />																										
															<input name="url" id="url" type="hidden" value="http://kinlane-productions.s3.amazonaws.com/pdf-samples/Building Super Scalable Systems in the Cloud.pdf" />														
															<button type="submit" class="blueblackrounded">
																<span>Preset URL</span>
															</button>
															</form>			
														</td>																											
													</tr>												
													
												</table>
											</td>
										</tr>										
										<tr>				
											<td align="left">				
												<strong>Order Custom Document w/ and Purchase</strong>	
											</td>
										</tr>		
										<tr>				
											<td align="left">	
											
												<table cellpadding="5" cellspacing="4" align="center" border="0" width="100%">
											
													<tr>
														<td align="center" width="20%">
															<!-- This widget goes straight to checkout -->
															<form action="index.php">			
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="5" />															
															<input type="hidden" name="Order_Type" value="checkout" />												
															<!-- document - id (from central or custom document / product ID) -->
															<input type="hidden" name="docid" value="aba38df8-56be-4f66-815f-41c0df13b0cb" />															
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />	
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="1" class="textinput" />																														
															<input name="url" id="url" type="text" class="textinput" size="4" value="" />
														</td>		
														<td align="center" width="15%">
															<button type="submit" class="blueblackrounded">
																<span>Submit Link</span>
															</button>
															</form>			
														</td>
														<td align="center" width="22%">
															<!-- This widget uploads a pdf to this page, then goes straight to checkout -->
															<form action="file-source.php" method="post" enctype="multipart/form-data">
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="5" />															
															<input type="hidden" name="Order_Type" value="checkout" />																
															<!-- document - id (from central or custom document / product ID) -->
															<input type="hidden" name="docid" value="aba38df8-56be-4f66-815f-41c0df13b0cb" />																
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />	
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="1" class="textinput" />																														
															<input name="uploadfile" id="uploadfile" type="file" class="textinput" size="1" value="" />
														</td>		
														<td align="center" width="15%">
															<button type="submit" class="blueblackrounded">
																<span>Upload PDF</span>
															</button>
															</form>			
														</td>		
														<td align="center" width="28%">
															<!-- This button goes straight to checkout -->
															<form action="index.php" method="post">	
															<!-- markup - % (example: 5,10,15,20,25) -->
															<input type="hidden" name="Markup" value="5" />															
															<input type="hidden" name="Order_Type" value="checkout" />															
															<!-- document - id (from central or custom document / product ID) -->
															<input type="hidden" name="docid" value="aba38df8-56be-4f66-815f-41c0df13b0cb" />																
															<!-- document - type (bound, single) -->
															<input type="hidden" name="type" value="bound" />				
															<!-- document - quantity -->
															<input type="text" name="quantity" value="1" size="2" class="textinput" />																											
															<input name="url" id="url" type="hidden" value="http://kinlane-productions.s3.amazonaws.com/pdf-samples/Building Super Scalable Systems in the Cloud.pdf" />														
															<button type="submit" class="blueblackrounded">
																<span>Preset URL</span>
															</button>
															</form>			
														</td>																											
													</tr>												
													
												</table>
											</td> 
										</tr>		
										<tr>				
											<td align="center"><br /></td>							
										</tr>																										
										<tr>				
											<td align="center">													
												<!-- This button goes straight to checkout -->
												<form action="index.php" method="post">	
												
												<input type="hidden" name="Order_Type" value="purchase" />
												
												<!-- Order Email -->										
												<input type="hidden" name="Order_Email" value="kin.lane@mimeo.com" />	
													
												<!-- Shipping Address -->	
												<input type="hidden" name="Shipping" value="1" />										
												<input type="hidden" name="Delivery_Name" value="Kin Lane" />	
												<input type="hidden" name="Delivery_Phone" value="555-555-5555" />	
												<input type="hidden" name="Delivery_Address1" value="460 Park Avenue South" />	
												<input type="hidden" name="Delivery_Address2" value="" />	
												<input type="hidden" name="Delivery_City" value="New York" />	
												<input type="hidden" name="Delivery_State" value="NY" />	
												<input type="hidden" name="Delivery_Zip" value="10016" />	
												<input type="hidden" name="Delivery_Country" value="US" />	
												
												<!-- Billing Address -->
												<input type="hidden" name="Billing" value="1" />	
												<input type="hidden" name="Billing_Name" value="Kin Lane" />	
												<input type="hidden" name="Billing_Phone" value="555-555-5555" />	
												<input type="hidden" name="Billing_Address1" value="460 Park Avenue South" />	
												<input type="hidden" name="Billing_Address2" value="" />	
												<input type="hidden" name="Billing_City" value="New York" />	
												<input type="hidden" name="Billing_State" value="NY" />	
												<input type="hidden" name="Billing_Zip" value="10016" />	
												<input type="hidden" name="Billing_Country" value="US" />
												
												<!-- Payment -->
												<input type="hidden" name="Payment" value="1" />	
												<input type="hidden" name="Card_Number" value="4111111111111111" />	
												<input type="hidden" name="Expiration_Month" value="12" />	
												<input type="hidden" name="Expiration_Year" value="2012" />	
												<input type="hidden" name="CVC_Number" value="123" />	
												
												<!-- Delivery -->
												<input type="hidden" name="Delivery_Method" value="Ground" />			
												
												<!-- markup - % (example: 5,10,15,20,25) -->
												<input type="hidden" name="Markup" value="5" />												
																									
												<!-- document - id (from central or custom document / product ID) -->
												<input type="hidden" name="docid" value="aba38df8-56be-4f66-815f-41c0df13b0cb" />																
												<!-- document - type (bound, single) -->
												<input type="hidden" name="type" value="bound" />					
												Credit Card Purchase&nbsp;&nbsp;&nbsp;
												<!-- document - quantity -->
												<input type="text" name="quantity" value="1" size="2" class="textinput" />																							
												<input name="url" id="url" type="hidden" value="http://kinlane-productions.s3.amazonaws.com/pdf-samples/Building Super Scalable Systems in the Cloud.pdf" />	
												<button type="submit" class="blueblackrounded">
													<span>GO</span>
												</button>												
												</form>
														
											</td>							
										</tr>	
										<tr>				
											<td align="center">								
																
												<!-- This button goes straight to checkout -->
												<form action="index.php" method="post">		
												
												<input type="hidden" name="Order_Type" value="purchase" />			
												
												<!-- Order Email -->										
												<input type="hidden" name="Order_Email" value="kin.lane@mimeo.com" />	
													
												<!-- Shipping Address -->										
												<input type="hidden" name="Delivery_Name" value="Kin Lane" />	
												<input type="hidden" name="Delivery_Phone" value="555-555-5555" />	
												<input type="hidden" name="Delivery_Address1" value="460 Park Avenue South" />	
												<input type="hidden" name="Delivery_Address2" value="" />	
												<input type="hidden" name="Delivery_City" value="New York" />	
												<input type="hidden" name="Delivery_State" value="NY" />	
												<input type="hidden" name="Delivery_Zip" value="100169" />	
												<input type="hidden" name="Delivery_Country" value="US" />	
												
												<!-- Billing Address -->
												<input type="hidden" name="Billing_Name" value="Kin Lane" />	
												<input type="hidden" name="Billing_Phone" value="555-555-5555" />	
												<input type="hidden" name="Billing_Address1" value="460 Park Avenue South" />	
												<input type="hidden" name="Billing_Address2" value="" />	
												<input type="hidden" name="Billing_City" value="New York" />	
												<input type="hidden" name="Billing_State" value="NY" />	
												<input type="hidden" name="Billing_Zip" value="100169" />	
												<input type="hidden" name="Billing_Country" value="US" />
												
												<!-- Delivery -->
												<input type="hidden" name="Delivery_Method" value="Ground" />	
												
												<!-- markup - % (example: 5,10,15,20,25) -->
												<input type="hidden" name="Markup" value="5" />																												
																							
												<!-- document - id (from central or custom document / product ID) -->
												<input type="hidden" name="docid" value="aba38df8-56be-4f66-815f-41c0df13b0cb" />																
												<!-- document - type (bound, single) -->
												<input type="hidden" name="type" value="bound" />	
												Mimeo Credit Purchase
												<!-- document - quantity -->
												<input type="text" name="quantity" value="1" size="2" class="textinput" />																										
												<input name="url" id="url" type="hidden" value="http://kinlane-productions.s3.amazonaws.com/pdf-samples/Building Super Scalable Systems in the Cloud.pdf" />	
												<button type="submit" class="blueblackrounded">
													<span>GO</span>
												</button>
												</form>
											</td>							
										</tr>										
									</table>														
													
								</td>							
							</tr>	
						</table>		
			
			 		</td>							
				</tr>	
			</table>
			</center>
		</div>
	</div>
</div>

 
</body>
</html>
