<?php
namespace Net\MJDawson\AddonManager\Core;

class Installer {
    public function install(){
        $this->installAdmin();
    }
    private function installAdmin(){
        $code = "/*\n" .
                "|--------------------------------------------------------------------------\n" .
                "| Custom AddonSettings\n" .
                "|--------------------------------------------------------------------------\n" .
                "|\n" .
                "| Endpoint: /admin/addonSettings\n" .
                "|\n" .
                "*/\n" .
                "Route::group(['prefix' => 'addonSettings'], function () {\n" .
                "    Route::get('/');\n" .
                "    Route::get('/plugins');\n" .
                "    Route::get('/themes');\n\n" .
                "    Route::post('/');\n".
                "    Route::post('/plugins');\n".
                "    Route::post('/themes');   \n".         
                "});\n";
        // Check that admin is not already installed
        if (!is_file(dirname(__FILE__).'/../../routes/admin.php')) {
            $this->failedToInstallAddon();
        }
        $contents = file_get_contents(dirname(__FILE__).'/../../routes/admin.php');
        if(!str_contains($contents, "Endpoint: /admin/addonSettings")){
            // Needs installing
            $newData = $contents."\n\n".$code;
            file_put_contents(dirname(__FILE__).'/../../routes/admin.php', $newData);

            // Check that it installed successful
            $contents = file_get_contents(dirname(__FILE__).'/../../routes/admin.php');
            if(!str_contains($contents, "Endpoint: /admin/addonSettings")){
                // Failed to install
                $this->failedToInstallAddon();
            }
        }
    }
    private function failedToInstallAddon(){
        // Handle the failure to install the addon
        echo "Failed to install addon.";
        exit();
    }
}