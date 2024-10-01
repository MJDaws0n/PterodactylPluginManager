#   PterdactylPluginManager
Easily manage plugins and themes. Easily create your own plugins and themes using custom API

#   Install
Login to your server over SSH (or SFTP but this tutorial is for SSH)

##  Enter the panel directory
```sh
# Replace /var/www/pterodactyl with the location of your pterodactyl panel
cd /var/www/pterodactyl
```

##  Download the latest version
```sh
# Download latest release
curl -Lo addon.tar.gz https://github.com/MJDaws0n/PterodactylPluginManager/releases/latest/download/app.tar.gz
tar -xzvf addon.tar.gz
```
```sh
# Update permissions
chmod -R 755 PterodactylPluginManager
```

```sh
# If using NGINX or Apache (not on RHEL / Rocky Linux / AlmaLinux)
chown -R www-data:www-data /var/www/pterodactyl/*

# If using NGINX on RHEL / Rocky Linux / AlmaLinux
chown -R nginx:nginx /var/www/pterodactyl/*

# If using Apache on RHEL / Rocky Linux / AlmaLinux
chown -R apache:apache /var/www/pterodactyl/*
```

##  Activate
In `./public/index.php` just bellow
```php
if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}
```
Add this code
```php
/*
|--------------------------------------------------------------------------
| Register The Pterodactyl Plugin Manager
|--------------------------------------------------------------------------
|
*/
require __DIR__ . '/../PterodactylPluginManager/connector.php';
```

At the bottom comment out
```php
$response->send();
```

And add this
```php
$addon = new Net\MJDawson\AddonManager\Connector($response);
```

#   Deactivate
If something is not working, rather than <a href="#unistall">unistalling</a>, we recommend that you just deactivate the addon

Open  `./public/index.php` and comment out
```php
$addon = new Net\MJDawson\AddonManager\Connector($response);
```

Then uncomment
```php
$response->send();
```

If you still have issues try commenting out
```php
require __DIR__ . '/../PterodactylPluginManager/connector.php';
```

#   Unistall
If you are trying to debug an issue, then try <a href="#deactivate">deactivating</a> instead of uninstalling

##  Deactivate
Run the steps in <a href="#deactivate">deactivating</a>

##  Delete
Delete the `./PterodactylPluginManager` folder
###  Enter the panel directory
```sh
# Replace /var/www/pterodactyl with the location of your pterodactyl panel
cd /var/www/pterodactyl
```

### Delete addon folder
``` sh
rm -r ./PterodactylPluginManager
```

### Consider patch options
The default pterodactyl addon and any built in plugins do not make any changes to the code
that can cause any panel damage.

That being said, it still does make changes to the original code. If you want to remove all changes made by the addon, you should consider, taking a copy of your default pterodactyl
.env file and re-installing the panel using:
```sh
# Find the up to date commands at https://pterodactyl.io/panel/1.0/getting_started.html#download-files
curl -Lo panel.tar.gz https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz
tar -xzvf panel.tar.gz
chmod -R 755 storage/* bootstrap/cache/
```

