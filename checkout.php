<?php 
session_start ();
$Level = ""; 

include "config.php";
include "system/class_mimeo_order_service.php";
include "system/mimeo-rest-client.php";
include "system/xml_string_to_array.php";

$IP_Address = $_SERVER['REMOTE_ADDR'];
$Title = "";
$Published_By = "";
$Shipping_Option_ID ="";

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
	
if(isset($_SESSION['Order_Type'])&&$_SESSION['Order_Type']=='checkout')
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
		$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
		$ItemQuote = $MimeoOrderService->getItemQuote($PDF_ID,$Style,$Size,$Color,$Quantity,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);
		
		}		
	}
	
//Grab the PDF Information
$PDFQuery = "SELECT * FROM pdf WHERE ID = " . $PDF_ID;
$PDFResult = mysql_query($PDFQuery) or die('Query failed: ' . mysql_error());

if($PDFResult && mysql_num_rows($PDFResult))
	{						
	$PDF = mysql_fetch_assoc($PDFResult);	
	$Display_Title = trim($PDF['Title']);
	$Display_URL = trim($PDF['URL']);
	$Display_Page_Count = $PDF['Page_Count'];
	}
	
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
	$Order_Email = $PDF['Order_Email'];
	$Shipping_Option_ID = $PDF['Shipping_Option_ID'];
	}		
	
// Get the last quote. 
$PDFLogQuery = "SELECT * FROM pdf_order_log WHERE $PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' ORDER BY ID DESC";
$PDFLogResult = mysql_query($PDFLogQuery) or die('Query failed: ' . mysql_error());

if($PDFLogResult && mysql_num_rows($PDFLogResult))
	{						
	$PDFLog = mysql_fetch_assoc($PDFLogResult);

	$SendXML = $PDFLog['Order_XML'];
	$Document_ID = $PDFLog['Document_ID'];
	$Style = $PDFLog['Style'];
	$Size = $PDFLog['Size'];
	$Color = $PDFLog['Color'];
	}	
	
// Default Setting for Style
if(!isset($Style))
	{
	$Style = "preso";
	}
// Default Setting for Size
if(!isset($Size))
	{
	$Size = "standard";
	}
///Default Setting for Color
if(!isset($Color))
	{
	$Color = "blackwhite";
	}
if(!isset($Quantity) && $Quantity < 1)
	{
	$Quantity = "1";
	}
	
// If we have a shipping option then we have necessary info, lets get quote.
if($Shipping_Option_ID!='')
	{
	//Order Quote
	$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
	$OrderQuote = $MimeoOrderService->getOrderQuote($PDF_ID,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);	
	}
