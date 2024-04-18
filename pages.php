<?php
global $settings;
echo "<script>const copyrightText = ".str_replace('{year}', date("Y"), json_encode($settings['copyright'])).";</script>";
$file_path = dirname(__FILE__).'/pages.js';
if (file_exists($file_path)) {
    $file_content = file_get_contents($file_path);
    $base64_content = base64_encode($file_content);
    $mime_type = mime_content_type($file_path);
    $data_url = 'data:' . $mime_type . ';base64,' . $base64_content;
    echo "<script src=\"$data_url\"></script>";
} else {
    echo "Error: Pages JS file missing. $file_path";
}