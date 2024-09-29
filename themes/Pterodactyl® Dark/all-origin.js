function updatePageStyles(){
    var currentUri = window.location.pathname;
    document.body.classList.remove("server_page");
    document.body.classList.remove("home_page");
    document.body.classList.remove("auth_page");

    document.body.classList.remove("account_page");
    document.body.classList.remove("account_account");
    document.body.classList.remove("account_api");
    document.body.classList.remove("account_ssh");
    document.body.classList.remove("account_activity");

    document.body.classList.remove("server_console");
    document.body.classList.remove("server_files");
    document.body.classList.remove("server_files_edit");
    document.body.classList.remove("server_databases");
    document.body.classList.remove("server_schedules");
    document.body.classList.remove("server_users");
    document.body.classList.remove("server_backups");
    document.body.classList.remove("server_network");
    document.body.classList.remove("server_startup");
    document.body.classList.remove("server_settings");
    document.body.classList.remove("server_activity");

    /* To ensure it works properly with the subdomains plugin */
    document.body.classList.remove("server_subdomains");

    if (currentUri.startsWith("/server/")) {
        document.body.classList.add("server_page");
        if(currentUri.includes('/files')){
            if(currentUri.includes('/edit') || currentUri.includes('/new')){
                document.body.classList.add("server_files_edit");
            }
            document.body.classList.add("server_files");
        } else if(currentUri.includes('/databases')){
            document.body.classList.add("server_databases");
        } else if(currentUri.includes('/schedules')){
            document.body.classList.add("server_schedules");
        } else if(currentUri.includes('/users')){
            document.body.classList.add("server_users");
        } else if(currentUri.includes('/backups')){
            document.body.classList.add("server_backups");
        } else if(currentUri.includes('/network')){
            document.body.classList.add("server_network");
        } else if(currentUri.includes('/startup')){
            document.body.classList.add("server_startup");
        } else if(currentUri.includes('/settings')){
            document.body.classList.add("server_settings");
        } else if(currentUri.includes('/activity')){
            document.body.classList.add("server_activity");
        } else if(currentUri.includes('/subdomains')){
            document.body.classList.add("server_subdomains");
        } else{
            document.body.classList.add("server_console");
        }
    } else if (currentUri == "/" || !window.location.origin.includes('/')) {
        document.body.classList.add("home_page");
    } else if (currentUri.startsWith("/auth/login")) {
        document.body.classList.add("auth_page");
    } else if (currentUri.startsWith("/account")) {
        document.body.classList.add("account_page");
        if(currentUri.includes('/api')){
            document.body.classList.add("account_api");
        } else if(currentUri.includes('/ssh')){
            document.body.classList.add("account_ssh");
        } else if(currentUri.includes('/activity')){
            document.body.classList.add("account_activity");
        } else {
            document.body.classList.add("account_account");
        }
    }
}

document.addEventListener('pageChanged', ()=>{
    updatePageStyles();
})