<?php

require("archive.php"); 
include 's900_constants.php';
include 'kmlconfig.php';



function createNodeElement($Doc, $NodeName, $NodeText)
{
$NodeElement = $Doc->createElement($NodeName);
  $valueText = $Doc->createTextNode($NodeText);
  $NodeElement->appendChild($valueText);
  return $NodeElement;
}


function calculateHeading($GPSlat1, $GPSlon1,$GPSlat2, $GPSlon2)
{
$lat2 = floatval($GPSlat2);
$north2 = strpos($GPSlat2,'N');
$lon2 = floatval($GPSlon2);
$east2 = strpos($GPSlon2,'E');

if ($north2 === FALSE) :
  $lat2 = -$lat2;
endif;
if ($east2 === FALSE) :
  $lon2 = -$lon2;
endif;
if (($lon2 == 0) || ($lat2 == 0))
 return (-1);

$lat1 = floatval($GPSlat1);
$north1 = strpos($GPSlat1,'N');
$lon1 = floatval($GPSlon1);
$east1 = strpos($GPSlon1,'E');

if ($north1 === FALSE) :
  $lat1 = -$lat1;
endif;
if ($east1 === FALSE) :
  $lon1 = -$lon1;
endif;
if (($lon1 == $lon2) && ($lat1 == $lat2))
 return (-1);

 $Y2minusY1 = sin(deg2rad($lon2) - deg2rad($lon1)) * cos(deg2rad($lat2));
 $X2minusX1 = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - 
 sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1));

 # $bearing = (rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360) % 360;

  $bearing = atan2($Y2minusY1, $X2minusX1);
  $bearing = rad2deg($bearing);
  $bearing = ($bearing + 360) % 360;
  
  return $bearing;
}

function calculateSpeed($GPSlat1, $GPSlon1,$GPSlat2, $GPSlon2,$time2, $time1)
{
$lat2 = floatval($GPSlat2);
$north2 = strpos($GPSlat2,'N');
$lon2 = floatval($GPSlon2);
$east2 = strpos($GPSlon2,'E');

if ($north2 === FALSE) :
  $lat2 = -$lat2;
endif;
if ($east2 === FALSE) :
  $lon2 = -$lon2;
endif;
if (($lon2 == 0) || ($lat2 == 0))
 return (-1);

$lat1 = floatval($GPSlat1);
$north1 = strpos($GPSlat1,'N');
$lon1 = floatval($GPSlon1);
$east1 = strpos($GPSlon1,'E');

if ($north1 === FALSE) :
  $lat1 = -$lat1;
endif;
if ($east1 === FALSE) :
  $lon1 = -$lon1;
endif;
if (($lon1 == $lon2) && ($lat1 == $lat2))
 return (-1);
 
    $delta_lat = $lat1 - $lat2 ;
    $delta_lon = $lon1 - $lon2 ;

    $earth_radius = 6372.795477598;

    $alpha    = $delta_lat/2;
    $beta     = $delta_lon/2;
    $a        = sin(deg2rad($alpha)) * sin(deg2rad($alpha)) + cos(deg2rad($lat2)) * cos(deg2rad($lat1)) * sin(deg2rad($beta)) * sin(deg2rad($beta)) ;
    $c        = asin(min(1, sqrt($a)));
    $distance = 2*$earth_radius * $c;
    $distance = round($distance, 4);
		$Speed_knots = ($distance * 3600) / ($time2-$time1); // km/h
		$Speed_knots = $Speed_knots / 1.852;
		$Speed_Knots = round($Speed_knots, 1);


  return $Speed_Knots;
}

function createSchema($Doc, $order, $vartype)
{
#    <Schema id="schema">
#      <gx:SimpleArrayField name="heartrate" type="int">
#        <displayName>Heart Rate</displayName>
#       <displayName>Cadence</displayName>
#      </gx:SimpleArrayField>
#      <gx:SimpleArrayField name="power" type="float">
#        <displayName>Power</displayName>
#      </gx:SimpleArrayField>
#    </Schema>
global  $vartype;
  $createSchemaElement = $Doc->createElement('Schema');
  $createSchemaElement->setAttribute('id', 'schema');
   
  foreach ($row as $key => $order)
  {
    $simplearayElement = $Doc->createElement('gx:SimpleArrayField');
    $simplearayElement->setAttribute('name', $key);
    $simplearayElement->setAttribute('type', $vartype[1]);
    $displayName = $Doc->createTextNode($key);
    $simplearayElement->appendChild($displayName);
    $createSchemaElement->appendChild($simplearayElement);
	}
  return $createSchemaElement;
}
##############################################################################
function createTrack($TrackElement, $Doc, $row)
{
  # This creates a <gx:track##> element for a row of data.
  # A row is a dict.
  # Loop through the columns and create a <Data> element for every field that has a value.
  # WHEN
  
  global $lastCoordinates;
  
  $whenModeElement = $Doc->createElement('when');
  #altidudeModeElement->Create(valueElement)
  $valueText = $row["UTC (YYYY-MM-DD hh:mm)"];
  $valueTextY = substr($valueText,0,10);
  $valueTextT = substr($valueText,11,16) ;
  $valueText = $valueTextY . 'T' . $valueTextD . ':00Z';
  $valueText = $Doc->createTextNode($valueText);
  $whenModeElement->appendChild($valueText);
  $TrackElement->appendChild($whenModeElement);
  # coordinates
  $coordModeElement = $Doc->createElement('gx:coord');
  #altidudeModeElement->Create(valueElement);
  $valueText = $Doc->createTextNode($row["POS.Long (degree)"] . ' ' . $row["POS.Lat (degree)"] . ' 0');
  $lastCoordinates = $valueText;
  $coordModeElement->appendChild($valueText);
  $TrackElement->appendChild($coordModeElement);
  return $TrackElement;
}


