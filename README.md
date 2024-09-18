#   PterdactylPluginManager
Easily manage plugins a themes. Easily create your own plugins and themes using the custom API

DOES NOT WORK PROPERLY YET DO USE

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
<<<<<<< HEAD
```
=======
```
>>>>>>> 216701fd809502736b5f43ddd5548a91ba9ac577
