<?php
namespace Net\MJDawson\AddonManager\Core;

class Config {
    private static $settings = null;
    private static $settingsFile = null;

    public function __construct() {
        self::$settingsFile = dirname(__FILE__) . '/../adminSettings.json';

        // Load settings if not already loaded
        if (self::$settings === null) {
            if (file_exists(self::$settingsFile)) {
                $jsonContent = file_get_contents(self::$settingsFile);
                self::$settings = json_decode($jsonContent, true);
            } else {
                echo "Error. adminSettings.json does not exist.";
                self::$settings = []; // Fallback to an empty array if the file does not exist
            }
        }
    }
    public function getSettings() {
        return self::$settings;
    }
    public function updateSettings(array $newSettings) {
        // Merge new settings with existing ones
        self::$settings = array_merge(self::$settings, $newSettings);

        // Save the updated settings back to the file
        $jsonContent = json_encode(self::$settings, JSON_PRETTY_PRINT);
        if (file_put_contents(self::$settingsFile, $jsonContent) === false) {
            echo "Error. Could not save settings to adminSettings.json.";
        }
    }
}