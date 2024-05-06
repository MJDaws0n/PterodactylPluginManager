<?php
global $settings;
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    echo "<script>const copyrightText = ".str_replace('{year}', date("Y"), json_encode($settings['copyright'])).";</script>";
    $file_path = dirname(__FILE__).'/pages.js';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $base64_content = base64_encode($file_content);
        $mime_type = mime_content_type($file_path);
        $data_url = 'data:' . $mime_type . ';base64,' . $base64_content;
        echo "<script src=\"$data_url\"></script>";
    } else {
        echo "Error: Pages JS file missing. $file_path";
    }
    if(explode('/', $currentUrl)[1] != 'admin'){
        $theme = json_decode(file_get_contents(dirname(__FILE__) . '/themes/'.$settings['theme'].'/main.ptero'), true);
        $links = '';
        foreach ($theme['non-admin']['default']['links'] as $link) {
            $links .= json_encode($link).',';
        }
        $links = substr_replace($links,'', -1);

        $styles = '';
        foreach ($theme['non-admin']['default']['styles'] as $link) {
            $styles .= json_encode(str_replace(array("\n", "\r"), '', file_get_contents(dirname(__FILE__) . '/themes/'.$settings['theme'] . $link)));
        }

        $scripts = '';
        foreach ($theme['non-admin']['default']['scripts'] as $link) {
            $scripts .= json_encode(str_replace(array("\n", "\r"), '', file_get_contents(dirname(__FILE__) . '/themes/'.$settings['theme'] . $link)));
        }
        
        ?>
        <script>
        if(typeof onAdminPage == 'undefined'){
            document.addEventListener('DOMContentLoaded', () => {
                <?php if(isset($theme['use_default']) && !$theme['use_default']){?>
                    // Remove all CSS
                    var cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
                
                    cssLinks.forEach(function(link) {
                        // Remove the <link> element
                        link.parentNode.removeChild(link);
                    });
                
                    var styleElements = document.querySelectorAll('style');
                    
                    styleElements.forEach(function(style) {
                        // Remove the <style> element
                        style.parentNode.removeChild(style);
                    });
                <?php }?>

                // Add new styles
                var links = [
                    <?php echo $links;?>
                ];
                var styles = [
                    <?php echo $styles;?>
                ];
                var scripts = [
                    <?php echo $scripts;?>
                ];

                const headStyles = document.createElement('style');
                headStyles.innerHTML = styles.join('');
                document.head.append(headStyles);

                const headScripts = document.createElement('script');
                headScripts.innerHTML = scripts.join('');
                document.head.append(headScripts);

                var tempContainer = document.createElement('div');
                tempContainer.innerHTML = links.join('');
                tempContainer.childNodes.forEach(function(node) {
                    if (node.tagName && node.tagName.toLowerCase() === 'script') {
                        var script = document.createElement('script');
                        script.textContent = node.innerText;
                        document.head.appendChild(script);
                    } else {
                        document.head.appendChild(node.cloneNode(true));
                    }
                });
            });
        }
        </script>
        <?php
    }
}