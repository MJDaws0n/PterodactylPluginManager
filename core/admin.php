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
        if(isset($this->currentUri[1]) && $this->currentUri[1] == 'addonSettings' && isset($this->currentUri[1]) && $this->currentUri[2] == 'themes'){
            return 'addonSettings/themes';
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
            case('addonSettings'):{
                if(isset($_POST['addon:copyright'])){
                    // Get the settings
                    require_once dirname(__FILE__) . '/config.php';
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
            case('addonSettings/plugins'):{
                if(isset($_POST['addon:plugin'])){
                    // Get the settings
                    require_once dirname(__FILE__) . '/config.php';
                    $settings = new Config();
                    $addonSettings = $settings->getSettings();

                    $pluginName = $_POST['addon:plugin'];

                    // Check the plugin exists and is valid
                    require_once(dirname(__FILE__) . '/plugins.php');
                    $pluginManager = new PluginsManager;

                    // Check plugin folder exists
                    if(!file_exists(dirname(__FILE__) . '/../plugins/'.$pluginName.'/main.ptero')){
                        echo "Error invalid plugin";
                        exit();
                    }

                    // Check plugin is valid JSON
                    if(!$plugin = json_decode(file_get_contents(dirname(__FILE__) . '/../plugins/'.$pluginName.'/main.ptero'), true)){
                        echo "Error invalid plugin";
                        exit();
                    }

                    // Check the plugin config is valid
                    if(!$pluginManager->validateConfig($plugin)){
                        echo "Error invalid plugin";
                        exit();
                    }

                    // Check if it's already active so we can decide what the toggle does
                    if (in_array($pluginName, $addonSettings['plugins'])) {
                        // If it's already active, deactivate it
                        $addonSettings['plugins'] = array_diff($addonSettings['plugins'], [$pluginName]);
                    } else {
                        // If it's not active, activate it
                        array_push($addonSettings['plugins'], $pluginName);
                    }

                    // Save changes
                    $settings->updateSettings($addonSettings);
                    
                    // Redirect back to the plugins page with the GET page
                    header('location: /admin/addonSettings/plugins');
                    exit();
                }
                if(isset($_FILES['addon:plugin_upload'])){
                    // Uplaod the plugin
                    require_once(dirname(__FILE__) . '/plugins.php');
                    $pluginManager = new PluginsManager;

                    $pluginManager->upload($_FILES['addon:plugin_upload']);
                    header('location: /admin/addonSettings/plugins');
                    exit();
                }
                if(isset($_POST['addon:plugin_delete'])){
                    // Uplaod the plugin
                    require_once(dirname(__FILE__) . '/plugins.php');
                    $pluginManager = new PluginsManager;

                    $pluginManager->delete($_POST['addon:plugin_delete']);
                    header('location: /admin/addonSettings/plugins');
                    exit();
                }
                break;
            }
            case('addonSettings/themes'):{
                if(isset($_POST['addon:theme'])){
                    // Get the settings
                    require_once dirname(__FILE__) . '/config.php';
                    $settings = new Config();
                    $addonSettings = $settings->getSettings();

                    $themeName = $_POST['addon:theme'];

                    // Check the theme exists and is valid
                    require_once(dirname(__FILE__) . '/themes.php');
                    $themeManager = new ThemesManager;

                    // Check theme folder exists
                    if(!file_exists(dirname(__FILE__) . '/../themes/'.$themeName.'/main.ptero')){
                        echo "Error invalid theme";
                        exit();
                    }

                    // Check theme is valid JSON
                    if(!$theme = json_decode(file_get_contents(dirname(__FILE__) . '/../themes/'.$themeName.'/main.ptero'), true)){
                        echo "Error invalid theme";
                        exit();
                    }

                    // Check the theme config is valid
                    if(!$themeManager->validateConfig($theme)){
                        echo "Error invalid theme";
                        exit();
                    }

                    // Update the theme
                    $addonSettings['theme'] = $themeName;

                    // Save changes
                    $settings->updateSettings($addonSettings);
                    
                    // Redirect back to the addonsettings page with the GET page
                    header('location: /admin/addonSettings/themes');
                    exit();
                }
                break;
            }
        }
    }
    private function manageGet($dom, $xpath, $addHtmlListner){
        // Run the admin patcher
        require_once dirname(__FILE__).'/adminPatcher.php';
        $patcher = new AdminPatcher();
        $patcher->patch($addHtmlListner, $this->currentUri);

        switch($this->getPage()){
            case('addonSettings'):{
                $addHtmlListner(function($HTML, $status){
                    if(file_exists(dirname(__FILE__).'/../pages/addonSettings.html')){
                        // We are not using $HTML becasue it is just most likley going to be a 404 page, so we might aswel just overide it
                        require_once(dirname(__FILE__).'/config.php');
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
            }
            case('addonSettings/plugins'):{
                $addHtmlListner(function($HTML, $status){
                    if(file_exists(dirname(__FILE__).'/../pages/plugins.html')){
                        // We are not using $HTML becasue it is just most likley going to be a 404 page, so we might aswel just overide it
                        require_once(dirname(__FILE__).'/config.php');
                        $settings = new Config();
                        $addonSettings = $settings->getSettings();

                        $site = file_get_contents(dirname(__FILE__).'/../pages/plugins.html');
                        $site = str_replace('{{CRSF_TOKEN}}', strval(session()->token()), $site);
                        $site = str_replace('{{APP_URL}}', strval($_ENV['APP_URL']), $site);
                        $site = str_replace('{{GRAVATAR_URL}}', strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower(auth()->user()['email']))) . '?s=160'), $site);
                        $site = str_replace('{{USER_NAME}}', strval(auth()->user()['name_first'] . ' ' . auth()->user()['name_last']), $site);
                        $site = str_replace('{{COMPANY_NAME}}', strval(config('app.name')), $site);
                        $site = str_replace('{{COPYRIGHT}}', htmlspecialchars(strval($addonSettings['copyright'])), $site);

                        // Display the plugins
                        require_once(dirname(__FILE__).'/plugins.php');
                        $pluginsManager = new PluginsManager;
                        $plugins = $pluginsManager->getPlugins();

                        $pluginString = '';

                        foreach ($plugins as $plugin) {
                            // Set the button to either active or not active
                            $enable_disableButton = '<button class="btn btn-sm btn-primary pull-right" onclick="addon.enable(event)" control-id="ControlID-8">Enable</button>';

                            if($plugin['active']){
                                // Plugin is active
                                $enable_disableButton = '<button class="btn btn-sm btn-primary pull-right" onclick="addon.enable(event)" control-id="ControlID-8">Disable</button>';
                            }

                            // Set the plugins author section (we need to know if there is a URL)
                            $author = '<p>'.$plugin['author'].'</p>';

                            if(isset($plugin['url'])){
                                // Plugin is active
                                $author = '<a target="_blank" href="'.htmlspecialchars($plugin['url']).'">'.$plugin['author'].'</a>';
                            }

                            // Add to the plugin string
                            $pluginString .= '
                            <tr>
                            <td>
                                <p>'.$plugin['name'].'</p>
                            </td>
                            <td>
                                '.$author.'
                            </td>
                            <td>
                                <p>'.$plugin['description'].'</p>
                            </td>
                            <td class="text-center">
                                '.$enable_disableButton.'
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger pull-left muted muted-hover" onclick="addon.delete(event)" control-id="ControlID-5"><i class="fa fa-trash-o"></i></button>
                            </td>
                            </tr>
                            ';
                        }

                        $site = str_replace('{{PLUGINS}}', $pluginString, $site);


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
            }
            case('addonSettings/themes'):{
                $addHtmlListner(function($HTML, $status){
                    if(file_exists(dirname(__FILE__).'/../pages/themes.html')){
                        // We are not using $HTML becasue it is just most likley going to be a 404 page, so we might aswel just overide it
                        require_once(dirname(__FILE__).'/config.php');
                        $settings = new Config();
                        $addonSettings = $settings->getSettings();

                        $site = file_get_contents(dirname(__FILE__).'/../pages/themes.html');
                        $site = str_replace('{{CRSF_TOKEN}}', strval(session()->token()), $site);
                        $site = str_replace('{{APP_URL}}', strval($_ENV['APP_URL']), $site);
                        $site = str_replace('{{GRAVATAR_URL}}', strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower(auth()->user()['email']))) . '?s=160'), $site);
                        $site = str_replace('{{USER_NAME}}', strval(auth()->user()['name_first'] . ' ' . auth()->user()['name_last']), $site);
                        $site = str_replace('{{COMPANY_NAME}}', strval(config('app.name')), $site);
                        $site = str_replace('{{COPYRIGHT}}', htmlspecialchars(strval($addonSettings['copyright'])), $site);

                        // Display the themes
                        require_once(dirname(__FILE__).'/themes.php');
                        $themesManager = new ThemesManager;
                        $themes = $themesManager->getThemes();

                        $themeString = '';

                        foreach ($themes as $theme) {
                            // Set the button to either active or not active
                            $enable_disableButton = '<button class="btn btn-sm btn-primary pull-right" control-id="ControlID-8" onclick="addon.enable(event)">Enable</button>';

                            if($theme['active']){
                                // Theme is active
                                $enable_disableButton = '';
                            }

                            // Set the themes author section (we need to know if there is a URL)
                            $author = '<p>'.$theme['author'].'</p>';

                            if(isset($theme['url'])){
                                // Theme is active
                                $author = '<a target="_blank" href="'.htmlspecialchars($theme['url']).'">'.$theme['author'].'</a>';
                            }

                            // Add to the theme string
                            $themeString .= '
                            <tr>
                            <td>
                                <p>'.$theme['name'].'</p>
                            </td>
                            <td>
                                '.$author.'
                            </td>
                            <td>
                                <p>'.$theme['description'].'</p>
                            </td>
                            <td class="text-center">
                                '.$enable_disableButton.'
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger pull-left muted muted-hover" control-id="ControlID-5"><i class="fa fa-trash-o"></i></button>
                            </td>
                            </tr>
                            ';
                        }

                        $site = str_replace('{{THEMES}}', $themeString, $site);


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
            }
            default: {
                // Add the admin tabs
                $this->addAdminTabs($xpath, $dom);
            }
        }
    }
}