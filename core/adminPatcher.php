<?php
namespace Net\MJDawson\AddonManager\Core;

class AdminPatcher{
    private $errorLisnters = [];

    public function patch($addHtmlListner, $currentUri) {
        // Set the copyright text
        $addHtmlListner(function($HTML, $status){
            // Create the document
            $dom = new \DOMDocument(); 
            @$dom->loadHTML($HTML, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
            $xpath = new \DOMXPath($dom);

            // Get the copyright
            require_once dirname(__FILE__) . '/config.php';
            
            $config = new Config;
            $settings = $config->getSettings();
            $copyrightHTML = '{{COPYRIGHT}}';

            // Run the variable exchanger on the copyright
            require_once dirname(__FILE__) . '/variables.php';
            $variables = new Variables;

            $variables->variableExchange($copyrightHTML, $settings);

            // Get the footer
            $footer = $xpath->query("//footer[@class='main-footer']")->item(0);

            // Check the footer element was found
            if ($footer == null) {
                return ['<p>footer missing, makesure addon is up to date</p>', 404];
            }

            // Remove the random bit of text (Why is it a node not a child, no-one knows)
            $footer->removeChild($footer->firstChild->nextSibling->nextSibling);

            // Remove the link
            $footer->removeChild($footer->firstChild->nextSibling->nextSibling);

            // Clear the random '-'
            $footer->firstChild->nextSibling->nextSibling->nodeValue = '';

            // Set the innerHTML to the copyright
            $copyright = $dom->createDocumentFragment();
            $copyright->appendXML($this->convertHtmlEntitiesToNumeric($copyrightHTML));
            $footer->appendChild($copyright);

            return [$dom->saveHTML(), 200];
        }, 1);

        if($currentUri[0] == 'admin' && !isset($currentUri[1])){
            // Add the version infomation
            $addHtmlListner(function($HTML, $status){
                // Create the document
                $dom = new \DOMDocument(); 
                @$dom->loadHTML($HTML, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
                $xpath = new \DOMXPath($dom);

                // Get the copyright
                require_once dirname(__FILE__) . '/config.php';
                
                $config = new Config;
                $settings = $config->getSettings();
                $version = $settings['version'];

                // Get the overview text
                $overview = $xpath->query("//div[@class='box-body']")->item(0);

                // Check the overview element was found
                if ($overview == null) {
                    return ['<p>overview missing. Make sure addon is up to date</p>', 404];
                }

                $addonInfo = $dom->createDocumentFragment();
                if($latestVersion = $this->checkForUpdates()){
                    $addonInfo->appendXML($this->convertHtmlEntitiesToNumeric('<br></br>Your addon is <b>not up-to-date!</b> The latest version is <code>'.$latestVersion.'</code> and you are currently running version <code>'.$version.'</code>.'));
                
                    // Change the colour to red
                    $box = $xpath->query("//div[contains(@class, 'box')]")->item(0);
                    if ($box == null) {
                        return ['<p>status box missing. Make sure addon is up to date</p>', 404];
                    }
                    $box->setAttribute('class','box box-danger');

                } else{
                    $addonInfo->appendXML($this->convertHtmlEntitiesToNumeric('<br></br>You are running Pterodactyl Addon version <code>'.$version.'</code>. Your addon is up-to-date!'));
                }

                $overview->appendChild($addonInfo);

                return [$dom->saveHTML(), 200];
            }, 1);
        }
    }
    public function onError($listner){
        array_push($this->errorLisnters, $listner);
    }
    private function error($error){
        foreach ($this->errorLisnters as $listner) {
            $listner($error);
        }
    }
    // This function is only here cause the PHP HTML parser thing is so stupid and just can't seem to understand anything
    private function convertHtmlEntitiesToNumeric($html) {
        // Define an array of named entities and their numeric equivalents
        $namedEntities = [
            '&nbsp;'  => '&#160;',
            '&lt;'    => '&#60;',
            '&gt;'    => '&#62;',
            '&amp;'   => '&#38;',
            '&quot;'  => '&#34;',
            '&apos;'  => '&#39;',
            '&copy;'  => '&#169;',
            '&reg;'   => '&#174;',
            '&euro;'  => '&#8364;',
            // Add more named entities later if it's needed
        ];
    
        // Replace named entities with numeric equivalents
        $html = strtr($html, $namedEntities);
    
        return $html;
    }
    
    // Check if we need to do an update
    private function checkForUpdates() {
        $cacheFile = dirname(__FILE__). '/../version_cache.txt';
        $cacheTime = 86400; 
    
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
            // Load from cache if cache is still valid
            $contents = file_get_contents($cacheFile);
        } else {
            // Fetch from URL and update cache
            $url = 'https://github.com/MJDaws0n/PterodactylPluginManager/releases/latest/download/default.version';
            $contents = @file_get_contents($url);
            
            // Check if fetching was successful
            if ($contents !== false) {
                // Save the fetched data into the cache file
                file_put_contents($cacheFile, $contents);
            } else {
                return false;
            }
        }
    
        $latestVersion = trim($contents);
    
        // Get current version
        require_once dirname(__FILE__) . '/config.php';
        
        $config = new Config;
        $currentVersion = $config->getSettings()['version'];
    
        // Check if the versions match
        if ($currentVersion != $latestVersion) {
            return $latestVersion;
        }
        return false;
    }
}