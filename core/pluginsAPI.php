<?php
namespace Net\MJDawson\AddonManager\Core;

class PluginsAPI{
    private $editListners = [];
    private $patches = [
        'vendors~dashboard~server' => [],
        'vendors~auth~dashboard~server' => [],
        'dashboard~server' => [],
        'dashboard' => [],
        'bundle' => [],
        'auth' => [],
        '7' => [],
        '8' => [],
        '9' => [],
        '10' => [],
        '11' => [],
        '12' => [],
        '13' => [],
        '14' => [],
        'server' => []
    ];

    public function helloWorld() : String {
        return "Hello World";
    }
    public function getPage() : array {
        // Get the current pages URI
        $currentUri = $this->get_uri_components();
    
        $ret = [
            'method' => $_SERVER['REQUEST_METHOD'],      // HTTP method
            'uri' => $currentUri,                        // Full URI components
            'query_params' => $_GET,                     // Any query string parameters
            'headers' => getallheaders(),                // Request headers
            'ip_address' => $_SERVER['REMOTE_ADDR'],     // Client IP address
            'user_agent' => $_SERVER['HTTP_USER_AGENT'], // Client's user agent
            'timestamp' => time(),                       // Request timestamp
            'end' => 'unsure'                            // Just makes sure there is no errors
        ];
    
        // Determine the page type based on the URI
        if ($currentUri[0] == ''
            || $currentUri[0] == '/'
            || $currentUri[0] == 'server'
            || $currentUri[0] == 'account') {
            $ret['end'] = 'front';
        }
    
        if ($currentUri[0] == 'admin') {
            $ret['end'] = 'back';
        }
    
        return $ret;
    }
    public function addHtmlListner($function, $priority) : void {
        $this->editListners[] = ['function' => $function, 'priority' => $priority];
    }
    public function listAllHTMLListners() : array {
        return $this->editListners;
    }
    public function listAllPatches() : array {
        return $this->patches;
    }
    public function getUser() : object {
        return auth()->user();
    }
    public function vendorDashPatch($needle, $haystack) : void {
        array_push($this->patches['vendors~dashboard~server'], [$needle, $haystack]);
    }
    public function vendorAuthDashPatch($needle, $haystack) : void {
        array_push($this->patches['vendors~auth~dashboard~server'], [$needle, $haystack]);
    }
    public function dashServerPatch($needle, $haystack) : void {
        array_push($this->patches['dashboard~server'], [$needle, $haystack]);
    }
    public function dashPatch($needle, $haystack) : void {
        array_push($this->patches['dashboard'], [$needle, $haystack]);
    }
    public function bundlePatch($needle, $haystack) : void {
        array_push($this->patches['bundle'], [$needle, $haystack]);
    }
    public function serverPatcher($needle, $haystack) : void {
        array_push($this->patches['server'], [$needle, $haystack]);
    }
    public function numPatch7($needle, $haystack) : void {
        array_push($this->patches['7'], [$needle, $haystack]);
    }
    public function numPatch8($needle, $haystack) : void {
        array_push($this->patches['8'], [$needle, $haystack]);
    }
    public function numPatch9($needle, $haystack) : void {
        array_push($this->patches['9'], [$needle, $haystack]);
    }
    public function numPatch10($needle, $haystack) : void {
        array_push($this->patches['10'], [$needle, $haystack]);
    }
    public function numPatch11($needle, $haystack) : void {
        array_push($this->patches['11'], [$needle, $haystack]);
    }
    public function numPatch12($needle, $haystack) : void {
        array_push($this->patches['12'], [$needle, $haystack]);
    }
    public function numPatch13($needle, $haystack) : void {
        array_push($this->patches['13'], [$needle, $haystack]);
    }
    public function numPatch14($needle, $haystack) : void {
        array_push($this->patches['14'], [$needle, $haystack]);
    }
    public function authPatcher($needle, $haystack) : void {
        array_push($this->patches['auth'], [$needle, $haystack]);
    }




    private function get_uri_components() {
        $parsed_url = parse_url($this->get_current_url());
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $trimmed_path = trim($path, '/');
        $components = explode('/', $trimmed_path);
        return $components;
    }
    private function get_current_url(){
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $request_uri = $_SERVER['REQUEST_URI'];
        return $scheme . "://" . $host . $request_uri;
    }
}

class HtmlParser{
    private $dom;
    private $xpath;

    public function __construct($HTML) {
        // Stupid fix for a stupid feature
        $HTML = str_replace(array('<p>', '</p>'), array(
            '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
            '</phpStupid>'
        ), $HTML);
    
        // Create the document
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTML($HTML, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
        // Initialize xpath
        $this->xpath = new \DOMXPath($this->dom);
    }
    public function dom(){
        return $this->dom;
    }
    public function xpath(){
        return $this->xpath;
    }
    public function getHTML() : String {
        // Save the HTML
        $HTML = $this->dom->saveHTML();

        // This is the strange thing that PHP breaks, it adds like <p>'s randomly, so we need to remove them
        $HTML = str_replace(array('<p>', '</p>'), array('', ''), $HTML);

        // Then we need to set out old <p>'s back to an actual <p>
        // * Probably a much better way of doing this
        $HTML = str_replace(array(
            '<phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">',
            '</phpStupid id="phpStupid" class="phpStupid" data-phpStupid="phpStupid">'
        ), array('<p>', '</p>'), $HTML);

        return $HTML;
    }
}