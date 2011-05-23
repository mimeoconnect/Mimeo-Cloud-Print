<?php 
$Scribd_API_Key = "3872ph6tehhs6wrk9onsu";
$Scribd_Document_ID = "37360086";
$Scribd_URL = "http://api.scribd.com/api?method=print.getPrintInfo&api_key=" . $Scribd_API_Key . "&doc_id=" . $Scribd_Document_ID;
echo $Scribd_URL . "<br />";
$xml = simplexml_load_file($Scribd_URL); 

$Title = $xml->title;
echo "Title: " . $Title . "<br />";

$Download_Link = $xml->download_link;
echo "Download Link: " . $Download_Link . "<br />";

$Page_Count = $xml->page_count;
echo "Page Count: " . $Page_Count . "<br />";

$Height = $xml->height;
echo "Height: " . $Height . "<br />";

$Width = $xml->width;
echo "Height: " . $Width . "<br />";

$DPI = $xml->dpi;
echo "DPI: " . $DPI . "<br />";
?>
<textarea rows="10" cols="55"><?php echo $xml;?></textarea>
