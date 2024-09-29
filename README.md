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