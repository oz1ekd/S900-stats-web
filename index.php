<?php
ob_start(); 
echo "<head>";
echo "<link href=\"inc/style.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />";
echo "</head>";
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 
 

include('kmlconfig.php');

?>
<body>
<center>
<?php
$filen = '';

	if (strstr($_SERVER['REQUEST_URI'],'statsfile'))
	{
		$filen = $_GET["statsfile"];
	}
	else 
	{
		// clean upload directory
		//exec ("rm -f " . getcwd() .  "/upload/*.*");
	}

  echo '<p><br>Upload your .csv or .csv.gz file and retrieve the google kmz file for your own use afterwards.</br></br>';
  echo '<b>Disclaimer:</b> You are about to upload a file with statistics data to this web site. By uploading this file you accept that Thrane & Thrane uses the data for analysis. <br>All data sets will be made anonymous.</p> ';

	?>


	<form action="upload_file.php" method="post"
 enctype="multipart/form-data">
 <input type="file" name="file" id="file" size="40" accept="text/plain|text/comma-separated-values|text/csv|application/vnd.ms-excel|application/gzip|application/x-gzip-compressed|application/x-gzip"/> 

 <input type="submit" name="submit" value="Upload"/>
 </form>
<?php

 if (strlen($filen) > 0)
 {
 	echo '<b>Download:</b> <a href="http://';
 	echo $SERVER_PATH . $SERVER_UPLOAD_DIR . $filen;
 	echo '">' . $filen . '</a> </br>'  ;
 }

// echo 'Files are only avaliable during the current session </br>';
	echo '<br/>';
  echo '<script src="//www.gmodules.com/ig/ifr?url=http://dl.google.com/developers/maps/embedkmlgadget.xml&amp;up_kml_url=http://';
    echo $SERVER_PATH . $SERVER_UPLOAD_DIR . $filen . '?rand=' . rand();
    echo '&amp;up_view_mode=earth&amp;up_earth_2d_fallback=0&amp;up_earth_fly_from_space=1&amp;up_earth_show_nav_controls=1&amp;up_earth_show_buildings=1&amp;up_earth_show_terrain=1&amp;up_lookat_range=45000&amp;up_earth_show_roads=1&amp;up_earth_show_borders=1&amp;up_earth_sphere=earth&amp;up_maps_zoom_out=1&amp;up_maps_default_type=map&amp;synd=open&amp;w=900&amp;h=600&amp;border=%23ffffff%7C0px%2C1px+solid+%23004488%7C0px%2C1px+solid+%23ffffff%7C0px%2C1px+solid+%23ffffff%7C0px%2C1px+solid+%23ffffff&amp;output=js"></script>';
?>
 
 </center>
 
 </body></html>
