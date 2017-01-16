<?php

require_once('config.php');

$sPostcodeRegex = '#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]) ?[0-9][ABD-HJLNP-UW-Z]{2})$#';

if(!array_key_exists('postcode', $_GET) || !preg_match($sPostcodeRegex, $_GET['postcode']))
{
  echo 0;
  die;
}



// Get cURL resource
$curl = curl_init();

$arrHeaders = array(
  'Authorization: PharmOutcomes '.PHARMOUTCOMES_KEY
);

$query = http_build_query([
 'postcode' => $_GET['postcode'],
 'range' => 4000
]);

// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'http://localhost:3618/providers/range?'.$query,
    CURLOPT_HTTPHEADER => $arrHeaders
));
// Send the request & save response to $resp
$resp = curl_exec($curl);
$arrResp = json_decode($resp);
header('Content-Type: application/json');
if(array_key_exists('status_code', $arrResp))
{
  echo 9;
  die;
}
echo $resp;
// Close request to clear up some resources
curl_close($curl);
