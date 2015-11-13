#!/usr/bin/php 
<?PHP

$pgmName = 'pdb_monitorsyslog';
$pdbBaseDir = '/opt/phpDashButton/';
$pdbConfig = $pdbBaseDir . 'config/pdb_config.ini';

$pdbConfig = parse_ini_file($pdbConfig, true);

$pdbSyslog = '/var/log/syslog';
$pdbLockFileName = $pdbBaseDir . "config/$pgmName.lock";
$pdbPidFilename = $pdbBaseDir . "config/$pgmName.pid";

$fpLock = fopen("$pdbLockFileName", 'w+');
/* Activate the LOCK_NB option on an LOCK_EX operation */
if(!flock($fpLock, LOCK_EX | LOCK_NB)) {
//    echo 'Unable to obtain lock';
    exit();
}
//$fhEvent = fopen("$homeDir/$EventFileName", 'w');
//fclose($fhEvent);

writePidInfo("$pdbPidFilename");


//$fp = popen("tail -f {$syslog} -n1 2>&1", 'r');


for(;;){
	if(!is_readable($pdbSyslog)){
		echo("Cannot read $pdbSyslog\n");
		fclose($fpLock); 
		exit();
	}
	$fp = popen("tail -f $pdbSyslog -n1 2>&1", 'r');
	while(is_resource($fp)){
		$line = fgets($fp);
		
//	Nov 10 07:43:46 raspberrypi hostapd: wlan0: STA 10:ae:60:4f:44:83 IEEE 802.11: authenticated
		$Interface = "/hostapd: wlan0:/";
		$authenticated = "/IEEE 802.11: authenticated/";
		if(preg_match($Interface, $line) AND preg_match($authenticated, $line)){
			$items = explode(' ', $line);
			
			echo($items[7] . "\n");
			echo $pdbConfig['MAC_' . $items[7]]['ButtonName'] . "\n";
			
			if(isset($pdbConfig['MAC_' . $items[7]])){
				if($pdbConfig['MAC_' . $items[7]]['Active'] == 1){
					
					
				}
			}
		}
		
		
		
		
		
	}
	
}


fclose($fpLock);

//********************************************************************************
function writePidInfo($fileName){
	
	$myPid = getmypid();
	$fpPid = popen("cat /proc/{$myPid}/cmdline", 'r');
	$pidName = fgets($fpPid);
	pclose($fpPid);
	
	$fpPid = fopen("$fileName", 'w');
	fputs($fpPid, $myPid .'|'. $pidName);
	fclose($fpPid);
}