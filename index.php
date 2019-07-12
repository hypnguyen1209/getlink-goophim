<?php
header("Content-type: text/javascript");
include 'JavaScriptUnpacker.php';
$url = $_GET['url'];
$ch = curl_init($url);
curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3833.0 Safari/537.36',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => true
));
$data = curl_exec($ch);
    curl_close($ch);
preg_match_all('#">eval(.+?)</script>#', $data, $arrayJS);
$eval = 'eval'.$arrayJS[1][0];
$unpack = new JavaScriptUnpacker();
$reponse = $unpack->Unpack($eval);
echo $reponse;



