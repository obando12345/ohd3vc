<?php

$percent = $_GET['percent'];
// $percent = (int)$argv[1];
echo($percent . "\n");

$url = "https://api-http.littlebitscloud.cc/devices/00e04c02aac4/output";
$curl = curl_init($url);
$option = array(
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer 590ab685c9e48cc4549a62186e5590d5a00e15fd715343ddabed0e49d3f0e35c",
        "Accept: application/vnd.littlebits.v2+json",
    ),
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(array(
        'percent' => $percent,
        'duration_ms' => -1
    )),
);

curl_setopt_array($curl, $option);

$result = curl_exec($curl);

echo $result;

?>
