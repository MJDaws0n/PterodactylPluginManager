<?php
namespace Net\MJDawson\AddonManager\Core;

class Variables{
    public function variableExchange(&$html, $settings){
        // Set the year in the copyright
        $copyright = str_replace('{year}', date('Y'), $settings['copyright']);
        
        // Update the html
        $html = str_replace('{{COPYRIGHT}}', $copyright, $html);
        return $html;
    }
}