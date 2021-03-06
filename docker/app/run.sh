#!/bin/bash
sleep 10
HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var

php composer.phar install
php bin/console doctrine:migrations:migrate
php bin/console fixtures:load
php bin/console fos:elastica:populate

service apache2 start
service varnish start


tail -f /var/log/apache2/project_access.log