function logf($message)
{
$logfile = fopen("/var/www/kml/upload/kml.log",'a');
fwrite($logfile, $message . "\x0a\x0d");
fclose($logfile);
}
#####################################################
## Style functions
#####################################################
##############################################################
function makeStyleNormalHeading($Doc, $number)
{
#  <!-- Normal track-none style -->
#        <Style id="track-0_n">                                                        
#          <IconStyle>                                                                 
#            <color>                                                                   
#              ffff0000                                                                
#            </color>                                                                  
#            <scale>                                                                   
#              0.5                                                                     
#            </scale>                                                                  
#            <heading>                                                                 
#              0                                                                       
#            </heading>                                                                
#            <Icon>                                                                    
#              <href>                                                                  
#                http://earth.google.com/images/kml-icons/track-directional/track-0.png
#              </href>                                                                 
#            </Icon>                                                                   
#          </IconStyle>                                                                
#          <LabelStyle>                                                                
#            <scale>                                                                   
#              0                                                                       
#            </scale>                                                                  
#          </LabelStyle>                                                               
#        </Style>                                                                      
                                                                                        
global $trackStylesHeadingPng;
global $trackStylesHeadingNormal;
global $trackStylesHeadingHighLi;
global $trackStylesHeadingColor;

global $trackColor;
global $lastLong;
global $lastLat;

  $scalesize = '0.5';
  if ($number == 17)
  {
    $scalesize = '0.8';
  }
  if ($number == 18)
  {
    $scalesize = '0.8';
  }
  if ($number == 19)
  {
    $scalesize = '0.8';
  }
  if ($number == 20)
  {
    $scalesize = '0.8';
  }
    
  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', $trackStylesHeadingNormal[$number]);
   $iconStyleElement = $Doc->createElement('IconStyle');
    $color = $Doc->createElement('color');
     $valueText = $Doc->createTextNode($trackStylesHeadingColor[$number]);
     $color->appendChild($valueText);
    $iconStyleElement->appendChild($color);   
    $scaleElement = $Doc->createElement('scale');
     $valueText = $Doc->createTextNode($scalesize);
     $scaleElement->appendChild($valueText);
    $iconStyleElement->appendChild($scaleElement);
    $headingElement = $Doc->createElement('heading');
     $valueText = $Doc->createTextNode('0');
     $headingElement->appendChild($valueText);
    $iconStyleElement->appendChild($headingElement);
     $iconElement = $Doc->createElement('Icon');
     $hrefElement = $Doc->createElement('href');
     $valueText = $trackStylesHeadingPng[$number];
     $valueText = $Doc->createTextNode($valueText);
     $hrefElement->appendChild($valueText);
    $iconElement->appendChild($hrefElement);
   $iconStyleElement->appendChild($iconElement);
$styleurlelement->appendChild($iconStyleElement);
   $labelStyleElement = $Doc->createElement('LabelStyle');
    $scaleElement = $Doc->createElement('scale');
     $valueText = $Doc->createTextNode('0');
     $scaleElement->appendChild($valueText);
    $labelStyleElement->appendChild($scaleElement);
   $styleurlelement->appendChild($labelStyleElement);
  
  return $styleurlelement;
}
##############################################################
function makeStyleHighlightHeading($Doc, $number)
{
#  <!-- Normal track-none style -->;
global $trackStylesHeadingPng;
global $trackStylesHeadingHighLi;
global $trackStylesHeadingColor;
 
  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', $trackStylesHeadingHighLi[$number]);
  $scalesize = '0.5';
  $iconStyleElement = $Doc->createElement('IconStyle');
  $color = $Doc->createElement('color');
  if ($number == 19)
  {
    $scalesize = '0.8';
  }
  if ($number == 20)
  {
    $scalesize = '0.8';
  } 
  $valueText = $Doc->createTextNode($trackStylesHeadingColor[$number]);
  $color->appendChild($valueText);
  $iconStyleElement->appendChild($color);
  $styleurlelement->appendChild($iconStyleElement);
  $scaleElement = $Doc->createElement('scale');
  $iconStyleElement->appendChild($scaleElement);
  $valueText = $Doc->createTextNode('1.2');
  $scaleElement->appendChild($valueText);
  $headingElement = $Doc->createElement('heading');
  $iconStyleElement->appendChild($headingElement);
  $valueText = $Doc->createTextNode('0');
  $headingElement->appendChild($valueText);
  $iconElement = $Doc->createElement('Icon');
  $iconStyleElement->appendChild($iconElement);
  $hrefElement = $Doc->createElement('href');
  $iconElement->appendChild($hrefElement);
  $valueText = $trackStylesHeadingPng[$number];
  $valueText = $Doc->createTextNode($valueText);
  $hrefElement->appendChild($valueText);
#  documentElement->appendChild($styleurlelement);
  return $styleurlelement;
}  
##############################################################
function makeStyleNormalTrackLine($Doc, $number)
{
#  <!-- Normal line style -->
#  <Style id="track-0_n">                                                        
#    <LineStyle>
#      <color>ff0000ff</color>
#      <width>10</width>
#    </LineStyle>
#    </Style>                                                                  
                                                                                        
global $trackStylesLineNormal;
global $trackStylesLineHighLi;
global $trackStylesLineColor;
global $temp;
global $bgrBlack;

  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', $trackStylesLineNormal[$number]);
   $lineStyleElement = $Doc->createElement('LineStyle');
    $color = $Doc->createElement('color');
     $valueText = $Doc->createTextNode($trackStylesLineColor[$number]);
     $color->appendChild($valueText);
    $lineStyleElement->appendChild($color);   

    $trackLineElement = $Doc->createElement('width');
    $valueText = $Doc->createTextNode('2');
     
     $trackLineElement->appendChild($valueText);
    $lineStyleElement->appendChild($trackLineElement);
   $styleurlelement->appendChild($lineStyleElement);
  
  return $styleurlelement;
}
##############################################################
function makeStyleHighlightTrackLine($Doc, $number)
{
#  <!-- Normal line style -->
#  <Style id="track-0_n">                                                        
#    <LineStyle>
#      <color>ff0000ff</color>
#      <width>20</width>
#    </LineStyle>
#    </Style>                                                                  
                                                                                        
global $trackStylesLineNormal;
global $trackStylesLineHighLi;
global $trackStylesLineColor;

  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', $trackStylesLineHighLi[$number]);
   $lineStyleElement = $Doc->createElement('LineStyle');
    $colorElement = $Doc->createElement('color');
    $valueText = $Doc->createTextNode($trackStylesLineColor[$number]);
    $colorElement->appendChild($valueText);
    $lineStyleElement->appendChild($colorElement);   

    $trackLineElement = $Doc->createElement('width');
     $valueText = $Doc->createTextNode('4');
     $trackLineElement->appendChild($valueText);
    $lineStyleElement->appendChild($trackLineElement);
   $styleurlelement->appendChild($lineStyleElement);
  
  return $styleurlelement;
}
##############################################################
##############################################################################
function makeStyleMapHeading($Doc, $number)
{
global $trackHeading;
global $trackStylesHeadingNormal;
global $trackStylesHeadingHighLi;
##
  $styleMapElement = $Doc->createElement('StyleMap');
  $styleMapElement->setAttribute('id', $trackHeading[$number]);
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('normal');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = $trackStylesHeadingNormal[$number];
  $valueText = '#' . $valueText;
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);

  $styleMapElement->appendChild($pairElement);
