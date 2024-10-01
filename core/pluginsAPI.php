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

    /**
     * Returns "Hello World" string
     * 
     * @return string
     */
    public function helloWorld() : String {
        return "Hello World";
    }
    /**
     * Gathers and returns an array of request data including method, URI, query parameters, headers, and more.
     * Determines the type of page (front, login, or back) based on the URI.
     * 
     * @return array The request information including method, URI, and page type.
     */
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
            if($this->getUser() !== null){
                $ret['end'] = 'front';
            } else{
                $ret['end'] = 'login';
            }
        }
    
        if ($currentUri[0] == 'admin') {
            $ret['end'] = 'back';
        }
    
        return $ret;
    }
    /**
     * Adds an HTML listener function with a specific priority.
     * 
     * @param callable $function The listener function to add.
     * @param int $priority The priority of the listener.
     * 
     * @return void
     */
    public function addHtmlListner($function, $priority) : void {
        $this->editListners[] = ['function' => $function, 'priority' => $priority];
    }
    /**
     * Returns a list of all registered HTML listeners.
     * 
     * @return array The list of HTML listeners.
     */
    public function listAllHTMLListners() : array {
        return $this->editListners;
    }
    /**
     * Returns all registered patches.
     * 
     * @return array The list of patches.
     */
    public function listAllPatches() : array {
        return $this->patches;
    }
    /**
     * Returns the currently authenticated user or null if not authenticated.
     * 
     * @return mixed The authenticated user or null.
     */
    public function getUser() {
        if((!auth()->check())){
            return null;
        }
        return auth()->user();
    }
        /**
     * Adds a patch to the 'vendors~dashboard~server' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function vendorDashPatch($needle, $haystack) : void {
        array_push($this->patches['vendors~dashboard~server'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the 'vendors~auth~dashboard~server' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function vendorAuthDashPatch($needle, $haystack) : void {
        array_push($this->patches['vendors~auth~dashboard~server'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the 'dashboard~server' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function dashServerPatch($needle, $haystack) : void {
        array_push($this->patches['dashboard~server'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the 'dashboard' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function dashPatch($needle, $haystack) : void {
        array_push($this->patches['dashboard'], [$needle, $haystack]);
    }
    /** 
     * Adds a patch to the 'bundle' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function bundlePatch($needle, $haystack) : void {
        array_push($this->patches['bundle'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the 'server' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function serverPatcher($needle, $haystack) : void {
        array_push($this->patches['server'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '7' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch7($needle, $haystack) : void {
        array_push($this->patches['7'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '8' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch8($needle, $haystack) : void {
        array_push($this->patches['8'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '9' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch9($needle, $haystack) : void {
        array_push($this->patches['9'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '10' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch10($needle, $haystack) : void {
        array_push($this->patches['10'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '11' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch11($needle, $haystack) : void {
        array_push($this->patches['11'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '12' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch12($needle, $haystack) : void {
        array_push($this->patches['12'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '13' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch13($needle, $haystack) : void {
        array_push($this->patches['13'], [$needle, $haystack]);
    }
    /**
     * Adds a patch to the '14' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
    public function numPatch14($needle, $haystack) : void {
        array_push($this->patches['14'], [$needle, $haystack]);
    }
        /**
     * Adds a patch to the 'auth' category.
     * 
     * @param mixed $needle The needle to add.
     * @param mixed $haystack The haystack to add.
     * 
     * @return void
     */
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

    /**
     * Constructor for the HTML parser. It replaces <p> tags with custom placeholders, creates a DOM document, and initializes an XPath object.
     * 
     * @param string $HTML The HTML to parse.
     */
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
    /**
     * Returns the DOM document.
     * 
     * @return \DOMDocument The DOM document object.
     */
    public function dom(){
        return $this->dom;
    }
    /**
     * Returns the XPath object.
     * 
     * @return \DOMXPath The XPath object.
     */
    public function xpath(){
        return $this->xpath;
    }
    /**
     * Retrieves the HTML content and replaces custom placeholders back to <p> tags.
     * 
     * @return string The modified HTML content.
     */
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