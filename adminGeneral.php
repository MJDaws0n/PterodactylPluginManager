<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom:copyright'])){
    $newCopy = $_POST['custom:copyright'];
  
    $jsonFile = dirname(__FILE__) . '/adminSettings.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $settingsObject = json_decode($jsonContent, true);
    }
  
    $settingsObject['copyright'] = $newCopy;
    print_r($settingsObject);
    chmod($file, 0666);
    file_put_contents($jsonFile, json_encode($settingsObject));
    header('location: /admin/custom/general');
    exit();
}

// Code for replace
$replacement = " ";
if(explode('/', $currentUrl)[1] == 'admin'){
    $file_path = dirname(__FILE__).'/admin.js';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $base64_content = base64_encode($file_content);
        $mime_type = mime_content_type($file_path);
        $data_url = 'data:' . $mime_type . ';base64,' . $base64_content;
        $replacement = "<script src=\"$data_url\"></script>";
    } else {
        echo "Error: Admin file missing. $file_path";
    }
}

// Get the current domain of the website
$currentDomain = $_SERVER['HTTP_HOST'];

// Set the URL to ping
$url = "https://$currentDomain/admin/settings?get=true";

// Get all cookies that the current user has
$cookies = "";
foreach ($_COOKIE as $name => $value) {
    $cookies .= $name . '=' . $value . '; ';
}

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, $cookies);

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Save the response to a variable
    $pingResponse = $response;
    
    // Output the response
    echo str_replace($replacement, '', $pingResponse);
}

curl_close($ch);

$jsonFile = dirname(__FILE__) . '/adminSettings.json';

// Check if the file exists
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $settingsObject = json_decode($jsonContent, true);
} else {
    echo "File not found: $jsonFile";
}
?>

<script>
    document.addEventListener('sideBarLoaded', function(event) {
        var spanElements = document.querySelectorAll('span');

        spanElements.forEach(function(spanElement) {
        if (spanElement.textContent.trim() == "General Settings") {
            spanElement.parentElement.parentElement.classList.add('active');
            spanElement.parentElement.parentElement.classList.remove('1');
        }
        if (spanElement.textContent.trim() == "Settings") {
            spanElement.parentElement.parentElement.classList.add('1');
            spanElement.parentElement.parentElement.classList.remove('active');
        }
    });
    
    var title = document.querySelector('.content-wrapper .content-header h1');
    title.innerHTML = `Pterodactyl Custom Options<small>Condifgure Pterodactyl to your (actual) liking.</small>`;
    
    var directory = document.querySelector('.content-wrapper .breadcrumb li');
    directory.innerHTML = `<a href="/admin">Admin  >  Custom</a>`;
    var directory2 = document.querySelector('.content-wrapper .breadcrumb li.active');
    directory2.textContent = `Settings`;
    
    // Tabs
    var tabs = document.querySelector('.content-wrapper .nav-tabs-custom.nav-tabs-floating .nav.nav-tabs');
    Array.from(tabs.children).forEach((tab)=>{
        if(tab.firstChild.textContent == 'Mail' || tab.firstChild.textContent == 'Advanced'){
            tab.remove();
        }
    });
    
    // Settings
    // Remove other options
    var settingsOptions = document.querySelectorAll('.content-wrapper .content .box-body .form-group.col-md-4');
    var i = 0;
    
    settingsOptions.forEach((option)=>{
        if(i == 0){
            option.firstElementChild.textContent = 'Copyright Text';
            option.children[1].children[0].name = 'custom:copyright'
            option.children[1].children[0].value = <?php echo json_encode($settingsObject['copyright']);?>;
            option.children[1].children[1].children[0].textContent = 'The Copyright to display on all of the pages on the panel and on the footer of the emails pterodactyl sends.';
        } else{
            option.remove();
        }
        i++;
    });
    
    // Make the saving work
    var settingsOptions = document.querySelector('.content-wrapper .content .col-xs-12 .box form');
    settingsOptions.action = '/admin/custom/general';
});
</script>