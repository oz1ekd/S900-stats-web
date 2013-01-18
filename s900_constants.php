<?php


//#######################################################################################################

$HostInfo = 'SAILOR 900 VSAT';
$HostName = '';
$AcuSerialNumber = '';
$AduSerialNumber = '';
$OldSwVersion = 'before 1.30';
$SwVersion = $OldSwVersion;
$SwVersion13x = '1.3x';

$vartype =  array("int","string","float","float","float","string","string","int","int","int","int","int","int","int","int","int","int","int","int","string","string","string","string","int","string","string","int","int","int","int","int");

$IconStyleIndicator = 0;

// used for drawing tracks
$lastCoordinates = 'NoFix'; 

// used to check for ACU restart
$LastLogLine = 0;
$temp = 0;

//#######################################################################################################
 $bgrLtGreen 	= "ff00ffd8";
 $bgrGreen 		= "ff00ff7f";
 $bgrRed   		= "ff0000ff";
 $bgrDkBLue   = "ffff0000";
 $bgrYellow 	= "ffff00ff";
 $bgrOrange 	= "ff3399ff";
 $bgrBlue 		= "ffffd700";
 $bgrBlack 		= "ff000000";
 $bgrWhite   	= "ffffffff";
//#######################################################################################################

$trackLineArrSize = 9;

$trackLine =  
              array("trackLine-Single",  "trackLine-DualActive",  "trackLine-DualInActive",
										"trackLine-Single_w","trackLine-DualActive_w","trackLine-DualInActive_w",
										"trackLine-Single_e","trackLine-DualActive_e","trackLine-DualInActive_e");
	
$trackStylesLineNormal = 
              array("trackLine-Single_n",  "trackLine-DualActive_n",  "trackLine-DualInActive_n",
										"trackLine-Single_w_n","trackLine-DualActive_w_n","trackLine-DualInActive_w_n",
										"trackLine-Single_e_n","trackLine-DualActive_e_n","trackLine-DualInActive_e_n");

$trackStylesLineHighLi = 
              array("trackLine-Single_h",  "trackLine-DualActive_h",  "trackLine-DualInActive_h",
										"trackLine-Single_w_h","trackLine-DualActive_w_h","trackLine-DualInActive_w_h",
										"trackLine-Single_e_h","trackLine-DualActive_e_h","trackLine-DualInActive_e_h");

$trackStylesLineColor = 
              array($bgrGreen ,$bgrOrange ,$bgrLtGreen ,
										 $bgrBlue, $bgrBlue, $bgrBlue,
										$bgrRed,$bgrRed,$bgrRed);
      
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

// $warnlist		= array(0,-4.5,-98,-98,-98,-98,+2);
// $errorlist 	= array(0,-2.5,-70,-70,-70,-70,+20);

 $warnlist		= array(0,0,0,-97,0,0,+2);
 $errorlist 	= array(0,0,0,-70,0,0,+20);

 $paramchecklist 	= array(
 									"RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)",
                  "RSSI.Av","RX Lock. (%)","Logon. (%)","Pos OK. (%)","VMU Connection. (%)","Blocking. (%)" );

// ##  
// ##   $colorStyleNumber 	= array(17				,17				,17				,17				,17				,19,
// ##   														18				,18				,18				,18				,18				,20);
// ##   $colorStyleLimit 	= array(-4.5			,-98			,-98			,-98			,-98			,+2,
// ##   														-2.5			,-70			,-70			,-70			,-70			,+20);
 $colorStyleNumber 	= array(17				,17				,17				,17				,17				,19,
 														18				,18				,18				,18				,18				,20);
 $colorStyleLimit 	= array(0			,0		,-97			,0			,0			,+2,
 														0			,0		,-70			,0			,0			,+20);
 $colorStyleText 		= array("warning"	,"warning","warning","warning","warning","warning",
 														'fault'		,'fault'	,'fault'	,'fault'	,'fault'	,'fault');
 $colorStyleCheckCount = 5;
 

 $lastLong = 0;
 $lastLat  = 0;
?>