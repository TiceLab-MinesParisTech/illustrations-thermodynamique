<?php

class PengRobinson {
	var $w;
	var $Tc;
	var $Pc;
	var $T;
	var $a;
	var $b;
	
	private function init() {
		$alpha = $this->alpha();
		$this->a = 0.45724 * pow(R * $this->Tc, 2) * $alpha / $this->Pc;
		$this->b = 0.07780 * R * $this->Tc / $this->Pc;
	}

	private function alpha() {
		$k = ($this->w < 0.491) 
			? 0.37464 + 1.54226 * $this->w - 0.26992 * pow($this->w, 2)
			: 0.379642 + 1.48503 * $this->w - 0.164423 * pow($this->w, 2) + 0.016666 * pow($this->w, 3);
		return pow(1 + $k * (1 - sqrt($this->T / $this->Tc)), 2);
	}

	public function polynome($A, $B) {
		return array(
			3 => 1,
			2 => $B - 1,
			1 => $A - 3 * pow($B, 2) - 2 * $B,
			0 => -1 * ($A * $B - pow($B, 2) - pow($B, 3))
		);
	}

	public function polynomeParameters($P) {
		return array(
			"A" => ($this->a * $P) / pow(R * $this->T, 2),
			"B" => ($this->b * $P) / (R * $this->T)
		);
	}
	
	public function VLEquilibrium($threshold = 0.00001) {
		$P = $this->Pc * exp(5.37 * (1 + $this->w) * (1 - $this->Tc / $this->T));

		$fliq = 2;
		$fvap = 1;
		while (abs($fliq / $fvap - 1) >= $threshold) {
			$arr = $this->polynomeParameters($P);
			$A = $arr["A"];
			$B = $arr["B"];

			$polynome = $this->polynome($A, $B);
			$Z = self::roots($polynome);

			if (count($Z) <= 1)
				return false;

			$Zliq = min($Z);
			$Zvap = max($Z);

			if ($Zliq <= 0)
				return false;

			$fliq = $P * exp(($Zliq - 1) - log($Zliq - $B) - $A * log(($Zliq + (1 + sqrt(2)) * $B) / ($Zliq + (1 - sqrt(2)) * $B)) / (2 * sqrt(2) * $B));
			$fvap = $P * exp(($Zvap - 1) - log($Zvap - $B) - $A * log(($Zvap + (1 + sqrt(2)) * $B) / ($Zvap + (1 - sqrt(2)) * $B)) / (2 * sqrt(2) * $B));
			$P = $P * $fliq / $fvap;
		}

		return array(
			"P" => $P,
			"liq" => array(
				"Z" => $Zliq,
				"Vm" => $this->Vm($Zliq, $P)
			),
			"vap" => array(
				"Z" => $Zvap,
				"Vm" => $this->Vm($Zvap, $P)
			)
		);
	}

	public function P($Vm) { 
		return R * $this->T / ($Vm - $this->b) - $this->a / (pow($Vm, 2) + 2 * $this->b * $Vm - pow($this->b, 2));
	}

	public function Vm($Z, $P) {
		return $Z * R * $this->T / $P;
	}
	
	public function Z($P) { 
		$arr = $this->polynomeParameters($P);
		$polynome = $this->polynome($arr["A"], $arr["B"]);
		return self::roots($polynome);
	}

	public static function roots($polynome) {
		if ($polynome[3] != 1) {
			return false;
		}
		
		$p = $polynome[1] - pow($polynome[2], 2) / 3;
		$q = $polynome[0] - ($polynome[1] * $polynome[2] / 3) + (2 * pow($polynome[2], 3) / 27);
		$r = pow($p / 3, 3) + pow($q / 2, 2);
		
		//--Case $r > 0 => 1 solution
		if ($r > 0) {
			return array(
				pow($q / -2 + sqrt($r), 1 / 3) + pow($q / -2 - sqrt($r), 1/3) - $polynome[2] / 3
			);
		}

		//--Case $r <= 0 => 3 solutions
		$theta = acos($q / -2 * sqrt(-27 / pow($p, 3)));
		$result = array();
		foreach (array(0, 1, 2) as $k) {
			$result[] = 2 * sqrt($p / -3) * cos(($theta + 2 * $k * PI) / 3) - $polynome[2] / 3;
		}
		return $result;
	}

	public function __construct($w, $Tc, $Pc, $T) {
		$this->w = $w;
		$this->Tc = $Tc;
		$this->Pc = $Pc;
		$this->T = $T;
		$this->init();		
	}
}

