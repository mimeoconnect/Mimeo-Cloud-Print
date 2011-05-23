<?php
session_start ();
$Level = "";
include "config.php";
include "system/class_mimeo_order_service.php";
include "libraries/mimeo-rest-client.php";
include "libraries/xml_string_to_array.php";

$IP_Address = $_SERVER['REMOTE_ADDR'];
$Display_Title = "";
$Published_By = "";

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
	

//Grab the PDF Information
$PDFQuery = "SELECT * FROM pdf WHERE ID = " . $PDF_ID;
$PDFResult = mysql_query($PDFQuery) or die('Query failed: ' . mysql_error());

if($PDFResult && mysql_num_rows($PDFResult))
	{						
	$PDF = mysql_fetch_assoc($PDFResult);	
	$Display_Title = trim($PDF['Title']);
	$_SESSION['Display_Title'] = $Display_Title;
	//echo $_SESSION['Display_Title'];
	$Display_URL = trim($PDF['URL']);
	$_SESSION['Display_URL'] = $Display_URL;
	$Display_Page_Count = $PDF['Page_Count'];
	}
	
//Grab the PDF Order Information
$PDFQuery = "SELECT * FROM pdf_order WHERE PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "'";
$PDFResult = mysql_query($PDFQuery) or die('Query failed: ' . mysql_error());

if($PDFResult && mysql_num_rows($PDFResult))
	{						
	$PDF = mysql_fetch_assoc($PDFResult);	
	
	if(trim($PDF['Title'])!='')
		{
		$Display_Title = trim($PDF['Title']);
		$_SESSION['Display_Title'] = $Display_Title;
		}
	$Published_By = trim($PDF['Published_By']);
	$Quantity = $PDF['Quantity'];
	$Shipping_Option_ID = $PDF['Shipping_Option_ID'];
	}		
	
// Get the last quote. 
$PDFLogQuery = "SELECT * FROM pdf_order_log WHERE $PDF_ID = " . $PDF_ID . " AND IP_Address = '" . $IP_Address . "' ORDER BY ID DESC";
//echo $PDFLogQuery . "<br />";
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
	
