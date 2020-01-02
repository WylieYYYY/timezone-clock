# Timezone Clock
Timezone Clock is a website setup for dual timezone clock with weather informations.
> **Please run setup.sh once with no appid before distributing to protect your appid**

#### Screenshots
![Example Screenshot](screenshot.png "Main Screen")
![Example Menu Screenshot](screenshot-menu.png "Menu Options")
### Features:
- Compatible with legacy browsers;
- Scaled UI with browser window's size;
- Flexible JSON request proxy for centralised request to API source;
- Can be used with a free subscription of OpenWeatherMap;

### Setup
The website can be hosted on a PHP server (detects .html and .php as PHP files) or used on client side (private use only as there is no appid protection), it is compatible and will auto detect how it is used.  
Not all browsers allow client side because of CORS policy (such as Firefox) but can still be hosted locally and used. Also, there is no rate limit on client side, request limit imposed by OpenWeatherMap may be
exceeded and causes error.  
> Hosting locally with a PHP server is encouraged where possible due to above reasons.

Use *setup.sh* to setup. **For Bash and Apache on Linux.**
> Modification will be needed for other shells, server hosting programs or operating systems.

Server should be pointing to the parent directory and index.html
Remove the following files before starting a public server:
- .git
- .gitignore
- README.md
- screenshot.png
- screenshot-menu.png
- setup.sh

#### Setup script summary
For for other shells, server hosting programs or operating systems, here is a summary of the setup script, variables are in square brackets:
1. Read in user's appid
2. If no appid is supplied, use `{{OPENWEATHERMAP_APPID}}` as user's appid
3. Replace all occurence of `target_key = "&units=metric&appid=[wildcard match anything]";` with `target_key = "&units=metric&appid={{OPENWEATHERMAP_APPID}}";` in `scripts/apiproxy.php`
4. Replace all occurence of `{{OPENWEATHERMAP_APPID}}` in `scripts/apiproxy.php` with user's appid
5. Remove all JSON files from the `scripts` folder
6. Change all files' permission to `664 (-rw-rw-r--)`, directories' permission to `775 (drwxrwxr-x)` (See [Wikipedia](https://en.wikipedia.org/wiki/File_system_permissions#Traditional_Unix_permissions) for notation explanation)
7. Change permission of `setup.sh` to `775 (-rwxrwxr-x)` for future execution
8. Detect the user group of web server (For Apache on Linux, it is `http` by default and found after `Group` keyword in `/etc/httpd/conf/httpd.conf`)
9. Change owner to `[self username]:[web server group]` for all items in the repository directory and parent directories up to the one before root (For example, `wylie` in default Linux Apache environment with repository of path `/home/repos/timezone-clock`; `repos`, `timezone-clock` and everything in `timezone-clock` will have the owner of `wylie:http`)
10. Change the permission of the repository directory and parent directories up to the one before root to `775 (drwxrwxr-x)`
