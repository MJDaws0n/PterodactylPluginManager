<?php
namespace Net\MJDawson\AddonManager\Core;

class AdminPatcher{
    private $errorLisnters = [];

    public function patch($addHtmlListner) {
        // Set the copyright text
        $addHtmlListner(function($HTML, $status){
            // Create the document
            $dom = new \DOMDocument(); 
            @$dom->loadHTML($HTML, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
            $xpath = new \DOMXPath($dom);

            // Get the copyright
            // Check it's not already been loaded in
            if (!in_array(dirname(__FILE__) . '/config.php', get_included_files())){
                require dirname(__FILE__) . '/config.php';
            }
            
            $config = new Config;
            $settings = $config->getSettings();
            $copyrightHTML = '{{COPYRIGHT}}';

            // Run the variable exchanger on the copyright
            require dirname(__FILE__) . '/variables.php';
            $variables = new Variables;

            $variables->variableExchange($copyrightHTML, $settings);

            // Get the footer
            $footer = $xpath->query("//footer[@class='main-footer']")->item(0);

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
}