After you have downloaded the up to date panel, add you .env file in and [set permissions](https://pterodactyl.io/panel/1.0/getting_started.html#set-permissions)

Your panel is now up to date, completely free from the addon manager and any changes it made to the code.

This is not necessary at all unless a plugin has modified the original code breaking your panel.
Plugins should not do this but unfortnuataly it might happen, so this is a good way to get around it.

#   Creating Plugins
We suggest first having a panel you can test with, and using the `Hello World` that is pre-installed with your panel (also in [here](https://github.com/MJDaws0n/PterodactylPluginManager/tree/main/plugins/Hello%20World))

<b>PLEASE NOTE THE PLUGIN FOLDER NAME MUST BE THE SAME AS THE PLUGIN NAME</b>

## Config
Your plugin must have a `main.ptero` file, it can look something like [this](https://github.com/MJDaws0n/PterodactylPluginManager/blob/main/plugins/Hello%20World/main.ptero).
### Config Options
- `name`*: The name of your plugin
- `description`*: A short description of your plugin
- `version`*: The version of your plugin (this must be updated as the auto cache rolling system uses the version to determine if any pages need re-caching)
- `author`*: The author of your plugin
- `entry`*: The entry point of your plugin (the file that will be run when the plugin is loaded)
- `url`: The url of your plugin (will be linked under the author's name)
- `last_updated`: When the plugin was last updated (Never actually used, but is supported and should be in a form like `21/09/2024`)

Any other values are supported and will not be impacted so if you want to build a plugin that relyies on another plugin, that value will be passed through the plugin loader and just ignored by the build in system.

##  Writing the code
The plugins API i very limited and I am not planning much on upgrading it much, only when I need it, anything should be able to implemented by yourself just perhaps not as easy as you may like.

To start just create a file that is the same name as your entry point, and then just start coding. The API is faily simple but as I said very limited.

The code must initialize the PluginApi class and then end by returning it. Without this the plugin will cause issues.

### Bundle Patches
Bundle Patches are an essential way the whole addon works. In basic terms it works by taking a string - the needle, finding it in the script and changing it to another string - the haystack. 

An easy example of this is the Copyright. 
The hastack is the existing code in the `bundle.js`
```JavaScript
return Object(o.useEffect)((()=>{t&&(document.title=t)}),[t]),i.a.createElement(s.a,{timeout:150,classNames:"fade",appear:!0,in:!0},i.a.createElement(i.a.Fragment,null,i.a.createElement(l,{className:r},n&&i.a.createElement(u,{byKey:n}),a),i.a.createElement(f,null,i.a.createElement(p,null,i.a.createElement(d,{rel:"noopener nofollow noreferrer",href:"https://pterodactyl.io",target:"_blank"},"Pterodactyl®")," © 2015 - ",(new Date).getFullYear()))))
```

That looks really confusing but is simple to get by searching using the chrome dev tools for simple words like `Pterodactyl` or `Copyright`. Unfortunataly, it's harder to read as there is few spaces and newlines as it's already 'compiled' by react.

The hastack for the copyright
```JavaScript
return Object(o.useEffect)((() => {
    t && (document.title = t)
}), [t]), i.a.createElement(s.a, {
    timeout: 150,
    classNames: "fade",
    appear: !0,
    in: !0
}, i.a.createElement(i.a.Fragment, null, i.a.createElement(l, {
    className: r
}, n && i.a.createElement(u, {
    byKey: n
}), a), i.a.createElement(f, null, i.a.createElement(p, { dangerouslySetInnerHTML: { __html: `{{COPYRIGHT}}` }}))))
```
Which is slightly nice to read, but still a bit confusing. The `{{COPYRIGHT}}` is the main copyright  string that we want to replace. The variables manager simply replaces this with the copyright stored in the config file we have.

The interesting part of this method is that we don't actually modify any files, we just edit the HTML of the main page. Doing this we can load a custom URL which when loaded we can load a custom script. This creates an issue with the cache, which is why use a cache rolling system where the script has different parameters based on the enabled plugins, their version, themes, their versions, and the addon version.

Unfortunately because of the way pterodactyl is made, there are many JS files it uses. In fact, there are 15, many are self-explanatory such as `auth` or `server~dashboard`, while others are more confusing such as `12.js`. Unfortunately, you have to just keep checking for the part of the code you want to modify until you find it. I usually it find it takes around 5 - 10 minutes to find one thing to change which is not ideal, but it's the best I can do with the current API. You can output a custom script that just modifies the HTML, which is nice, but I find it feels unprofessional and may open up vulnerabilities.

The [api](#api) section gives you all the functions you can use to modify the bundles. A good example of a working bundle in the a plugin is [Gravitar PFP](https://github.com/MJDaws0n/PterodactylPluginManager/blob/main/plugins/Gravitar%20PFP/main.php) where [needle](https://github.com/MJDaws0n/PterodactylPluginManager/blob/main/plugins/Gravitar%20PFP/patches/pfp/needle.js)
```JavaScript
return a.createElement("svg",{viewBox:"0 0 36 36",fill:"none",role:"img",xmlns:"http://www.w3.org/2000/svg",width:e.size,height:e.size},e.title&&a.createElement("title",null,e.name),a.createElement("mask",{id:"mask__beam",maskUnits:"userSpaceOnUse",x:0,y:0,width:36,height:36},a.createElement("rect",{width:36,height:36,rx:e.square?void 0:72,fill:"#FFFFFF"})),a.createElement("g",{mask:"url(#mask__beam)"},a.createElement("rect",{width:36,height:36,fill:t.backgroundColor}),a.createElement("rect",{x:"0",y:"0",width:36,height:36,transform:"translate("+t.wrapperTranslateX+" "+t.wrapperTranslateY+") rotate("+t.wrapperRotate+" 18 18) scale("+t.wrapperScale+")",fill:t.wrapperColor,rx:t.isCircle?36:6}),a.createElement("g",{transform:"translate("+t.faceTranslateX+" "+t.faceTranslateY+") rotate("+t.faceRotate+" 18 18)"},t.isMouthOpen?a.createElement("path",{d:"M15 "+(19+t.mouthSpread)+"c2 1 4 1 6 0",stroke:t.faceColor,fill:"none",strokeLinecap:"round"}):a.createElement("path",{d:"M13,"+(19+t.mouthSpread)+" a1,0.75 0 0,0 10,0",fill:t.faceColor}),a.createElement("rect",{x:14-t.eyeSpread,y:14,width:1.5,height:2,rx:1,stroke:"none",fill:t.faceColor}),a.createElement("rect",{x:20+t.eyeSpread,y:14,width:1.5,height:2,rx:1,stroke:"none",fill:t.faceColor}))))
```
Is replaced in the [haystack](https://github.com/MJDaws0n/PterodactylPluginManager/blob/main/plugins/Gravitar%20PFP/patches/pfp/haystack.js)
```JavaScript
return a.createElement("img",{width:e.size,height:e.size,src:net_mjdawson_gravitarpfp.gravitarURL,style:{borderRadius:'10%',}})
```
No sane person can understand the needle there, but that is the code that already exists that you want to remove. The haystack is the only thing we actually wrote, and if you take a look at it, it's farly simply. It returns (we need to return as that is what we replaced it with) and new image, with the width of e.size (the same as from the haystack), the height of e.size (the same as from the haystack), the src of the image which is a variable that has beend simply added as a standard variable in the main page, using
```php
$script->appendChild($parser->dom()->createTextNode("
    const net_mjdawson_gravitarpfp = {
        gravitarURL: '".htmlspecialchars($gravitarURL)."'
    };
"));
```
then settings the border radiuse to 10%. 

Although not everything can be this simple, it's really not that hard with lots of trial and error. TH most important things to remember is to make sure you only modify the code that has not been formatted as spaces and newlines are really easy to break your code.


### API
#### helloWorld
Name: `helloWorld`\
Class: `PluginsAPI` \
Description: `Returns "Hello World" string`\
Returns: `string`

#### getPage
Name: `getPage`\
Class: `PluginsAPI` \
Description: `Gathers and returns an array of request data including method, URI, query parameters, headers, and more.`\
Returns: `array`

#### addHtmlListner
Name: `addHtmlListner`\
Class: `PluginsAPI` \
Description: `Adds an HTML listener function with a specific priority.`\
Returns: `void`

#### listAllHTMLListners
Name: `listAllHTMLListners`\
Class: `PluginsAPI` \
Description: `Returns a list of all registered HTML listeners.`\
Returns: `array`

#### listAllPatches
Name: `listAllPatches`\
Class: `PluginsAPI` \
Description: `Returns all registered patches.`\
Returns: `array`

#### getUser
Name: `getUser`\
Class: `PluginsAPI` \
Description: `Returns the currently authenticated user or null if not authenticated.`\
Returns: `mixed`

#### vendorDashPatch
Name: `vendorDashPatch`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'vendors~dashboard~server' category.`\
Returns: `void`

#### vendorAuthDashPatch
Name: `vendorAuthDashPatch`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'vendors~auth~dashboard~server' category.`\
Returns: `void`

#### dashServerPatch
Name: `dashServerPatch`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'dashboard~server' category.`\
Returns: `void`

#### dashPatch
Name: `dashPatch`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'dashboard' category.`\
Returns: `void`

#### bundlePatch
Name: `bundlePatch`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'bundle' category.`\
Returns: `void`

#### serverPatcher
Name: `serverPatcher`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'server' category.`\
Returns: `void`

#### numPatch7
Name: `numPatch7`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '7' category.`\
Returns: `void`

#### numPatch8
Name: `numPatch8`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '8' category.`\
Returns: `void`

#### numPatch9
Name: `numPatch9`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '9' category.`\
Returns: `void`

#### numPatch10
Name: `numPatch10`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '10' category.`\
Returns: `void`

#### numPatch11
Name: `numPatch11`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '11' category.`\
Returns: `void`

#### numPatch12
Name: `numPatch12`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '12' category.`\
Returns: `void`

#### numPatch13
Name: `numPatch13`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '13' category.`\
Returns: `void`

#### numPatch14
Name: `numPatch14`\
Class: `PluginsAPI` \
Description: `Adds a patch to the '14' category.`\
Returns: `void`

#### authPatcher
Name: `authPatcher`\
Class: `PluginsAPI` \
Description: `Adds a patch to the 'auth' category.`\
Returns: `void`

#### get_uri_components
Name: `get_uri_components`\
Class: `PluginsAPI` \
Description: `Parses the current URL to get the URI components.`\
Returns: `array`

#### get_current_url
Name: `get_current_url`\
Class: `PluginsAPI` \
Description: `Retrieves the current URL.`\
Returns: `string`

#### __construct
Name: `__construct`\
Class: `HtmlParser` \
Description: `Constructor for the HTML parser. It replaces <p> tags with custom placeholders, creates a DOM document, and initializes an XPath object.`\
Returns: `void`

#### dom
Name: `dom`\
Class: `HtmlParser` \
Description: `Returns the DOM document.`\
Returns: `\DOMDocument`

#### xpath
Name: `xpath`\
Class: `HtmlParser` \
Description: `Returns the XPath object.`\
Returns: `\DOMXPath`

#### getHTML
Name: `getHTML`\
Class: `HtmlParser` \
Description: `Retrieves the HTML content and replaces custom placeholders back to <p> tags.`\
Returns: `string`

## Creating a theme
Tutorial comming soon. For now take a look at the [Pterodactyl Dark Theme](https://github.com/MJDaws0n/PterodactylPluginManager/tree/main/themes/Pterodactyl%C2%AE%20Dark) config.

REMEMBER THEME NAME MUST BE THE SAME AS THE FOLDER NAME