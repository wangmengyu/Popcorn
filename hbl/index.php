<?php
////////////////////////////////////////////////////////
/// Loadiine.ovh PHP Parsing Script for MP4 Payloads ///
////////////////////////////////////////////////////////

///////////////////////////////
// Put your payload's name below, make sure this script and your .mp4 are within the same folder
$payload = '551hbl.mp4';
///////////////////////////////

// The following lines does not need to modified. However it includes guidance explanations to show you what it does step-after-step

// Verify if the payload is present
$fm=@fopen($payload,'rb');

if(!$fm) {
  // In case of loading failure due to the payload missing, end the action and leave a message about it
  echo "<b>!! ALERT !!</b> The file '$payload' is missing from the server, please check your script and try again.";
  // On Loadiine.ovh, the behavior in case of 404 error is a bit different :
  // header('Location: index.php?err=pnfhbl550');
  die();
}

// Let's begin the script by first getting the exact size of the .mp4 we're about to use
$size=filesize($payload);
$begin=0;
$end=$size;

// Parsing Header
if(isset($_SERVER['HTTP_RANGE'])) {
  if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
    $begin=intval($matches[0]);
    if(!empty($matches[1])) {
      $end=intval($matches[1]);
    }
  }
}

// Are we in the end of the file or not?
if($begin>0||$end<$size)
  header('HTTP/1.0 206 Partial Content');
else
  header('HTTP/1.0 200 OK');

// With all gathered informations, we are now ready to send correct headers to the browser
header("Content-Type: video/mp4");
header('Accept-Ranges: bytes');
header('Content-Length:'.($end-$begin));
header("Content-Disposition: inline;");
header("Content-Range: bytes $begin-$end/$size");
header("Content-Transfer-Encoding: binary\n");
header('Connection: close');

// At this exact moment, WiiU is loading its video player according to "Content-Type: video/mp4"
// When loaded too quickly, the video player can still freeze. So let's leave him 1 second to pop-up
sleep(1);

// Video player is now running smoothly, we send him our parsed Payload 
$cur=$begin;
fseek($fm,$begin,0);
while(!feof($fm)&&$cur<$end&&(connection_status()==0))
{ print fread($fm,min(1024*16,$end-$cur));
  $cur+=1024*16;
  usleep(1000);
}
// The job is now done! We close the script by a word that speak for itself 
die();