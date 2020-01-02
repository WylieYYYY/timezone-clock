#!/bin/bash
repos_dir="$(dirname "$(readlink -f "$0")")"
httpd_group="$(awk '/Group / { print $2 }' /etc/httpd/conf/httpd.conf)"
read -srp 'OpenWeatherMap application ID (Entered string will not be visible): ' appid
[ -z "$appid" ] && appid='{{OPENWEATHERMAP_APPID}}'
echo
sed -i 's/target_key = "&units=metric&appid=.*";/target_key = "\&units=metric\&appid={{OPENWEATHERMAP_APPID}}";/g' "$repos_dir/scripts/apiproxy.php"
sed -i "s/{{OPENWEATHERMAP_APPID}}/$appid/g" "$repos_dir/scripts/apiproxy.php"
rm -f "$repos_dir/scripts/"*.json
find "$repos_dir" -type f -exec chmod 664 {} \;
find "$repos_dir" -type d -exec chmod 775 {} \;
chmod +x "$repos_dir/setup.sh"
chown -R "$(id -un)":"$httpd_group" "$repos_dir"
while [ "$(echo "$repos_dir" | tr -cd '/' | wc -c)" -gt 1 ]; do
	chown "$(id -un)":"$httpd_group" "$repos_dir"
	chmod 775 "$repos_dir"
	repos_dir="$(dirname "$repos_dir")"
done
exit