## 
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('highlight');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = $trackStylesHeadingHighLi[$number];
  $valueText = '#' . $valueText;
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);
  $styleMapElement->appendChild($pairElement);
  return $styleMapElement;
}
##############################################################################
function makeStyleMapTrackLine($Doc, $number)
{
##
global $trackLine;
global $trackStylesLineNormal;
global $trackStylesLineHighLi;
##
  $styleMapElement = $Doc->createElement('StyleMap');
  $styleMapElement->setAttribute('id', $trackLine[$number]);
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('normal');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = $trackStylesLineNormal[$number];
  $valueText = '#' . $valueText;
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);

  $styleMapElement->appendChild($pairElement);
## 
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('highlight');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = $trackStylesLineHighLi[$number];
  ##test af syntax
  $valueText = "#{$trackStylesLineHighLi[$number]}";
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);
  $styleMapElement->appendChild($pairElement);
  return $styleMapElement;
}
##############################################################
function makeStyleNormalWaypointval($Doc)
{ 
#  <!-- Normal track-none style -->;
global $trackStylesHeadingPng;
global $bgrGreen;

  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', 'waypoint_n');
  
  $iconStyleElement = $Doc->createElement('IconStyle');
  $color = $Doc->createElement('color');
  $valueText = $bgrGreen;
  $valueText = $Doc->createTextNode($valueText);
  $color->appendChild($valueText);
  $iconStyleElement->appendChild($color);
  $styleurlelement->appendChild($iconStyleElement);
  
  $iconElement = $Doc->createElement('Icon');
  $iconStyleElement->appendChild($iconElement);
  $hrefElement = $Doc->createElement('href');
  $iconElement->appendChild($hrefElement);
  $valueText = $Doc->createTextNode($trackStylesHeadingPng[0]);
  $hrefElement->appendChild($valueText);
   
  return $styleurlelement;
}
 
##############################################################
function makeStyleHighlightWaypointval($Doc)
{
global $trackStylesHeadingPng;
global $bgrPink;
#  <!-- Normal track-none style -->;
  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', 'waypoint_h');
  
  $iconStyleElement = $Doc->createElement('IconStyle');
  $color = $Doc->createElement('color');
  $valueText = $Doc->createTextNode($bgrPink);
  $color->appendChild($valueText);
  $iconStyleElement->appendChild($color);
  $styleurlelement->appendChild($iconStyleElement);
  $scaleElement = $Doc->createElement('scale');
  $iconStyleElement->appendChild($scaleElement);
  $valueText = $Doc->createTextNode('1.2');
  $scaleElement->appendChild($valueText);
  $iconElement = $Doc->createElement('Icon');
  $iconStyleElement->appendChild($iconElement);
  $hrefElement = $Doc->createElement('href');
  $iconElement->appendChild($hrefElement);
  $valueText = $Doc->createTextNode($trackStylesHeadingPng[0]);
  $hrefElement->appendChild($valueText);
  return $styleurlelement;
}
##############################################################
function makeStyleHighlightWarnWaypointval($Doc)
{
global $trackStylesHeadingPng;
global $bgrGreen;
#  <!-- Normal track-none style -->;
  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', 'waypoint_h_Warn');
  
  $iconStyleElement = $Doc->createElement('IconStyle');
  $color = $Doc->createElement('color');
  $valueText = $Doc->createTextNode($bgrGreen);
  $color->appendChild($valueText);
  $iconStyleElement->appendChild($color);
  $styleurlelement->appendChild($iconStyleElement);
  $scaleElement = $Doc->createElement('scale');
  $iconStyleElement->appendChild($scaleElement);
  $valueText = $Doc->createTextNode('1.2');
  $scaleElement->appendChild($valueText);
  $iconElement = $Doc->createElement('Icon');
  $iconStyleElement->appendChild($iconElement);
  $hrefElement = $Doc->createElement('href');
  $iconElement->appendChild($hrefElement);
  $valueText = $Doc->createTextNode($trackStylesHeadingPng[0]);
  $hrefElement->appendChild($valueText);
  return $styleurlelement;
}  
##############################################################
function makeStyleHighlightErrorWaypointval($Doc)
{
global $trackStylesHeadingPng;
global $bgrRed;
#  <!-- Normal track-none style -->;
  $styleurlelement = $Doc->createElement('Style');
  $styleurlelement->setAttribute('id', 'waypoint_h_Err');
  
  $iconStyleElement = $Doc->createElement('IconStyle');
  $color = $Doc->createElement('color');
  $valueText = $Doc->createTextNode($bgrRed);
  $color->appendChild($valueText);
  $iconStyleElement->appendChild($color);
  $styleurlelement->appendChild($iconStyleElement);
  $scaleElement = $Doc->createElement('scale');
  $iconStyleElement->appendChild($scaleElement);
  $valueText = $Doc->createTextNode('1.2');
  $scaleElement->appendChild($valueText);
  $iconElement = $Doc->createElement('Icon');
  $iconStyleElement->appendChild($iconElement);
  $hrefElement = $Doc->createElement('href');
  $iconElement->appendChild($hrefElement);
  $valueText = $Doc->createTextNode($trackStylesHeadingPng[0]);
  $hrefElement->appendChild($valueText);
  return $styleurlelement;
}
##############################################################################

function makeStyleMapWaypointval($Doc)
{
##
global $trackStylesHeadingPng;
  $styleMapElement = $Doc->createElement('StyleMap');
  $styleMapElement->setAttribute('id', 'waypoint');
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('normal');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = 'waypoint_n';
  $valueText = '#' . $valueText;
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);

  $styleMapElement->appendChild($pairElement);
