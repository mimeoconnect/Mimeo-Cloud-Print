<style type="text/css">
<!--
.style1 {font-family: Georgia, "Times New Roman", Times, serif} A:link {background: none; text-decoration: none; color:#000000;} A:visited {background: none; text-decoration: none; color: #000000;} A:active {background: #333333; font-weight:bold; } A:hover {background: #333333; color:#FFFFFF;} .pic img{ border: 1px solid #ccc;}
-->
</style>

<?php

// --- Important --
// You *must* set your API key in the boxconfig file.
// Set debug value to 'true' on line 30 of boxlib file for return output
// Optional auth_token setup in Box Config file for saved sessions

require 'box_config.php';


// Get Ticket to Proceed

$ticket_return = $box->getTicket ();

if ($box->isError()) {
    echo $box->getErrorMsg();
} else {
	
	$ticket = $ticket_return['ticket'];

}

// Get Friends

//$friends = $box->GetFriends ();

// Get Account Tree

$tree = $box->getAccountTree ();



// List files for download


echo " <img src='download.png'/><br/><br/> <div font='georgia' font-size=18px>";

for ($i=0, $tree_count; $i<$tree_count; $i++) {
if (isset($tree['file_name'][$i])){
	
echo "<table bgcolor='#66cc66'  CELLPADDING='15' border='0' ><TR><TD><strong><a href='http://box.net/api/1.0/download/".$_SESSION['auth_token']."/".$tree['file_id'][$i]."'>Download ".$tree['file_name'][$i]."</a></TD></TR></table><img src='tail.png'><br/><br/>";

}}


// Upload File
?>

<img src='upload.png'/><br/><br/>

<form action="<? echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST">
<input type="hidden" name="ticket" value="$max_file_size" />
<input type="hidden" name="auth_token" value="$max_file_size" />
<input type="hidden" name="MAX_FILE_SIZE" value="$max_file_size" />
<input type="file" name="new_file1" />
<input type="submit" name="upload_files" value="Upload File" />
</form>

<?

if (isset($_FILES['new_file1'])) {
	
	$upload = $box->UploadFile  ();
	
	
	if ($upload['status'] == 'upload_ok') {
		echo "You've just uploaded ".$upload['file_name']."!";
		
}else{
	echo "Whoops...".$upload['error'];
}}
	?>
