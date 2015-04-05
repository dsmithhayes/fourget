#!/usr/bin/env php
<?php namespace DSH\fourget;

/*
 * `fourget` is a command line utility used for downloading all of the
 * images from a thread on 4chan.org. 
 * 
 * author:  Dave Smith-Hayes
 * version: 0.1
 */

//The first argument must always be the URI of the thread
if(isset($argv[1]))
    $uri = $argv[1];
else
    die("No URI given.");

// set the cURL options
$curl_opts = array(
    CURLOPT_URL => null,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true
);

//      01                2  3      4        5           6
// http://boards.4chan.org/mu/thread/48186754/the-mu-wiki
$uri = explode("/", $uri);

$board     = $uri[3];  // "mu"
$thread_id = $uri[5];  // "48186754"

$thread_uri  = "http://a.4cdn.org/" . $board;
$thread_uri .= "/thread/" . $thread_id . ".json";

$curl_opts[CURLOPT_URL] = $thread_uri;

// begin the cURLing
$ch = curl_init()
    or die("curl: " . curl_error($ch));

curl_setopt_array($ch, $curl_opts);

$json = curl_exec($ch)
    or die("curl: " . curl_error($ch));

// hopefully this is a JSON string...
$thread_json = json_decode($json);

// builds the array of URL's to the images.
foreach($thread_json->posts as $post) {
    if(isset($post->tim)) {
        $url  = "http://i.4cdn.org/" . $board;
        $url .= "/" . $post->tim;
        $url .= $post->ext;

        $image_urls[] = $url;
    }
}

// downloads and saves each image
foreach($image_urls as $img) {
    $curl_opts[CURLOPT_URL] = $img;
    curl_setopt_array($ch, $curl_opts);
    
    $img = explode("/", $img);
    $last = count($img) - 1;
    
    $image = curl_exec($ch)
        or die("curl: " . curl_error($ch));
    
    $f = fopen($img[$last], "w+")
        or die("fopen");
    
    if(fwrite($f, $image))
        echo "Saved: " . $img[$last] . "\n";
    else
        echo "Error saving: " . $img[$last] . "\n";
    
    fclose($f);
}

?>
