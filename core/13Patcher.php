<?php
namespace Net\MJDawson\AddonManager\Core;

class Patcher {
    private $errorLisnters = [];
    private $patchString = '13Patcher';
    private $nameString = '13';

    public function load($pluginInstances) {
        $dir = dirname(__FILE__) . '/../../public/assets/';
        $files = glob($dir . $this->nameString.'*.js');
        $content = !empty($files) ? file_get_contents($files[0]) : '';

        // Load config
        if (!in_array(dirname(__FILE__) . '/config.php', get_included_files())) {
            require_once dirname(__FILE__) . '/config.php';
        }
        $config = new Config();
        $settings = $config->getSettings();

        // Add plugin patches
        foreach ($pluginInstances as $pterodactyl) {
            $patches = $pterodactyl->listAllPatches();
            foreach($patches[$this->nameString] as $patch){
                $content = str_replace($patch[0], $patch[1], $content);
            }
        }

        // Apply patches
        $this->applyPatches($content, $settings);

        // Append JS files (e.g., pages.js)
        $this->appendPagesJS($content, $settings);

        // Set headers for caching and content type
        header("Content-Type: application/javascript");
        $cacheDuration = 2592000;
        header("Cache-Control: public, max-age=$cacheDuration");
        header("Pragma: cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $cacheDuration) . " GMT");

        // Output the content
        echo $content;
        exit();
    }
    private function applyPatches(&$content, $settings) {
        // Get all patch directories except "append"
        $patchDir = dirname(__FILE__) . '/'.$this->patchString.'/';
        $patches = glob($patchDir . '*', GLOB_ONLYDIR);

        foreach ($patches as $patch) {
            if (basename($patch) === 'append') continue; // Skip "append" folder

            $needlePath = $patch . '/needle.js';
            $haystackPath = $patch . '/haystack.js';

            if (file_exists($needlePath) && file_exists($haystackPath)) {
                $needle = file_get_contents($needlePath);
                $haystack = file_get_contents($haystackPath);

                // If necessary, handle variables in the haystack
                if (file_exists(dirname(__FILE__) . '/variables.php')) {
                    require_once dirname(__FILE__) . '/variables.php';
                    $variables = new Variables();
                    $variables->variableExchange($haystack, $settings);
                }

                // Apply the patch
                $content = str_replace($needle, $haystack, $content);
            } else {
                $this->error(500, 'Missing Patch: ' . basename($patch));
            }
        }
    }
    private function appendPagesJS(&$content, $settings) {
        $appendPath = dirname(__FILE__) . '/'.$this->patchString.'/append/pages.js';
        if (file_exists($appendPath)) {
            $append = file_get_contents($appendPath);
            $content .= $append;
        } else {
            $this->error(500, 'Missing Patch: append/pages.js');
        }
    }
    public function onError($listener) {
        $this->errorLisnters[] = $listener;
    }
    private function error($error) {
        foreach ($this->errorLisnters as $listener) {
            $listener($error);
        }
    }
}
