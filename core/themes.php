<?php
namespace Net\MJDawson\AddonManager\Core;

class ThemesManager{
    public function getThemes() {
        // Search through the current dir then ../themes for all folders
        // Then inside each folder open the main.ptero file and get the
        // Infomation from it in JSON format
        $themes = array();

        if(!is_dir(dirname(__FILE__) . '/../themes/')){
            // Punish the idiot
            echo "Themes directory non existance.";
            exit();
        }
        $themesFolders = array_filter(glob(dirname(__FILE__) . '/../themes/' . '/*'), 'is_dir');
        $themes = [];

        foreach ($themesFolders as $themeFolder) {
            if(file_exists($themeFolder.'/main.ptero')){
                // It's a theme, lets get the infomation
                if(!$theme = json_decode(file_get_contents($themeFolder.'/main.ptero'), true)){
                    // Theme has an invalid config, now we just don't try to load the theme
                    continue;
                }

                // Now need to update the themes to display if it's active or not
                $theme['active'] = false;

                // Check it's not already been loaded in
                if (!in_array(dirname(__FILE__) . '/config.php', get_included_files())){
                    require dirname(__FILE__) . '/config.php';
                }

                // Get the config
                $config = new Config;
                $settings = $config->getSettings();

                // Check if the theme is in the active theme list
                if($theme['name'] == $settings['theme']){
                    $theme['active'] = true;
                }

                if(!$this->validateConfig($theme)){
                    // theme has an invalid config, now we just don't try to load the theme
                    continue;
                }


                // Add the themes config to the file
                array_push($themes, $theme);
            }
        }

        return $themes;
    }
    public function validateConfig($config){
        // Themes config use the following (* for required):
        // name*
        // description*
        // author*
        // url
        // last_updated
        // template_default

        if(
            isset($config['name']) && is_string($config['name']) && 
            isset($config['description']) && is_string($config['description']) && 
            isset($config['author']) && is_string($config['author'])
        ){
            return true;
        }
        return false;
    }
    public function load(){
        // Get the current theme
        $themeName = $this->getTheme();

        // Check theme exists
        if(!file_exists(dirname(__FILE__) . '/../themes/'.$themeName.'/main.ptero')){
            echo "Error: theme does not exsits";
            exit();
        }

        // Check theme is valid JSON
        if(!$theme = json_decode(file_get_contents(dirname(__FILE__) . '/../themes/'.$themeName.'/main.ptero'), true)){
            echo "Error: theme has invalid config";
            exit();
        }

        // Check the theme is valid
        if(!$this->validateConfig($theme)){
            echo "Error: theme has invalid config";
            exit();
        }

        $retTheme = [
            'name' => $theme['name'],
            'description' => $theme['description'],
            'author' => $theme['author'],
            'scripts' => [],
            'styles' => []
        ];

        // Run through and load all the scripts
        if(isset($theme['load_js'])){
            foreach($theme['load_js'] as $script){
                // Check if the script is a url
                if(str_starts_with($script, 'http://') || str_starts_with($script, 'https://')){
                    // Script is a URL, makes our life easy we just load that URL
                    array_push($retTheme, $script);
                } else{
                    // Should just be a file, first check that it exists
                    if(file_exists(dirname(__FILE__) . '/../themes/'.$themeName.'/'.$script)){
                        // Give them out custom format
                        array_push($retTheme, '/addon/scripts/'.$script);
                    } else{
                        echo "Error: theme trying to load non-existent script";
                        exit();
                    }
                }
            }
        }

        return $retTheme;
    }
    public function getTheme(){
        //  Get the current theme

        // Check it's not already been loaded in
        if (!in_array(dirname(__FILE__) . '/config.php', get_included_files())){
            require dirname(__FILE__) . '/config.php';
        }

        // Get the config
        $config = new Config;
        $settings = $config->getSettings();

        return $settings['theme'];
    }
}