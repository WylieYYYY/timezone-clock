#!/bin/bash
repos_dir="$(dirname "$(readlink -f "$0")")"
httpd_group="$(awk '/Group / { print $2 }' /etc/httpd/conf/httpd.conf)"
rm -f 'scripts/apijson.sqlite3'
cat <<EOS | sqlite3 'scripts/apijson.sqlite3'
CREATE TABLE Response(
	Location    TEXT PRIMARY KEY NOT NULL,
	Json        TEXT             NOT NULL,
	RequestTime INTEGER          NOT NULL
);
EOS
find "$repos_dir" -type f -exec chmod 664 {} \;
find "$repos_dir" -type d -exec chmod 775 {} \;
chmod +x "$repos_dir/setup.sh"
sudo chown -R "$(id -un)":"$httpd_group" "$repos_dir"
while [ "$(echo "$repos_dir" | tr -cd '/' | wc -c)" -gt 1 ]; do
	sudo chown "$(id -un)":"$httpd_group" "$repos_dir"
	chmod 775 "$repos_dir"
	repos_dir="$(dirname "$repos_dir")"
done
exit
