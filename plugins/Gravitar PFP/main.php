<?php
// A simple plugin that changes the default pterodactyl user
// icon to the gravitar icon from the users email
use Net\MJDawson\AddonManager\Core\PluginsAPI;
use Net\MJDawson\AddonManager\Core\HtmlParser;

// Build addon manager
$pterodactyl = new PluginsAPI;

// Ensure that the addon is only loaded on the front end
if($pterodactyl->getPage()['end'] == 'front'){
    // Add a HTML listener to modify the HTML - we need to add scripts
    $pterodactyl->addHtmlListner(function($HTML, $status /* Variables must still be deffined even if it's not used */)use($pterodactyl){
        // Create a HTML parser
        $parser = new HtmlParser($HTML, true);

        // Add crypto JS which is essential for the md5 functions
        $headerElement1 = $parser->dom()->createElement('script');
        $headerElement1->setAttribute('src', 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/core.js');
        $headerElement2 = $parser->dom()->createElement('script');
        $headerElement2->setAttribute('src', 'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/md5.js');

        // Create the gravitar url from the email address
        $gravitarURL = strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower($pterodactyl->getUser()['email']))) . '?s=20');

        // Add the JS that the patcher requires to use
        $script = $parser->dom()->createElement('script');

        // Set the script's type attribute to JavaScript
        $script->setAttribute('type', 'text/javascript');

        // Add inner script content
        $script->appendChild($parser->dom()->createTextNode("
            const net_mjdawson_gravitarpfp = {
                gravitarURL: '$gravitarURL'
            };
        "));

        // Get the head
        $head = $parser->xpath()->query('//head');

        // Check it is found
        if ($head->length > 0) {
            // Append the scripts
            $head->item(0)->appendChild($headerElement1);
            $head->item(0)->appendChild($headerElement2);
            $head->item(0)->appendChild($script);
        }

        // Save the HTML
        $HTML = $parser->getHTML();

        // Must return the new HTML and http code
        return [ $HTML, 200 ];
    }, 3 /* Priotory 3 */);
}


// Create a vendorDashPatch - this is to modify the vendors~dashboard~server.js file,
// this can be easily done by using the custom patch API

// Check that the needle and haystack files exist
if(!file_exists(dirname(__FILE__).'/patches/pfp/haystack.js')){
    echo "Gravitar haystack patch missing. Please re-install the plugin.";
    exit();
}
if(!file_exists(dirname(__FILE__).'/patches/pfp/needle.js')){
    echo "Gravitar needle patch missing. Please re-install the plugin.";
    exit();
}
$pterodactyl->vendorDashPatch(file_get_contents(dirname(__FILE__).'/patches/pfp/needle.js'), file_get_contents(dirname(__FILE__).'/patches/pfp/haystack.js'));


return $pterodactyl;