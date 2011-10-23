<?php

$file_to_up = getenv('TM_FILEPATH');

if(substr($file_to_up, -5) == ".haml") {
  $file_to_up = str_replace("_haml/", "", substr($file_to_up, 0, -5));
}

if(substr($file_to_up, -7) == ".coffee") {
  $file_to_up = str_replace("_coffee/", "assets/", substr($file_to_up, 0, -7).".js");
}

if(substr($file_to_up, -5) == ".sass") {
  $file_to_up = str_replace("_sass/", "assets/", substr($file_to_up, 0, -5));
}

$assetKey = calc_asset_key(getenv('TM_FILEPATH')); 

//if its an image file, throw an error message.
if(is_binary(getenv('TM_FILEPATH'))) {
    echo "*Error: This is an image file. Use Send Selected Assets to Shopify instead.";
    exit();
}

$filecontents = htmlspecialchars(file_get_contents('php://stdin'), ENT_QUOTES, 'UTF-8');
$reqData = sprintf($xmlDataTemp, $filecontents, $assetKey);

//Dump the xml into a tmp file
$xmlFile = tempnam('/tmp', 'foo').'.xml';
file_put_contents($xmlFile, $reqData);

$response = send_asset($api_key, $password, $store ,$xmlFile);

if('200' == $response) {
    echo "Uploaded {$assetKey} to {$config->current}.";
} else {
    // Not ideal, but it works. Problem (though not much of one ): 
    // response on a fail will return the full curl page: ie, shopify 404 full html, + error code at the bottom
    // Will robustify if it becomes an issue. 
    echo "*Error: Could not upload {$assetKey} to {$config->current}." ;
    output_error($response);
}
//And clean up
unlink($xmlFile);