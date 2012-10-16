<?PHP
error_reporting(0); // this needs to be set to 0 when releases are released
include("../password_protect.php"); 
global $SERVER_PATH;
global $SERVER_UPLOAD_DIR;
global $SERVER_KMZSTORE_DIR;
global $SERVER_STATSSTORE_DIR;

/**
 * File: filelist.php
 *
 * Bobb's File List System
 */
  $version = '3.2.0a';
/* 
 * Read the README file. It is a must read for all admins and
 * programmers that use or intend to edit this program. If you read nothing else
 * in that file, read "Notes about security" near the bottom.
 *
 * Bobb's File Manage System is the legal property of its developers whose names are listed in the COPYRIGHT file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

  // SETINGS:
/******************************************************/

// GENERAL FILELIST SETTINGS
$display_icon      = false;                 // display "new" when they are new files/dirs
$display_new       = false;                 // display "new" when they are new files/dirs
$display_updated   = false;                 // display "updated" when they are updated files/dirs
$new_time_secs     = 60 * 60 * 24 * 14;    // "new" and "updated" deadline, 14 days
$error_spacer      = 0.5;                  // for per word errors;    exact match 0~~.5~~1 accept all (see below for further info); used in function isclosematch()
$error_spacer2     = 0.3;                  // for per segment errors; exact match 0~~.5~~1 accept all (see below for further info); used in function isclosematch()
$show_all_stats    = false;                 // show or don't show the "totals" stats at the bottom of the page
$organize_ord      = false;                 // when organizing, organize ignoring leading 'the's or 'a's?
$show_add_info     = false;                 // some files such as images, text, music, and video files will have more information that can be displayed about it (music and video files require getid3() (www.sourceforge.net/projects/getid3/)
$show_file_time    = true;                 // 
$line_break        = "\n";                 // line break for creating files; "\n" is general line break, but some Windows apps like "\n\r" better
$auto_icon         = false;                 // this feature allows an admin to edit the acceptable file extensions without having to change the linked icon images. So unless custom icons or obscure extensions (that icons should be linked to) are being used, this remaining true is most likely a good thing

/*
 * ERROR_SPACER explaination
 *
 * 0 will only return exact matches. 1 will return everything (you don't want
 * that!) I suggest somewhere inbetween. 0.5 allows about half the characters to
 * be changed, and still return true. I use this on per word matches, since it
 * will allow for more spelling erros in a word. 0.3 allows a little less than a
 * third of the characters to be wrong. Per segment matches are more strict
 * since they aren't as clean cut, and will also include spaces and other odd
 * characters that normally may not be considered.
 *
 */

if(isset($_GET['prop']) || isset($_GET['note']))
  $dontdispheader = true;
else
  $dontdispheader = false;


// If $auto_icon is enabled, you can ignore the $iconlink array.
// these two keys will match together to display
// the right icons with the filetype
$accept = array(
  // acceptible file endings. for best result include the '.' in a desired file extension
  // if you add '' to the list, it will display all files.
'.zip',
'.tar.gz',
'.7z',
'.rar',
'.exe',
'.msi',
'.html',
'.htm',
'.php',
'.php3',
'.jpg',
'.gif',
'.tiff',
'.tif',
'.avi',
'.asf',
'.mpg',
'.mpeg',
'.wmv',
'.mov',
'.mp3',
'.wma',
'.wav',
'.psd',
'.txt',
'.list',
'.kml',
'.kmz',
'.csv',
'.csv.gz',
''
);

// ignore this if $auto_icon is enabled
if(!$auto_icon || !$dontdispheader){
  $iconlink = array(
    // linked to src of image you want
  '/icons/compressed.gif',
  '/icons/compressed.gif',
  '/icons/compressed.gif',
  '/icons/compressed.gif',
  '/icons/burst.gif',
  '/icons/comp.gray.gif',
  '/icons/layout.gif',
  '/icons/layout.gif',
  '/icons/layout.gif',
  '/icons/image2.gif',
  '/icons/image2.gif',
  '/icons/image2.gif',
  '/icons/image2.gif',
  '/icons/movie.gif',
  '/icons/movie.gif',
  '/icons/movie.gif',
  '/icons/movie.gif',
  '/icons/movie.gif',
  '/icons/movie.gif',
  '/icons/sound2.gif',
  '/icons/sound2.gif',
  '/icons/sound2.gif',
  '/icons/sound1.gif',
  '/icons/image1.gif',
  '/icons/text.gif',
  '/icons/text.gif',
  '/icons/text.gif',
  '/icons/compressed.gif',
  '/icons/text.gif',
  '/icons/compressed.gif',
  '/icons/unknown.gif'
  );
}

/******************************************************/

  // $loc1 is the path on the computer filelist's location
$loc1 = dirname(__FILE__);

  // makes a standardized variable for $PHP_SELF, which is in two places depending on the PHP version
if(isSet($PHP_SELF))
  $phpSelf = $PHP_SELF;
else
  $phpSelf = $_SERVER['PHP_SELF'];

if(!is_dir('filelist/'))
  mkdir('filelist/') or die('Not enough permissions to create directory');

hits(); // log hits/page views

  // assigns $php_version to an array, representing the version and sub versions
$php_version = explode('.', phpversion());

