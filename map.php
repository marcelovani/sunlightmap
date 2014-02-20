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

if(!preg_match('/.edesign.nl(\/.*)?$/i', $_SERVER['HTTP_REFERER'])) {
    header ('Content-type: image/png');
    readfile('render.png');
    die();
}

$suffix = '_live.png';
if(isset($_REQUEST['big'])) 
    $suffix = '.png';

$custom = false;
$mktime = mktime();
//var_dump($mktime);
if(isset($_REQUEST['custom'])) {
    $tmptime = intval($_REQUEST['custom']);
    if($tmptime > 0) {
        $mktime = $tmptime;
        $custom = true;
    }
}
//var_dump($mktime);

$debug = 1;

$modifiedTime = filemtime('render.png');

if($modifiedTime < $mktime - 300 || $custom) { //300s = 5 min
    $filename_day = 'day'.$suffix;
    $filename_night = 'night'.$suffix;
    $mapoffset = 0;//.25; //hour

    /*
                    y+
                    |    0:00 UTC
                    |     z+
                    |    /
                    |  /
                    | /
    18:00            |/
    x- -------------*------------------x+  6:00
                   /|
                  / |
                 /    |
                /    | 
               /    |
              z-    |
            12:00    y-
    */

    require_once('sphere.class.php');
    require_once('vec3.class.php');

    function mixColors($im, $colors) {
        $shares = 0;
        $r = 0;
        $g = 0;
        $b = 0;
        
        foreach($colors as $color) {
            $rgb = $color[0];
            $share = $color[1];
            
            if($share > 0) {
                $r += (($rgb >> 16) & 0xFF) * $share;
                $g += (($rgb >> 8) & 0xFF) * $share;
                $b += ($rgb & 0xFF) * $share;
                $shares += $share;
            }
        }
        
        $r = round($r / $shares);
        $g = round($g / $shares);
        $b = round($b / $shares);
        
        return imagecolorallocate($im, $r, $g, $b);
    }

    $dayOfYear = 174; //june 21th
    $time = 12;//in hours GMT and UTC

    $daysInYear = 365 + date('L', $mktime);
    $dayOfYear = date('z', $mktime);
    $time = date('H', $mktime) + (date('i', $mktime) / 60) + (date('s', $mktime) / 3600);
    $timeZoneOffset = date('Z', $mktime);


    $time = ($time + 24 + 6 - ($timeZoneOffset/3600) - $mapoffset);

    while($time > 24)
        $time = $time - 24;

    $time = $time  / 24;


    $earth = new Sphere(new Vec3(0,0,0), 1);
    $pointingFromEarthToSun = new Vec3(sin((2*M_PI) * $time), 0, cos((2*M_PI) * $time));

    //$pointingFromEarthToSun->normalize();
    //var_dump($pointingFromEarthToSun);

    //seasonOffset takes care of the earth's 23,5 degree tilt (in y);
    /*
        21 juni staat de zon recht boven de steenbokskeerkring en begint de zomer op het noordelijke halfrond
        
        365 = 1 periode
        
        hoek = a cos(b*x + c) + d
        31+ 30 + 31+ 30+ 31+ 21 = 174
        a = 23,5
        b = 
        
        hoek = 23.5 * cos((2* PI * (x - 174)) / 365)
        
        y is dan dus als volgt te berekenen
        
        tan(hoek) = y / 1 , dus t = tan(hoek)
    */
    $tilt = 23.5 * cos((2 * M_PI * ($dayOfYear - 173)) / $daysInYear); //tilt is de hoek die het zonlicht met het vlak van de evenaar maakt
    $seasonOffset = new Vec3(0, tan(M_PI *2* ($tilt/360)), 0);

    $pointingFromEarthToSun = $pointingFromEarthToSun->add($seasonOffset);

    /*
     u0,v0            u200, v0
      |
      |
      | u0, v100            u200, v100
      +------------------------- u+

     $imageDimensions = 200x100;
    */

    $info = getimagesize($filename_day);
    $maxU = $info[0];
    $maxV = $info[1];

    $im = @imagecreatefrompng($filename_day)
        or die('Cannot Initialize new GD image stream');
    $in = @imagecreatefrompng($filename_night)
        or die('Cannot Initialize new GD image stream');

    $halfMaxV = $maxV / 2;
    $doubleMaxV = $maxV * 2;

    $pointingFromEarthToSun->normalize();

    for($u = 0; $u < $maxU; $u++) {
        for($v = 0; $v < $maxV; $v++) {
            
            $phi = (($v / $doubleMaxV) - 1)*(2*M_PI); //latitude(o met verticale streep erin)
            $theta = ($u/$maxU)*(2*M_PI); //longitude (o met horizontale streep erin)
            
            //$y = -1 * (($v / $halfMaxV) - 1);
            $y = cos($phi);
            $x = sin($phi) * cos($theta); //cos($theta) * sin(($v / $halfMaxV) - 1);
            $z = sin($phi) * sin($theta); //sin(($u/$maxU)*(2*M_PI)) * sin(($v / $halfMaxV) - 1);
            
            $earthNormal = new Vec3($x, $y, $z);
            $earthNormal->normalize();
            
            $angleBetweenSurfaceAndSunlight = $pointingFromEarthToSun->dot($earthNormal);
            
            if($angleBetweenSurfaceAndSunlight <= -.1) { // The $pointingFromEarthToSun vector is pointing into the earth (it cannot see the sun)
                imagesetpixel($im, $u, $v, imagecolorat($in, $u, $v)); //night
            } elseif($angleBetweenSurfaceAndSunlight < .1) { // The $pointingFromEarthToSun almost misses the earth
                $fractionDay = ($angleBetweenSurfaceAndSunlight+.1) * 5;
                
                $colors = array(
                    array(imagecolorat($im, $u, $v), $fractionDay),
                    array(imagecolorat($in, $u, $v), 1 - $fractionDay)
                );

                imagesetpixel($im, $u, $v, mixColors($im, $colors)); //dusk
            } elseif($angleBetweenSurfaceAndSunlight > .97) { // The $pointingFromEarthToSun almost is aligned with the normal, we should make a sun reflection
                $dayColor = imagecolorat($im, $u, $v);
                $blue = $dayColor & 0xFF;
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                if(false && $blue > 35 && $red < 20 && $green < 20) { //only add extra white when above sea: land can't reflect
                    $fractionNotReflection = (1 - $angleBetweenSurfaceAndSunlight) * 30;
                    
                    $colors = array(
                        array(imagecolorat($im, $u, $v), $fractionNotReflection * 15),
                        array(imagecolorallocate($im, 255, 255, 255), 1 - $fractionNotReflection)
                    );
                    
                    imagesetpixel($im, $u, $v, mixColors($im, $colors));  //reflect extra white
                }
            } else {
                imagesetpixel($im, $u, $v, imagecolorat($im, $u, $v));  //day
            }
        }
    }
    if($debug && !$custom) {
        imagepng($im, 'render.png');
    } elseif ($debug) {
        header ('Content-type: image/png');
        imagepng($im);
    }
    
    imagedestroy($im);
    imagedestroy($in);
}

if($debug && !$custom) {
    header ('Content-type: image/png');
    $im = imagecreatefrompng('render.png');
    imagepng($im);
    imagedestroy($im);
}
