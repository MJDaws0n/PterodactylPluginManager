#   PterdactylPluginManager
Easily manage plugins and themes. Easily create your own plugins and themes using custom API

#   Install
Add this as a folder in ./pterodactyl/

Add this code to ./pterodactly/public/index.php just bellow define('LARAVEL_START', microtime(true));

```php
/*
|--------------------------------------------------------------------------
| Register The Pterodactyl Plugin Manager
|--------------------------------------------------------------------------
|
*/
require __DIR__ . '/../PterodactylPluginManager/connector.php';
```

Replace
```php
$response->send();
```

With
```php
$addon = new Net\MJDawson\AddonManager\Connector($response);
```

Finally update file permissions to fix any possible errors when creating files
```sh
chmod -R 755 /var/www/pterodactyl/PterodactylPluginManager
```