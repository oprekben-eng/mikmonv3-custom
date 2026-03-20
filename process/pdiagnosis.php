<?php
$session = isset($_GET['session']) ? $_GET['session'] : '';

include('../include/config.php');
include('../include/readcfg.php'); // Ini akan memicu session_start() bawaan Mikhmon
include_once('../lib/routeros_api.class.php');

if (empty($session) || !isset($_SESSION["mikhmon"])) {
    die("Akses ditolak.");
}

$type = isset($_POST['type']) ? $_POST['type'] : '';
$target = isset($_POST['target']) ? trim($_POST['target']) : '';

if(empty($target)) {
    die("Target kosong.");
}

$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($iphost, $userhost, decrypt($passwdhost))) {
    
    if ($type == 'ping') {
        echo "[admin@MikroTik] > ping $target count=5\n\n";
        
        $results = $API->comm("/ping", array(
            "address" => $target,
            "count" => "5"
        ));
        
        if (isset($results['!trap'])) {
             echo "Error: " . $results['!trap'][0]['message'] . "\n";
        } else {
            foreach($results as $res) {
                if(isset($res['status'])) {
                    echo "Status: " . $res['status'] . "\n";
                } else if(isset($res['host'])) {
                    $ttl = isset($res['ttl']) ? " ttl=".$res['ttl'] : "";
                    $time = isset($res['time']) ? " time=".$res['time'] : "";
                    echo "host=".$res['host'] ." size=".$res['size'] . $ttl . $time . "\n";
                }
            }
        }
    } 
    elseif ($type == 'traceroute') {
        echo "[admin@MikroTik] > traceroute $target\n\n";
        
        // Count=1 on traceroute sends 1 packet per hop
        $results = $API->comm("/tool/traceroute", array(
            "address" => $target,
            "count" => "1"
        ));
        
        if (isset($results['!trap'])) {
             echo "Error: " . $results['!trap'][0]['message'] . "\n";
        } else {
            echo str_pad("HOP", 5) . str_pad("ADDRESS", 20) . str_pad("LOSS", 8) . str_pad("SENT", 8) . str_pad("STATUS/TIME", 20) . "\n";
            echo "------------------------------------------------------------\n";
            foreach($results as $res) {
                $hop = isset($res['hop']) ? $res['hop'] : "?";
                $addr = isset($res['address']) ? $res['address'] : "";
                $loss = isset($res['loss']) ? $res['loss']."%" : "";
                $sent = isset($res['sent']) ? $res['sent'] : "";
                $status = isset($res['status']) ? $res['status'] : (isset($res['time'])?$res['time']:"");
                
                echo str_pad($hop, 5) . str_pad($addr, 20) . str_pad($loss, 8) . str_pad($sent, 8) . $status . "\n";
            }
        }
    }
    else {
        echo "Perintah tidak valid.";
    }

    $API->disconnect();
} else {
    echo "Gagal terhubung ke API Router MikroTik Anda.";
}
?>
