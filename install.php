<?php
/*
Postfix SPF for ISPConfig (or not)
Copyright (c) 2015, Alexandre Alouit <alexandre.alouit@gmail.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

$backup_dir = "/var/backup/";
$backup_file = date("Ymdhis")."-ISPConfig-postfix-spf.tar.gz";
$listing = array(
0 => array(
"source" => "/etc/postfix/main.cf",
"destination" => "/etc/postfix/main.cf",
"owners" => "root:root", "permissions" => "644"),
1 => array(
"source" => "/etc/postfix/master.cf",
"destination" => "/etc/postfix/master.cf",
"owners" => "root:root", "permissions" => "644")
);

// check we are root
if(exec("whoami") != "root") {
	echo "Please run this script as root or using sudo";
	exit;
}

// backup postfix conf
if(!file_exists($backup_dir)) {
	echo "Backup directory not found.\n";
	mkdir($backup_dir, 0700);
}

if(!file_exists($backup_dir)) {
	echo "Create it, and relaunch me!\n";
	exit;
}

echo "Create backup on " . $backup_dir . " directory\n";
$filelist = "";

foreach($listing as $key => $value) {
	$filelist = $filelist . " " . $value["destination"];
}

exec("/bin/tar -czf " . $backup_dir  . $backup_file . " " . $filelist);

if(!file_exists($backup_dir . $backup_file)) {
	echo "There was a problem with the backup file.\n";
	exit;
}

echo "Backup finished\n";

echo "Reload package and install postfix-policyd-spf-python\n";
exec("apt-get update && apt-get install -y postfix-policyd-spf-python");

echo "Configure postfix\n";
exec("postconf -e 'policy-spf_time_limit = 3600s'");
exec("postconf -e \"smtpd_recipient_restrictions=$(postconf -d smtpd_recipient_restrictions | cut -c32-), check_policy_service unix:private/policy-spf\"");
exec("echo -e 'policy-spf  unix  -       n       n       -       -       spawn\n  user=nobody argv=/usr/bin/policyd-spf' >> /etc/postfix/master.cf");

// reload postfix
echo "Install completed, postfix reloading..\n";
exec("postfix reload");

echo "Done my job. Enjoy!\n";
exit;
?>
