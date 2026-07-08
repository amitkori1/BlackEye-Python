<?php
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $file = 'ip_bench.txt';
    $victim = "IP: ";
    $ipaddress = "127.0.0.1\r\n";
    $useragent = " User-Agent: ";
    $browser = "Mozilla/5.0";

    $fp = fopen($file, 'a');
    fwrite($fp, $victim);
    fwrite($fp, $ipaddress);
    fwrite($fp, $useragent);
    fwrite($fp, $browser);
    fclose($fp);
}
$end = microtime(true);
echo "Current: " . ($end - $start) . " seconds\n";
unlink('ip_bench.txt');

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $file = 'ip_bench.txt';
    $victim = "IP: ";
    $ipaddress = "127.0.0.1\r\n";
    $useragent = " User-Agent: ";
    $browser = "Mozilla/5.0";

    $fp = fopen($file, 'a');
    fwrite($fp, $victim . $ipaddress . $useragent . $browser);
    fclose($fp);
}
$end = microtime(true);
echo "Optimized (fwrite): " . ($end - $start) . " seconds\n";
unlink('ip_bench.txt');

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $file = 'ip_bench.txt';
    $victim = "IP: ";
    $ipaddress = "127.0.0.1\r\n";
    $useragent = " User-Agent: ";
    $browser = "Mozilla/5.0";

    file_put_contents($file, $victim . $ipaddress . $useragent . $browser, FILE_APPEND);
}
$end = microtime(true);
echo "Optimized (file_put_contents): " . ($end - $start) . " seconds\n";
unlink('ip_bench.txt');