## 
  $pairElement = $Doc->createElement('Pair');
  
  $keyElement = $Doc->createElement('key');
  $pairElement->appendChild($keyElement);
  $valueText = $Doc->createTextNode('highlight');
  $keyElement->appendChild($valueText);
  $styleurlelement = $Doc->createElement('styleUrl');
  $pairElement->appendChild($styleurlelement);
  $valueText = 'waypoint_h';
  $valueText = '#' . $valueText;
  $valueText = $Doc->createTextNode($valueText);
  $styleurlelement->appendChild($valueText);
  $styleMapElement->appendChild($pairElement);
  return $styleMapElement;
}
#######################################################################
function  createLookAt($Doc, $row)
{
# This creates a <gx:track##> element for a row of data.
# A row is a dict.
#    <LookAt>
#      <gx:TimeSpan>
#       <begin>2012-02-19T00:01:00Z</begin>
#        <end>2012-02-23T23:59:00Z</end>
#      </gx:TimeSpan>
#      <longitude>3.860500</longitude>
#      <latitude>58.689500</latitude>
#      <range>379640.054079</range>
#    </LookAt>
  $lookAtElement  = $Doc->createElement('LookAt');
$gx_TimeSpanElement  = $Doc->createElement('gx:TimeSpan');

  $beginElement  = $Doc->createElement('begin');
  $gx_TimeSpanElement->appendChild($beginElement);
  $valueText = $row['UTC (YYYY-MM-DD hh:mm)'];
  $valueTextY = substr($valueText,0,10);
  $valueTextT = '00:00:00'; 
  $valueText = $valueTextY . 'T' . $valueTextT .'Z';
  $valueText = $Doc->createTextNode($valueText);
  $beginElement->appendChild($valueText);

  $endElement  = $Doc->createElement('end');
  $gx_TimeSpanElement->appendChild($endElement);
  $valueText = $row['UTC (YYYY-MM-DD hh:mm)'];
  $valueTextY = substr($valueText,0,4);
  $valueTextY += 1;
  $valueTextD = substr($valueText,4,6); 
  $valueTextT = '23:59:00';
  $valueText = $valueTextY . $valueTextD . 'T' . $valueTextT . 'Z';
  $valueText = $Doc->createTextNode($valueText);
  $endElement->appendChild($valueText);
  $lookAtElement->appendChild($gx_TimeSpanElement);

  $longitudeElement  = $Doc->createElement('longitude');
  $valueText = $Doc->createTextNode($row["POS.Long (degree)"]);
  $longitudeElement->appendChild($valueText);
  $lookAtElement->appendChild($longitudeElement);
  $latitudeElement  = $Doc->createElement('latitude');
  $valueText = $Doc->createTextNode($row["POS.Lat (degree)"]);
  $latitudeElement->appendChild($valueText);
  $lookAtElement->appendChild($latitudeElement);
  $rangeElement  = $Doc->createElement('range');
  $valueText = $Doc->createTextNode('400000');
  $rangeElement->appendChild($valueText);
  $lookAtElement->appendChild($rangeElement);
  return $lookAtElement;
}

##############################################################################
function checkWarningErrorLevels($DualAntennaMode)
{
## override style in case of warning condition
## RSSI.Av,RX Lock. (%),Logon. (%),Pos OK. (%),VMU Connection. (%),Blocking. (%)
## ADDED for DUAL version 1.3x forward
## DualAntenna.active (%), DualAntenna.mode, DualAntenna.logon_remote
global  $IconStyleIndicator;
global  $colorStyleNumber;
global  $colorStyleLimit;
global  $colorStyleCheckCount;
global  $colorStyleText;
global  $paramchecklist;
global  $temp;

$resul = 0;
   $i = 0;
   switch($DualAntennaMode)
   {
   	case "SINGLE":
		   foreach ($paramchecklist as $key)
		   {
		   	 if ($colorStyleLimit[$i] < 0)
		   	 {
			     if (((floatval($temp[$key]) < abs($colorStyleLimit[$i]))) )
			     {
			       $temp[$key] = $temp[$key] . ' !' . $colorStyleText[$i];
			       if (( $result < 6) && ($i <= $colorStyleCheckCount)) 
			        $resul = 3;
			       if ($i > $colorStyleCheckCount)
			        $resul = 6;
			//         logf('Limit :' . $colorStyleLimit[$i] . ' data : ' . $key . ' ' . $row[$key]);
					   if (( $IconStyleIndicator < $colorStyleNumber[$i]) && ($key == 'Blocking. (%)') )
					   {
					     $IconStyleIndicator = $colorStyleNumber[$i];
					   }
			     }
			   }
		     if ($colorStyleLimit[$i] > 0)
		     {
			     if (((floatval($temp[$key]) > abs($colorStyleLimit[$i]))) )
			     {
			       $temp[$key] = $temp[$key] . ' !' . $colorStyleText[$i] ;
			       if (( $result < 6) && ($i <= $colorStyleCheckCount)) 
			        $resul = 3;
			       if ($i > $colorStyleCheckCount)
			        $resul = 6;
			//         logf('Limit   :' . $colorStyleLimit[$i] . ' data : ' . $key . ' ' . $row[$key]);
			    
					   if (( $IconStyleIndicator < $colorStyleNumber[$i]) && ($key == 'Blocking. (%)') )
					   {
					     $IconStyleIndicator = $colorStyleNumber[$i];
					   }
			     }
			   }    
		     $i = $i + 1;
		    } 
		    break;
	    case 'SLAVE':
		    $resul = 1;
	    	$logonpct = 100 - ($temp['DualAntenna.active (%)'] - $temp['Logon. (%)']);
		    if ($logonpct < 70 )
		    {
		    	// red marking
//		    	$IconStyleIndicator = 18;
		    	$temp['DualAntenna.active (%)'] = $temp['DualAntenna.active (%)'] . ' !';
		    	$temp['Logon. (%)'] = $temp['Logon. (%)'] . ' !';
		    	$resul = 7;
		    }
		    else
		    if ($logonpct < 97 )
		    {
		    	// green marking
//		    	$IconStyleIndicator = 17;
		    	$temp['DualAntenna.active (%)'] = $temp['DualAntenna.active (%)'] . ' !';
		    	$temp['Logon. (%)'] = $temp['Logon. (%)'] . ' !';
		    	$resul = 4;
		    }
	    	if ($temp['DualAntenna.active (%)'] < 50)
	    		$resul++;	    	
	    	break;
	    case 'MASTER':
		    $resul = 1;
	    	$logonpct = $temp['DualAntenna.logon_remote (%)'] + $temp['Logon. (%)'];
		    if ($logonpct < 70 )
		    {
		    	// red marking
//		    	$IconStyleIndicator = 18;
		    	$temp['DualAntenna.logon_remote (%)'] = $temp['DualAntenna.logon_remote (%)'] . ' !';
		    	$temp['Logon. (%)'] = $temp['Logon. (%)'] . ' !';
		    	$resul = 7;
		    }
		    else
		    if ($logonpct < 97 )
		    {
		    	// green marking
//		    	$IconStyleIndicator = 17;
		    	$temp['DualAntenna.logon_remote (%)'] = $temp['DualAntenna.logon_remote (%)'] . ' !';
		    	$temp['Logon. (%)'] = $temp['Logon. (%)'] . ' !';
		    	$resul = 4;
		    }
	    	if ($temp['DualAntenna.active (%)'] < 50)
	    		$resul++;
	    	break;
	 }
	    	

    return $resul;
}

