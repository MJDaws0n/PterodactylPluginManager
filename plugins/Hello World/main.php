<?php
// Hello World
// The first ever addon on Pterodactyl addon
// By MJDawson

// You must start by including the the Addon API
use Net\MJDawson\AddonManager\Core\PluginsAPI;

// Initialize the object
// This is used throughout the entire script, it connects all other files together
$pterodactyl = new PluginsAPI;

// To get a list of all functions, you can open the /core/PluginsAPI.php file inside the pterodactyl addon folder
// This is the example function that just returns "Hello World"
$pterodactyl->helloWorld(); // Returns "Hello World"

// Looks like this in the backend
// public function helloWorld() : String{
//     return "Hello World";
// }


// If you open the whole pterocatyl addon folder in your IDE you should be
// able to get correct auto-completion and also may be able to see all function options
// It works in vscode with a PHP extension installed, but try your own thing

// Must end with returning your pterodactyl variable, this is required to allow the addon to
// use HTML listeners (not present in this code, but still must be returned. I't a very powerful feature)
return $pterodactyl;