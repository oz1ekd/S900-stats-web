<?php


//#######################################################################################################

$HostInfo = 'SAILOR 900 VSAT';
$HostName = '';
$AcuSerialNumber = '';
$AduSerialNumber = '';
$OldSwVersion = 'before 1.30';
$SwVersion = $OldSwVersion;

$vartype =  array("int","string","float","float","float","string","string","int","int","int","int","int","int","int","int","int","int","int","int","string","string","string","string","int","string","string","int","int","int","int","int");

$IconStyleIndicator = 0;

// used for drawing tracks
$lastCoordinates = 'NoFix'; 

// used to check for ACU restart
$LastLogLine = 0;
$temp = 0;

//#######################################################################################################

$trackLineArrSize = 9;

$trackLine =  
              array("trackLine-Single",  "trackLine-Master",  "trackLine-Slave",
										"trackLine-Single_w","trackLine-Master_w","trackLine-Slave_w",
										"trackLine-Single_e","trackLine-Master_e","trackLine-Slave_e");
	
$trackStylesLineNormal = 
              array("trackLine-Single_n",  "trackLine-Master_n",  "trackLine-Slave_n",
										"trackLine-Single_w_n","trackLine-Master_w_n","trackLine-Slave_w_n",
										"trackLine-Single_e_n","trackLine-Master_e_n","trackLine-Slave_e_n");

$trackStylesLineHighLi = 
              array("trackLine-Single_h",  "trackLine-Master_h",  "trackLine-Slave_h",
										"trackLine-Single_w_h","trackLine-Master_w_h","trackLine-Slave_w_h",
										"trackLine-Single_e_h","trackLine-Master_e_h","trackLine-Slave_e_h");

$trackStylesLineColor = 
              array("ff7fff00","ff7fff00","ff7fff00",
										"ff00d7ff","ff00d7ff","ff00d7ff",
										"ff0000ff","ff0000ff","ff0000ff");
      
//#######################################################################################################

$trackHeadingArrSize = 21;
         
$trackHeading = array("empty","track-0","track-1","track-2","track-3","track-4","track-5",
                "track-6","track-7","track-8","track-9","track-10","track-11","track-12",
                "track-13","track-14","track-15","warning","error","blockingWarning","blockingError");

$trackStylesHeadingNormal = array("track-none_n",
                     "track-0_n","track-1_n","track-2_n","track-3_n",
                     "track-4_n","track-5_n","track-6_n","track-7_n",
                     "track-8_n","track-9_n","track-10_n","track-11_n",
                     "track-12_n","track-13_n","track-14_n","track-15_n",
                     "warning_n","error_n","blockingWarning_n","blockingError_n");

$trackStylesHeadingHighLi = array("track-none_h","track-0_h","track-1_h","track-2_h","track-3_h",
                     "track-4_h","track-5_h","track-6_h","track-7_h","track-8_h",
                     "track-9_h","track-10_h","track-11_h","track-12_h","track-13_h",
                     "track-14_h","track-15_h","warning_h","error_h","blockingWarning_h","blockingError_h");

$trackStylesHeadingPng = array(
									"http://earth.google.com/images/kml-icons/track-directional/track-none.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-0.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-1.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-2.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-3.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-4.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-5.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-6.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-7.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-8.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-9.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-10.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-11.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-12.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-13.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-14.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-15.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-none.png",
                  "http://earth.google.com/images/kml-icons/track-directional/track-none.png",
                  "http://maps.google.com/mapfiles/kml/paddle/B-lv.png",
                  "http://maps.google.com/mapfiles/kml/paddle/B-lv.png");

//#######################################################################################################

//## warning and error parameters
//## texts for parameter list
 $paramwlist = array("Warning levels","RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)");
 $paramelist = array("Error levels","RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)");

 $warnlist		= array(0,-4.5,-98,-98,-98,-98,+2);
 $errorlist 	= array(0,-2.5,-70,-70,-70,-70,+20);

 $paramchecklist 	= array(
 									"RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)",
                  "RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)" );

 $colorStyleNumber 	= array(17				,17				,17				,17				,17				,19,
 														18				,18				,18				,18				,18				,20);
 $colorStyleLimit 	= array(-4.5			,-98			,-98			,-98			,-98			,+2,
 														-2.5			,-70			,-70			,-70			,-70			,+20);
 $colorStyleText 		= array("warning"	,"warning","warning","warning","warning","warning",
 														'fault'		,'fault'	,'fault'	,'fault'	,'fault'	,'fault');
 $colorStyleCheckCount = 5;
 
 $bgrGreen 	= "ff7fff00";
 $bgrRed   	= "ff0000ff";
 $bgrBlue   	= "ffffffff";
 $bgrYellow 	= "ff00d7ff";
 $lastLong = 0;
 $lastLat  = 0;
?>