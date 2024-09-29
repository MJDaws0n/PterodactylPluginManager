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
                require_once dirname(__FILE__) . '/config.php';

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
        // Themes config use the following (* for require_onced):
        // name*
        // description*
        // author*
        // version*
        // url
        // last_updated
        // template_default

        if(
            isset($config['name']) && is_string($config['name']) && 
            isset($config['description']) && is_string($config['description']) && 
            isset($config['author']) && is_string($config['author']) &&
            isset($config['version']) && is_string($config['version'])
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
                    array_push($retTheme['scripts'], $script);
                } else{
                    // Should just be a file, first check that it exists
                    if(file_exists(dirname(__FILE__) . '/../themes/'.$themeName.'/'.$script)){
                        // Give them out custom format
                        array_push($retTheme['scripts'], '/addon/scripts/theme/'.$script);
                    } else{
                        echo "Error: theme trying to load non-existent script";
                        exit();
                    }
                }
            }
        }
        if(isset($theme['load_css'])){
            foreach($theme['load_css'] as $style){
                // Check if the style is a url
                if(str_starts_with($style, 'http://') || str_starts_with($style, 'https://')){
                    // Style is a URL, makes our life easy we just load that URL
                    array_push($retTheme['styles'], $style);
                } else{
                    // Should just be a file, first check that it exists
                    if(file_exists(dirname(__FILE__) . '/../themes/'.$themeName.'/'.$style)){
                        // Give them out custom format
                        array_push($retTheme['styles'], '/addon/styles/theme/'.$style);
                    } else{
                        echo "Error: theme trying to load non-existent style";
                        exit();
                    }
                }
            }
        }

        return $retTheme;
    }
    public function getTheme(){
        //  Get the current theme
        require_once dirname(__FILE__) . '/config.php';

        // Get the config
        $config = new Config;
        $settings = $config->getSettings();

        return $settings['theme'];
    }
    public function setTheme($themeName){
        // Set the theme
        require_once dirname(__FILE__) . '/config.php';

        // Get the config
        $config = new Config;
        $settings = $config->getSettings();

        foreach ($this->getThemes() as $theme) {
            if ($theme['name'] === $themeName) {
                $settings['theme'] = $themeName;
                echo "Updated theme";
                break;
            }
        }
        
        $config->updateSettings($settings);
    }
    public function getThemeByName($name){
        if(file_exists(dirname(__FILE__) . '/../themes/'.strval($name).'/main.ptero')){
            $theme = file_get_contents(dirname(__FILE__) . '/../themes/'.strval($name).'/main.ptero');
            if(!$theme = json_decode($theme, true)){
                return null;
            }
            if(!$this->validateConfig($theme)){
                return null;
            }

            return $theme;
        }
        return null;
    }

    public function upload($file) {
        // Check if a file was uploaded
        if (!isset($file)) {
            return [1, 'Invalid file'];
        }
    
        // Ensure it's a .zip file
        $fileType = mime_content_type($file['tmp_name']);
        if ($fileType !== 'application/zip') {
            return [1, 'Invalid file type. Please upload a valid .zip theme.'];
        }
    
        // Define the themes directory
        $themeDir = dirname(__FILE__) . '/../themes/';
        
        // Create a temporary location for the uploaded zip file
        $tempDir = sys_get_temp_dir() . '/' . basename($file['name']);
        
        // Move the uploaded file to the temp directory
        if (!move_uploaded_file($file['tmp_name'], $tempDir)) {
            return [1, "Failed to move the uploaded file."];
        }
    
        // Open and extract the .zip file
        $zip = new \ZipArchive();
        if ($zip->open($tempDir) === TRUE) {
            $extractPath = $themeDir . basename($file['name'], '.zip');
    
            // Check if the folder already exists
            if (is_dir($extractPath)) {
                $zip->close();
                return [1, "Theme directory already exists."];
            }
    
            // Extract the contents of the zip
            $zip->extractTo($extractPath);
            $zip->close();
    
            // Check for the presence of main.ptero file
            $mainPtero = $extractPath . '/main.ptero';
            if (!file_exists($mainPtero)) {
                // Cleanup: remove the extracted folder
                $this->removeDirectory($extractPath);
                return [1, "Invalid theme. main.ptero not found."];
            }
    
            // Validate the theme config
            $themeConfig = json_decode(file_get_contents($mainPtero), true);
            if (!$themeConfig || !$this->validateConfig($themeConfig)) {
                echo "Invalid theme configuration.";
                // Cleanup: remove the extracted folder
                $this->removeDirectory($extractPath);
                return [1, "Invalid theme. main.ptero not found."];
            }
    
            // Change ownership and permissions of the extracted directory
            chown($extractPath, 'www-data');  // Change ownership to www-data
            chmod($extractPath, 0755);        // Set permissions to 755
        } else {
            unlink($tempDir);
            return [1, "Failed to open the zip file."];
        }
    
        // Remove the temporary zip file
        unlink($tempDir);
        return [0, 'Good'];
    }
    
    private function removeDirectory($path) {
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($path . '/' . $file)) {
                        $this->removeDirectory($path . '/' . $file);
                    } else {
                        unlink($path . '/' . $file);
                    }
                }
            }
            rmdir($path);
        }
    }
    public function delete($themeName){
        // Theme folder path
        $themeDir = dirname(__FILE__) . '/../themes/' . $themeName;

        // Ensure the themes folder exists
        if (is_dir($themeDir)) {
            // Delete the entire theme folder and its contents
            function deleteDirectory($dir) {
                // show php errors
                if (!is_dir($dir)) return;
                $items = scandir($dir);
    
                foreach ($items as $item) {
                    if ($item == '.' || $item == '..') continue;
    
                    $itemPath = $dir . '/' . $item;
                    
                    if (is_dir($itemPath)) {
                        deleteDirectory($itemPath); // Recursively delete subdirectories
                    } else {
                        unlink($itemPath); // Delete files
                    }
                }
    
                rmdir($dir); // Finally, remove the directory itself
            }
    
            // Perform deletion
            deleteDirectory($themeDir);
    
            echo "Theme '$themeName' has been successfully deleted.";
        } else {
            echo "Theme '$themeName' not found.";
        }
    }
}