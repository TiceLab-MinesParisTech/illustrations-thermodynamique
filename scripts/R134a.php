<?php

include("class/graph.php");
include("class/path.php");
include("class/peng_robinson.php");
include("class/const.php");
include("class/svg.php");

function celciusToKelvin($T) {
	return 273.15 + $T;
}

function Vm2P($const, $Vm) {
	if ($Vm > $const["enveloppe"]["liq"]["Vm"] && $Vm < $const["enveloppe"]["vap"]["Vm"]) {
		return array(
			"x" => $Vm,
			"y" => $const["enveloppe"]["P"],
			"t" => array(
				"x" => 1,
				"y" => 0
			)
		);
	}
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $const["T"]);
	return array(
		"x" => $Vm,
		"y" => $pr->P($Vm)
	);
}

function graphIsotherme($const, $from, $to, $step, $T) {
	$graph = new Graph();
	$const["T"] = $T;
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $const["T"]);

	$const["enveloppe"] = $pr->VLEquilibrium();

	$graph->traceFct("Vm2P", $const, $from, $to, $step, $step / 100);
	return $graph;
}

function courbeBulle($const, $T) {
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $T);
	$arr = $pr->VLEquilibrium();
	return array(
		"x" => $arr["liq"]["Vm"],
		"y" => $arr["P"]
	);
}

function graphCourbeBulle($const, $from, $to, $step) {
	$graph = new Graph();
	$graph->traceFct("courbeBulle", $const, $from, $to, $step, $step / 100);
	return $graph;
}

function courbeRose($const, $T) {
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $T);
	$arr = $pr->VLEquilibrium();
	return array(
		"x" => $arr["vap"]["Vm"],
		"y" => $arr["P"]
	);
}

function graphCourbeRose($const, $from, $to, $step) {
	$graph = new Graph();
	$graph->traceFct("courbeRose", $const, $from, $to, $step, $step / 100);
	return $graph;
};

function exportGraph($graph, $setting) {
	$graph->scale($setting["scaleX"], $setting["scaleY"]);
	$graph->trim($setting["x1"], $setting["y1"], $setting["x2"], $setting["y2"]);
	$graph = $graph->filterByDistance(20);
	return $graph->toPath()->export();
}

function VmC($const) {
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $const["Tc"]);
	$arr = $pr->Z($const["Pc"]);
	return (count($arr) == 1) ? $pr->Vm($arr[0], $const["Pc"]) : false;
}

function exportPath($d, $class, $id = false) {
	return "<path class=\"$class\" ".($id ? "id=\"$id\"" : "")." d=\"".$d."\"/>\n";
}

$R134a = array(
	"Tc" => 374.21,
	"Pc" => 4059280.0, //Pa
	"w" => 0.32684
);

$VmC = VmC($R134a);

$setting = array(
	"from" =>  $VmC * 0.7,
	"to" => $VmC * 1.4,
	"step" => 0.00000001,
	"scaleX" => pow(10, 3) * 5000,
	"scaleY" => 200 / 100000, //200px per bar
	"x1" => 1231 - 300,
	"y1" => 8118 - 200,
	"x2" => 1231 + 300,
	"y2" => 8118 + 200
);

//die($VmC * $setting["scaleX"].", ".$R134a["Pc"] * $setting["scaleY"]);

/*
$Coordc = array(
	"x" => Path::ffloat(VmC($R134a) * $setting["scaleX"]),
	"y" => Path::ffloat($R134a["Pc"] * $setting["scaleY"] * -1)
);

*/

for ($t = 97, $i = 0; $t <= 104; $t += 0.1, $i++) {
	$graph = graphIsotherme($R134a, $setting["from"], $setting["to"], $setting["step"], celciusToKelvin($t));
	$d = exportGraph($graph, $setting);
	echo exportPath($d, "isotherme ref".($i % 10 == 0 ? " unit" : ""), str_replace(".", "-", sprintf("isotherme%0.2f-celcius", $t)));
}
/*
for ($t = 371; $t <= 377; $t += 0.01) {
	$graph = graphIsotherme($R134a, $setting["from"], $setting["to"], $setting["step"], $t);
	$d = exportGraph($graph, $setting);
	echo exportPath($d, "isotherme", str_replace(".", "-", sprintf("isotherme%0.2f", $t)));	
}
*/

/*$graphs = array(
	"courbe-rose" => graphCourbeRose($R134a, 372, 374.208, 0.001),
	"courbe-bulle" => graphCourbeBulle($R134a, 372, 374.208, 0.001)
);

foreach($graphs as $graph) {
	$d = exportGraph($graph, $setting);
	echo exportPath($d);	
}
*/
