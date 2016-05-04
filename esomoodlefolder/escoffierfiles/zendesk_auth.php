<?php
include_once "/firebase/JWT.php";
use \Firebase\JWT\JWT;

require_once "../config.php";
require_login();

// Log your user in.

$key       = "xV8dS7QwDsEpgUWudD6ADstMWSByoIpPea1RRZxkPSlr0GfG";
$subdomain = "escoffier";
$now       = time();

$token = array(
  "jti"   => md5($now . rand()),
  "iat"   => $now,
  "name"  => $USER->firstname." ".$USER->lastname,
  "email" => $USER->email,
  "phone" => $USER->phone1,
  "external_id" => $USER->id
);

$jwt = JWT::encode($token, $key);
$location = "https://" . $subdomain . ".zendesk.com/access/jwt?jwt=" . $jwt;
//*
if(isset($_GET["return_to"])) {
  $location .= "&return_to=" . urlencode($_GET["return_to"]);
}//*/
//$location .= "&return_to=" . urlencode("https://escoffier.zendesk.com/hc/en-us/requests");

// Redirect
header("Location: " . $location);

?>
