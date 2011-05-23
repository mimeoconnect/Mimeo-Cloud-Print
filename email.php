<?php 
session_start ();
$Level = "";
include "config.php"; 
include "system/mimeo-rest-client.php";
include "system/xml_string_to_array.php";
include "system/common.php";

define('REGEX_URL','/http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/');

// Make a database connection
mysql_connect($dbserver,$dbuser,$dbpassword) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbname);

/* try to connect */
$inbox = imap_open($Email_HostName,$Email_UserName,$Email_Password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
  
  /* begin output var */
  $output = '';
  
  /* put the newest emails on top */
  rsort($emails);
  
  /* for every email... */
  foreach($emails as $email_number) 
  	{
  	
  	$File_Status = "";
    
    /* get information specific to this email */
    $overview = imap_fetch_overview($inbox,$email_number,0);
    $structure = imap_fetchstructure($inbox,$email_number);
    $message = imap_fetchbody($inbox,$email_number,2);
    
    var_dump($overview[0]);
    
    $Email_Viewed = $overview[0]->seen;
    $Email_Subject = $overview[0]->subject;
    $Email_From = $overview[0]->from;
    $Email_Date = $overview[0]->date;
    $Email_Message = $message;
    
  	$attachments = array();
	if(isset($structure->parts) && count($structure->parts)) {
	
		for($i = 0; $i < count($structure->parts); $i++) {
	
			$attachments[$i] = array(
				'is_attachment' => false,
				'filename' => '',
				'name' => '',
				'attachment' => ''
			);
			
			if($structure->parts[$i]->ifdparameters) {
				foreach($structure->parts[$i]->dparameters as $object) {
					if(strtolower($object->attribute) == 'filename') {
						$attachments[$i]['is_attachment'] = true;
						$attachments[$i]['filename'] = $object->value;
						//echo "1: " .  $object->value . "<br />";
					}
				}
			}
			
			if($structure->parts[$i]->ifparameters) {
				foreach($structure->parts[$i]->parameters as $object) {
					if(strtolower($object->attribute) == 'name') {
						$attachments[$i]['is_attachment'] = true;
						$attachments[$i]['name'] = $object->value;
						//echo "1: " .  $object->value . "<br />";
					}
				}
			}
			
			if($attachments[$i]['is_attachment']) 
				{
				$attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
				if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
					$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
				}
				elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
					$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
				}
			}
		}
	}      
	
	$Is_Attachment = $attachments[1]['is_attachment'];
	
	$Attachment_Filename = $attachments[1]['filename'];
	//echo "File Name " . $Attachment_Filename . "<br />";
	$Attachment_Name = $attachments[1]['name'];
	//echo "File Name " . $Attachment_Name . "<br />";
	$Attachment_Content = $attachments[1]['attachment'];
	
	//echo "Is Attachment " . $Is_Attachment . "<br />";
	
   // echo "Viewed: " . $Email_Viewed . "<br />";
    //echo "Subject: " . $Email_Subject . "<br />";
    echo "From: " . $Email_From . "<br />";
    //echo "Date: " . $Email_Date . "<br /><br />";
    if($Is_Attachment==1)
    	{
  	   $File_Status = "Attachment";
    	}
    else
  	   {
		//echo "Body: " . $Email_Message . "<br /><br /><hr />";
		
        $anylinks = getLinks($Email_Message);
        $AnyLinks = 0;
		foreach($anylinks as $Name => $Value) 
			{
			$Body_Link_URL =  $Name;
		   	$File_Status = "Body Link";   
		   	$AnyLinks = 1; 
		 	}  	
		 	   
		if($AnyLinks==0)
			{
		  	$File_Status = "None";
			} 
				
		
    	}    
    	
    //echo $File_Status . "<br />";
    if($File_Status == "Attachment")
	    {
	    
	    //echo "~" . $Attachment_Content . "~";
    	
		//echo "File Name: " . $Attachment_Filename . "<br />";
		//echo "Name: " . $Attachment_Name . "<br />";
		//echo "Attachment: " . $Attachment_Content . "<br />";    
		
    	$Save_Filename = "files/" . str_replace(" ", "_", $Attachment_Filename);
		
        $fh = fopen($Save_Filename, "w");
        if($fh==false)
            die("unable to create file");
        fputs($fh,$Attachment_Content,strlen($Attachment_Content));
        fclose ($fh);	
        
        $Attachment_Link_URL = "http://nimbus.laneworks.net/" . $Save_Filename;
        
        $PostCheckQuery = "SELECT ID FROM pdf_email WHERE Email_From = '" . $Email_From . "' AND File_URL = '" . $Attachment_Link_URL . "'";
		$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
		
		if($CheckResult && mysql_num_rows($CheckResult))
			{						
			$CheckResult = mysql_fetch_assoc($CheckResult);	
			$Message = $CheckResult;
			}
		else
			{        
        
	        echo "We found a message from " . $Email_From . " that had attachment: " . $Attachment_Link_URL . "<br />";
	        
		    $Message_To = $Email_From;
		    $Message_From = "info@Kinlane.com";
			$Message_Subject = "Print File with Mime: " . $Email_Subject;
			 
			$Headers = "From: " . $Message_From . "\r\n";
			$Headers .= "Reply-To: ". $Message_To . "\r\n";
			$Headers .= "MIME-Version: 1.0\r\n";
			$Headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";		 
			 
			$Message_Body = '<html><body>';
			 $Message_Body .= "We receieved an email from you titled: " . $Email_Subject . "<br />";
			 $Message_Body .= "To print your file please follow <a href=" . chr(34) . "http://nimbus.laneworks.net?url=" . urlencode($Attachment_Link_URL) . chr(34) . ">this link</a><br />";
			 $Message_Body .= '</html></body>';
			 
			 echo "TO:" . $Message_To . "<br />";
			 
			 if (mail($Message_To, $Message_Subject, $Message_Body, $Headers)) 
			 	{
			   	echo("<p>Message successfully sent!</p>");
			  	} 
			 else 
			  	{
			   	echo("<p>Message delivery failed...</p>");
			  	}   
	
			// Record This
			$Email_Date = date('Y-m-d H:i:s', strtotime($Email_Date));
			$File_URL = $Attachment_Link_URL;
			$query = "INSERT INTO pdf_email(Email_From,Email_Date,Email_Subject,Email_Message,File_Status,File_URL) VALUES('" . $Email_From . "','" . $Email_Date . "','" . $Email_Subject . "',' ','" . $File_Status . "','" . $File_URL . "')";
			echo $query;
			mysql_query($query) or die('Query failed: ' . mysql_error());		  	
    	
			}
		echo "<hr />";
	    }
  	else if($File_Status == "Body Link")
	    {
	    
        $PostCheckQuery = "SELECT ID FROM pdf_email WHERE Email_From = '" . $Email_From . "' AND File_URL = '" . $Attachment_Link_URL . "'";
		$CheckResult = mysql_query($PostCheckQuery) or die('Query failed: ' . mysql_error());
		
		if($CheckResult && mysql_num_rows($CheckResult))
			{						
			$CheckResult = mysql_fetch_assoc($CheckResult);	
			$Message = $CheckResult;
			}
		else
			{    	    
			
	    	echo "We found a message from " . $Email_From . " that had link in body: " . $Body_Link_URL . "<br />";
			//echo "File Name: " . $Attachment_Filename . "<br />";
			//echo "Name: " . $Attachment_Name . "<br />";
			//echo "Attachment: " . $Attachment_Content . "<br />";    
			
		     $Message_To = "kinlane@gmail.com";
			 $Message_From = "info@Kinlane.com";
			 $Message_Subject = "Print File with Mime: " . $Email_Subject;
			 
			$Headers = "From: " . $Message_From . "\r\n";
			$Headers .= "Reply-To: ". $Message_To . "\r\n";
			$Headers .= "MIME-Version: 1.0\r\n";
			$Headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";		 
			 
			$Message_Body = '<html><body>';
			 $Message_Body .= "We receieved an email from you titled: " . $Email_Subject . "<br />";
			 $Message_Body .= "To print your file please follow <a href=" . chr(34) . "http://nimbus.laneworks.net?url=" . urlencode($Body_Link_URL) . chr(34) . ">this link</a><br />";
			 $Message_Body .= '</html></body>'; 
			 
			 if (mail($Message_To, $Message_Subject, $Message_Body,$Headers)) 
			 	{
			   	echo("<p>Message successfully sent!</p>");
			  	} 
			 else 
			  	{
			   	echo("<p>Message delivery failed...</p>");
			  	}  
			  	
			// Record This
			$Email_Date = date('Y-m-d H:i:s', strtotime($Email_Date));
			$File_URL = $Body_Link_URL;
			$query = "INSERT INTO pdf_email(Email_From,Email_Date,Email_Subject,Email_Message,File_Status,File_URL) VALUES('" . $Email_From . "','" . $Email_Date . "','" . $Email_Subject . "','" . $Email_Message . "','" . $File_Status . "','" . $File_URL . "')";
			echo $query;
			mysql_query($query) or die('Query failed: ' . mysql_error());	

			}
    	echo "<hr />";
	    }	  
	 else
	 	{  
    	
	 	echo "Email from " . $Email_From . " had no attachments or links to files.";
    	
		// Record This
		$Email_Date = date('Y-m-d H:i:s', strtotime($Email_Date));
		$File_URL = "";
		$query = "INSERT INTO pdf_email(Email_From,Email_Date,Email_Subject,Email_Message,File_Status,File_URL) VALUES('" . $Email_From . "','" . $Email_Date . "','" . $Email_Subject . "','" . $Email_Message . "','" . $File_Status . "','" . $File_URL . "')";
		//echo $query;
		mysql_query($query) or die('Query failed: ' . mysql_error()); 

		echo "<hr />";
	 	}
    
  	}
}

imap_close($inbox);
?>
<br /><br />
<p align="center"><strong>Email Pull Process</strong></p>
<p align="center"><strong>This will be scheduled and run every minute.</strong></p>
<p align="center"><a href="file-source.php">Return to File Source Page</a></p>