<?php

class Graph {
	var $dots = array();

	public static function module($v) {
		return sqrt(pow($v["x"], 2) + pow($v["y"], 2));
	}

	public static function distance($dot1, $dot2) {
		return self::module(array(
			"x" => $dot1["x"] - $dot2["x"],
			"y" => $dot1["y"] - $dot2["y"]
		));
	}

	public function filterByDistance($min) {
		$result = array();

		reset($this->dots);
		$result[] = current($this->dots);

		next($this->dots);
		while($dot = current($this->dots)) {
			if (!next($this->dots)) {
				$result[] = $dot;
				break;
			}
			
			end($result);
			$prev = current($result);
			if (self::distance($prev, $dot) > $min) {
				$result[] = $dot;
			}
		}
		return new Graph($result);
	}

	public function filterByAngle($min) {
		$result = array();
		foreach($this->dots as $key => $dot) {
			if (count($result) == 0) {
				$result[] = $dot;
			}
			else {
				end($result);
				$last = current($result);

				$projection = array(	
					"x" => $dot["x"] + $last["t"]["x"] * (($dot["y"] - $last["y"]) / $last["t"]["y"]),
					"y" => $dot["y"] + $last["t"]["y"] * (($dot["x"] - $last["x"]) / $last["t"]["x"])
				);

				if (self::distance($dot, $projection) > $min) {
					$result[] = $dot;
				}
			}
		}
		return new Graph($result);
	}

	public function trim($x1, $y1, $x2, $y2) {
		foreach ($this->dots as $key => $dot) {
			if ($dot["x"] > $x1 && $dot["x"] < $x2 && $dot["y"] > $y1 && $dot["y"] < $y2)
				break;
			unset($this->dots[$key]);
		}

		$arr = array_reverse($this->dots, true);
		foreach ($arr as $key => $dot) {
			if ($dot["x"] > $x1 && $dot["x"] < $x2 && $dot["y"] > $y1 && $dot["y"] < $y2)
				break;
			unset($this->dots[$key]);
		} 
	}

	public static function normalizeVectorLength($v, $length) {
		$scale = $length / self::module($v);
		return array(
			"x" => $v["x"] * $scale,
			"y" => $v["y"] * $scale
		);
	}

	function clear() {
		$this->dots = array();
	}
	
	function traceFct($fct, $const, $from, $to, $step, $dx) {
		$this->clear();
		for ($x = $from; $x < $to; $x += $step) {
			$dot = $fct($const, $x);

			if (!isset($dot["t"])) {
				$prev = $fct($const, $x - $dx);
				$next = $fct($const, $x + $dx);
				$dot["t"] = array(
					"x" => ($next["x"] - $prev["x"]),
					"y" => ($next["y"] - $prev["y"])
				);
			}

			$this->dots[] = $dot;
		}
	}

	function scale($factorX, $factorY) {
		foreach($this->dots as $key => $dot) {
			$this->dots[$key]["x"] *= $factorX;
			$this->dots[$key]["y"] *= $factorY;
			$this->dots[$key]["t"]["x"] *= $factorX;
			$this->dots[$key]["t"]["y"] *= $factorY;
		}
	}

	function toPath() {
		$result = array();

		reset($this->dots);
		while ($dot1 = current($this->dots)) {
			$dot2 = next($this->dots);
			if (!$dot2)
				break;
			
			$distance = self::distance($dot1, $dot2);

			$t1 = self::normalizeVectorLength($dot1["t"], $distance / 4);
			$v1 = array("x" => $dot1["x"] + $t1["x"], "y" => $dot1["y"] + $t1["y"]);

			$t2 = self::normalizeVectorLength($dot2["t"], $distance / 4);
			$v2 = array("x" => $dot2["x"] - $t2["x"], "y" => $dot2["y"] - $t2["y"]);
			
			$result[] = array("d1" => $dot1, "t1" => $v1, "d2" => $dot2, "t2" => $v2);
		}

		return new Path($result);
	}

	function debug() {
		print_r($this->dots);
	}
		
	function __construct($dots = false) {
		if ($dots) $this->dots = $dots;
	}
}
