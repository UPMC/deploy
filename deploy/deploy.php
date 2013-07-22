<?php

//------------------------------------------
// Settings
//------------------------------------------

define('__DOMAIN_CONTROLLER', 'PPI');
define('__DOMAIN_ADMINISTRATOR', 'admin');
define('__DOMAIN_PASSWORD', 'password');


//------------------------------------------
// Functions library
//------------------------------------------

function setProgress($progress) {
  $fp = fopen($_SERVER['SystemRoot'].'/AdminTools/progress', 'w');
  fwrite($fp, (int)$progress);
  fclose($fp);
}

function getProgress() {
  return (int)@file_get_contents($_SERVER['SystemRoot'].'/AdminTools/progress');
}

function getSettings() {

  echo "Getting system settings...";
  $try = 0;
  
  do {
    $addr = shell_exec('ipconfig');
	preg_match_all('#134\.157\.107\.[0-9]+#', $addr, $addr);
	
	foreach ($addr[0] as $row) {
	
	  if ($row != '134.157.107.254') {
	  
	    $row = shell_exec('nslookup '.$row);
		
		if (preg_match('#(s[0-9]{3})-[0-9]{2}#i', $row, $row) > 0) {
	      $settings = $row;
		}
	  }
	}
	
    sleep(10*$try);
    $try++;
  }
  while (!isset($settings));
  
  echo " Done.\n";
  return $settings;
}

function computerIntegrate() {

  if ($_SERVER["LOGONSERVER"] != '\\'.__DOMAIN_CONTROLLER) {
    echo "Integrate to domain controller...";
    $i = 0;
    
    do {
	  echo ' '.($i+1);
      exec('net use * /delete /yes', $a, $return);
      exec('netdom join '.$_SERVER['COMPUTERNAME'].' /Domain:'.__DOMAIN_CONTROLLER.' /UserD:'.__DOMAIN_CONTROLLER.'\\'.__DOMAIN_ADMINISTRATOR.' /PasswordD:'.__DOMAIN_PASSWORD, $a, $return);
	  sleep(10*$i);
      $i++;
    }
    while ($return != 0);
    
    echo " Done.\n";
  }
}

function computerRename($settings) {

  if ($settings[0] != $_SERVER['COMPUTERNAME']) {
    echo "Setting the new hostname...";
    $i = 0;
    
    do {
      echo ' '.($i+1);
      exec('netdom renamecomputer '.$_SERVER['COMPUTERNAME'].' /NewName:'.$settings[0].' /Force', $a, $return);
      sleep(10*$i);
      $i++;
    }
    while ($return != 0);
    
    echo " Done.\n";
  }
}

function computerActivate() {
  echo "Activating Windows software...";
  shell_exec('slmgr /ato');
  echo " Done.\n";
}

function computerTime() {
  echo "Updating system time...";
  $i = 0;
  
  while (date('Y') < 2012) {
    echo ' '.($i+1);
    shell_exec('net stop w32time');
    shell_exec('net start w32time');
    sleep(10*$i);
    $i++;
  }
  
  echo " Done.\n";
}

function computerCleanup() {
  echo "Updating registry...";
  shell_exec('C:\Windows\Sysnative\reg.exe delete "HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Run" /v Deploy /f');
  shell_exec('C:\Windows\Sysnative\reg.exe delete "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon" /v AutoAdminLogon /f');
  shell_exec('C:\Windows\Sysnative\reg.exe delete "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon" /v DefaultDomainName /f');
  shell_exec('C:\Windows\Sysnative\reg.exe delete "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon" /v DefaultUserName /f');
  echo " Done.\n";
}

function computerRestart() {
  echo "Restarting the computer... in 3,";
  sleep(1); echo " 2,"; sleep(1); echo " 1,"; sleep(1); echo " 0\n";
  shell_exec('shutdown /r /f /t 0');
}

function computerShutdown() {
  echo "Shutting down the computer... in 3,";
  sleep(1); echo " 2,"; sleep(1); echo " 1,"; sleep(1); echo " 0\n";
  shell_exec('shutdown /s /f /t 0');
}

//------------------------------------------
// Main program
//------------------------------------------

$progress = getProgress();
$settings = getSettings();

if ($progress == 1) {
  computerTime();
  computerActivate();
  setProgress(2);
  computerRestart();
}
else if ($progress == 2) {
  computerRename($settings);
  setProgress(3);
  computerRestart();
}
else if ($progress == 3) {
  computerTime();
  computerIntegrate();
  computerCleanup();
  setProgress(4);
  computerShutdown();
}
