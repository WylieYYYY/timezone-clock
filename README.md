# Timezone clock
Timezone clock is a website setup for dual timezone clock with weather informations.
> **Please run setup.sh once with appid being {{OPENWEATHERMAP_APPID}} before submitting a pull request to protect your API key**

### Features:
- Compatible to legacy browsers;
- Scaled UI with browser window's size;
- Flexible JSON request proxy for centralised request to API source;
- Can be used with a free subscription of OpenWeatherMap;

### Setup
The website can be hosted on a PHP server (detects .html and .php as PHP files) or used on client side (private use only as there is no API key protection), it is compatible and will auto detect how it is used.  
Use *setup.sh* to setup. **For bash and apache on linux.**
> Modification will be needed for other shells, server hosting programs or operating systems.

Server should be pointing to the parent directory and index.html
Remove the following files before starting a public server.
- README.md
- setup.sh
- .gitignore
- .git
