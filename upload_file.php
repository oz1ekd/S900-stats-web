 <?php
include ("createkml.php");
include 'kmlconfig.php';

 
 $fn = "";
 if ( (($_FILES["file"]["type"] == "text/plain") ||
       ($_FILES["file"]["type"] == "text/comma-separated-values") ||
       ($_FILES["file"]["type"] == "text/csv") ||
       ($_FILES["file"]["type"] == "application/vnd.ms-excel") ||
       ($_FILES["file"]["type"] == "application/gzip") ||
       ($_FILES["file"]["type"] == "application/x-gzip-compressed") ||
       ($_FILES["file"]["type"] == "application/x-gzip")) &&  
       ($_FILES["file"]["size"] < 300000) )
  { 
   if ($_FILES["file"]["error"] > 0)
     {
     echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
     }
   else
     {
//     echo "Upload   : " . $_FILES["file"]["name"] . "<br />";
//     echo "Type     : " . $_FILES["file"]["type"] . "<br />";
//     echo "Size     : " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
//     echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
     // remove unwanted characters from filename
 		 $_FILES["file"]["name"] = filename_safe($_FILES["file"]["name"]);
       move_uploaded_file($_FILES["file"]["tmp_name"],getcwd() . $SERVER_UPLOAD_DIR . $_FILES["file"]["name"]);
     }
     // handle gzip file
     $fn = $_FILES["file"]["name"];
     $patterna = '/\.csv\.gz$/mi'; 
     $patternb = '/\.csv$/im'; 

     if (preg_match ($patterna, $fn) == 1)
     {
      copy(getcwd() .  $SERVER_UPLOAD_DIR . $fn , getcwd() . $SERVER_STATSSTORE_DIR . $fn );
      gunzip_f(getcwd() .  $SERVER_UPLOAD_DIR . $fn);
      unlink(getcwd() .  $SERVER_UPLOAD_DIR . $fn);
      $fn = substr($fn,0,strlen($fn)-3);
     }
     else if (preg_match ($patternb, $fn) == 1)
     {
      gzip_f(getcwd() .  $SERVER_UPLOAD_DIR . $fn);
      copy(getcwd() .  $SERVER_UPLOAD_DIR . $fn . ".gz" , getcwd() . $SERVER_STATSSTORE_DIR . $fn . ".gz");
      unlink(getcwd() .  $SERVER_UPLOAD_DIR . $fn . ".gz");
     }
     else 
     {
       echo "<center> <h2>*** Invalid file! ***<br /> </h2>";
       echo $_FILES["file"]["name"] . '<br /></center>';
       unlink ( getcwd() .  $SERVER_UPLOAD_DIR  . $_FILES["file"]["name"] );
       echo "<meta http-equiv='refresh' content='5;url=index.php'>";
       exit();
		 }
		 // filename with extension .csv
     if ( MainCreateKML( $fn ) == NULL)
     {
       echo "<center> <h2>*** Invalid file content! ***<br /> </h2>";
       echo $_FILES["file"]["name"] . '<br /></center>';
       unlink ( getcwd() .  $SERVER_UPLOAD_DIR  . $_FILES["file"]["name"] );
       echo "<meta http-equiv='refresh' content='5;url=index.php'>";
       exit();
		 }     	
  	 $fn  = substr($fn,0,strlen($fn)-3);
     // cleanup file housekeeping
  	 // Remove KMZ file
  	 if (file_exists(getcwd() . $SERVER_KMZSTORE_DIR . $fn . "kmz"))
       unlink ( getcwd() . $SERVER_KMZSTORE_DIR . $fn . "kmz" ) or die("Unable to delete file! " . $fkmz );
     // copy KMZ file
     copy(getcwd() .  $SERVER_UPLOAD_DIR . $fn . "kmz"  , getcwd() . $SERVER_KMZSTORE_DIR . $fn . "kmz" ) or die("Unable to copy file! " . $fn . "kmz");
  	 // Remove CSV file
     unlink ( getcwd() .  $SERVER_UPLOAD_DIR . $fn . "csv"  ) or die("Unable to delete file! " . $fn . "csv" );
  	 // Remove KML file
     unlink ( getcwd() .  $SERVER_UPLOAD_DIR . $fn . "kml" ) or die("Unable to delete file! " . $fn . "kml" );

     $fn = substr($_FILES["file"]["name"],0,strpos($_FILES["file"]["name"],'.'));
     $fn  = $fn . ".kmz";
     echo "<meta http-equiv='refresh' content='0;url=http://";
     echo $SERVER_PATH;
     echo "/index.php?statsfile=" . $fn ."'>";
   }
 else
   {
     echo "Invalid file";
     echo "Upload: " . $_FILES["file"]["name"] . "<br />";
     echo "Type: " . $_FILES["file"]["type"] . "<br />";
     echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
		// show file info for 25 seconds
     echo "<meta http-equiv='refresh' content='25;url=http://'>";
  }
  
############################################################################################################
  
  function gzip_f($filename)
{
  $fnr = fopen($filename, 'rb');
  $fngz = gzopen($filename . ".gz", "w9");
  while (!feof($fnr)) 
  {
  	$data = fread($fnr, 8192);
  	gzwrite($fngz, $data);
  }
  gzclose($fngz);
  fclose($fnr);
}

function gunzip_f($filename)
{
  $fnr = fopen(substr($filename,0,strlen($filename)-3), 'wb');
  $fngz = gzopen($filename, "rb");
  if ( $fngz)
  while (!feof($fngz)) 
  {
  	$data = gzread($fngz, 8192);
  	fwrite($fnr, $data);
  }
  gzclose($fngz);
  fclose($fnr);
}
 
 function filename_safe($filename) {
// 
$pattern = array('/[0-9A-z\_\.]*/', '/-/', '/ /'); 
// $pattern = array('/^[a-zA-Z0-9]+\.[a-zA-Z]{3,4}$/', '/-/', '/ /'); 
$replace = array('$0', '_', '_'); 

// Return filename
 return preg_replace($pattern, $replace, $filename); 

 }
 ?> 
