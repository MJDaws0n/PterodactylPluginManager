
document.addEventListener('DOMContentLoaded', ()=>{
    const sidebar = document.getElementsByClassName('sidebar-menu')[0];

    const panelCustomisationHeader = document.createElement('li');
    panelCustomisationHeader.classList.add("header");
    panelCustomisationHeader.textContent = 'CUSTOM OPTIONS';
    sidebar.append(panelCustomisationHeader);

    const generalSettings = document.createElement('li');
    generalSettings.classList.add("1");
    generalSettings.innerHTML = `<a href="https://panel.webworkshub.online/admin/custom/general"><i class="fa fa-wrench"></i><span>General Settings</span></a>`;
    sidebar.append(generalSettings);

    const themeSettings = document.createElement('li');
    themeSettings.classList.add("1");
    themeSettings.innerHTML = `<a href="https://panel.webworkshub.online/admin/custom/themes"><i class="fa fa-edit"></i><span>Themes</span></a>`;
    sidebar.append(themeSettings);

    const pluginSettings = document.createElement('li');
    pluginSettings.classList.add("1");
    pluginSettings.innerHTML = `<a href="https://panel.webworkshub.online/admin/custom/plugins"><i class="fa fa-gamepad"></i><span>Plugins</span></a>`;
    sidebar.append(pluginSettings);
  
    document.dispatchEvent(new Event('sideBarLoaded'));
  
    if (window.location.pathname.startsWith('/admin')) {
        var regex = /\/admin[\/?]+$/g;
        if (regex.test(window.location.pathname+'/')){
            const newInfo = document.createElement('div');
            document.querySelector('.content').children[1].querySelector('[class^="col-xs-"] .box-body').append(newInfo);
            newInfo.innerHTML = 'You are running PterodactylPluginManager version <code>'+addonVersion+'</code>.';
            if(upToDate){
                newInfo.innerHTML += 'Your addon is up to date!';
            } else{
                newInfo.innerHTML += 'Your addon is not up to date!';
            }
        }
    }
})