if($php_version[0] > 4 || ($php_version[0] == 4 && $php_version[1] >= 2)){

  if(!$dontdispheader){
  
    $logFile = 'filelist/last_update.list';
    if($show_all_stats && is_file($logFile)){
      $array_of_log_file = file($logFile);
      $number_of_dirs  = trim($array_of_log_file[1]);
      $number_of_files = trim($array_of_log_file[2]);
      $number_of_all   = trim($array_of_log_file[1]) + trim($array_of_log_file[2]);
      $total_file_size = trim($array_of_log_file[3]);
      $last_update     = trim($array_of_log_file[0]);
    } else
      $number_of_all   = 10000;
  
      // max amount of time to wait before updating (with $noa ^ 2 / 5000, this will be about 5.6 hours)
    if($number_of_all > 10000)
      $number_of_all = 10000;
  
    $update_log_sec   = round($number_of_all * $number_of_all / 5000);          // how frequently to update number of files/filesize log
  }

    // $cur_filename is the name of the index file, so it can be named anything, and still work correctly
  $cur_filename = '.php';
  if(strstr($phpSelf, '/')){
    $i = strlen($phpSelf);
    while($i > 0 && $cur_filename == '.php'){
      $i--;
      if(substr($phpSelf, $i, 1) == '/')
        $cur_filename = substr($phpSelf, $i + 1, strlen($phpSelf));
    }
  }

    // $cur_dir is the directory path from the web server
  $cur_dir = stripslashes(str_replace("/$cur_filename", '', $phpSelf));

    // $loc is the location (in filelist) to view files in $loc directory
  if(!isset($_GET['loc']) || @$_GET['loc'] == '/' || @strstr($_GET['loc'], '..') || @strstr(strtolower($_GET['loc']), 'filelist'))
    $loc = '.';
  else
    $loc = $_GET['loc'];

    // the next three tests make sure $loc is standardized to easier use
  if(substr($loc, 0, 1) == '.')
    $loc = substr($loc, 1);

  if(substr($loc, 0, 1) != '/')
    $loc = '/' . $loc;

  if(substr($loc, -1) != '/')
    $loc = $loc . '/';

    // get arangment type from the URL, if it doesn't exist, set to default
  if(isset($_GET['arange']))
    $arange = $_GET['arange'];
  else
    $arange = 'fd';

    // split $arange into its two components
  $ar1 = substr($arange, 0, 1);
  if($ar1 != 'n' && $ar1 != 't' && $ar1 != 's'&& $ar1 != 'f')
    $ar1 = 'n';
  $ar2 = substr($arange, 1, 1);
  if($ar2 != 'a' && $ar2 != 'd')
    $ar2 = 'a';

  if($auto_icon && !$dontdispheader){
    $autoicon_ud = false;
    if(is_file('filelist/autoicon.list')){
      $autoicon_file = file('filelist/autoicon.list');
      if(trim($autoicon_file[0]) <= filemtime($loc1 . '/' . $cur_filename))
        $autoicon_ud = true;
      else
        for($i = 1; $i < count($autoicon_file); $i++)
          $iconlink[$i - 1] = trim($autoicon_file[$i]);
    } else
      $autoicon_ud = true;

    if($autoicon_ud){
      if(in_array('',$accept)){
        if($accept[count($accept) - 1] != ''){
          $blkloc = array_search('', $accept);
          //unset($accept[$blkloc]);
          for($i = $blkloc; $i < count($accept); $i++)
            $accept[$i] = $accept[$i + 1];
          $accept[count($accept) - 1] = '';
        }
      }

      $lst1 = array(
      '.zip','.tar.gz','.7z','.rar','.exe','.msi','.html','.htm','.php','.php3',
      '.php4','.php5','.phtml','.jpg','.jpeg','.gif','.tiff','.tif','.png','.avi',
      '.asf','.mpg','.mpeg','.wmv','.mov','.mp3','.wma','.wav','.psd','.txt',
      '.list','.bin','.dvi','.iso','.pdf','.js','.vbs','.tar',''
      );

      $lst2 = array(
      '/icons/compressed.gif','/icons/compressed.gif','/icons/compressed.gif',
      '/icons/compressed.gif','/icons/burst.gif','/icons/comp.gray.gif',
      '/icons/layout.gif','/icons/layout.gif','/icons/layout.gif',
      '/icons/layout.gif','/icons/layout.gif','/icons/layout.gif',
      '/icons/layout.gif','/icons/image2.gif','/icons/image2.gif',
      '/icons/image2.gif','/icons/image2.gif','/icons/image2.gif',
      '/icons/image2.gif','/icons/movie.gif','/icons/movie.gif',
      '/icons/movie.gif','/icons/movie.gif','/icons/movie.gif',
      '/icons/movie.gif','/icons/sound2.gif','/icons/sound2.gif',
      '/icons/sound1.gif','/icons/image1.gif','/icons/text.gif',
      '/icons/text.gif','/icons/binary.gif','/icons/dvi.gif',
      '/icons/diskimg.gif','/icons/pdf.gif','/icons/script.gif',
      '/icons/script.gif','/icons/tar.gif','/icons/unknown.gif'
      );

      foreach($lst1 as $i => $val){
        if(in_array($val, $accept)){
          $aryky = array_search($val, $accept);
          $iconlink[$aryky] = $lst2[$i];
        }
      }

      $write = implode($line_break, $iconlink);
      $fout = fopen($loc1 . '/filelist/autoicon.list', 'w');
      $fp = fwrite($fout, time() . $line_break . $write);
      fclose($fout);

    }
  }

    // if the array is setup to accept all extensions set vairable
  if(in_array('', $accept))
    $accept_all = true;
  else
    $accept_all = false;

    // if additional information can be displayed about the files, test for getid3
  if($show_add_info)
    if(@include_once('../inc/getid3/getid3.php')){
      $getid3_true = 1; $getid3_true2 = 1;
    } else {
      $getid3_true = 0; $getid3_true2 = 0;
    }
  else {
    $getid3_true = 0;
    if(@include_once('../inc/getid3/getid3.php'))
      $getid3_true2 = 1;
    else
      $getid3_true2 = 0;
  }

    // set default of $'_loc to the root directory
  $parent_loc = $phpSelf . '?loc=.';
    // set $parent_loc to actual location parent directory
  $array_of_path = explode('/', substr($loc, 0, -1));
  for($i = 1; $i < count($array_of_path) - 1; $i++)
    $parent_loc .= '/' . $array_of_path[$i];

    // get directory names aranged into $sup_dirs to display in title and the top of the page
  $dir_left = '.' . substr($loc, 0, -1);
  $sup_dirs[0] = $dir_left;
  while(substr($dir_left, 1) != ''){
    $array_of_path = explode('/', $dir_left);
    $lnk_num = count($sup_dirs);
    $sup_dirs[$lnk_num] = '.';
    for($i = 1; $i < count($array_of_path) - 1; $i++)
      $sup_dirs[$lnk_num] .= '/' . $array_of_path[$i];
    $dir_left = $sup_dirs[$lnk_num];
  }

  if(isset($_GET['search_value']) && $_GET['search_value'] != '' && $_GET['search_value'] != 'Search'){
    $search_value = $_GET['search_value'];
    $count = 0;
    // not \ / ; * ? " < > | #
    // characters 32-126 !34 !35 !42 !47 !59 !60 !62 !63 !92 !124
    for($i = 32; $i <= 126; $i++)
      if($i != 34 && $i != 35 && $i != 42 && $i != 47 && $i != 59
      && $i != 60 && $i != 62 && $i != 63 && $i != 92 && $i != 124){
        $chr_array[$count] = chr($i);
        $count++;
      }
  
    for($i = 0; $i < strlen($search_value); $i++){
      $char = substr($search_value, $i, 1);
      $chr_done = false;
      for($j = 0; $j < count($chr_array) && !$chr_done; $j++){
        if($chr_array[$j] == $char)
          $chr_done = true;
      }
      if(!$chr_done){
        echo '<font color="red">Search contains invalid character, "'. $char .'"</font><br>';
        $search_value = 'Search';
      }
    }
  } else
    $search_value = 'Search';
  if(isset($_GET['showsearch']) && $_GET['showsearch'] != '')
    $showsearch   = $_GET['showsearch'];
  else
    $showsearch = 0;
  if(isset($_GET['exactmatch']) && $_GET['exactmatch'] != '')
    $exactmatch   = $_GET['exactmatch'];
  else
    $exactmatch = 0;

  $adlnk = '';
  $adlnk2 = '';
  if($search_value != 'Search'){
    $adlnk2 .= '&search_value=' . $search_value;
    if(isset($showsearch))
      $adlnk2 .= '&showsearch=' . $showsearch;
    if(isset($exactmatch))
      $adlnk2 .= '&exactmatch=' . $exactmatch;
  }

  echo '<html><head>';
  echo '<STYLE type=text/css><!--';
  echo 'A:link{color:#FFFFFF}';
  echo 'A:visited{color:#FFFFFF}';
  echo 'A:active{color:#F0F0F0}';
  echo 'A:hover{color:#E0E0E0;text-decoration:none}';
  echo '--></STYLE><title>List in root';

    // display the path of where filelist is currently pointing
  $array_of_path = explode('/', substr($loc, 0, -1));
  for($i = 1, $j = count($sup_dirs) - 2; $i < count($array_of_path); $i++, $j--)
    echo ' < ' . $array_of_path[$i];

  echo '</title>';
  echo "</head><body bgcolor=\"#000000\" text=\"#FFFFFF\" vlink=\"#FFFFFF\" alink=\"#FFFFFF\" link=\"#FFFFFF\">";
  echo '<a href="http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . '?logout=1">Logout</a> <br/>';
  if(!$dontdispheader){

    echo '<table width="100%" cellpadding="2" cellspacing="0" border="0"><tr><td>';
    echo '<font size="+2"><b>List in ';
    echo "<a href=\"$phpSelf?loc=.$adlnk\">root</a>\n";
      // display the path of where filelist is currently pointing
    for($i = 1, $j = count($sup_dirs) - 2; $i < count($array_of_path); $i++, $j--)
      echo ' < ' . "<a href=\"$phpSelf?loc=$sup_dirs[$j]$adlnk\">$array_of_path[$i]</a>\n";

    echo '</b></font>';
    echo '</td></tr></table><br>';

  }


  // if the associated variable is found in the URL and the user has permissions to do such thing then continue

    // if a note is going to be displayed
  if(isset($_GET['note'])){
    $note = $_GET['note'];

    //<a href="" onClick="JavaScript:window.open(\''.$phpSelf.'?note=1\',\'note\',\'width=200,height=200,dependent=yes,location=no,scrollbars=yes,menubar=no,status=no,resizable=yes\')">(?)</a>

    switch($note){
      case 2:
        echo 'The file path is determined by the path from the File Manage file. This does not include any additional path that the server may have, nor does it include the webserver\'s path.';
        break;
      case 3:
        echo 'Audio/Video information is gathered from the open source program <a href="http://www.sourceforge.net/projects/getid3/">getid3()</a>. ';
        break;
      case 5:
        echo 'A search locates files and folders in the current directory with exact and similar matches (unless "Exact results only" is selected). Currently similar matches can be '. ($error_spacer * 100) .'% incorrect on a per word basis, and '. ($error_spacer2 * 100) .'% incorrect on a per phrase basis. Per phrase will include spaces and other chance word anomalies that would make it more likely to match, so it should be a lower value.';
        break;
      default:
        echo 'No note for this';
    }

    echo '<br><input type="button" onClick="JavaScript=window.close()" value="Close Window">';

  } else



    // displays properties of a file
  if(isset($_GET['prop']) && $_GET['prop'] != '' && is_file($loc1 . $_GET['prop']) && allowed($_GET['prop'], true, false, true)){

    $prop = $_GET['prop'];

      // if getid3 is going to be called, set up $filename, then get info
    if($getid3_true2){
      $filename = $loc1 . $prop;
      $getID3 = new getID3;
      $fileinfo = $getID3->analyze($filename);
    }

    echo '<div align="center"><font size="+1"><b>File Properties</b></font></div><br>';
    echo '<table cellpadding="4" cellspacing="0" border="1" align="center">';
    echo '<tr><td align="right">File name</td><td><a title="Open file in separate window" target="_blank" href="' .$cur_dir . $prop .'">'. get_name($prop) .'</a></td></tr>';
    echo '<tr><td align="right"><a href="" onClick="JavaScript:window.open(\''.$phpSelf.'?note=2\',\'note\',\'width=200,height=200,dependent=yes,location=no,scrollbars=yes,menubar=no,status=no,resizable=yes\')">(?)</a> File path</td><td>'. $prop .'</td></tr>';
    $file_size = filesize($loc1 . $prop);
    echo '<tr><td align="right">File size</td><td>';
    if($file_size > 980)
      echo filesz($file_size) . ', ';
    echo $file_size .' bytes</td></tr>';
    echo '<tr><td align="right">File created</td><td>'. date("F d, Y H:i (O)", filectime($loc1 . $prop)) .'</td></tr>';
    echo '<tr><td align="right">File modified</td><td>'. date("F d, Y H:i (O)", filemtime($loc1 . $prop)) .'</td></tr>';
    echo '<tr><td align="right">File accessed</td><td>'. date("F d, Y H:i (O)", fileatime($loc1 . $prop)) .'</td></tr>';

    @$image_info = getimagesize($loc1 . $prop);
    if(isset($image_info[2])){

      $image_type_array = array(NULL,'GIF','JPG','PNG','SWF','PSD','BMP','TIFF(intel byte order)',
      'TIFF(motorola byte order)','JPC','JP2','JPX','JB2','SWC','IFF','WBMP','XBM');
      $image_channel_array = array(NULL,' (gray scale)',NULL,' (RGB)',' (CMYK)');

      echo '<tr><td align="right">Image info:</td><td>';
      echo 'Image resolution: '. $image_info[0] .'x'. $image_info[1] .'<br>';
      echo 'Image type: '. $image_type_array[$image_info[2]] .'<br>';
      if(isset($image_info['bits']))
        echo 'Image depth: '. $image_info['bits'] .'-bit<br>';
      if(isset($image_info['channels']))
        echo 'Image channels: '. $image_info['channels'] . $image_channel_array[$image_info['channels']] .'<br>';
      echo '</td></tr>';
    } else
      // get a heck of a lot of ID3 info here, dang!
    if($getid3_true2 && isset($fileinfo['fileformat'])){
      echo '<tr><td align="right"><a href="" onClick="JavaScript:window.open(\''.$phpSelf.'?note=3\',\'note\',\'width=175,height=150,dependent=yes,location=no,scrollbars=yes,menubar=no,status=no,resizable=yes\')">(?)</a> Audio/Video Info</td><td>';
      echo 'Format: '. $fileinfo['fileformat'] .'<br>';
      echo 'Length: '. $fileinfo['playtime_string'] .'<br>';
      echo 'Bitrate: '. round($fileinfo['bitrate'] / 1000) .' kbps<br>';
      if(isset($fileinfo['video']['codec']))
        echo 'Video codec: '. $fileinfo['video']['codec'] .'<br>';
      echo 'Audio channels: '. $fileinfo['audio']['channels'] .'<br>';
      echo 'Audio sample rate: '. $fileinfo['audio']['sample_rate'] .' Hz<br>';
      if(isset($fileinfo['audio']['bits_per_sample']))
        echo 'Audio sample size: '. $fileinfo['audio']['bits_per_sample'] .' bits<br>';

      if(isset($fileinfo['tags']['id3v1']) || isset($fileinfo['tags']['id3v1'])){
        if(isset($fileinfo['tags']['id3v1']['title'][0]))
          echo 'Song title: '. $fileinfo['tags']['id3v1']['title'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v1']['artist'][0]))
          echo 'Song artist: '. $fileinfo['tags']['id3v1']['artist'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v1']['album'][0]))
          echo 'Song album: '. $fileinfo['tags']['id3v1']['album'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v2']['genre'][0]))
          echo 'Song genre: '. $fileinfo['tags']['id3v2']['genre'][0] .'<br>';
        elseif(isset($fileinfo['tags']['id3v1']['genre'][0]))
          echo 'Song genre: '. $fileinfo['tags']['id3v1']['genre'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v1']['track'][0]))
          echo 'Song track: '. $fileinfo['tags']['id3v1']['track'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v1']['year'][0]))
          echo 'Song year: '. $fileinfo['tags']['id3v1']['year'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v2']['composer'][0]))
          echo 'Song composer: '. $fileinfo['tags']['id3v2']['composer'][0] .'<br>';
        if(isset($fileinfo['tags']['id3v2']['publisher'][0]))
          echo 'Song publisher: '. $fileinfo['tags']['id3v2']['publisher'][0] .'<br>';
      } else
      if(isset($fileinfo['tags'][$fileinfo['fileformat']])){
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['title'][0]))
          echo 'Song title: '. $fileinfo['tags'][$fileinfo['fileformat']]['title'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['artist'][0]))
          echo 'Song artist: '. $fileinfo['tags'][$fileinfo['fileformat']]['artist'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['album'][0]))
          echo 'Song album: '. $fileinfo['tags'][$fileinfo['fileformat']]['album'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['genre'][0]))
          echo 'Song genre: '. $fileinfo['tags'][$fileinfo['fileformat']]['genre'][0] .'<br>';
        elseif(isset($fileinfo['tags'][$fileinfo['fileformat']]['genre'][0]))
          echo 'Song genre: '. $fileinfo['tags'][$fileinfo['fileformat']]['genre'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['track'][0]))
          echo 'Song track: '. $fileinfo['tags'][$fileinfo['fileformat']]['track'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['year'][0]))
          echo 'Song year: '. $fileinfo['tags'][$fileinfo['fileformat']]['year'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['composer'][0]))
          echo 'Song composer: '. $fileinfo['tags'][$fileinfo['fileformat']]['composer'][0] .'<br>';
        if(isset($fileinfo['tags'][$fileinfo['fileformat']]['publisher'][0]))
          echo 'Song publisher: '. $fileinfo['tags'][$fileinfo['fileformat']]['publisher'][0] .'<br>';
      }

      if(isset($fileinfo['video']['resolution_x']))
        echo 'Video resolution: '. $fileinfo['video']['resolution_x'] . 'x' . $fileinfo['video']['resolution_y'] .'<br>';
      if(isset($fileinfo['video']['frame_rate']))
        echo 'Video frame rate: '. $fileinfo['video']['frame_rate'] .'<br>';
      if(isset($fileinfo['video']['bits_per_sample']))
        echo 'Video sample size: '. $fileinfo['video']['bits_per_sample'] .' bits<br>';
      if(isset($fileinfo[$fileinfo['fileformat']]['video']['color_depth']))
        echo 'Video color depth: '. $fileinfo[$fileinfo['fileformat']]['video']['color_depth'] .'-bit<br>';
      if(isset($fileinfo['tags_html'][$fileinfo['fileformat']]['author'][0]))
        echo 'Video author: '. $fileinfo['tags_html'][$fileinfo['fileformat']]['author'][0] .'<br>';
      
      echo '</td></tr>';
    }
    echo '</table>';
    echo '<a href="JavaScript:window.close()">Click here to close this window.</a>';



  } else {



    echo '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td align="left">';
    if ($display_icon)
      echo '<img src="/icons/folder.open.gif">';
    echo '<a href="' . $parent_loc . $adlnk . '">Parent Directory</a></td>';
    echo '<td align="right">Search this directory <a href="" onClick="JavaScript:window.open(\''.$phpSelf.'?note=5\',\'note\',\'width=250,height=280,dependent=yes,location=no,scrollbars=yes,menubar=no,status=no,resizable=yes\')">(?)</a>: <form action="'.$phpSelf.'" method="get"><input type="text" maxlength="255" value="'.$search_value.'" name="search_value"><br>';
    if($showsearch)
      echo '<input type="checkbox" name="showsearch" value="1" checked>Show only search results?<br>';
    else
      echo '<input type="checkbox" name="showsearch" value="1">Show only search results?<br>';
    if($exactmatch)
      echo '<input type="checkbox" name="exactmatch" value="1" checked>Exact results only?<br>';
    else
      echo '<input type="checkbox" name="exactmatch" value="1">Exact results only?<br>';
    echo '<input type="hidden" name="loc" value="'.$loc.'">';
    if($search_value != 'Search')
      echo "<input type=\"button\" value=\"Clear Search\" onClick=\"JavaScript:location.href='$phpSelf?loc=$loc'\"> ";
    echo '<input type="submit" value="Search"></form>';
    echo '</td></tr></table>';

    if($search_value != 'Search')
      echo 'Search results are <b>bold</b>.<br><br>';

    echo "\n";
    echo '<table cellpadding="4" cellspacing="0" border="0">';

    echo '<tr><td></td><td><b>';
    if($ar1 == 'n' && $ar2 == 'a')
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=nd\">File Name</a>";
    else
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=na\">File Name</a>";
    echo '</b></td>';

    echo '<td align="center"><b>';
    if($ar1 == 's' && $ar2 == 'a')
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=sd\">Size</a>";
    else
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=sa\">Size</a>";
    echo '</b></td>';

    echo '<td align="center"><b>';
    if($ar1 == 't' && $ar2 == 'a')
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=td\">Type</a>";
    else
      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=ta\">Type</a>";
    echo '</b></td><td></td>';

    echo '<td align="center"><b>';
    if($show_file_time){
	    if($ar1 == 'f' && $ar2 == 'a')
	      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=fd\">File modified time</a>";
	    else
	      echo "<a href=\"$phpSelf?loc=$loc$adlnk$adlnk2&arange=fa\">File modified time</a>";
		}
	  echo '</b></td>';
	
    if($show_add_info)
      echo '<td><b>Additional Info</b></td>';

    echo '<td></td>';

    echo '</tr>';

    $array_of_dirs = array();
    $array_of_files = array();

    $i = 0;
    $j = 0;
      // open the current directory
    if($handle = opendir($loc1 . $loc)){
      while(false !== ($file = readdir($handle))){

          // though this is kind of weird, I had the variables set up this way. I may change it in the future to get rid of one more line of code (mm optimization, tasty?)
        $file2 = $file;
        $file  = $loc .  $file;
    
        if(is_file($loc1 . $file) && !strstr(strtolower($file), 'filelist')
        && substr($file2, 0, 1) != '.' && !strstr($file, '..') && !strstr($file, '#') && $file2 != $cur_filename){

            // set use this to default, for whence it becomes 1 the loop will end
          $use_this = 0;
            // run through each acceptable extension
          for($k = 0; $k < count($accept) && $use_this != 1; $k++){
              // if the file has the correct extension, continue
            if(strtolower($accept[$k]) == strtolower(substr($file2, strlen($file2) - strlen($accept[$k]), strlen($accept[$k])))){

                // $organize_ord organizes the file names ignoring any leading 'the's and 'a's. With or without $organize_ord, the files are organized ignoring anything but letters and numbers
              if($organize_ord && strtolower(substr($file2, 0, 4)) == 'the ')
                $orgn = ereg_replace("[^[:alnum:]]", '', strtolower(trim(substr($file2, 3))));
              elseif($organize_ord && strtolower(substr($file2, 0, 2)) == 'a ')
                $orgn = ereg_replace("[^[:alnum:]]", '', strtolower(trim(substr($file2, 1))));
              elseif($organize_ord && strtolower(substr($file2, 0, 3)) == 'an ')
                $orgn = ereg_replace("[^[:alnum:]]", '', strtolower(trim(substr($file2, 2))));
              else
                $orgn = ereg_replace("[^[:alnum:]]", '', strtolower(trim($file2)));
    
                // determine the filetype, if there is no '.' or no extension in the filename, then return "none"
              $ftype = 'none';
    
              if(strstr($file2, '.')){
                $m = strlen($file2);
                while($m > 0 && $ftype == 'none'){
                  $m--;
                  if(substr($file2, $m, 1) == '.')
                    $ftype = strtolower(substr($file2, $m + 1, strlen($file2)));
                }
              }

              // store file size (in bytes)
              $size = filesize($loc1 . $file);
              // store file size (in bytes)
              $ftime = filemtime($loc1 . $file);

                // the organization type determines the order in which the variables are stored
              if($ar1 == 'n'){
                $array_of_files[$j]['orgn']   = $orgn;
                $array_of_files[$j]['ftype']  = $ftype;
                $array_of_files[$j]['size']   = $size;
                $array_of_files[$j]['ftime']   = $ftime;
              } else
              if($ar1 == 't'){
                $array_of_files[$j]['ftype']  = $ftype;
                $array_of_files[$j]['orgn']   = $orgn;
                $array_of_files[$j]['size']   = $size;
                $array_of_files[$j]['ftime']   = $ftime;
              } else
              if($ar1 == 's'){
                $array_of_files[$j]['size']   = $size;
                $array_of_files[$j]['ftype']  = $orgn;
                $array_of_files[$j]['ftype']   = $ftype;
                $array_of_files[$j]['ftime']   = $ftime;
              } else
              if($ar1 == 'f'){
                $array_of_files[$j]['ftime']  = $ftime;
                $array_of_files[$j]['orgn']   = $orgn;
                $array_of_files[$j]['ftype']  = $ftype;
                $array_of_files[$j]['size']  = $size;
              }

                // these variables are last since they don't affect organization
              $array_of_files[$j]['file']   = $file;
              $array_of_files[$j]['file2']  = $file2;
              $array_of_files[$j]['extkey'] = $k;
    
              $j++;
                // get the heck out of the loop
              $use_this = 1;
            } // if(strtolower($accept[$k]) == strtolower(substr($file2, ...
          } // for($k = 0; $k < count($accept) && $use_this != 1; $k++)
        } // if(is_file($loc1 . $file) && substr($file2, 0, 8) != 'filelist' && ...
        else
    
        if(is_dir($loc1 . $file) && substr($file2, 0, 1) != '.'
        && !strstr(strtolower($file2), 'filelist') && substr($file2, 0, 6) != 'getid3'
        && substr($file2, 0, 12) != 'My Playlists' && !strstr($file2, '#')){

            // $organize_ord organizes the directory names ignoring any leading 'the's and 'a's. With or without $organize_ord, the directories are organized ignoring anything but letters and numbers
          if(strtolower(substr($file2, 0, 4)) == 'the ' && $organize_ord)
            $array_of_dirs[$i]['orgn'] = ereg_replace("[^[:alnum:]]", '', strtolower(trim(substr($file2, 3))));
          elseif(strtolower(substr($file2, 0, 2)) == 'a ' && $organize_ord)
            $array_of_dirs[$i]['orgn'] = ereg_replace("[^[:alnum:]]", '', strtolower(trim(substr($file2, 1))));
          else
            $array_of_dirs[$i]['orgn'] = ereg_replace("[^[:alnum:]]", '', strtolower(trim($file2)));

            // store directory name to array
          $array_of_dirs[$i]['file'] = $file;
          $array_of_dirs[$i]['file2'] = $file2;

          $i++;
        } // if(is_dir($loc1 . $file) && substr($file2, 0, 1) != '.' && ...
    
      } // while(false !== ($file = readdir($handle)))
        // close out of the directory
      closedir($handle); 
    } // if($handle = opendir($loc1 . $loc))

      // if any directories were found, store them to $array_of_all
    if(@count($array_of_dirs) > 0){
      $u_array_of_dirs = $array_of_dirs;
      sort($array_of_dirs);
      $array_of_all = $array_of_dirs;
    }
      // if any files were found store that info to $array_of_all
    if(@count($array_of_files) > 0){
      $u_array_of_files = $array_of_files;
      if($ar2 == 'a')
        sort($array_of_files);
      else
        rsort($array_of_files);
        // if directories were already stored to $array_of_all, then store each file key to $array_of_all
      if(count($array_of_dirs) > 0){
        for($i = 0; $i < count($array_of_files); $i++){
          $j = count($array_of_all);
          $array_of_all[$j]['file']   = $array_of_files[$i]['file'];
          $array_of_all[$j]['file2']  = $array_of_files[$i]['file2'];
          $array_of_all[$j]['extkey'] = $array_of_files[$i]['extkey'];
          $array_of_all[$j]['size']   = $array_of_files[$i]['size'];
          $array_of_all[$j]['ftype']  = $array_of_files[$i]['ftype'];
          $array_of_all[$j]['ftime']  = $array_of_files[$i]['ftime'];
        }
      } else
        $array_of_all = $array_of_files;
          // if no directories were stored, just store the files to $array_of_all
    }

    if(!isset($array_of_all))
      $array_of_all = array();

      // ready to display all the crap that was just stored
    foreach($array_of_all as $j => $file){
    
      $file   = $array_of_all[$j]['file'];
      $file2  = $array_of_all[$j]['file2'];
      $ftype = $array_of_all[$j]['ftype'];
    
        // this section is for files, later is for directories; also, exclude UNIX hidden files
      if(is_file($loc1 . $file)){
          // nice long test, so that it only does the things it needs to do, when it needs to do it; and if only the finds are to be shown, only they will be
        if(($showsearch == 1 && isclosematch($file2, $search_value, $exactmatch, 0)) || $showsearch != 1 || $search_value == 'Search'){
    
          $extkey = $array_of_all[$j]['extkey'];
    
          $ftype  = $array_of_all[$j]['ftype'];
          $size   = $array_of_all[$j]['size'];
    
            // if there is a file type then get the file's name (no extension)
          if($ftype != 'none')
            $name2 = substr($file2, 0, strlen($file2) - strlen($ftype) - 1);
          else
            $name2 = $file2;

            // show file time if enabled
			    if($show_file_time)
			      $file_time = date("F d, Y H:i (O)", filemtime($loc1 . $file));

            // if additional info is enabled, test each file to see if it can extract any info from it
          if($show_add_info){
            $add_info = '';
              // if the file is a text file, display some of it's contents
            if($ftype == 'txt' || $ftype == 'list' || $ftype == 'nfo'
            || $ftype == 'log' || $ftype == 'diz'){
              $contents  = file($loc1 . $file);
              @$contents2 = $contents[0] . $contents[1] . $contents[2] . rtrim($contents[3]);
              $contlen = strlen($contents2);
              if($contlen > 40){
                $contlen = 40;
                $contover = true;
              } else
                $contover = false;
              $add_info = 'Number of lines: ' . count($contents) . '; <b>Contents:</b> ' . str_replace("\n", ' :: ', str_replace("\r", '', htmlspecialchars(substr($contents2, 0, $contlen))));
              if($contover)
                $add_info .= '...';
            } else
              // if the file is an HMTL file, display it's title, or body, or contents
            if($ftype == 'htm' || $ftype == 'html'){
              $titover = false;
              $bodover = false;
              $conover = false;
              if($php_version[0] > 4 || ($php_version[0] == 4 && $php_version[1] >= 3))
                $contents = file_get_contents($loc1 . $file);
              else {
                $array_of_contents = file($loc1 . $file);
                $contents = implode('', $array_of_contents);
              }
              $contents2 = strtolower($contents);
              if(strstr($contents2, '<title>')){
                $titlepos1 = 7 + strpos($contents2, '<title>');
                $contents2 = substr($contents2, $titlepos1);
                if(strstr($contents2, '</title>')){
                  $titlepos2 = strpos($contents2, '</title>');
                  if($titlepos2 > 40){
                    $titlepos2 = 40;
                    $titover = true;
                  }
                  $add_info = '<b>Title:</b> ' . htmlspecialchars(substr($contents, $titlepos1, $titlepos2));
                  if($titover)
                    $add_info .= '...';
                }
              } else
              if(strstr($contents2, '<body')){
                $bodypos1 = 6 + strpos($contents2, '<body');
                $contents2 = substr($contents2, $bodypos1);
                $bodypos3 = 1 + strpos($contents2, '>');
                $contents2 = substr($contents2, $bodypos3);
                if(strstr($contents2, '</body>')){
                  $bodypos2 = strpos($contents2, '</body>');
                  if($bodypos2 > 40){
                    $bodypos2 = 40;
                    $bodover = true;
                  }
                  $bodycont = substr($contents, $bodypos1 + $bodypos3, $bodypos2);
                  $add_info = '<b>Body:</b> ' . str_replace("\n", ' :: ', str_replace("\r", '', htmlspecialchars($bodycont)));
                  if($bodover)
                    $add_info .= '...';
                }
              } else {
                $cont_len = strlen($contents);
                if($cont_len > 40){
                  $cont_len = 40;
                  $conover = true;
                }
                $add_info = '<b>Contents:</b> ' . str_replace("\n", ' :: ', str_replace("\r", '', htmlspecialchars(substr($contents, 0, $cont_len))));
                if($conover)
                  $add_info .= '...';
              }
            } else
            if($ftype == 'php' || $ftype == 'php3' || $ftype == 'phtml'
            || $ftype == 'php5' || $ftype == 'php4')
              $add_info = 'Number of lines: ' . count(file($loc1 . $file));
            else
    
             // supported by 4.3.2: GIF, JPG, PNG, SWF, SWC, PSD, TIFF, BMP, IFF, JP2, JPX, JB2, JPC, XBM, and WBMP
            if($php_version[0] > 4 || ($php_version[0] == 4 && $php_version[1] > 3) || ($php_version[0] == 4 && $php_version[1] == 3 && $php_version[2] >= 2))
              if($ftype == 'gif' || $ftype == 'jpg' || $ftype == 'jpeg'
              || $ftype == 'png' || $ftype == 'swf' || $ftype == 'swc'
              || $ftype == 'psd' || $ftype == 'tiff' || $ftype == 'bmp'
              || $ftype == 'iff' || $ftype == 'jp2' || $ftype == 'jpx'
              || $ftype == 'jb2' || $ftype == 'jpc' || $ftype == 'xbm'
              || $ftype == 'wbmp' || $ftype == 'tif')
                if($tmp = getimagesize($loc1 . $file))
                  $add_info = 'Resolution: ' . $tmp[0] . 'x' . $tmp[1];
            else
                // supported by 4.3.0: GIF, JPG, PNG, SWF, SWC, PSD, TIFF, BMP, and IFF
              if($php_version[0] > 4 || ($php_version[0] == 4 && $php_version[1] >= 3))
                if($ftype == 'gif' || $ftype == 'jpg' || $ftype == 'jpeg'
                || $ftype == 'png' || $ftype == 'swf' || $ftype == 'swc'
                || $ftype == 'psd' || $ftype == 'tiff' || $ftype == 'bmp'
                || $ftype == 'iff' || $ftype == 'tif')
                  if($tmp = getimagesize($loc1 . $file))
                    $add_info = 'Resolution: ' . $tmp[0] . 'x' . $tmp[1];
            else
                // supported by 4.2.0: GIF, JPG, PNG, SWF, PSD, TIFF, BMP, and IFF
              if($php_version[0] > 4 || ($php_version[0] == 4 && $php_version[1] >= 2))
                if($ftype == 'gif' || $ftype == 'jpg' || $ftype == 'jpeg'
                || $ftype == 'png' || $ftype == 'swf' || $ftype == 'psd'
                || $ftype == 'tiff' || $ftype == 'bmp' || $ftype == 'iff'
                || $ftype == 'tif')
                  if($tmp = getimagesize($loc1 . $file))
                    $add_info = 'Resolution: ' . $tmp[0] . 'x' . $tmp[1];
            else
                // supported before 4.2.0: GIF, JPG, PNG, SWF, PSD, BMP, and IFF
              if($ftype == 'gif' || $ftype == 'jpg' || $ftype == 'jpeg'
              || $ftype == 'png' || $ftype == 'swf' || $ftype == 'psd'
              || $ftype == 'bmp' || $ftype == 'iff')
                if($tmp = getimagesize($loc1 . $file))
                  $add_info = 'Resolution: ' . $tmp[0] . 'x' . $tmp[1];
          }

            // if getid3 is going to be called, set up $filename, then get info
          if($show_add_info && $getid3_true && ($add_info == '' || !isset($add_info))){
            $filename = $loc1 . $file;
            $getID3 = new getID3;
            $fileinfo = $getID3->analyze($filename);

            $add_info = '';
    
            if($getid3_true && isset($fileinfo['video']['codec'])){
              if(isset($fileinfo['bitrate']))
                $file_br = $fileinfo['bitrate'];
              if(isset($fileinfo['playtime_string']))
                $add_info = 'Length: ' . $fileinfo['playtime_string'] . '; ';
              $add_info = $add_info . 'Resolution: ' . $fileinfo['video']['resolution_x'] . 'x' . $fileinfo['video']['resolution_y'] . ';';
              if(isset($file_br))
                $add_info = 'Bitrate: ' . round($file_br / 1000) . ' kbps; Codec: ' . $fileinfo['video']['codec'];
            } else
            if($getid3_true && isset($fileinfo['audio']['bitrate'])){
              $file_br = $fileinfo['bitrate'];
              $add_info = 'Length: ' . $fileinfo['playtime_string'] . '; Bitrate: ' . round($file_br / 1000) . ' kbps';
              if($ftype == 'wma')
                $add_info = $add_info . ' (MP3 equiv: ' . round($file_br / 600) . ' kbps)';
            }
          }

          $file_size = filesz(filesize($loc1 . $file));
          echo "\n<tr><td>";
          if ($display_icon)
           echo "<img src=\"$iconlink[$extkey]\">";
          echo "</td><td>";
          if ($ftype == 'kmz')
           echo "<a href=\"$SERVER_PATH/index.php?statsfile=$file\">";
          else
            echo "<a href=\"$SERVER_PATH$cur_dir$file\">";
           // if there is a search occuring, and the test of the search string in the isclosematch() function returns true; then bold the name so it can be seen
          if($search_value != 'Search' && isclosematch($file2, $search_value, $exactmatch, !$exactmatch))
            echo "<b>$name2</b>";
          else
            echo $name2;
          echo '</a></td><td align="center">';
          echo $file_size . '</td>';
          echo '<td align="center">';
          echo $ftype . '</td><td align="center">';
            // if the file's creation time is newer than the current time - the "new time" deadline (set above in seconds) then display "new"
          if($display_new && filectime($loc1 . $file) > time() - $new_time_secs)
            echo '<font color="red">New</font>';
          elseif($display_updated && filemtime($loc1 . $file) > time() - $new_time_secs)
            echo '<font color="yellow">Updtd</font>';
          echo '</td>';
          if($show_file_time)
            echo '<td>' . $file_time . '</td>';
          if($show_add_info)
            echo '<td>' . $add_info . '</td>';
          $fileenc = str_replace('&', "%26", $file);
          echo '<td><a title="Get properties of file" href="" onClick="';
          echo "JavaScript:window.open('$phpSelf?loc=$loc&prop=$fileenc','properties','width=430,height=580,dependent=yes,location=no,scrollbars=yes,menubar=no,status=no,resizable=yes')";
          echo '">Prop</a></td></tr>';
        }
    
      } else
        // this section is for directories, previous was for files; also, exclude the '.' and '..' directories
      if(is_dir($loc1 . $file)){
          // if there is a search occuring, and the test of the search string in the isclosematch() function returns true, or not only search results are being displayed, or there is no search; then continue, so the results can be displayed
        if(($showsearch == 1 && isclosematch($file2, $search_value, $exactmatch, 0)) || $showsearch != 1 || $search_value == 'Search'){
    
          echo "\n<tr><td><img src=\"/icons/folder.gif\"></td><td><a href=\"$phpSelf?loc=.$file$adlnk\">";
            // if there is a search occuring, and the test of the search string in the isclosematch() function returns true; then bold the name so it can be seen
          if($search_value != 'Search' && isclosematch($file2, $search_value, $exactmatch, !$exactmatch))
            echo "<b>$file2</b>";
          else
            echo $file2;
          echo '</a></td><td colspan="2" align="center">Directory</td><td>';
            // if the directory has been modified since the deadline, then display it, if the directory is new, then display so, if not, then display "updated"
          if($display_new && filectime($loc1 . $file) > time() - $new_time_secs)
            echo '<font color="red">New</font>';
          elseif($display_updated && filemtime($loc1 . $file) > time() - $new_time_secs)
            echo '<font color="yellow">Updated</font>';
    
          echo '</td>';
          if($show_file_time)
            echo '<td></td>';
          if($show_add_info)
            echo '<td></td>';
          echo '</tr>';
        }
      }

    }

    echo "\n</table>\n";
    echo '<br><br>';
    if ($display_icon)
      echo '<img src="/icons/folder.open.gif">';
    echo '<a href="' . $parent_loc . $adlnk . '">Parent Directory</a>';

      // this area scans all subdirectories and stores the amount of files, directories, filesize, and time stamp into a file for access later
      // depending on the amount of files and directories, this could be a very long processes, so it doesn't happen every time a page loads
      // based on a variable set at the beginning of this file, it makes this refresh based on the amount of files and directories
      // this all boils down to the custom recursive flscandir() function. View that furthur down to see what is actually happening
      // it then takes all this information and displays it at the bottom of the page
    if($show_all_stats){
      $get_new_info = 0;

      if(!is_dir('filelist/'))
        mkdir('filelist/');

      if(!is_file($logFile))
        $get_new_info = 1;
      else {
        $array_of_log_file = file($logFile);
        if(trim($array_of_log_file[0]) < time() - $update_log_sec)
          $get_new_info = 1;
        else {
          $number_of_dirs  = trim($array_of_log_file[1]);
          $number_of_files = trim($array_of_log_file[2]);
          $total_file_size = trim($array_of_log_file[3]);
          $last_update     = trim($array_of_log_file[0]);
        }
      }

      if($get_new_info == 1){

        $number_of_files = 0;
        $number_of_dirs  = 0;
        $total_file_size = 0;
        $count = 0;

        flscandir('.');

        $fout = fopen($logFile, 'w');
        $fp = fwrite($fout, time() . $line_break . $number_of_dirs . $line_break . $number_of_files . $line_break . $total_file_size . $line_break);
        fclose($fout);
        $last_update = time();
      }

      $file_size = filesz($total_file_size);
      echo "<br><br>As of " . date("F d, Y H:i (O)", $last_update) . " there is a total of $number_of_dirs directories, containing $number_of_files files, with a total file size of $file_size\n\n";
    }
  }

} else {

  if($php_version[0] < 4 || ($php_version[0] == 4 && $php_version[1] < 2)){
    echo 'Server\'s PHP version is too old. It must be at least 4.2.0.<br>';
    echo 'Visit <a href="http://www.php.net">PHP.net</a> to get the newest version.';
  }
}

  // always display this info for obvious legal reasons
