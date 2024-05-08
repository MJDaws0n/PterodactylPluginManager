<?php
$currentUrl = $_SERVER['REQUEST_URI'];
if(!explode('/', $currentUrl)[1] == 'addonapi'){
    exit();
}
if(isset(explode('/', $currentUrl)[2])){
    if(file_exists(dirname(__FILE__) . '/plugins/'.explode('/', $currentUrl)[2].'/api.php')){
        include(dirname(__FILE__) . '/plugins/'.explode('/', $currentUrl)[2].'/api.php');
    }
}