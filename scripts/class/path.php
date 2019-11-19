<?php
class Path {
	var $items;

	public static function ffloat($f, $decimals = 1) {
		return sprintf("%0.".$decimals."f", $f);
	}

	public static function fcoord($dot, $scaleX = 1, $scaleY = -1) {
		return self::ffloat($dot["x"] * $scaleX).','.self::ffloat($dot["y"] * $scaleY);
	}
	
	function export() {
//		print_r($this->items);
		$d = false;
		foreach($this->items as $item) {
			if (!$d) {
				$d = "M ".self::fcoord($item["d1"]);
			}
			else {
				$d .= " C ".self::fcoord($item["t1"])." ".self::fcoord($item["t2"])." ".self::fcoord($item["d2"]);
			}
		}
		return $d;
	}

	public static function scale($from, $to, $step, $subdiv, $orientation = "x", $scale = 100) {
		$pattern = $orientation == "x" ? "M %pos,0 v %width" : "M 0,%pos h -%width";
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
	
	function __construct($items = false) {
		if ($items) $this->items = $items;
	}
}