echo '<br><br><br><font size="-1"><a target="_blank" href="http://www.sourceforge.net/projects/filelist/">Bobb\'s File List System</a> version ' . $version . '.<br>';
echo '&copy; 2004-2005 Bobb\'s File List System Development Team.<br>';
echo 'These pages are licensed under the <a href="http://www.opensource.org/licenses/gpl-license.php" target="_blank">GNU General Public License</a>.<br>';
echo 'These pages and the software that generates them are NOT covered by any kind of warranty, either expressed or implied.<br>';
echo 'Redistribution is permitted under the same license.<br>';
echo 'Modified by Svend Stave, Thrane & Thrane A/S 2012.</font>';

echo '</body></html>';



/******************************************************************************
 *     Below here are all the functions called by all the previous code.      *
 ******************************************************************************/



  // function keeps track of page views and unique hits and logs them in a file
  // so they can be accessed later for statistics.
  // Some ideas and lines of code taken from OutSide Photos
  // (www.sourceforge.net/projects/outside-photos/) copyright 2004-2005 by
  // OutSide Photos Development Team under the GNU General Public License,
  // redistibution is allowed under the same licese. View the file LICENSE that
  // was packaged with this file, or download a copy at
  // www.opensource.org/licenses/gpl-license.php
function hits(){

    // this keeps track of all the page views
  $pageviewsfile = 'filelist/pageviews.list';
  if(is_file($pageviewsfile)){
    $array_of_pageviews = file($pageviewsfile);
    $number_of_pageviews = $array_of_pageviews[0] + 1;
    $pageviewdate = $array_of_pageviews[1];
  } else {
    $number_of_pageviews = 1;
    $pageviewdate = time();
  }
    // fopen is set to w (instead of a) so that it will overwrite the previous value
  $fout = fopen($pageviewsfile, 'w');
  $fp = fwrite($fout, $number_of_pageviews . $GLOBALS['line_break'] . $pageviewdate);
  fclose($fout);

    // this keeps track of all unique hits
  if(isset($_SERVER['REMOTE_ADDR'])){
    $hitsfile = 'filelist/hits.list';
    if(is_file($hitsfile)){
      $array_of_hits = file($hitsfile);
      for($i = 1; $i < count($array_of_hits); $i++)
        $array_of_hits[$i - 1] = trim($array_of_hits[$i]);
      if(!in_array($_SERVER['REMOTE_ADDR'], $array_of_hits, true)){
        $fout = fopen($hitsfile, 'a');
        $fp = fwrite($fout, $_SERVER['REMOTE_ADDR'] . $GLOBALS['line_break']);
        fclose($fout);
      }
    } else {
      $fout = fopen($hitsfile, 'a');
      $fp = fwrite($fout, time() . $GLOBALS['line_break'] . $_SERVER['REMOTE_ADDR'] . $GLOBALS['line_break']);
      fclose($fout);
    }
  }

} // end of hits()



  // takes $name and returns just the name of the file, no directories
  // also outputs $pre_ex as global for other uses
