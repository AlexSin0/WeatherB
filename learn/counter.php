<?php

$show = false;

session_start();

if (!isset($_SESSION['username'])) {

    $_SESSION['username'] = 'counted';

    //Get data
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $uri = $_SERVER['REQUEST_URI'];
    $user = $_SERVER['PHP_AUTH_USER'] ?? 'Hi';
    $ip = $_SERVER['REMOTE_ADDR'];
    $ref = $_SERVER['HTTP_REFERER'] ?? 'Hi';
    $date = date('r');

    $str = "$date - IP: $ip | Agent: $agent | URL: $uri | Referrer: $ref | Username: $user \n";

    //Write to file
    $fp = fopen("logs.txt", "a");
    fputs($fp, $str);
    fclose($fp);
}

//session_unset();

$date = date('Y-m-d', time());

$conn = new mysqli('localhost', 'root', null, 'counter');

$conn->query("DELETE FROM `list_ip` WHERE (`date`!=\"$date\")");
$conn->query("UPDATE `statistics` SET `hosts`=0, `hits`=0 WHERE(`date`!=\"$date\")");
$conn->query("UPDATE `statistics` SET `date`=\"$date\"");

$ip = $_SERVER['REMOTE_ADDR'];
$result = $conn->query("SELECT * FROM `list_ip` WHERE (`ip`=\"$ip\") ");
$row = $result->num_rows;

if ($show) {
    echo '<br><u>statistics:</u> <br><br>';
}
if ($row > 0) {
    $result = $conn->query("SELECT `hosts`, `hits`, `total` FROM `statistics`");
    $row = $result->fetch_array();
    $new_hits = ++$row['hits'];
    $new_total = ++$row['total'];
    $conn->query("UPDATE `statistics` SET `hits`=\"$new_hits\", `total`=\"$new_total\"");
    if ($show) {
        output_img($row['hosts'], $new_hits, $new_total);
    }
} else {
    $conn->query("INSERT INTO `list_ip` (`ip`, `date`) VALUES(\"$ip\", \"$date\")") or die($conn->error);
    $result = $conn->query("SELECT `hosts`, `hits`, `total` FROM `statistics`");
    $row = $result->fetch_array();
    $new_hosts = ++$row['hosts'];
    $new_hits = ++$row['hits'];
    $new_total = ++$row['total'];
    $conn->query("UPDATE `statistics` SET `hosts`=\"$new_hosts\", `hits`=\"$new_hits\", `total`=\"$new_total\"");
    if ($show) {
        output_img($new_hosts, $new_hits, $new_total);
    }
}

$res = $conn->query("SELECT `ip`, `count` FROM `ip_list2` WHERE(`ip`=\"$ip\")");
$row = $res->num_rows;

if ($show) {
    echo '<br><u>ip_list2:</u> <br><br>';
}
if ($row == 0) {
    $conn->query("INSERT INTO `ip_list2` (`ip`, `count`) VALUES(\"$ip\", 1)");
    if ($show) {
        $res = $conn->query("SELECT `ip`, `count` FROM `ip_list2`");
        while ($row = $res->fetch_array()) {
            echo 'IP: ' . $row['ip'] . ' Count: ' . $row['count'] . '\n';
        }
    }
} else {
    $res = $conn->query("SELECT `ip`, `count` FROM `ip_list2` WHERE(`ip`=\"$ip\")");
    $row = $res->fetch_array();
    $count = ++$row['count'];
    $conn->query("UPDATE `ip_list2` SET `count`=\"$count\" WHERE(`ip`=\"$ip\") ");
    if ($show) {
        $res = $conn->query("SELECT `ip`, `count` FROM `ip_list2`");
        while ($row = $res->fetch_array()) {
            echo 'IP: ' . $row['ip'] . ' Count: ' . $row['count'] . '<br>';
        }
    }
}

$conn->close();

function output_img($hosts, $hits, $total)
{
    echo 'Hosts: ' . $hosts . '<br>';
    echo 'Hits: ' . $hits . '<br>';
    echo 'Total: ' . $total . '<br>';
}
?>