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

# Rename the folder
mv PterodactylPluginManager-main PterodactylPluginManager

# Update permissions
chmod -R 755 PterodactylPluginManager
```

Add this code to `./public/index.php` just bellow `define('LARAVEL_START', microtime(true))`;

```php
/*
|--------------------------------------------------------------------------
| Register The Pterodactyl Plugin Manager
|--------------------------------------------------------------------------
|
*/
require __DIR__ . '/../PterodactylPluginManager/connector.php';
```

At the bottom replace
```php
$response->send();
```

With
```php
$addon = new Net\MJDawson\AddonManager\Connector($response);
```