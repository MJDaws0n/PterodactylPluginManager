<?php
// Need to make this not lag later somehow
// $url = 'https://github.com/MJDaws0n/PterodactylPluginManager/releases/download/latest/default.version';
// $contents = file_get_contents($url);

// if ($contents !== false) {
//     $latestVersion = substr_replace($contents ,"", -1);
// }

use function Pterodactyl\Http\editAdmin;

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

$currentUrl = get_uri_components();

/*if (!isset($_GET['get']) && $_SERVER['REQUEST_URI'] != '/sanctum/csrf-cookie' &&
!strpos($_SERVER['REQUEST_URI'],'.json') &&
!strpos($_SERVER['REQUEST_URI'],'api') &&
!strpos($_SERVER['REQUEST_URI'],'.php')
){

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
            // On the Plugins page
            if(explode('/', $currentUrl)[3] == 'plugins'){
                include(dirname(__FILE__).'/adminPlugins.php');
            }
            exit();
        }
    }
}*/

// Enable the plugins
/*foreach ($settings['plugins'] as $plugin) {
    if(file_exists(dirname(__FILE__) . '/plugins/'.$plugin.'/main.ptero') && json_decode(file_get_contents(dirname(__FILE__) . '/plugins/'.$plugin.'/main.ptero')) !== null){
        $pluginConfig = json_decode(file_get_contents(dirname(__FILE__) . '/plugins/'.$plugin.'/main.ptero'), true);

        if(isset($pluginConfig['execute']) && file_exists(dirname(__FILE__) . '/plugins/'.$plugin.'/'.$pluginConfig['execute'])){
            include(dirname(__FILE__) . '/plugins/'.$plugin.'/'.$pluginConfig['execute']);
        }
    }
}*/

// Custom API page
/*if(explode('/', $currentUrl)[1] == 'pluginapi'){
    include(dirname(__FILE__) . '/pluginAPIManagement.php');
    exit();
}*/

// Default Tabs
global $tabs;
$tabs = [
    [
        "name" => "Addon Settings",
        "location" => "/admin/addonSettings",
        "icon" => "fa-wrench"
    ],
    [
        "name" => "Addon Plugins",
        "location" => "/admin/plugins",
        "icon" => "fa-edit"
    ],
    [
        "name" => "Themes",
        "location" => "/admin/themes",
        "icon" => "fa-wrench"
    ]
];

function addAdminTab($name, $location, $icon){
    global $tabs;
    array_push($tabs,[
        "name" => "$name",
        "location" => "$location",
        "icon" => "$icon"
    ]);
}

// HTML edit listners
$editListners = [];

function addHtmlListner($function) {
    global $editListners;
    array_push($editListners, $function);
}
function get_uri_components() {
    $parsed_url = parse_url(get_current_url());
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $trimmed_path = trim($path, '/');
    $components = explode('/', $trimmed_path);
    return $components;
}
function get_current_url(){
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $request_uri = $_SERVER['REQUEST_URI'];
    return $scheme . "://" . $host . $request_uri;
}

function pluginManager($response){
    if(skipLoad()){
        $response->send();
        return;
    }

    // Check if all the custom settings are intalled correctly
    require(dirname(__FILE__).'/install.php');
    $installer = new AddonInstaller();
    $installer->install();

    $website = $response->getContent();

    $headers = $response->headers->all();
    $status = $response->getStatusCode();
    $statusText = Response::$statusTexts[$status] ?? '';

    global $tabs;
    global $editListners;

    foreach ($editListners as $listner) {
        $site = $listner($website, $status) ?: [$website, $status];

        $website = $site[0] ?: $website;
        $status = $site[1] ?: $status;
    }


    header(sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $status, $statusText));

    // Send headers
    foreach ($headers as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    // Fix PHP being stupid and adding unwanted tags
    $website = str_replace(array('<p>', '</p>'), array(
        '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
        '</phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">'
    ), $website);
    $dom = new DOMDocument();
    @$dom->loadHTML($website, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);

    if(get_uri_components()[0] == 'admin'){
        if($sidebarMenuElements = $xpath->query('//section[contains(@class, "sidebar")]/ul[contains(@class, "sidebar-menu")]')->item(0) ?: null){
            // Add new elements
            $section = $dom->createElement('li');
            $section->nodeValue = 'ADDON';
            $section->setAttribute('class', 'header');
    
            $sidebarMenuElements->appendChild($section);
            foreach ($tabs as $tab) {
                // Add new elements
    
                // Container
                $container = $dom->createElement('li');
                $container->setAttribute('class', '1');
    
                // Link
                $link = $dom->createElement('a');
                $link->setAttribute('href', $tab['location']);
    
                // Text
                $text = $dom->createElement('span');
                $text->nodeValue = $tab['name'];
    
                // Icon
                $icon = $dom->createElement('i');
                $icon->setAttribute('class', 'fa '.$tab['icon']);
    
                $container->appendChild($link);
                $link->appendChild($icon);
                $link->appendChild($text);
    
                $sidebarMenuElements->appendChild($container);
            }
        }
        // Run the admin script for admin pages
        require(dirname(__FILE__).'/admin.php');

        editAdmin($dom, $xpath);
    }

    $website = $dom->saveHTML();
    $website = str_replace(array('<p>', '</p>'), array('', ''), $website);
    $website = str_replace(array(
        '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
        '</phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">'
    ), array('<p>', '</p>'), $website);

    // Display the HTML
    echo $website;
}

// Condition in which the addon does not load
function skipLoad(){
    // For some reason does not work with it
    if(get_uri_components()[0] == 'sanctum' && get_uri_components()[1] == 'csrf-cookie'){
        return true;
    }
    if(get_uri_components()[0] == 'auth' && get_uri_components()[1] == 'logout'){
        return true;
    }

    return false;
}

class Response{
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];
}