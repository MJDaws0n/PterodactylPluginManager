<?php
// Need to make this not lag later somehow
// $url = 'https://github.com/MJDaws0n/PterodactylPluginManager/releases/download/latest/default.version';
// $contents = file_get_contents($url);

// if ($contents !== false) {
//     $latestVersion = substr_replace($contents ,"", -1);
// }
$latestVersion = 'v1.0.0-pre';
$version = 'v1.0.0-pre';

$upToDate = 'false';
if($version == $latestVersion){
    $upToDate = 'true';
}

global $config;
$config = [
    'version' => $version,
    'latest' => $latestVersion,
    'upToDate' => $upToDate
];
if (!isset($_GET['get']) && $_SERVER['REQUEST_URI'] != '/sanctum/csrf-cookie'){
    $currentUrl = $_SERVER['REQUEST_URI'];

    // Get configuration
    global $settings;
    $settings = [];
    $settings['copyright'] = "Does not exist";
    if (file_exists(dirname(__FILE__) . '/adminSettings.json')) {
        $jsonContent = file_get_contents(dirname(__FILE__) . '/adminSettings.json');
        $settings = json_decode($jsonContent, true);
    } else{
        echo "Error. adminSettings.json does not exist.";
    }

    // On the admin page
    if(explode('/', $currentUrl)[1] == 'admin'){
        echo "<script>const onAdminPage = true</script>";
        echo "<script>const addonVersion = '{$config['version']}'</script>";
        echo "<script>const upToDate = {$config['upToDate']}</script>";
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

    // On all non API pages
    if(explode('/', $currentUrl)[1] != 'api'){
        include(dirname(__FILE__).'/pages.php');
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
            // On the Themes page
            if(explode('/', $currentUrl)[3] == 'themes'){
                include(dirname(__FILE__).'/adminThemes.php');
            }
            exit();
        }
    }
}