##############################################################################
function CheckACUrestart($Doc, $row, $order)
{
global  $LastLogLine;

	$RestartElement = NULL;
	$timeas = $row['UTC. (s)'];
  $timedelta = $timeas - $LastLogLine;
  $LastLogLine = $timeas;
  
  if (($timedelta != '3600') and ($timedelta != '120'))
  {
	  $RestartElement = $Doc->createElement('Placemark');
	  $valueText = $row['UTC (YYYY-MM-DD hh:mm)'];
	  $valueTextY = substr($valueText,0,10);
	  $valueTextT = substr($valueText,11,16); 
	  $valueText = $valueTextY . 'T' . $valueTextT .':00Z';
	  $nameElement = createNodeElement($Doc, 'name', 'Log Jump ' . $valueText);
	  $RestartElement->appendChild($nameElement);
	  
	  $pointElement = $Doc->createElement('Point');
	  $coordinates = $row['POS.Long (degree)'] . ',' . $row['POS.Lat (degree)'] . ',0';
	  $coorElement = $Doc->createElement('coordinates');
	  $coorElement->appendChild($Doc->createTextNode($coordinates));
	  $pointElement->appendChild($coorElement);
	  $RestartElement->appendChild($pointElement);
  } 

  return $RestartElement;
}
##############################################################################
function createPlacemark($Doc, $row, $order)
{
global  $colorStyleNumber;
global  $colorStyleLimit;
global  $trackHeading; 
global  $IconStyleIndicator;
global 	$trackStylesLineColor;
global  $lastLong, $lastLat, $lastTime;
global  $lastCoordinates;
global  $temp;
global  $HostInfo;
global  $HostName;
global  $AcuSerialNumber;
global  $AduSerialNumber;
global  $SwVersion;
global  $OldSwVersion;
global 	$bgrWarning;
global  $bgrError;


  $placemarkElement = $Doc->createElement('Placemark');
 	$nameElement = createNodeElement($Doc, 'name',  $row['UTC (YYYY-MM-DD hh:mm)']);
  $placemarkElement->appendChild($nameElement);
  $timeStampElement  = $Doc->createElement('TimeStamp');
  $valueText = $row['UTC (YYYY-MM-DD hh:mm)'];
  $valueTextY = substr($valueText,0,10);
  $valueTextT = substr($valueText,11,16); 
  $valueText = $valueTextY . 'T' . $valueTextT .':00Z';
  $valueText = $Doc->createTextNode($valueText);
  $timeStampElement->appendChild($valueText);
  $placemarkElement->appendChild($timeStampElement);
  $extElement = $Doc->createElement('ExtendedData');
  $placemarkElement->appendChild($extElement);
## select icon style
## select default style
  $IconStyleIndicator = 0;

	$dataElement = $Doc->createElement('Data');
	$dataElement->setAttribute('name', 'System');
	$valueElement = $Doc->createElement('value');
	$dataElement->appendChild($valueElement);
	$valueElement->appendChild($Doc->createTextNode($HostInfo));
	$extElement->appendChild($dataElement);

	$dataElement = $Doc->createElement('Data');
	$dataElement->setAttribute('name', 'SW Version');
	$valueElement = $Doc->createElement('value');
	$dataElement->appendChild($valueElement);
	$valueElement->appendChild($Doc->createTextNode($SwVersion));
	$extElement->appendChild($dataElement);

	if ($SwVersion != $OldSwVersion)
	{
		$dataElement = $Doc->createElement('Data');
		$dataElement->setAttribute('name', 'Hostname');
		$valueElement = $Doc->createElement('value');
		$dataElement->appendChild($valueElement);
		$valueElement->appendChild($Doc->createTextNode($HostName));
		$extElement->appendChild($dataElement);
	
		$dataElement = $Doc->createElement('Data');
		$dataElement->setAttribute('name', 'ACU S/N');
		$valueElement = $Doc->createElement('value');
		$dataElement->appendChild($valueElement);
		$valueElement->appendChild($Doc->createTextNode($AcuSerialNumber));
		$extElement->appendChild($dataElement);
	
		$dataElement = $Doc->createElement('Data');
		$dataElement->setAttribute('name', 'ADU S/N');
		$valueElement = $Doc->createElement('value');
		$dataElement->appendChild($valueElement);
		$valueElement->appendChild($Doc->createTextNode($AduSerialNumber));
		$extElement->appendChild($dataElement);
  }
  
   $GPSSpeed = calculateSpeed($row['POS.Lat (degree)'], $row['POS.Long (degree)'],$lastLat, $lastLong,$row['UTC. (s)'],$lastTime );
   $GPSheading = calculateHeading($row['POS.Lat (degree)'], $row['POS.Long (degree)'],$lastLat, $lastLong );
   $lastLat = $row['POS.Lat (degree)'];
   $lastLong = $row['POS.Long (degree)'];
   $lastTime = $row['UTC. (s)'];
   if ($GPSheading != -1)
   {
   	$heading = $GPSheading * 10;
	  $heading = floatval($heading )/ 225;
 		$heading = round($heading);
 		$heading = $heading % 16;
 		$IconStyleIndicator = intval($heading) + 1;
 	 }
	// returns result in global $IconStyleIndicator
	// and 0 = OK, 3 = warning, 6 = error
	$temp = $row;
	if ($SwVersion != $OldSwVersion)
		$warninglevel = checkWarningErrorLevels($temp['DualAntenna.mode']);
	else
		$warninglevel = checkWarningErrorLevels('SINGLE');
	$row = $temp;
  # Loop through the columns and create a <Data> element for every field that has a $value.
  #if $row[$key]:
	foreach ($order as $key)
	{
		if ($key === 'Heading.Samp (degree)')
		{
			$dataElement = $Doc->createElement('Data');
			$dataElement->setAttribute('name', 'Speed.GPS (knots)');
			$valueElement = $Doc->createElement('value');
			$dataElement->appendChild($valueElement);
			if ($GPSSpeed != -1)
			{
			 $valueText = $Doc->createTextNode($GPSSpeed);
			}
			else
			{
			 $valueText = $Doc->createTextNode('cannot calculate!');
			}
			$valueElement->appendChild($valueText);
			$extElement->appendChild($dataElement);
			$dataElement = $Doc->createElement('Data');
			$dataElement->setAttribute('name', 'Heading.GPS (degree)');
			$valueElement = $Doc->createElement('value');
			$dataElement->appendChild($valueElement);
			if ($GPSheading != -1)
			{
			 $valueText = $Doc->createTextNode($GPSheading);
			}
			else
			{
			 $valueText = $Doc->createTextNode('cannot calculate!');
			}
			$valueElement->appendChild($valueText);
			$extElement->appendChild($dataElement);
		}
		$dataElement = $Doc->createElement('Data');
		$dataElement->setAttribute('name', $key);
		$valueElement = $Doc->createElement('value');
		$dataElement->appendChild($valueElement);
		$valueText = $Doc->createTextNode($row[$key]);
		$valueElement->appendChild($valueText);
		$extElement->appendChild($dataElement);
  }
#
    
  $styleURLElement = $Doc->createElement('styleUrl');
  $valueText = $Doc->createTextNode('#' . $trackHeading[$IconStyleIndicator]);
  $styleURLElement->appendChild($valueText);
  $placemarkElement->appendChild($styleURLElement);
//  if ($warninglevel > 2)
  {
	  $styleurlelement = $Doc->createElement('Style');
	  $iconStyleElement = $Doc->createElement('IconStyle');
	  $color = $Doc->createElement('color');
	  $valueText = $Doc->createTextNode($trackStylesLineColor[$warninglevel]);
	  $color->appendChild($valueText);
	  $iconStyleElement->appendChild($color);
	  $styleurlelement->appendChild($iconStyleElement);
	  $placemarkElement->appendChild($styleurlelement);
  }
  
  $pointElement = $Doc->createElement('Point');
  $coordinates = $row['POS.Long (degree)'] . ',' . $row['POS.Lat (degree)'] . ',0';
  $coorElement = $Doc->createElement('coordinates');
  $coorElement->appendChild($Doc->createTextNode($coordinates));
  $pointElement->appendChild($coorElement);
  $placemarkElement->appendChild($pointElement);

  return $placemarkElement;
}
####################################################
function createPath($Doc, $row, $order)
{
global  $lastCoordinates;
global  $trackLine;
global  $temp;
global 	$SwVersion;
global  $OldSwVersion;

  $pathElement = $Doc->createElement('Placemark');
//
//  $nameElement = createNodeElement($Doc, 'name', $row['UTC (YYYY-MM-DD hh:mm)']);
//  $pathElement->appendChild($nameElement);

## override style in case of error condition
## RSSI.Av,RX Lock. (%),Logon. (%),Pos OK. (%),VMU Connection. (%),Blocking. (%)
#   i = 0
#   for $key in paramelist:
#     if ( $key in $order ):
#       if (errorlist[i] < 0 and $headingi < 18 ):
#         if (($row[$key]) and (floatval($row[$key]) < abs(errorlist[i])) ):
#           $headingi = 18
#       elif (errorlist[i] > 0 and $headingi < 18 ):
#         if (($row[$key]) and (floatval($row[$key]) > abs(errorlist[i])) ):
#           $headingi = 18
#    i = i + 1
    
//  $pathElement = $Doc->createElement('track');
  $styleURLElement = $Doc->createElement('styleUrl');
  // 0 = single, 1 = master, 2 = slave
  // 0 = ok, 3 = warning, 6 = fault
  $temp = $row;
  $trackLineIndex = 0;
	
	if ($SwVersion != $OldSwVersion)
		$trackLineIndex = checkWarningErrorLevels($temp['DualAntenna.mode']);
	else
		$trackLineIndex = checkWarningErrorLevels('SINGLE');

 	$valueText = '#' . $trackLine[$trackLineIndex];
  $valueText = $Doc->createTextNode($valueText);
  $styleURLElement->appendChild($valueText);
  $pathElement->appendChild($styleURLElement);
  
#//  $timeStampElement  = $Doc->createElement('TimeStamp');
#//  $valueText = $row['UTC (YYYY-MM-DD hh:mm)'];
#//  $valueTextY = substr($valueText,0,10);
#//  $valueTextT = substr($valueText,11,16); 
#//  $valueText = $valueTextY . 'T' . $valueTextT .':00Z';
#//  $valueText = $Doc->createTextNode($valueText);
#//  $timeStampElement->appendChild($valueText);
#//  $pathElement->appendChild($timeStampElement);

	  $lineElement = $Doc->createElement('LineString');
	  $coorElement = $Doc->createElement('coordinates');
	   $coordinates = $row['POS.Long (degree)'] . ',' . $row['POS.Lat (degree)'] . ',0 ';
	   $coorElement->appendChild($Doc->createTextNode($lastCoordinates));
	   $coorElement->appendChild($Doc->createTextNode($coordinates));
	   $lineElement->appendChild($coorElement);
	  $pathElement->appendChild($lineElement);

  return $pathElement;
}

 
 