function get_name($name){

  global $pre_ex;

  $pre_ex = '';

  if(substr($name, 0, 1) == '/')
    $name = substr($name, 1);
  if(strstr($name, '/')){
    for($i = strlen($name) - 1; $i >= 0; $i--){
      if(substr($name, $i, 1) == '/'){
        $pre_ex = substr($name, 0, $i + 1);
        return substr($name, strlen($pre_ex));
      }
    }
  }
  return $name;

} // end of get_name()



  // Is this an acceptable file to do anything with? Does it comply with all
  // needs such as accepted extension and is not the current file. Does it have
  // anything to do with filelist?
  // returns true/false based on results
function allowed($file, $dis_allowed_error, $dis_allowed_cont, $write_log){

  if(substr($file, 0, 1) != '/')
    if(substr($file, 0, 2) == './')
      $file = $GLOBALS['loc'] . substr($file, 2);
    else
      $file = $GLOBALS['loc'] . $file;

  if(!isset($GLOBALS['new_file']))
    $new_file = false;
  else
    $new_file = $GLOBALS['new_file'];

  if(isset($_POST['ren2'])
  || isset($_POST['cfname'])
  || isset($_FILES['userfile']))
    $new_file = true;

  if((is_file($GLOBALS['loc1'] . $file) || $new_file)
  && !strstr(strtolower($file), 'filelist') && !strstr($file, '..') && !strstr($file, '#') && $file != $GLOBALS['phpSelf'])
    if($GLOBALS['accept_all'])
      return true;
    else {
      foreach($GLOBALS['accept'] as $exts)
        if(strtolower($exts) == strtolower(substr($file, strlen($file) - strlen($exts), strlen($exts))))
            // get the heck out of the function
          return true;
    } // if(!$accept_all)

  if($dis_allowed_error)
    echo '<font color="red">This file ('.$file.') is off limits. If you believe this is in error, please contact the administrator of this site immidiately.</font><br><br>';
  if($dis_allowed_cont)
    echo '<a href="' . $GLOBALS['phpSelf'] . '?loc=' . $GLOBALS['loc'] . '">Click here to go back</a>';

  if($write_log)
    writelog('<font color="red">User tried to access a forbidden file</font>', 'unknown', $file, 2);

  return false;

} // end of allowed()



  // function returns a string of the filesize with proper denominator
  // returns false if number is not greater than 0 or it is a non-number
