<?php

function coord($c, $a, $r) {
	$rad = 2 * PI / 360 * $a;
	$x = $c["x"] + cos($rad) * $r;
	$y = $c["y"] - sin($rad) * $r;
	return array("x" => $x, "y" => $y);
}

function ffloat($f, $decimals = 1, $separator = false) {
	$str = sprintf("%0.".$decimals."f", $f);
	if ($separator)	$str = str_replace(".", $separator, $str);
	return $str;
}

function fcoord($coord) {
	return ffloat($coord["x"]).", ".ffloat($coord["y"]);
}

function fline($p1, $p2) {
	return "M ".fcoord($p1)." L ".fcoord($p2);
}

function fpath($id, $p1, $p2, $inverse = false) {
	return "<path id=\"$id\" d=\"".fline($inverse ? $p2 : $p1, $inverse ? $p1 : $p2)."\" class=\"transition\"/>";
}

define("PI", 3.1415927);

$r = 80;
$da = 15;

$gaz = array("x" => 0, "y" => 0);
$solide = array("x" => -289, "y" => 500);
$liquide = array("x" => 289, "y" => 500);

echo "<!-- G <=> L -->\n";
echo fpath("liquefaction", coord($gaz, 300 + $da, $r), coord($liquide, 120 - $da, $r))."\n";
echo fpath("vaporisation", coord($gaz, 300 - $da, $r), coord($liquide, 120 + $da, $r), true)."\n";
echo "\n";

echo "<!-- G <=> S -->\n";
echo fpath("sublimation", coord($solide, 60 + $da, $r), coord($gaz, 240 - $da, $r))."\n";
echo fpath("condensation", coord($solide, 60 - $da, $r), coord($gaz, 240 + $da, $r), true)."\n";
echo "\n";

echo "<!-- S <=> L -->\n";
echo fpath("solidification", coord($liquide, 180 + $da, $r), coord($solide, 0 - $da, $r))."\n";
echo fpath("fusion", coord($liquide, 180 - $da, $r), coord($solide, 0 + $da, $r), true)."\n";
echo "\n";


