<?php

define("ID",""); // Username or Empty to disable
define("PASSWORD",""); // Password or Empty to disable

// script: Garni Weather Station server PHP script
// version: 1.0 (MIT licence)
// author: Petr Fismol (buffy.cz)
$export = "{";
foreach ($_GET as $key => $value) {
    $export .=  '"' . strtolower($key) . '":"' . $value . '",';
}
$export .= '"time":"'.date("d.m.Y H:i:s").'",';
$export .= '"model":"1025 ARCUS",';
$export .= '"manufacturer":"Garni"}';
$jsonData = json_decode($export);

//echo $export; // comment this line for switch off browser data output

// Check ID and Password
if(!empty(PASSWORD) AND !empty(ID)){
    if(PASSWORD !== $jsonData->password AND ID !== $jsonData->id){
        exit ("Wrong ID or Password");
    }
}
// Remove credentials from meteo
unset($jsonData->id,$jsonData->password);

$jsonData->windspeedkmh = mph2kmph($jsonData->windspeedmph);
$jsonData->windgustkmh = mph2kmph($jsonData->windgustmph);
$jsonData->tempc = f2c($jsonData->tempf);
$jsonData->indoortempc = f2c($jsonData->indoortempf);
$jsonData->dewptc = f2c($jsonData->dewptf);
$jsonData->barohpa = in2hpa($jsonData->baromin);

// Sun
$jsonData->lux = round($jsonData->solarradiation *  12.6,0);
$jsonData->uvriskinmin = uvRisk($jsonData->uv);

// Wind
$windDetail = windPower($jsonData->windspeedmph);
$jsonData->windbeaurtscale = $windDetail["beaufortNum"];
$jsonData->windtype = $windDetail["name"];
$jsonData->windtxtdir = windDirection($jsonData->winddir);

// Rain
$jsonData->rainmm = in2mm($jsonData->rainin);
$jsonData->dailyrainmm = in2mm($jsonData->dailyrainin);

// Final write
$file = fopen(__DIR__."/data/garni.json", "w+");
fwrite($file, json_encode($jsonData));
fclose($file);

/* Meteo Functions */
/**
 * Wind power by Beaufort scale
 * @param $speedInMph
 * @return array
 */
function windPower($speedInMph)
{
    $windSpeed = (float) $speedInMph;
    switch ($windSpeed){
        case $windSpeed < 1:
            $beaufortNum = 0;
            $windName = "calm";
            break;
        case $windSpeed >= 1 && $windSpeed <= 3:
            $beaufortNum = 1;
            $windName = "lightAir";
            break;
        case $windSpeed >= 4 && $windSpeed <= 7:
            $beaufortNum = 2;
            $windName = "lightBreeze";
            break;
        case $windSpeed >= 8 && $windSpeed <= 12:
            $beaufortNum = 3;
            $windName = "gentleBreeze";
            break;
        case $windSpeed >= 13 && $windSpeed <= 18:
            $beaufortNum = 4;
            $windName = "moderateBreeze";
            break;
        case $windSpeed >= 19 && $windSpeed <= 24:
            $beaufortNum = 5;
            $windName = "freshBreeze";
            break;
        case $windSpeed >= 25 && $windSpeed <= 31:
            $beaufortNum = 6;
            $windName = "strongBreeze";
            break;
        case $windSpeed >= 32 && $windSpeed <= 38:
            $beaufortNum = 7;
            $windName = "highWindModGale";
            break;
        case $windSpeed >= 39 && $windSpeed <= 46:
            $beaufortNum = 8;
            $windName = "galeFreshGale";
            break;
        case $windSpeed >= 47 && $windSpeed <= 54:
            $beaufortNum = 9;
            $windName = "stronghGale";
            break;
        case $windSpeed >= 55 && $windSpeed <= 63:
            $beaufortNum = 10;
            $windName = "storm";
            break;
        case $windSpeed >= 64 && $windSpeed <= 72:
            $beaufortNum = 11;
            $windName = "violentStorm";
            break;
        case $windSpeed >= 73:
            $beaufortNum = 12;
            $windName = "hurricane";
            break;
        default:
            $beaufortNum = "N/A";
            $windName = "error";
            break;
    }
    return [
        "beaufortNum" => $beaufortNum,
        "name" => $windName
    ];
}

/**
 * Wind direction
 * @param $degrees
 * @return string
 */
function windDirection($degrees)
{
    $dir = (float) $degrees;
    $textDir = "";
    if($dir > 337.5 && $dir <= 360){
        $textDir="N";
    }
    if($dir > 0 && $dir <= 22.5){
        $textDir="N";
    }
    if($dir > 22.5 && $dir <= 67.5){
        $textDir="NE";
    }
    if($dir > 67.5 && $dir <= 112.5){
        $textDir="E";
    }
    if($dir > 112.5 && $dir <= 157.5){
        $textDir="SE";
    }
    if($dir > 157.5 && $dir <= 202.5){
        $textDir="S";
    }
    if($dir > 202.5 && $dir <= 247.5){
        $textDir="EW";
    }
    if($dir > 247.5 && $dir <= 292.5){
        $textDir="W";
    }
    if($dir > 292.5 && $dir <= 337.5){
        $textDir="NW";
    }
    return $textDir;
}

/**
 * Limit time on SUN
 * @param $uv
 * @return int 0 - is safety
 */
function uvRisk($uvInput)
{
    $riskTimeMin = 0;
    switch ($uvInput){
        case $uvInput >= 0 && $uvInput <= 2:
            $riskTimeMin = 0;
            break;
        case $uvInput >= 3 && $uvInput <= 5:
            $riskTimeMin = 45;
            break;
        case $uvInput >= 6 && $uvInput <= 7:
            $riskTimeMin = 30;
            break;
        case $uvInput >= 8 && $uvInput <= 10:
            $riskTimeMin = 15;
            break;
        case $uvInput >= 11:
            $riskTimeMin = 10;
            break;
    }
    return $riskTimeMin;
}

# utility functions for unit conversion
function f2c($f) {
    return round(5 / 9 * ($f - 32),2);
}
function in2hpa($in) {
    return round($in * 33.86389,2);
}
function p2dec($p) {
    return round($p / 100,2);
}
function mph2kmph($mph) {
    return round($mph * 1.609344,2);
}
function in2mm($in) {
    return round($in * 25.4,2);
}
