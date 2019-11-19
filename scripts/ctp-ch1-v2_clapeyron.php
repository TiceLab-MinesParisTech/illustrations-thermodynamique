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
	$graph = $graph->filterByDistance(5);
	return $graph->toPath()->export();
}

function exportGraphToSVG($svg, $graph, $id, $setting) {
	$d = exportGraph($graph, $setting);

	$element = $svg->getElementById($id);
	if (!$element) 
		return false;
		
	$element->setAttribute("d", $d);
	return true;
}

function VmC($const) {
	$pr = new PengRobinson($const["w"], $const["Tc"], $const["Pc"], $const["Tc"]);
	$arr = $pr->Z($const["Pc"]);
	return (count($arr) == 1) ? $pr->Vm($arr[0], $const["Pc"]) : false;
}
$H2O = array(
	"Tc" => 647.3,
	"Pc" => 22.12e6, //Pa
	"w" => 0.344
);

$setting = array(
	"from" =>  0.01 / pow(10, 3),
	"to" => 0.8 / pow(10, 3),
	"step" => 0.00000001,
	"scaleX" => pow(10, 3) * 1500,
	"scaleY" => 1.5 / 100000, //1.5px per bar
	"x1" => 0,
	"y1" => 0,
	"x2" => 0.8 * pow(10, 3),
	"y2" => 540
);

$graphs = array(
	"isotherme700" => graphIsotherme($H2O, $setting["from"], $setting["to"], $setting["step"], 700),
	"isotherme647" => graphIsotherme($H2O, $setting["from"], $setting["to"], $setting["step"], 647),
	"isotherme600" => graphIsotherme($H2O, $setting["from"], $setting["to"], $setting["step"], 600),
	"isotherme580" => graphIsotherme($H2O, $setting["from"], $setting["to"], $setting["step"], 580),
	"isotherme560" => graphIsotherme($H2O, $setting["from"], $setting["to"], $setting["step"], 560),
	"courbe-rose" => graphCourbeRose($H2O, celciusToKelvin(0.01), 647.1, 0.1),
	"courbe-bulle" => graphCourbeBulle($H2O, celciusToKelvin(0.01), 647.1, 0.1)
);
	

$filename = "../ctp-ch1-v2_clapeyron-H2O.svg"; 

$svg = new Svg();
$svg->load($filename);
foreach($graphs as $id => $graph) {
	exportGraphToSVG($svg, $graph, $id, $setting);
}

$element = $svg->getElementById("point-critique");
if ($element) {
	$element->setAttribute("cx", Path::ffloat(VmC($H2O) * $setting["scaleX"]));
	$element->setAttribute("cy", Path::ffloat($H2O["Pc"] * $setting["scaleY"] * -1));
}

$element = $svg->getElementById("axis-graduation-x");
if ($element) {
	$element->setAttribute("d", Path::scale(0, 0.6, 0.1, 2, "x", $setting["scaleX"] / pow(10, 3)));
}

$pr = new PengRobinson($H2O["w"], $H2O["Tc"], $H2O["Pc"], 580);
$arr = $pr->VLEquilibrium();
$element = $svg->getElementById("point-bulle");
if ($element) {
	$element->setAttribute("cx", Path::ffloat($arr["liq"]["Vm"] * $setting["scaleX"]));
	$element->setAttribute("cy", Path::ffloat($arr["P"] * $setting["scaleY"] * -1));
}

$element = $svg->getElementById("point-rose");
if ($element) {
	$element->setAttribute("cx", Path::ffloat($arr["vap"]["Vm"] * $setting["scaleX"]));
	$element->setAttribute("cy", Path::ffloat($arr["P"] * $setting["scaleY"] * -1));
}


$svg->save($filename);

