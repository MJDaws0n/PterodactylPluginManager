<?php
namespace Net\MJDawson\AddonManager\Core;

class Admin{
    private $currentUri = [];
    private $tabs = [];

    public function __construct($u, $t){
        $this->currentUri = $u;
        $this->tabs = $t;
    }
    public function editAdmin($dom, $xpath, $addHtmlListner){
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            return $this->manageGet($dom, $xpath, $addHtmlListner);
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            return $this->managePost($dom, $xpath, $addHtmlListner);
        }
    }
    private function getPage(){
        if(isset($this->currentUri[1]) && $this->currentUri[1] == 'addonSettings' && !isset($this->currentUri[2])){
            return 'addonSettings';
        }
        if(isset($this->currentUri[1]) && $this->currentUri[1] == 'addonSettings' && isset($this->currentUri[1]) && $this->currentUri[2] == 'plugins'){
            return 'addonSettings/plugins';
        }
        return null;
    }
    private function addAdminTabs($xpath, $dom){
        if($sidebarMenuElements = $xpath->query('//section[contains(@class, "sidebar")]/ul[contains(@class, "sidebar-menu")]')->item(0) ?: null){
            // Add new elements
            $section = $dom->createElement('li');
            $section->nodeValue = 'ADDON';
            $section->setAttribute('class', 'header');
    
            $sidebarMenuElements->appendChild($section);
            foreach ($this->tabs as $tab) {
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
    }
    private function managePost($dom, $xpath, $addHtmlListner){
        switch($this->getPage()){
            case('addonSettings'):
                if(isset($_POST['addon:copyright'])){
                    // Get the settings
                    require(dirname(__FILE__).'/config.php');
                    $settings = new Config();
                    $addonSettings = $settings->getSettings();

                    // Update the copyright
                    $addonSettings['copyright'] = $_POST['addon:copyright'];

                    // Save changes
                    $settings->updateSettings($addonSettings);
                    
                    // Redirect back to the addonsettings page with the GET page
                    header('location: /admin/addonSettings');
                    exit();
                }
                break;
        }
    }
    private function manageGet($dom, $xpath, $addHtmlListner){
        // Run the admin patcher
        require dirname(__FILE__).'/adminPatcher.php';
        $patcher = new AdminPatcher();
        $patcher->patch($addHtmlListner);

        switch($this->getPage()){
            case('addonSettings'):
                $addHtmlListner(function($HTML, $status){
                    if(file_exists(dirname(__FILE__).'/../pages/addonSettings.html')){
                        // We are not using $HTML becasue it is just most likley going to be a 404 page, so we might aswel just overide it
                        require(dirname(__FILE__).'/config.php');
                        $settings = new Config();
                        $addonSettings = $settings->getSettings();

                        $site = file_get_contents(dirname(__FILE__).'/../pages/addonSettings.html');
                        $site = str_replace('{{CRSF_TOKEN}}', strval(session()->token()), $site);
                        $site = str_replace('{{APP_URL}}', strval($_ENV['APP_URL']), $site);
                        $site = str_replace('{{GRAVATAR_URL}}', strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower(auth()->user()['email']))) . '?s=160'), $site);
                        $site = str_replace('{{USER_NAME}}', strval(auth()->user()['name_first'] . ' ' . auth()->user()['name_last']), $site);
                        $site = str_replace('{{COMPANY_NAME}}', strval(config('app.name')), $site);
                        $site = str_replace('{{COPYRIGHT}}', htmlspecialchars(strval($addonSettings['copyright'])), $site);

                        // Update the dom and xpath
                        $dom = new \DOMDocument(); 
                        @$dom->loadHTML($site, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
                        $xpath = new \DOMXPath($dom);

                        // Add the admin tabs
                        $this->addAdminTabs($xpath, $dom);

                        // Get the tab
                        $tabElement = $xpath->query("//a[@href='/admin/".$this->getPage()."']");

                        // Check the tab exists
                        if($tabElement){
                            // Set to active
                            $tabElement->item(0)->parentNode->setAttribute('class', 'active');
                        }
  
                        // Re-generate the HTML
                        $site = $dom->saveHTML();

                        return [$site, 200];
                    }

                    // Add the listener
                    if(file_exists(dirname(__FILE__).'/../pages/error500.html')){
                        return [file_get_contents(dirname(__FILE__).'/../pages/error500.html'), 500];
                    }
                    return ["Error 500. Failed to load both the requested page and error 500 page.", 500];
                }, 2);
                break;
            case('addonSettings/plugins'):
                $addHtmlListner(function($HTML, $status){
                    if(file_exists(dirname(__FILE__).'/../pages/plugins.html')){
                        // We are not using $HTML becasue it is just most likley going to be a 404 page, so we might aswel just overide it
                        require(dirname(__FILE__).'/config.php');
                        $settings = new Config();
                        $addonSettings = $settings->getSettings();

                        $site = file_get_contents(dirname(__FILE__).'/../pages/plugins.html');
                        $site = str_replace('{{CRSF_TOKEN}}', strval(session()->token()), $site);
                        $site = str_replace('{{APP_URL}}', strval($_ENV['APP_URL']), $site);
                        $site = str_replace('{{GRAVATAR_URL}}', strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower(auth()->user()['email']))) . '?s=160'), $site);
                        $site = str_replace('{{USER_NAME}}', strval(auth()->user()['name_first'] . ' ' . auth()->user()['name_last']), $site);
                        $site = str_replace('{{COMPANY_NAME}}', strval(config('app.name')), $site);
                        $site = str_replace('{{COPYRIGHT}}', htmlspecialchars(strval($addonSettings['copyright'])), $site);

                        // Update the dom and xpath
                        $dom = new \DOMDocument(); 
                        @$dom->loadHTML($site, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
                        $xpath = new \DOMXPath($dom);

                        // Add the admin tabs
                        $this->addAdminTabs($xpath, $dom);

                        // Get the tab
                        $tabElement = $xpath->query("//a[@href='/admin/".$this->getPage()."']");

                        // Check the tab exists
                        if($tabElement->item(0) !== null){
                            // Set to active
                            $tabElement->item(0)->parentNode->setAttribute('class', 'active');
                            
                        }

                        // Re-generate the HTML
                        $site = $dom->saveHTML();

                        return [$site, 200];
                    }

                    // Add the listener
                    if(file_exists(dirname(__FILE__).'/../pages/error500.html')){
                        return [file_get_contents(dirname(__FILE__).'/../pages/error500.html'), 500];
                    }
                    return ["Error 500. Failed to load both the requested page and error 500 page.", 500];
                }, 2);
                break;
            default:
                // Add the admin tabs
                $this->addAdminTabs($xpath, $dom);
        }
    }
}