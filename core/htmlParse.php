<?php
namespace Net\MJDawson\AddonManager\Core;
use Net\MJDawson\AddonManager\Core\BundlePatcher;

class htmlParse {
    private $errorListners = [];
    private $editListners = [];
    private $tabs = [];

    public function __construct($pluginInstances) {
        // Convert plugin instances HTML listners to htmlParse HTML listners
        foreach ($pluginInstances as $instance) {
            foreach ($instance->listAllHTMLListners() as $listner) {
                $this->addHtmlListner($listner['function'], $listner['priority']);
            }
        }
    }
    public function parse($content, $status, $uri){   
        // Fix PHP being stupid and adding unwanted tags using an incredibly insane method
        $content = str_replace(array('<p>', '</p>'), array(
            '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
            '</phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">'
        ), $content);

        // No clue what this does, but ChatGPT says i need the \ to make it use the PHP namespace not the Net\MDaawson one
        $dom = new \DOMDocument();

        // WFT this @ do, not clue
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);

        // This is like used to edit the HTML i think
        $xpath = new \DOMXPath($dom);

        // Make changes
        // Add the admin tabs
        if($uri[0] == 'admin'){
            // Check the admin page is not 404 by checking for presence of the session token
            if(!session()->token() && $_SERVER['REQUEST_METHOD'] !== 'POST'){
                return [ 'html' => '', 'status' => '404'];
            }
            // Run the admin script for admin pages
            require_once(dirname(__FILE__).'/admin.php');
            $admin = new Admin($uri, $this->tabs);
            $admin->editAdmin($dom, $xpath, [$this, 'addHtmlListner']);

            if(!auth()->user() !== null){
                // Not logged in so 404
                return [ 'html' => '', 'status' => '404'];
            }
        }

        // Run the bundle patcher
        require_once dirname(__FILE__).'/bundlePatcher.php';
        $patcher = new Patcher();
        $patcher->onError(function($err){
            $this->error(500, $err);
        });
        $patcher->patch($xpath, $dom, $uri);

        // This saves the DOM (whatever that means) back into HTML that we can just echo
        $content = $dom->saveHTML();

        // This is the strange thing that PHP breaks, it adds like <p>'s randomly, so we need to remove it
        $content = str_replace(array('<p>', '</p>'), array('', ''), $content);

        // Then we need to set out old <p>'s back to an actual <p>
        // * Probably a much better way of doing this
        $content = str_replace(array(
            '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
            '</phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">'
        ), array('<p>', '</p>'), $content);

        // Run the HTML listners
        usort($this->editListners, function($a, $b) {
            return $b['priority'] - $a['priority']; // Sort by descending priority
        });
        foreach ($this->editListners as $listener) {
            $listnerFunction = $listener['function']; // Extract the function

            $site = $listnerFunction($content, $status);

            $content = $site[0] ?: $content;
            $status = $site[1] ?: $status;
        }

    
        // Display the HTML
        return [ 'html' => $content, 'status' => $status];
    }
    public function addAdminTab($name, $location, $icon){
        array_push($this->tabs,[
            "name" => "$name",
            "location" => "$location",
            "icon" => "$icon"
        ]);
    }
    public function addHtmlListner($function, $priority) {
        $this->editListners[] = ['function' => $function, 'priority' => $priority];
    }    
    public function onError($listner){
        array_push($this->errorListners, $listner);
    }
    private function error($error){
        foreach ($this->errorListners as $listner) {
            $listner($error);
        }
    }
}