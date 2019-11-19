<?php

function ffloat($f, $decimals = 4, $separator = false) {
	$str = sprintf("%0.".$decimals."f", $f);
	if ($separator)	$str = str_replace(".", $separator, $str);
	return $str;
}

function f($x) {
	return 4 * (pow($x, -12) - pow($x, -6));
}

function fPrime($x) {
	return 4 * (-12 * pow($x, -13) + 6 * pow($x, -7));
}

function fRepulsif($x) {
	return 4 * pow($x, -12);
}

function fRepulsifPrime($x) {
	return 4 * -12 * pow($x, -13);
}

function fAttractif($x) {
	return 4 * -1 * pow($x, -6);
}

function fAttractifPrime($x) {
	return 4 * 6 * pow($x, -7);
}

function debug($min, $max, $step) {
	for ($x = $min; $x <= $max; $x += $step) {
		echo ffloat($x)."\t".ffloat(f($x))."\t".ffloat(fPrime($x))."\n";
	}
}


function tangente($c, $length) {
	$x = sqrt(pow($length, 2) / (pow($c, 2) + 1));
	return array(
		"x" => $x,
		"y" => $x * $c
	);
}

function fcoord($point, $scaleX, $scaleY) {
	return ffloat($point["x"] * $scaleX, 2).' '.ffloat($point["y"] * $scaleY, 2);
}

function curve($fct, $derive, $x1, $x2) {
	$y1 = $fct($x1);
	$c1 = $derive($x1);

	$y2 = $fct($x2);
	$c2 = $derive($x2);

	$distance = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
	$t2 = tangente($c2, $distance / 4);
	$t1 = tangente($c1, $distance / 4);
	
	$points = array(
		array("x" => $x1, "y" => $y1),
		array("x" => $x1 + $t1["x"], "y" => $y1 + $t1["y"]),
		array("x" => $x2 - $t2["x"], "y" => $y2 - $t2["y"]),
		array("x" => $x2, "y" => $y2),
	);
	return $points;
}

function fcurve($points, $scaleX = 100, $scaleY = -100) {
	echo '<path d="'.
		'M '.fcoord($points[0], $scaleX, $scaleY).' '
		.'C '.fcoord($points[1], $scaleX, $scaleY).' '.fcoord($points[2], $scaleX, $scaleY).' '.fcoord($points[3], $scaleX, $scaleY)
		.'" class="curve"/>'."\n";

	echo '<path d="'.
		'M '.fcoord($points[0], $scaleX, $scaleY)
		.'L '.fcoord($points[1], $scaleX, $scaleY)
		.'" class="tangente"/>'."\n";

	echo '<path d="'.
		'M '.fcoord($points[3], $scaleX, $scaleY)
		.'L '.fcoord($points[2], $scaleX, $scaleY)
		.'" class="tangente"/>'."\n";
}

function curve_path($fct, $derive, $from, $to, $step, $scaleX = 100, $scaleY =-100) {
	$d = "";
	for ($x = $from; $x <= $to; $x += $step) {
		$points = curve($fct, $derive, $x, $x + $step);
		if ($d == "") {
			$d = "M ".fcoord($points[0], $scaleX, $scaleY);
		}
		$d .= " C ".fcoord($points[1], $scaleX, $scaleY).' '.fcoord($points[2], $scaleX, $scaleY).' '.fcoord($points[3], $scaleX, $scaleY);
	}
	return $d;
}


function scale_path($from, $to, $step, $subdiv, $orientation = "x", $scale = 100) {
	$pattern = $orientation == "x" ? "M %pos 0 v %width" : "M 0 %pos h -%width";
	$search = array("%pos", "%width");
	$d = "";
	for ($x = $from; abs($x) <= abs($to); $x += $step) {
		for ($i = 0; $i < $subdiv; $i++) {
			$pos = ($x + ($i * $step / $subdiv)) * $scale;
			if ($d != "") $d .= " ";
			$replace = array($pos, $i == 0 ? 10 : 5);
			$d .= str_replace($search, $replace, $pattern);
			if ($x == $to)
				break;
		}
	}
	return $d;
}



echo curve_path("f", "fPrime", 0.9, 3, 0.05, 150, -150);
//echo scaleY_path(0, 2, 0.5, 5, -150);
//echo scale_path(0, -1.5, -0.5, 5, "y", -150)."\n";
//echo scale_path(0, 3, 0.5, 5, "x", 150)."\n";
//echo curve_path("fAttractif", "fAttractifPrime", 0.95, 3, 0.05, 150, -150);

//arc(1, 1.12246);
