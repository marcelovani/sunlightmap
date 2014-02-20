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

class Vec3 {
    public $X, $Y, $Z;
    
    public function __CONSTRUCT($x, $y = null, $z = null) {
        if($y === null) {
            $this->X = $x->X;
            $this->Y = $x->Y;
            $this->Z = $x->Z;
        } else {
            $this->X = $x;
            $this->Y = $y;
            $this->Z = $z;
        }
    }
    
    public function dot($vec) {
        return ($this->X*$vec->X) + ($this->Y*$vec->Y) + ($this->Z*$vec->Z);
    }
    
    public function add($vec) {
        return new Vec3($this->X + $vec->X, $this->Y + $vec->Y, $this->Z + $vec->Z);
    }
    
    public function minus($vec) {
        return new Vec3($this->X - $vec->X, $this->Y - $vec->Y, $this->Z - $vec->Z);
    }
    
    public function times($multiplier) {
        return new Vec3($this->X * $multiplier, $this->Y * $multiplier, $this->Z * $multiplier);
    }
    
    public function normalize($to = 1) {
        $invLength = $to / $this->length();
        $this->X *= $invLength;
        $this->Y *= $invLength;
        $this->Z *= $invLength;
    }
    
    public function lengthSquared() {
        return pow($this->X, 2) + pow($this->Y, 2) + pow($this->Z, 2);
    }
    
    public function length() {
        return sqrt($this->lengthSquared());
    }
    
    public function copy() {
        return new Vec3($this);
    }
}
