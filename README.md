#   PterdactylPluginManager
Easily manage plugins a themes. Easily create your own plugins and themes using the custom API

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
pluginManager($response);
```