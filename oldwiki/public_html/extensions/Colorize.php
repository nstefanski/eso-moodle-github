<?php
$wgExtensionFunctions[] = "wfColorizeSetup";
$wgExtensionCredits['parserhook'][] = array(
    'name' => 'Colorize',
    'url' => 'https://www.mediawiki.org/wiki/Extension:Colorize',
    'author' => 'Javier Valcarce Garcia',
    'version' => '0.2',
    'description' => 'Makes text to appear more fun',
);
 
function wfColorizeSetup() {
 
    global $wgParser;
    $wgParser->setHook( "colorize", "wfColorizeRender" );
}
 
function wfColorizeRender( $input, $argv, $parser ) { 
 
    // Character styles
    $input = utf8_decode($input);
    $output = ""; //To stop the "Undefined Variable" errors in the webserver logfile
 
    for ($i = 0; $i < strlen($input); $i++)
      {
    $s = rand(0, 9) * 8 + 150;
    $w = rand(5, 9) * 100;
    $r = rand(20, 220);
    $g = rand(20, 220);
    $b = rand(20, 220);
 
    $output .= 
      '<span style="font-size: ' . strval($s) . '%; font-weight:' 
      . strval($w) . ';color: #' . dechex($r) . dechex($g) . dechex($b) 
      . ';">';
 
    $output .= $input[$i];
    $output .= '</span>';
      }
 
    return utf8_encode($output);
}