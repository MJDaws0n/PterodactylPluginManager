<?php
$currentUrl = $_SERVER['REQUEST_URI'];

// On the admin page
if(explode('/', $currentUrl)[1] == 'admin'){
    $file_path = dirname(__FILE__).'/admin.js';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $base64_content = base64_encode($file_content);
        $mime_type = mime_content_type($file_path);
        $data_url = 'data:' . $mime_type . ';base64,' . $base64_content;
        echo "<script src=\"$data_url\"></script>";
    } else {
        echo "Error: Admin file missing. $file_path";
    }
}

// On the custom admin pages
if(explode('/', $currentUrl)[1] == 'admin' && explode('/', $currentUrl)[2] == 'custom'){
    if(explode('/', $currentUrl)[3] == 'general'
    || explode('/', $currentUrl)[3] == 'themes'
    || explode('/', $currentUrl)[3] == 'plugins'
    ){
        // On the general page
        if(explode('/', $currentUrl)[3] == 'general'){
          include(dirname(__FILE__).'/adminGeneral.php');
        }
        exit();
    }
}