else
	{
	$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
	$ItemQuote = $MimeoOrderService->getItemQuote($PDF_ID,$Style,$Size,$Color,$Quantity,$root_url,$user_name,$password,$IP_Address,$_SESSION['Markup']);
	$OrderQuote = $ItemQuote;
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="overflow: hidden; background: transparent;">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Mimeo Print | Check Out</title>
<meta name="Description" content=""/>
<meta name="Keywords" content=""/>

<link href="/css/site.css" rel="stylesheet" type="text/css" />

<!-- Jquery Love -->
<script type="text/javascript" src="/js/jquery.js"></script>

<!-- Trying to Keep These JavaScripts as Simple, Self Describing, and Separate as I Can -->
<script type="text/javascript">

	function saveDeliveryField(field)
		{

		Table = 'pdf_order';
		Field_Type = 'text';
		Field = field.name;
		Value = field.value;
		
		  //Save Published By
		  $.post("ajax-handlers/save_field.php", {
			  	ID_Name:'PDF_ID',
			  	ID:<?php echo $PDF_ID;?>,
			  	Table:Table,
			  	Field_Type:Field_Type,
			  	Field:Field,
			  	Value:Value,
			  	PDF_ID : <?php echo $PDF_ID;?>,
				IP_Address : '<?php echo $IP_Address;?>'
				}, function(data){
			  }) 

		//Let's see if there is enough to get order quote.
		f = document.CheckoutForm;
		if(f.Delivery_Address1.value!='' && f.Delivery_City.value!='' && f.Delivery_State.value!='' && f.Delivery_Zip.value!='' && f.Delivery_Country.value!='')
			{
			// Get Shipping Options
			getShippingOptions();
			}
		}	

	function saveBillingField(field)
		{
	
		Table = 'pdf_order';
		Field_Type = 'text';
		Field = field.name;
		Value = field.value;
		
		  //Save Published By
		  $.post("ajax-handlers/save_field.php", {
			  	ID_Name:'PDF_ID',
			  	ID:<?php echo $PDF_ID;?>,
			  	Table:Table,
			  	Field_Type:Field_Type,
			  	Field:Field,
			  	Value:Value,
			  	PDF_ID : <?php echo $PDF_ID;?>,
				IP_Address : '<?php echo $IP_Address;?>'
				}, function(data){
			  }) 	
		}		

	function saveShippingOption(field)
		{
		Table = 'pdf_order';
		Field_Type = 'text';
		Field = field.name;
		Value = field.value;
		
		  //Save Published By
		  $.post("ajax-handlers/save_field.php", {
			  	ID_Name:'PDF_ID',
			  	ID:<?php echo $PDF_ID;?>,
			  	Table:Table,
			  	Field_Type:Field_Type,
			  	Field:Field,
			  	Value:Value,
			  	PDF_ID : <?php echo $PDF_ID;?>,
				IP_Address : '<?php echo $IP_Address;?>'
				}, function(data){

				// Get new order quote
				getOrderQuote();
					
			  }) 			
		}

	function getShippingOptions()
		{  
		  $.post("ajax-handlers/get_shipping_options.php", {PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
		   if (data.length>0){ 
			     $("#deliver-option-container").html(data); 
			   } 			  
		  }) 
		}	
	 
	function getOrderQuote()
		{  
		  $.post("ajax-handlers/get_order_quote.php", {PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>',Markup : '<?php echo $_SESSION["Markup"];?>'}, function(data){
		   if (data.length>0){ 
		     $("#order-total-container").html(data); 
		   } 
		  }) 
		}

</script>

</head>
<body>

<div id="step3" class="generic">
	<div id="frame-body">		
		<div class="content" id="checkout-holder">
			<form method="post" action="confirmation.php" name="CheckoutForm">
			<input type="hidden" name="PDF_ID" value="<? echo $PDF_ID; ?>" />
			<div>
			<ol style="border: 1px soid #000;">
				<li id="delivery-address">
					<h2 style="width: 30px;"></h2>
					<div class="section-header">DELIVERY ADDRESS</div>
					<div class="fieldwrap">
						<label for="Delivery_Name">Full Name<span class="req">*</span></label>
						<input name="Delivery_Name" id="Delivery_Name" type="text" class="textinput" maxlength="255" value="<?php echo $Delivery_Name;?>" onChange="saveDeliveryField(this);" />
					</div>
					<div class="fieldwrap">
						<label for="Delivery_Phone">Phone<span class="req">*</span></label>
						<input name="Delivery_Phone" id="Delivery_Phone" type="text" class="textinput" maxlength="255" value="<?php echo $Delivery_Phone;?>" onChange="saveDeliveryField(this);" />
					</div>
					<div class="fieldwrap">
						<label for="Delivery_Address1">Address<span class="req">*</span></label>
						<input name="Delivery_Address1" id="Delivery_Address1" type="text" class="textinput" maxlength="255" value="<?php echo $Delivery_Address1;?>" onChange="saveDeliveryField(this);"  />
					</div>
					<div class="fieldwrap">
						<label for="Delivery_Address2">Address 2</label>
						<input name="Delivery_Address2" id="Delivery_Address2" type="text" class="textinput" maxlength="255" value="<?php echo $Delivery_Address2;?>" onChange="saveDeliveryField(this);"  />
					</div>
					<div class="fieldwrap fieldwrap-city">
						<label for="Delivery_City">City<span class="req">*</span></label>
						<input name="Delivery_City" id="Delivery_City" type="text" class="textinput" maxlength="255" value="<?php echo $Delivery_City;?>" onChange="saveDeliveryField(this);"  />
					</div>
					<div class="fieldwrap fieldwrap-state fieldwrap-state-delivery">
						<label for="Delivery_State">State<span class="req">*</span></label>
						<div id="delivery-state-container">
						
							<select name="Delivery_State" id="Delivery_State" class="required" title="Enter County/State" onChange="saveDeliveryField(this);">
							<option value="">  </option>
							<?php 
							$PostQuery = "SELECT abbrev FROM states ORDER BY abbrev ASC";				
							//echo $PostQuery . "<br />";
							$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());							
							while ($PostRow = mysql_fetch_assoc($PostResult))
								{                              
						         ?>
						         <option value="<?php echo $PostRow['abbrev'];?>"<?php if($Delivery_State==$PostRow['abbrev']){?>selected<?php }?>><?php echo $PostRow['abbrev'];?></option>
						         <?php
								 }					
							?>
							</select>

						</div>
					</div>
					<div class="fieldwrap fieldwrap-zip">
						<label for="Delivery_Zip">Zip<span class="req">*</span></label>
						<div id="delivery-zip-container">
							<input name="Delivery_Zip" id="Delivery_Zip" type="text" class="textinput" value="<?php echo $Delivery_Zip;?>" onChange="saveDeliveryField(this);" />
						</div>
					</div>
					<div class="clear"></div>
					<div class="fieldwrap fieldwrap-country">
						<label for="Delivery_Country">Country<span class="req">*</span></label>
						<select name="Delivery_Country" id="Delivery_Country" title="Enter Country" class="required" onChange="saveDeliveryField(this);" onChange="saveBillingField(this);">
							<?php 
							$PostQuery = "SELECT iso,name FROM country ORDER BY name ASC";				
							//echo $PostQuery . "<br />";
							$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());							
							while ($PostRow = mysql_fetch_assoc($PostResult))
								{                              
						         ?>
						         <option value="<?php echo $PostRow['iso'];?>"<?php if($Delivery_Country==$PostRow['iso']){?>selected<?php }?>><?php echo $PostRow['name'];?></option>
						         <?php
								 }					
							?>
						</select>
					</div>
					<div class="clear"></div>
				</li>			
				<li id="billing-address">
					<h2 style="width: 30px;"></h2>
					<div class="section-header">BILLING ADDRESS</div>
					<!--
					<div class="fieldwrap" id="use-shipping" style="width: 90px; border: 1px solid #000; float: right; top: -30px; position: aboslute;">
						<label class="check">
							<input type="checkbox" name="useShipping" id="use-shipping" checked="checked" />
							<span>Use shipping</span>
						</label>
					</div>
					-->
					<div class="clear fixclear"></div>
					<div class="fieldwrap">
						<label for="Billing_Name">Full Name<span class="req">*</span></label>
						<input name="Billing_Name" id="Billing_Name" type="text" class="textinput required validate-minimum-two-words" maxlength="255" value="<?php echo $Billing_Name;?>" onChange="saveBillingField(this);" />
					</div>
					<div class="fieldwrap">
						<label for="Billing_Phone">Phone<span class="req">*</span></label>
						<input name="Billing_Phone" id="Billing_Phone" type="text" class="textinput required validate-digits" maxlength="255" value="<?php echo $Billing_Phone;?>" onChange="saveBillingField(this);" />
					</div>
					<div class="fieldwrap">
						<label for="Billing_Address1">Address<span class="req">*</span></label>
						<input name="Billing_Address1" id="Billing_Address1" type="text" class="textinput required" maxlength="255" value="<?php echo $Billing_Address1;?>" onChange="saveBillingField(this);"  />
					</div>
					<div class="fieldwrap">
						<label for="Billing_Address2">Address 2</label>
						<input name="Billing_Address2" id="Billing_Address2" type="text" class="textinput" maxlength="255" value="<?php echo $Billing_Address2;?>" onChange="saveBillingField(this);"  />
					</div>
					<div class="fieldwrap fieldwrap-city">
						<label for="Billing_City">City<span class="req">*</span></label>
						<input name="Billing_City" id="Billing_City" type="text" class="textinput required" maxlength="255" value="<?php echo $Billing_City;?>" onChange="saveBillingField(this);"  />
					</div>
					<div class="fieldwrap fieldwrap-state fieldwrap-state-billing">
						<label for="Billing_State">State<span class="req">*</span></label>
						<div id="billing-state">
							<select name="Billing_State" id="Billing_State" class="required" title="Enter County/State" onChange="saveBillingField(this);">
							<option value="">  </option>
							<?php 
							$PostQuery = "SELECT abbrev FROM states ORDER BY abbrev ASC";				
							//echo $PostQuery . "<br />";
							$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());							
							while ($PostRow = mysql_fetch_assoc($PostResult))
								{                              
						         ?>
						         <option value="<?php echo $PostRow['abbrev'];?>"<?php if($Billing_State==$PostRow['abbrev']){?>selected<?php }?>><?php echo $PostRow['abbrev'];?></option>
						         <?php
								 }					
							?>
							</select>
						</div>
					</div>
					<div class="fieldwrap fieldwrap-zip">
						<label for="Billing_Zip">Zip<span class="req">*</span></label>
						<div id="billing-zip-container">
							<input name="Billing_Zip" id="Billing_Zip" type="text" class="textinput" value="<?php echo $Billing_Zip;?>" onChange="saveBillingField(this);" />
						</div>
					</div>
					<div class="clear"></div>
					<div class="fieldwrap fieldwrap-country">
						<label for="Billing_Country">Country<span class="req">*</span></label>
						<select name="Billing_Country" id="Billing_Country" title="Enter Country" class="required" onChange="saveBillingField(this);">
							<?php 
							$PostQuery = "SELECT iso,name FROM country ORDER BY name ASC";				
							//echo $PostQuery . "<br />";
							$PostResult = mysql_query($PostQuery) or die('Query failed: ' . mysql_error());							
							while ($PostRow = mysql_fetch_assoc($PostResult))
								{                              
						         ?>
						         <option value="<?php echo $PostRow['iso'];?>"<?php if($Billing_Country==$PostRow['iso']){?>selected<?php }?>><?php echo $PostRow['name'];?></option>
						         <?php
								 }					
							?>
						</select>
					</div>
					<div class="clear"></div>
				</li>
				<li id="delivery-methods-container">
					<div id="delivery-option">
						<h2 style="width: 30px;"></h2>
						<div class="section-header">DELIVERY OPTION</div>
						<div class="clear"></div>
						<div class="options" id="deliver-option-container" style="padding-top: 10px;">
							
							<?php 
							$ShipOptions = "";
							$OptionQuery = "SELECT * FROM pdf_order_shipping_options WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' ORDER BY Shipping_Option_Charge ASC";								
							//echo $OptionQuery . "<br />";
		
							$OptionResult = mysql_query($OptionQuery) or die('Query failed: ' . mysql_error());

							if($OptionResult && mysql_num_rows($OptionResult))
								{							
								while ($OptionRow = mysql_fetch_assoc($OptionResult))
									{                              
									$ShipOptions = $ShipOptions . '<label class="radio">';
									if($Shipping_Option_ID == $OptionRow['Shipping_Option_ID'])
										{
										$ShipOptions = $ShipOptions . '<input name="Shipping_Option_ID" type="radio" value="' . $OptionRow['Shipping_Option_ID'] . '" id=" Shipping_Option_ID" onClick="saveShippingOption(this);" checked />';
										}
									else
										{
										$ShipOptions = $ShipOptions . '<input name="Shipping_Option_ID" type="radio" value="' . $OptionRow['Shipping_Option_ID'] . '" id="Shipping_Option_ID" onClick="saveShippingOption(this);"  />';
										}									
									$ShipOptions = $ShipOptions . ' <strong>' . $OptionRow['Shipping_Option_Name'] . '</strong> by ' . date('m-d-Y',strtotime($OptionRow['Shipping_Option_Delivery_Date'])) . ' for <strong>$' . number_format($OptionRow['Shipping_Option_Charge'], 2, '.', ',') . '</strong>';
									$ShipOptions = $ShipOptions . '</label><br />';							                    
									 }	
								echo $ShipOptions;
								}
							else
								{
								?>
								<p align="center" style="font-size: 11px; color:#FF0000; padding-left: 30px;padding-top: 40px;">
									Delivery Options Will Show<br />After You Complete Delivery Information
								</p>									
								<?php
								}						
							?>
							
						</div>
					</div>
				</li>					
				<li id="payment">	
					<h2 style="width: 30px;"></h2>
					<div class="section-header">Payment</div>
					<div class="fieldwrap">
						<label for="Card_Number">Card number</label>
						<input name="Card_Number" id="Card_Number" type="text" class="textinput validate-creditcard" maxlength="255" value="" autocomplete="off" />
					</div>
					<div class="fieldwrap fieldwrap-exp">
						<label class="title">Expiry date</label>
						<label class="hidden" for="Expiration_Month">mm</label>
						<select name="Expiration_Month" id="Expiration_Month" title="Select month" class="required textinput">
							<option value="">mm</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
						<span>/</span>
						<label class="hidden" for="Expiration_Year">yy</label>
						<select name="Expiration_Year" id="Expiration_Year" title="Select year" class="required textinput">
							<option value="">yy</option>
							<option value="2011">2011</option>
							<option value="2012">2012</option>
							<option value="2013">2013</option>
							<option value="2014">2014</option>
						</select>
					</div>
					<div class="fieldwrap fieldwrap-cvc">
						<label for="CVC_Number">CVV</label>
						<input name="CVC_Number" id="CVC_Number" type="text" class="textinput" maxlength="3" autocomplete="off" />
						<a href="#" id="cvv-popup">What's this?</a>
					</div>
					<div class="clear"></div>
					<div id="cardtype" style="display: none;">
						<span class="data" style="display: none;">&nbsp;</span>
					</div>
					<div class="clear"></div>
				</li>
				<li id="email">
 					<h2 style="width: 30px;"></h2>
					<div class="section-header">EMAIL</div>
					<div class="fieldwrap">
						<input name="Order_Email" id="Order_Email" type="text" class="textinput" value="<?php echo $Order_Email;?>" onChange="saveBillingField(this);" />
					</div>
					<div class="clear"></div>
				</li>																					
			</ol>	
		
			<div class="clear"></div>	
		
			<table cellpadding="5" cellspacing="5" width="97%" border="0" align="right">
				<tr>
					<td colspan="2" align="center">
						<!-- Begin Book Cost -->
							<span style="font-size: 22px;"><strong>Book Cost:</strong></span>
							<span style="font-size: 22px;" id="order-total-container"><?php echo $OrderQuote;?></span></span>
						<!-- End Book Cost -->					
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<!-- Begin Agree To Terms -->
							<input type="checkbox" name="agree" id="agree" class="" />
							<span style="font-size: 14px;">I agree to the <a href="#" id="terms-popup" >terms and conditions</a><span class="req">*</span></span>
						<!-- End Agree To Terms -->				
					</td>
				</tr>			
				<tr>
					<td align="left">
						<!-- Begin Checkout Button -->
						<button type="button" class="blueblackrounded" onclick="location.href='edit-book.php?PDF_ID=<?php echo $PDF_ID;?>';">
							<span>< Edit Book</span>
						</button>
						<!-- End Checkout Button -->			
					</td>
					<td align="right">
						<script type="text/javascript">
							function ValidateOrder()
							{
							go = true;
							f = document.CheckoutForm;
							if(f.Card_Number.value=='')
								{
								go = false;
								alert("Please provide a credit card number before continuing!");
								}
							if(f.Expiration_Month.value==''&&go==true)
								{
								go = false;
								alert("Please select a credit card expiration month before continuing!");
								}		
							if(f.Expiration_Year.value==''&&go==true)
								{
								go = false;
								alert("Please select a credit card expiration year before continuing!");
								}	
							if(f.CVC_Number.value==''&&go==true)
								{
								go = false;
								alert("Please provide a credit card CVV number before continuing!");
								}
							if(f.agree.checked==false)
								{
								go = false;
								alert("You must agree to the terms and conditions before continuing!");
								}	
							if(go)
								{	
								f.submit();
								}																									
							}
						</script>
						<!-- Begin Purchase Button -->
						<button type="button" class="blueblackrounded" onclick="ValidateOrder();">
							<span>Purchase ></span>
						</button>
						<!-- End Purchase Button -->				
					</td>				
				</tr>			
			</table>			
					
			
		</div>
		</form>
		</div>
	</div>
</div>

 
</body>
</html>
