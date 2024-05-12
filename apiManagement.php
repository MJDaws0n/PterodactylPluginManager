<?php
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $currentUrl = $_SERVER['REQUEST_URI'];
    if(!explode('/', $currentUrl)[1] == 'addonapi'){
        exit();
    }
}