function filesz($size){

  if($size >= 0){
    if($size < 980)
      return ($size                          . ' bytes');
    else
    if($size < 1000000)
      return (round(($size / 1024),2)        . ' KB');
    else
    if($size < 1020000000)
      return (round(($size / 1048576),2)    . ' MB');
    else
      return (round(($size / 1073741824),3) . ' GB');
  } else
    return false;

} // end of filesz()



  // this is a function to determine if the entered search is at least close to
  // the file's name
  // $name = file name;
  // $search = entered search;
  // $exact = whether or not to just return true on exact matches;
  // $disp = whether or not to display "exact match" when returning true
function isclosematch($name, $search, $exact, $disp){

    // if the file name contains the search, then return "exact match"
  if(strstr(strtolower($name), strtolower($search))){
      // if display is not supressed, then display
    if($disp != 0)
      echo 'Exact match: ';
    return true;
  } else
      // if exact matches are the only returned true, then return all else false
    if($exact == 1)
      return false;

    // error spectrum that will be allowed, 1 = all should return true, 0 = must be exact match
    // these numbers are used so that the shorter the word the more strict it is (per letter) and visa versa
  $error_spacer  = $GLOBALS['error_spacer'];  // for per word errors
  $error_spacer2 = $GLOBALS['error_spacer2']; // for per segment errors

    // number of characters errors allowed (calculated from the numbers above and the length of the word)
  $num_of_error_chars  = strlen($search) * $error_spacer;  // for per word errors
  $num_of_error_chars2 = strlen($search) * $error_spacer2; // for per segment errors

    // rounded down integer of the number of letters that can be different from the file and and search
  $real_num_of_error_chars = floor(strlen($search) * $error_spacer2);

    // if the whole file name is less than $num_of_error_chars from the search then return true
  if(levenshtein(strtolower($name), strtolower($search)) <= $num_of_error_chars)
    return true;

    // split all the words of the file into an array to sort through
  $array_of_words = explode(' ', strtolower($name));

    // if a word in the file name is less than $num_of_error_chars from the search then return true
  foreach($array_of_words as $word)
    if(levenshtein($word, strtolower($search)) <= $num_of_error_chars)
      return true;

    // if a segment of the file name is less than $num_of_error_chars from the search then return true
  for($i = 0; $i < strlen($name) - strlen($search) + $real_num_of_error_chars; $i++)
    for($j = 0 - $real_num_of_error_chars; $j <= $real_num_of_error_chars; $j++){
      $name1 = substr($name, $i, strlen($search) + $j);
      if(levenshtein(strtolower($name1), strtolower($search)) <= $num_of_error_chars2)
        return true;
    }

    // all else fails: return false
  return false;
} // end of isclosematch()



  // This is a recursive function which scans a directory and all its
  // subdirectories. It collects total number of files, directories, and
  // filesize.
  // All that info is stored to global variables for the program to access
function flscandir($dir){
  $accept = $GLOBALS['accept'];
  global $file_array;
  $accept_all = $GLOBALS['accept_all'];

  if ($handle = opendir($dir)){           // if the folder exploration is sucsessful, continue
    while (false !== ($file = readdir($handle))){ // as long as storing the next file to $file is successful, continue
      $path = $dir . '/' . $file;
      $GLOBALS['new_file'] = true;
      if(is_file($path) && allowed($path, false, false, false)){
        $GLOBALS['number_of_files']++;
        $GLOBALS['total_file_size'] += filesize($path);
      } else
      if(is_dir($path) && substr($file, 0, 1) != '.' && !strstr(strtolower($file), 'filelist') && substr($file, 0, 6) != 'getid3' && substr($file, 0, 12) != 'My Playlists'){
        $GLOBALS['number_of_dirs']++;
        flscandir($path);
      }
    }
    closedir($handle); // close the folder exploration
  }

} // end of flscandir()

?>
