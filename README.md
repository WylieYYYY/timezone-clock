# Timezone Clock
Timezone Clock is a website setup for dual timezone clock with weather informations compatible 
with legacy browsers.

### Screenshots
![Example Screenshot](screenshot.png "Main Screen")
![Example Menu Screenshot](screenshot-menu.png "Menu Options")
### Features:
- Compatible with legacy browsers;
- Scaled UI with browser window's size;
- Flexible JSON request proxy for centralised request to API source;
- Can be used with a free subscription of OpenWeatherMap;

### Setup
The website can be hosted on a PHP server (detects .html and .php as PHP files) or used on client side (no setup required), it is compatible and will auto detect how it is used.
However, there is no rate limit on client side, request limit imposed by OpenWeatherMap may be
exceeded and causes error, but as long as you limit the times you refresh or change location, client side will be fine.

#### Setup instructions for hosting
Use *setup.sh* to setup. **For Bash, Apache and SQLite3 on Linux.**
> Modification will be needed for other shells, server hosting programs or operating systems.

Create `scripts/php.ini` file with `OPENWEATHERMAP_APPID=[your id here]` in it.  
Server should be pointing to the parent directory and index.html, **php.ini file must have restricted access.**
Remove the following files before starting a public server:
- .git
- .gitignore
- README.md
- screenshot.png
- screenshot-menu.png
- setup.sh

#### Setup script summary
For for other shells, server hosting programs or operating systems, here is a summary of the setup script, variables are in square brackets:
1. Remove `scripts/apijson.sqlite3`
2. Create SQLite3 database `scripts/apijson.sqlite3` with table named `Response`, with columns `Location TEXT PRIMARY KEY NOT NULL`, `Json TEXT NOT NULL` and `RequestTime INTEGER NOT NULL`
3. Change all files' permission to `664 (-rw-rw-r--)`, directories' permission to `775 (drwxrwxr-x)` (See [Wikipedia](https://en.wikipedia.org/wiki/File_system_permissions#Traditional_Unix_permissions) for notation explanation)
4. Change permission of `setup.sh` to `775 (-rwxrwxr-x)` for future execution
5. Detect the user group of web server (For Apache on Linux, it is `http` by default and found after `Group` keyword in `/etc/httpd/conf/httpd.conf`)
6. Change owner to `[self username]:[web server group]` for all items in the repository directory and parent directories up to the one before root (For example, `wylie` in default Linux Apache environment with repository of path `/home/repos/timezone-clock`; `repos`, `timezone-clock` and everything in `timezone-clock` will have the owner of `wylie:http`)
7. Change the permission of the repository directory and parent directories up to the one before root to `775 (drwxrwxr-x)`
