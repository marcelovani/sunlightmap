<?php
/* Copyright (c) 2009, J.P.Westerhof <jurgen.westerhof@gmail.com>

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
 */

if(isset($_GET['show_source'])) {
    show_source(__FILE__);
    die();
}
$date = date('Y-m-d');
$time = date('H:i:s');
$custom = 0;

if(isset($_REQUEST['date']))
    if(preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $_REQUEST['date'])) {
        $date = $_REQUEST['date'];
        $custom = 1;
    }
    
if(isset($_REQUEST['time']))
    if(preg_match('/\d{1,2}:\d{1,2}:\d{1,2}/', $_REQUEST['time'])) {
        $time = $_REQUEST['time'];
        $custom = 1;
    }

$matches = array();
preg_match('/(?P<year>\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2}) (?P<hour>\d{1,2}):(?P<minute>\d{1,2}):(?P<second>\d{1,2})/', $date.' '.$time, $matches);

$mktime = mktime($matches['hour'], $matches['minute'], $matches['second'], $matches['month'], $matches['day'], $matches['year']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>World sunlight map implementation using vectors</title>
</head>
<body style="font-family: Lucida Console; font-size: 10pt;">
<h1>World sunlight map</h1>
<table border="0" cellspacing="0" cellpadding="4" width="100%">
<tr><td style="width:408px;" valign="top">
<p>This is an implementation using vectors. It is discussed in the article '<a href="http://www.edesign.nl/2009/05/14/math-behind-a-world-sunlight-map/">Math behind a world sunlight map</a>' published at <a href="http://www.edesign.nl">eDesign.nl</a>. A form to render maps using custom dates and times is provided below. Also links are included to the source code of the files needed to run this example application yourself.</p>
<form method="post" action="index.php">
    <p><b>Set a custom date (format: yyyy-mm-dd):</b><br />
    <input type="text" name="date" style="width: 380px;" value="<?= htmlentities($date);?>" /><br />
    <b>Set a custom time (format: hh:mm:ss):</b><br />
    <input type="text" name="time" style="width: 380px;" value="<?= htmlentities($time);?>" /><br />
    <input type="checkbox" name="big" value="1" <?= (isset($_POST['big'])? 'checked="checked"':'') ?>/> Generate bigger map<br />
    <input type="submit" value="Generate" /></p>
</form>
<p style="font-size: 7pt;">World sunlight map implementation using vectors by <a href="http://www.edesign.nl">eDesign.nl</a><br />
Sources: <a href="?show_source">this file</a> | <a href="map.php?show_source">map.php</a> | <a href="sphere.class.php?show_source">sphere.class.php</a> | <a href="vec3.class.php?show_source">vec3.class.php</a><br />
Images(Source: <a href="http://www.nasa.gov/">Nasa</a>): <a href="night_live.png">night_live.png</a> | <a href="day_live.png">day_live.png</a> | <a href="night.png">night.png</a> | <a href="day.png">day.png</a><br />
</p>

<p style="font-size: 7pt;">Also countermeasures are installed to limit cpu load to eDesign visitors only. You can see how this is done by viewing <a href="nodeeplink.php?show_source">nodeeplink.php</a>. If you want this sunlight map on your site there are two options: 1) download and install the php scripts yourself, or 2) do deeplink to this image, but this will result in a cached image (which is not guaranteed up to date).</p>
<p>
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" style="border: 0px;" /></a>
  </p>
  
</td> 
 <td valign="top" style="border-left: 1px solid black;"><div id="matched" style="width:100%">

<h2>The map</h2>
<? if($custom == 0): ?>
    <a href="http://www.edesign.nl/2009/05/14/math-behind-a-world-sunlight-map/"><img src="map.php" alt="World sunlight map by eDesign.nl" style="border: none;" /></a><br />
    Rendered: <?= ((filemtime('render.png') < $mktime - 300)? date('F jS, Y H:i:s', $mktime): date('F jS, Y H:i:s', filemtime('render.png')).' (this is from cache)') ?>
<? else: ?>
    <a href="http://www.edesign.nl/2009/05/14/math-behind-a-world-sunlight-map/"><img src="map.php?custom=<?= $mktime ?><?= (isset($_POST['big'])? '&big=1':'') ?>" alt="World sunlight map by eDesign.nl" style="border: none;" /></a><br />
    Rendered what the earth looks like on <?= date('F jS, Y H:i:s', $mktime) ?>
<? endif; ?>

</div></td>
</tr>

</table>
</body>
</html>