#############################################################################
function createKML($Fcsv, $Fkml) 
{   
global $paramwlist;
global $paramelist;
global $errorlist;
global $warnlist;
global $lastLong, $lastLat;
global $lastCoordinates;
global $SHOW_LOGJUMP;
global $HostInfo;
global $HostName;
global $AcuSerialNumber;
global $AduSerialNumber;
global $SwVersion;
global $OldSwVersion;
global $SwVersion13x;

  $csvreader = fopen($Fcsv, "r");
  $order = fgetcsv($csvreader);
  $row = fgetcsv($csvreader);
  if (count($order) != count($row))
  {
  	return false;
  }
  $row = array_combine ( $order , $row );

	# read header record if 'DualAntenna.mode' present (version 1.30 and up)
	# else just do as usual
  if (array_key_exists ( 'DualAntenna.mode' , $row ))
  {
  	$SwVersion = $SwVersion13x;
	}
	# read header record if present (version 1.40 and up)
	# else just do as usual
  if ($order[0] == 'Hostname')
  {
  	$HostInfo = $row['System'];
  	$HostName = $row['Hostname'];
  	$AcuSerialNumber = $row['ACU_SN'];
  	$AduSerialNumber = $row['ADU_SN'];
  	$SwVersion = $row['SW_Ver'];
  	// get data record header and first row
  	$order = fgetcsv($csvreader);
  	$row = fgetcsv($csvreader);
	  if (count($order) != count($row))
	  {
	  	return false;
	  }
  	$row = array_combine ( $order , $row );
  }
  if ($order[0] != 'UTC. (s)')
  {
  	return false;
	}

  # This constructs the KML document from the CSV file.
  $Doc = new DOMDocument('1.0', 'UTF-8');
  $Doc->formatOutput = true;
  
  $kmlElement = $Doc->createElementNS('http://earth.google.com/kml/2.2', 'kml');
  $kmlElement->setAttribute('xmlns', 'http://earth.google.com/kml/2.2');
  $kmlElement->setAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');
  $kmlElement = $Doc->appendChild($kmlElement);
  $documentElement = $Doc->createElement('Document');
  $documentElement = $kmlElement->appendChild($documentElement);
  $documentElementName = createNodeElement($Doc, 'Snippet', $HostInfo);
  $documentElement->appendChild($documentElementName);
  
 
  // skip rows with incomplete position
  while ( ($row['POS.Lat (degree)'] == '') or  ($row['POS.Long (degree)'] == '') )
  {
   $row = fgetcsv($csvreader);
   $row = array_combine ( $order , $row );
   $lastLong = 0;
   $lastLat = 0;
  }
  
 
  $LookAtElement = createLookAt($Doc, $row);
  $documentElement->appendChild($LookAtElement);
  
  ## CreateStyleURLElement($Doc); 
  ## create point styles 
  $indexer = 0;
   while ($indexer < 21)
   {
     $styleurlelement = makeStyleNormalHeading($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);

     $styleurlelement = makeStyleHighlightHeading($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);

     $styleurlelement = makeStyleMapHeading($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);
     $indexer = $indexer + 1;
   }
  ## CreateStyleURLElement($Doc); 
  ## create line styles 
  $indexer = 0;
   while ($indexer < 9)
   {
     $styleurlelement = makeStyleNormalTrackLine($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);

     $styleurlelement = makeStyleHighlightTrackLine($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);

     $styleurlelement = makeStyleMapTrackLine($Doc, $indexer);
     $documentElement->appendChild($styleurlelement);
     $indexer = $indexer + 1;
   }
 
  $styleurlelement = makeStyleNormalWaypointval($Doc);
  $documentElement->appendChild($styleurlelement);
  $styleurlelement = makeStyleHighlightWaypointval($Doc);
  $documentElement->appendChild($styleurlelement);
  $styleurlelement = makeStyleMapWaypointval($Doc);
  $documentElement->appendChild($styleurlelement);
  
#  documentElementSchema = createSchema($Doc, order, vartype)
#  documentElement->appendChild(documentElementSchema)
  $documentElementFolder = $Doc->createElement('Folder');
  $documentElement->appendChild($documentElementFolder);
  $documentElementName = createNodeElement($Doc, 'name', 'Description');
  $documentElementFolder->appendChild($documentElementName);
  $placemarkElement = $Doc->createElement('Placemark');
  $placemarkElementName = createNodeElement($Doc, 'name', 'Parameter limits <br> Dual mode: <br>Blocking not marked!');
  $placemarkElement->appendChild($placemarkElementName);
  $extElement = $Doc->createElement('ExtendedData');
  $placemarkElement->appendChild($extElement);
   
  # Loop through the columns and create a <Data> element for every field that has a value.
  #if row[key]:
  $i = 0;
  while ( $i < 7 )
  {
    if ( (abs($warnlist[$i]) != 0) || ($i == 0) )
    {
	    $dataElement = $Doc->createElement('Data');
	    $dataElement->setAttribute('name', $paramwlist[$i]);
	    $valueElement = $Doc->createElement('value');
	    $dataElement->appendChild($valueElement);
	    if ($warnlist[$i] > 0)
	      $valueText = $Doc->createTextNode($paramwlist[$i] . ' > ' . abs($warnlist[$i]));
	    else if ($warnlist[$i] < 0)
	      $valueText = $Doc->createTextNode($paramwlist[$i] . ' < ' . abs($warnlist[$i]));
	    else
	      $valueText = $Doc->createTextNode('Marked Pink');
	     
	    $valueElement->appendChild($valueText);
	    $extElement->appendChild($dataElement);
  	}
    $i = $i+1;
  }
  
  $i = 0;
  while ($i < 7 )
  {
    if ( (abs($errorlist[$i]) != 0) || ($i == 0) )
    {
	    $dataElement = $Doc->createElement('Data');
	    $dataElement->setAttribute('name', $paramelist[$i]);
	    $valueElement = $Doc->createElement('value');
	    $dataElement->appendChild($valueElement);
	    if ($errorlist[$i] > 0)
	      $valueText = $Doc->createTextNode($paramelist[$i] . ' > ' . abs($errorlist[$i]));
	    else if ($errorlist[$i] < 0)
	      $valueText = $Doc->createTextNode($paramelist[$i] . ' < ' . abs($errorlist[$i]));
	    else
	      $valueText = $Doc->createTextNode('Marked Red');
	      
	    $valueElement->appendChild($valueText);
	    $extElement->appendChild($dataElement);
  	}
    $i = $i+1;
  }
  $documentElementFolder->appendChild($placemarkElement);
  
  $documentElementFolder = $Doc->createElement('Folder');
  $documentElement->appendChild($documentElementFolder);
  $documentElementName = createNodeElement($Doc, 'name', 'Points');
  $documentElementFolder->appendChild($documentElementName);
  $TimeSpanElement  = $Doc->createElement('TimeSpan');
  $beginElement  = $Doc->createElement('begin');
  $TimeSpanElement->appendChild($beginElement);
  $valueText = $row["UTC (YYYY-MM-DD hh:mm)"];
  $valueText = substr($valueText,0,10);
  $valueText = $valueText . 'T00:00:00Z';
  $valueText = $Doc->createTextNode($valueText);
  $beginElement->appendChild($valueText);

 
  $endElement  = $Doc->createElement('end');
  $TimeSpanElement->appendChild($endElement);
  $valueText = $row["UTC (YYYY-MM-DD hh:mm)"];
  $valueText = substr($valueText,0,10);
  $valueText = $valueText . 'T23:59:00Z';
  $valueText = $Doc->createTextNode($valueText);
  $endElement->appendChild($valueText);
  $documentElementFolder->appendChild($TimeSpanElement);

  while (($row = fgetcsv($csvreader, 0, ",")) != FALSE )
  {
 	  if (count($order) != count($row))
	  {
	  	return false;
	  }
    $row = array_combine ( $order , $row );
    if ( $SHOW_LOGJUMP )
    {
    	$RestartMark = CheckACUrestart($Doc, $row, $order);
		 if ( isset($RestartMark) )
		 {
				$documentElementFolder->appendChild($RestartMark);
		 }
		}
    $PlaceMark = createPlacemark($Doc, $row, $order);
    $documentElementFolder->appendChild($PlaceMark);
  }  
  fclose($csvreader);

  $documentElementFolder2 = $Doc->createElement('Folder');
  $documentElement->appendChild($documentElementFolder2);
  $documentElementName2 = createNodeElement($Doc, 'name', 'Tracks');
  $documentElementFolder2->appendChild($documentElementName2);

  $csvreader = fopen($Fcsv, "r");
  
  # Skip the header line.
  # csvReader.next()
  #csvreader.__next__()
  $order = fgetcsv($csvreader);
  $row = fgetcsv($csvreader);
  $row = array_combine ( $order , $row );
  if (($SwVersion != $OldSwVersion) && ($SwVersion != $SwVersion13x) )
  {
  	$order = fgetcsv($csvreader);
  	$row = fgetcsv($csvreader);
  	$row = array_combine ( $order , $row );
  }
  while (($row = fgetcsv($csvreader, 0, ",")) != FALSE )
  {
    $row = array_combine ( $order , $row );
    if (($row['POS.Valid'] == 'Fix') && ($lastCoordinates != 'NoFix'))
    {
    	$Path = createPath($Doc, $row, $order);
    	$documentElementFolder2->appendChild($Path);
 	    $lastCoordinates = $row['POS.Long (degree)'] . ',' . $row['POS.Lat (degree)'] . ',0 ';
    }
    else if ($row['POS.Valid'] == 'Fix')
    {
    	$lastCoordinates = $row['POS.Long (degree)'] . ',' . $row['POS.Lat (degree)'] . ',0 ';;
    }
    else
    {
    	$lastCoordinates = 'NoFix';
    }
  }  
  fclose($csvreader);
//  echo $Fkml . '<br/>';
  $kmlFile = fopen($Fkml, 'wb');
  $kmlresult = $Doc->saveXML();
  fwrite($kmlFile, $kmlresult);
//  $Doc->saveXML($Doc->toprettyxml('  ', $newl = '\n', $encoding = 'utf-8'));
  fclose($kmlFile);
  return true;
}

