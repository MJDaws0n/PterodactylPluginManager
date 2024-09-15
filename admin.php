<?php
namespace Pterodactyl\Http;

use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Middleware\TrustProxies;
use Pterodactyl\Http\Middleware\TrimStrings;
use Illuminate\Session\Middleware\StartSession;
use Pterodactyl\Http\Middleware\EncryptCookies;
use Pterodactyl\Http\Middleware\Api\IsValidJson;
use Pterodactyl\Http\Middleware\VerifyCsrfToken;
use Pterodactyl\Http\Middleware\VerifyReCaptcha;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Pterodactyl\Http\Middleware\LanguageMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Pterodactyl\Http\Middleware\Activity\TrackAPIKey;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Pterodactyl\Http\Middleware\MaintenanceMiddleware;
use Pterodactyl\Http\Middleware\EnsureStatefulRequests;
use Pterodactyl\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Pterodactyl\Http\Middleware\Api\AuthenticateIPAccess;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Pterodactyl\Http\Middleware\Api\Daemon\DaemonAuthenticate;
use Pterodactyl\Http\Middleware\Api\Client\RequireClientApiKey;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Pterodactyl\Http\Middleware\Api\Client\SubstituteClientBindings;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Pterodactyl\Http\Middleware\Api\Application\AuthenticateApplicationUser;

$currentUrl = get_uri_components();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom:copyright'])){
    $newCopy = $_POST['custom:copyright'];

    $jsonFile = dirname(__FILE__) . '/adminSettings.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $settingsObject = json_decode($jsonContent, true);
    }

    $settingsObject['copyright'] = $newCopy;
    chmod($file, 0666);
    file_put_contents($jsonFile, json_encode($settingsObject));
    header('location: /admin/addonSettings');
    exit();
}

function editAdmin($dom, $xpath){
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        if(isset($currentUrl[1]) && $currentUrl[1] == 'addonSettings'){
            addHtmlListner(function($HTML, $status) {
                if(file_exists(dirname(__FILE__).'/pages/addonSettings.html')){
                    $site = file_get_contents(dirname(__FILE__).'/pages/addonSettings.html');
                    $site = str_replace('{{CRSF_TOKEN}}', strval(session()->token()), $site);
                    $site = str_replace('{{APP_URL}}', strval($_ENV['APP_URL']), $site);
                    $site = str_replace('{{GRAVATAR_URL}}', strval('https://www.gravatar.com/avatar/' . md5(trim(strtolower(auth()->user()['email']))) . '?s=160'), $site);
                    $site = str_replace('{{USER_NAME}}', strval(auth()->user()['name_first'] . ' ' . auth()->user()['name_last']), $site);
                    return [$site, 200];
                }

                // Add the listener
    
                if(file_exists(dirname(__FILE__).'/pages/error500.html')){
                    return [file_get_contents(dirname(__FILE__).'/pages/error500.html'), 500];
                }
                return ["Error 500. Failed to load both the requested page and error 500 page.", 500];
            });
        }
    }
    
}