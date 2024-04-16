<?php
//
// This is a script to communicate with Litespeed and retrieve LSCACHE statistics.
// Copyright (c) 2015-2021 - Skylands Networks - www.skylands.com - noc@skylands.com
//

if (!array_key_exists('HTTP_HOST', $_SERVER)) {
    logHealthRequest("Invalid HTTP Host");
    header("HTTP/1.1 500"); // force failure on unknown state
    die("Invalid HTTP Host");
}

$httphost = $_SERVER['HTTP_HOST'];
$url = "https://localhost";

/* Override in the event that this site does not support https (?) */
if (file_exists(".shc-use-http")) { $url = "http://localhost"; }

/* Check for maintenance mode */
if (checkMaintMode() === true) {
    if (array_key_exists('maint_code', $_GET)) {
        logHealthRequest("Setting to maint code: " . $_GET['maint_code']);
        $httpCode=$_GET['maint_code'];
    } else {
        $httpCode = "200";
    }
    logHealthRequest("HEADER RETURN MAINT MODE");
    header("HTTP/1.1 $httpCode MAINTMODE");
    echo "Maintenance Mode Detected<BR>";
    exit(0);
}


/* Nothing sensitive here, just makes for easier troubleshooting */
echo "$url -> $httphost<BR>\n";

/* Setup curl to ignore verify ssl, and to pass the host header so we can talk to localhost and ignore DNS  */
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_NOBODY, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $httphost"));
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Skylands Networks Health Check');
$headers = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
#print_r(curl_error($ch));
if (array_key_exists('lscache', $_GET)) {
    $lmPos = stripos($headers, "x-litespeed-cache:");

    if ($lmPos !== false and $httpCode == 200) {
        logHealthRequest("200 OK");
        header("HTTP/1.1 200 OK");
        echo "Success!<BR>\n<PRE>Output [$httpCode]\n$headers<BR>\n";
    } else {
        logHealthRequest("501 CACHE FAIL");
        header("HTTP/1.1 501 CACHE FAIL");
        echo "<PRE>Error [$httpCode]\n$headers<BR>\n";
    }

    showCacheStats($url);
    exit(0);
}


# Test
logHealthRequest("Real HTTP Code: $httpCode (non 200 returns 500!)");

# test
echo "HTTP/1.1 $httpCode";
if ($httpCode != 200) {
    header("HTTP/1.1 500");
} else {
    header("HTTP/1.1 $httpCode");
}
echo "<BR><A HREF='?lscache=true'>Cache Stats</A><BR>";

###########################################################################

function checkMaintMode() {
    if (checkMagentoMaintMode() === true) { return true; }
    ## Add other checks here for WP, Prestashop, etc.

    return false;
}

###########################################################################

function checkMagentoMaintMode() {
    /* This file needs to be in magento root for this to work */
    $mFiles = array("var/.maintenance.flag", "var/maintenance.flag", ".shc-maint");
    foreach($mFiles as $mf) {
        if (file_exists($mf)) { return true; }
    }

}

###########################################################################

function showCacheStats($url) {
    $ch = curl_init($url . '/__LSCACHE/STATS');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Skylands Networks Health Check');
    $jsonData = curl_exec($ch);
    $data = json_decode($jsonData, true);
    $jsonPretty = json_encode($data, JSON_PRETTY_PRINT);
    echo "Raw Json: $jsonPretty<BR>\n";
    foreach ($data as $k=>$d) {
        $cached = $d['LITEMAGE_OBJS'];
        echo "$k Cached Objects = $cached<BR>\n";
    }
}

###########################################################################

function logHealthRequest($log) {
    if (!$hwnd = fopen("/var/log/skylands-hc.log", "a")) {
        echo "WARNING: Cannot log locally\n";
        return false; // we don't need to break a site because of logging
    }

    $date = new DateTime();
    $date = $date->format("Y-m-d h:i:s");


    if (!fwrite($hwnd, $date . " - " . $log . "\n")) {
        echo "WARNING: Cannot write to log file\n";
        return false;
    }

    if (!fclose($hwnd)) {
        echo "WARNING: Cannot close log file\n";
        return false;
    }

    return true;
}
?>