function mainCreateKML($csvFilename)
{
  # If an argument was passed to the script, it splits the argument on a comma
  # and uses the resulting list to specify an order for when columns get added.
  # Otherwise, it defaults to the order used in the sample.
global  $SERVER_UPLOAD_DIR;
 
  ## SAILOR 900 version 1.01 statistics file format
  # $order = array('UTC. (s)','UTC (YYYY-MM-DD hh:mm)','RSSI.Av','RSSI.Max','RSSI.Min','POS.Lat (degree)','POS.Long (degree)','POS.Valid','Heading.Samp (degree)','Heading.Max (degree)','Heading.Min (degree)','Heading.Range (+/-degree)','Antenna.Azi (degree)','Antenna.Azi Max (degree)','Antenna.Azi Min (degree)','Antenna.Azi Range (+/-degree)','Antenna.Ele (+/-degree)','Antenna.Ele Max (+/-degree)','Antenna.Ele Min (+/-degree)','Vsat.rx_lo_freq (GHz)','Vsat.tx_lo_freq (GHz)','Tracking.rf freq (GHz)','Tracking.type','Sat.long (degree)','Carrier rf.rx (GHz)','Carrier rf.tx (GHz)','RX Lock. (%)','Logon. (%)','Pos OK. (%)','VMU Connection. (%)','Blocking. (%)');
  $fname = substr($csvFilename,0,strlen($csvFilename)-3);
  $fcsv = $fname . 'csv';
  $ftmp = $fname . 'tmp';
  $fkml = $fname . 'kml';
  $fkmz = $fname . 'kmz';
  
## by Svend Stave (STA) march,april 2012
 if ( createKML(getcwd() . $SERVER_UPLOAD_DIR	 . $fcsv, getcwd() . $SERVER_UPLOAD_DIR	 . $fkml))
 {
  	 // Remove KMZ file
  	 if (file_exists(getcwd() .  $SERVER_UPLOAD_DIR	 . $fkmz))
     unlink ( getcwd() .  $SERVER_UPLOAD_DIR	 . $fkmz  ) or die("Unable to delete file! " . $fkmz );
 
  $kmzFile = new zip_file(getcwd() . $SERVER_UPLOAD_DIR	 . $fkmz);
  $kmzFile->set_options(array( 'overwrite' => 1, 'recurse' => 0, 'storepaths' => 0));
  $kmzFile->add_files(getcwd() . $SERVER_UPLOAD_DIR	 . $fkml);
  $kmzFile->create_archive();
  return $fkmz;
 }
 else
 {
 	return NULL;
 }
    
}
?>
