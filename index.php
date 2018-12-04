<?php
#ini_set('error_reporting', E_ALL);
#ini_set('display_errors', 1);
#error_reporting(E_ALL);

/*    CONFIG    */
##### Name of JSON IoT Device
$thingName = 'Garage Door Opener';

##### Configuration for multiple garage doors, mapping garage door number to GPIO pins for relay and LED
$relayPinMap[1] = array('relayPin' => '5', 'LEDPin' => '24'); // set relay pin and LED pin for garage opener 1
$relayPinMap[2] = array('relayPin' => '4', 'LEDPin' => '25'); // set relay pin and LED pin for garage opener 2

$relayHoldTime = 1000; // ms to hold the relay when triggered

##### LED flashing indicator configuration
$LEDspeed = 10; // how many ms between light turns on/off
$LEDlength = 5; // how many times the light turns on/off before flash sequence is complete
/*  END CONFIG  */

foreach ($relayPinMap as $relay => $relayArray) { // initialize gpio pins
	wiringpi("mode",$relayArray['relayPin'],"out"); // set output for relay
	wiringpi("mode",$relayArray['LEDPin'],"out"); // set output for LED
	wiringpi("write",$relayArray['LEDPin'],0); // turn on LED
}

// standard function for all iot devices
function addtlInfo(){
	foreach (explode("\n",shell_exec("cat /proc/cpuinfo")) as $key => $line) {
		$ary = explode(":",trim($line));
		$cpuinfo[trim($ary[0])] = trim($ary[1]);
	}

	foreach (explode("\n",shell_exec("cat /proc/meminfo")) as $key => $line) {
		$ary = explode(":",trim($line));
		$meminfo[trim($ary[0])] = trim($ary[1]);
	}

	$str   = @file_get_contents('/proc/uptime');
	$num   = floatval($str);
	$secs  = fmod($num, 60); $num = intdiv($num, 60);
	$mins  = $num % 60;      $num = intdiv($num, 60);
	$hours = $num % 24;      $num = intdiv($num, 24);
	$days  = $num;
	$uptime = $days.":".$hours.":".$mins.":".$secs;

	$wifi = preg_match('~ESSID:"(?<SSID>[A-z0-9]*)".*\n.*Frequency:(?<frequency>[0-9].[0-9]*.*Hz).*Access Point: (?<apMac>[0-9A-F:]*).*\n.*Bit Rate=(?<bitRate>[0-9.]* .*\/s).*\n.*\n.*.*\n.*Link Quality=(?<quality>[0-9/]*).*Signal level=(?<signal>[-0-9]* dBm)~', shell_exec('iwconfig wlan0'),$wifiOutput);
	if ($wifi) {
		$wifiOutput["wifiStatus"]  = "available";
	}else{
		$wifiOutput["wifiStatus"] = "unavailable";
	}
	return array('microtime' => microtime(TRUE), 'uptime' => $uptime, 'wifi' => $wifiOutput, 'cpuinfo' => $cpuinfo, 'meminfo' => $meminfo, 'version' => shell_exec("cat /proc/version"), 'ip' => $_SERVER['SERVER_ADDR']);
}

function wiringpi($mode,$pin,$data){ // this runs the shell command
	// Mode: mode, read, write
	// Pin: pin number
	// Data: out, 1/0, etc
	exec("gpio ".$mode." ".$pin." ".$data. " > /dev/null &");
}
function flashyflash($led,$speed = 10, $length = 5){ // flashes the LED indicator
	// speed - amount of time 1 LED is on
	// length - how many intervals before termination;
	wiringpi("write",$led,0); // turn on LEDs
	while (@$i <= $length) { // flash for $length times
		wiringpi("write",$led,1); // led off
		usleep($speed*1000);
		wiringpi("write",$led,0); // led on
		usleep($speed*1000);
		@$i++;
	}
}

function trigger($pin,$ms,$led){ // simulates a button press
	flashyflash($led,$LEDspeed, $LEDlength); // flash LEDs before triggering relay
	if (@!$_GET['debug']) {
		wiringpi("write",$pin,1); // press relay
		usleep($ms*1000); // wait specified amount of time 
		wiringpi("write",$pin,0); // release relay
	}
}

///////// EXECUTED AT RUNTIME
if ($relayPinMap[$_GET['trigger'] ]['relayPin']) {
	trigger($relayPinMap[ $_GET['trigger'] ]['relayPin'],
						$relayHoldTime,
						$relayPinMap[ $_GET['trigger'] ]['LEDPin']); // hold relay
	$response = TRUE;
}

$runmode = $_GET['debug'] ? "Debug" : "Production";

$addtlInfo = $_GET['addtlInfo'] ? addtlInfo():NULL;

$output = array('thingName' => $thingName, 'runmode' => $runmode, 'response' => $response , 'addtlInfo' => $addtlInfo);

echo json_encode($output, JSON_PRETTY_PRINT);

?>