/// If there is a published by, apply a label.
if(strlen($Published_By)>3)
	{
	$Published_By = "Published By: " . $Published_By;
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
$MimeoOrderService = new MimeoOrderService($dbserver,$dbname,$dbuser,$dbpassword);
$ItemQuote = $MimeoOrderService->getItemQuote($PDF_ID,$Style,$Size,$Color,$Quantity,$root_url,$user_name,$password,$IP_Address);

//echo "Style: " . $Style . "<br />";
//echo "Size: " . $Size . "<br />";
//echo "Color: " . $Color . "<br />";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="overflow: hidden; background: transparent;">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Mimeo Print</title>
<meta name="Description" content=""/>
<meta name="Keywords" content=""/>

<link href="/css/site.css" rel="stylesheet" type="text/css" />
<link href="/css/thickbox.css" rel="stylesheet" type="text/css" />

<!-- Jquery Love -->
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/thickbox.js"></script>

<!-- Trying to Keep These JavaScripts as Simple, Self Describing, and Separate as I Can -->
<script type="text/javascript">

	function viewFrontCover()
		{
		document.getElementById('preview-background-image').src = '/images/assets/standard_cover_preview.png';
		changeFor();
		document.getElementById("review-back-option").className = document.getElementById("review-back-option").className.replace(/\selected\b/,'');
		}

	function viewBackCover()
		{
		document.getElementById('preview-background-image').src = '/images/assets/standard_back_preview.png';
		document.getElementById('file-preview-title-text').innerHTML = "";
		document.getElementById('file-preview-for-text').innerHTML = "";
		document.getElementById("review-front-option").className = document.getElementById("review-front-option").className.replace(/\selected\b/,'');
		document.getElementById("review-back-option").className = "selected";	
		}	

	function changeStyle(selectStyle)
		{
		id = selectStyle.id;
		selection = selectStyle.value;
		//alert(selection);
		if(selection=='preso')
			{
			//Set style to preso
			document.getElementById("style-preso").className = "active";
			document.getElementById("style-paperback").className = "";
			document.getElementById("style-magazine").className = "";
			}
		else if(selection=='paperback')
			{
			//Set style to paperback
			document.getElementById("style-preso").className = "";
			document.getElementById("style-paperback").className = "active";
			document.getElementById("style-magazine").className = "";
			}		
		else if(selection=='magazine')	
			{
			//Set style to magainze
			document.getElementById("style-preso").className = "";
			document.getElementById("style-paperback").className = "";
			document.getElementById("style-magazine").className = "active";
			}

		//Get Item Quote 
		getItemQuote();			

		  //Save Published By
		  $.post("save_pdf_order_log.php", {Name : 'Style',Value : selection,Type : 'text',PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
			  
		  }) 
		
		}

	function changeSize(selectSize)
		{
		id = selectSize.id;
		selection = selectSize.value;
		if(selection=='standard')
			{
			//Set size to standard on display
			document.getElementById("size-standard").className = "active";
			document.getElementById("size-travel").className = "";
			}
		else	
			{
			//Set size to travel on display
			document.getElementById("size-standard").className = "";
			document.getElementById("size-travel").className = "active";
			}		

		//Get Item Quote
		getItemQuote();	

		  //Save Published By
		  $.post("save_pdf_order_log.php", {Name : 'Size',Value : selection,Type : 'text',PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
			  
		  }) 		
					
		}	

	function changeColor(selectColor)
		{
		id = selectColor.id;
		selection = selectColor.value;

		if(selection=='color')
			{
			//Set color option to selected
			document.getElementById("ink-color").className = "active";
			document.getElementById("ink-black").className = "";
			}
		else	
			{
			//Set black & white option to selected
			document.getElementById("ink-color").className = "";
			document.getElementById("ink-black").className = "active";
			}		

		  //Get Item Quote
		  getItemQuote();	
		
		  //Save Published By
		  $.post("save_pdf_order_log.php", {Name : 'Color',Value : selection,Type : 'text',PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
			  
		  }) 		
			
		}	

	function changeTitle()
		{
		Title = document.getElementById('Title').value;
		document.getElementById('file-preview-title-text').innerHTML = Title;

		  //Save Published By
		  $.post("save_title.php", {Title:Title,PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
			  }) 
		
		}	

	function changeFor()
		{
		PublishedBy = document.getElementById('Published_By').value;
		if(PublishedBy!='')
			{
			document.getElementById('file-preview-for-text').innerHTML = "Published By:  " + PublishedBy;
			}
		else
			{
			document.getElementById('file-preview-for-text').innerHTML = "";
			}
		
		  //Save Published By
		  $.post("save_published_by.php", {Published_By:PublishedBy,PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
		  }) 
		
		}	

	function getItemQuote()
		{
		
		//Get the Style
		for (var i=0; i < document.productform.documentstyle.length; i++)
		   {
		   if (document.productform.documentstyle[i].checked)
		      {
		      var style = document.productform.documentstyle[i].value;
		      }
		   }

		//Get the Size
		for (var i=0; i < document.productform.documentsize.length; i++)
		   {
		   if (document.productform.documentsize[i].checked)
		      {
		      var size = document.productform.documentsize[i].value;
		      }
		   }

		//Get the Color
		for (var i=0; i < document.productform.documentcolor.length; i++)
		   {
		   if (document.productform.documentcolor[i].checked)
		      {
		      var color = document.productform.documentcolor[i].value;
		      }
		   }	

		Quantity = document.productform.Quantity.value;

		  $("#display_cost").html('*.**'); 

		  $.post("get_item_quote.php", {style : style,size : size,color : color,quantity : Quantity,PDF_ID : <?php echo $PDF_ID;?>,IP_Address : '<?php echo $IP_Address;?>'}, function(data){
			  $("#display_cost").html(data); 
		  }) 
		
		}

	function changeQuantity()
		{
		
		Quantity = document.productform.Quantity.value;

		  //Save Published By
		  $.post("save_quantity.php", {Quantity : Quantity,PDF_ID : '<?php echo $PDF_ID;?>',IP_Address : '<?php echo $IP_Address;?>'}, function(data){

			  //Update Item Quote Now
			  getItemQuote();
			  
			  }) 
		
		}	

</script>

</head>
<body>

<div id="step2" class="generic">
	<div id="frame-body" style="">
		<div class="content">
			<div class="left-panel">
			
				<!-- Begin Preview Container -->
				<div id="preview-container">
				
					<!-- Begin Bread Crumbs -->
					<span class="summary" id="breadcrumbs">DOUBLE SIDED&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $Display_Page_Count;?> PAGES</span>
					<!-- End Bread Crumbs -->
					
					<div id="preview-background-image" style="background: url('images/step2/8x11.png');">
						
						<!-- Begin Preview Image -->
						<a href="flipbook-preview.php?keepThis=true&TB_iframe=true&height=600&width=1000&Display_URL=<?php echo $Display_URL;?>" title="Preview Print Document - <?php echo $_SESSION['Display_Title'];?>" class="thickbox">
						<img src="/images/assets/standard_cover_preview.png" style="margin-top: 50px; margin-bottom: 15px;" id="preview-image" border="0" />
						</a>
						<!-- End Preview Image -->
						
						<!-- Begin Title -->
						<div class="file-preview-title" id="file-preview-title-text">
							<? if(strlen($Display_Title) > 20 && strpos($Display_Title,chr(32)) == 0) {?>Title Too Long<?php } else {?> <?php  echo $Display_Title; }?>
						</div>	
						<!-- End Title -->
						
						<!-- Begin For -->
						<div class="file-preview-for" id="file-preview-for-text"><?php echo $Published_By;?></div>
						<!-- End For -->
							
					</div>
				</div>
				<ul id="preview-links">
					<li class="selected" id="preview-front-option"><a href="#" id="preview-front" onClick="viewFrontCover();"><span>Front</span></a></li>
					<li id="preview-back-option"><a href="#" id="preview-back" onClick="viewBackCover();"><span>Back</span></a></li>
				</ul>
				<div class="clear"></div>	
				<!-- End Preview Container -->
				 			
			</div>
			<div class="right-panel" style="">
			
				<form action="checkout.php" method="post" class="addtocart" id="product-form" name="productform"><div>
				<ol>
					<li id="book-style">
					
						<h2 style="width: 30px;"></h2>
						<div class="section-header">STYLE</div>
					
						<div class="styles">
							<label id="style-preso"<?php if($Style=='preso'){?> class="active"<?php }?>><input name="documentstyle" id="document-style-preso" type="radio" value="preso" onclick="changeStyle(this);"<?php if($Style=='preso'){?> checked="checked"<?php }?> /><span>Preso</span></label>
							<label id="style-paperback"<?php if($Style=='paperback'){?> class="active"<?php }?>><input name="documentstyle" id="document-style-paperback" type="radio" value="paperback" onclick="changeStyle(this);"<?php if($Style=='paperback'){?> checked="checked"<?php }?> /><span>Paperback</span></label>
							<label id="style-magazine"<?php if($Style=='magazine'){?> class="active"<?php }?>><input name="documentstyle" id="document-style-magazine" type="radio" value="magazine" onclick="changeStyle(this);"<?php if($Style=='magazine'){?> checked="checked"<?php }?> /><span>Magazine</span></label>
						</div>
					</li>
					<li id="book-size">
						<h2 style="width: 30px;"></h2>
						<div class="section-header">SIZE</div>
						<label id="size-standard" style="border: 1px solid #FFF;"<?php if($Size=='standard'){?> class="active"<?php }?>><input name="documentsize" id="document-size-standard" type="radio" value="standard" onclick="changeSize(this);"<?php if($Size=='standard'){?> checked="checked"<?php }?> /><span>Standard</span></label>
						<label id="size-travel"<?php if($Size=='travel'){?> class="active"<?php }?>><input name="documentsize" id="document-size-travel" type="radio" value="travel" onclick="changeSize(this);"<?php if($Size=='travel'){?> checked="checked"<?php }?> /><span>Travel</span></label>
					</li>
					<li id="print-pages-in">
						<h2 style="width: 30px;"></h2>
						<div class="section-header">COLOR</div>						
						<label id="ink-black"<?php if($Color=='blackwhite'){?> class="active"<?php }?>><input name="documentcolor" id="document-color-blackwhite" type="radio" value="blackwhite" onclick="changeColor(this);"<?php if($Color=='blackwhite'){?> checked="checked"<?php }?> /><span>Black &amp; White</span></label>
						<label id="ink-color"<?php if($Color=='color'){?> class="active"<?php }?>><input name="documentcolor" id="document-color-color" type="radio" value="color" onclick="changeColor(this);"<?php if($Color=='color'){?> checked="checked"<?php }?>  /><span>Color</span></label>
					</li>
					<li id="personalize-cover">
						<h2 style="width: 30px;"></h2>
						<div class="section-header">COVER</div>							
						<div class="fieldwrap">
							<label for="title-of-book">TITLE</label>
							<input name="Title" type="text" class="textinput" id="Title" maxlength="270" value="<?php echo $Display_Title;?>" onChange="changeTitle();" />
						</div>
						<div class="fieldwrap">
							<label for="Published_By">PUBLISHED BY</label>
							<input name="Published_By" id="Published_By" type="text" class="textinput" maxlength="270" value="<?php echo $Published_By;?>" onChange="changeFor();"/>
						</div>
					</li>
					<li id="quantity">
						<h2 style="width: 30px;"></h2>
						<div class="section-header">QUANTITY</div>
						<div class="fieldwrap">
							<label for="Quantity"></label>
							<input name="Quantity" id="Quantity" value="<?php echo $Quantity;?>" class="textinput" onChange="changeQuantity();" />
						</div>
					</li>
				</ol>
				
				
			</div>
			
			</form>
			<div class="clear"></div>
		</div>
		
		<table cellpadding="5" cellspacing="5" width="97%" border="0" align="right">
			<tr>
				<td colspan="2" align="center">
					<!-- Begin Book Cost -->
						<span style="font-size: 22px;"><strong>COST:</strong></span>
						<span style="font-size: 22px;" id="display_cost"><?php echo $ItemQuote;?></span></span>
					<div class="clear"></div>	
					<!-- End Book Cost -->					
				</td>
			</tr>
			<tr>
				<td align="left">
				
				</td>
				<td align="right">
					<!-- Begin Checkout Button -->
					<button type="button" class="blueblackrounded" onclick="location.href='checkout.php?PDF_ID=<?php echo $PDF_ID;?>';">
						<span>Checkout ></span>
					</button>
					<!-- End Checkout Button -->					
				</td>				
			</tr>			
		</table>	

	</div>
</div>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
</body>
</html>
