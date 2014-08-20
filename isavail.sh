#!/bin/bash

fqdn=$1
docroot=$2
whmcsdir=$3


: ${fqdn:=$(hostname --long)}
: ${docroot:="/var/www"}
: ${whmcsdir:="$docroot/whmcs"}






if [[ $EUID -ne 0 ]]; then
echo "This script must be run as root" 1>&2
exit 1
fi

if [ ! -d "$docroot" ]; then
echo "Document root directory $docroot doesn't exist"
exit 1
fi

if [ ! -d "$whmcsdir" ]; then
echo "WHMCS directory $whmcsdir doesn't exist"
exit 1
fi


httpuser=$(ps axho user,comm|grep -E "httpd|apache"|uniq|grep -v "root"|awk 'END {if ($1) print $1}')


if ! id -u $httpuser > "/dev/null"; then
echo "Couldn't locate HTTP server user id, please edit install.sh"
exit 1
fi


httpgroup=$(id -Gn $httpuser | cut -f1)


#location of whoisservers.php
oldlist=$whmcsdir/includes/whoisservers.php

if [ ! -w $oldlist ]; then
echo "Couldn't config WHMCS domain availabity, please check $whmcsdir"
exit 1
fi

#location of PHP binary
php=`whereis -b php | cut -f2 -d " "`


if [ ! -x $php ]; then
echo "Couldn't locate PHP binary, please edit install.sh"
exit 1
fi

cd ./INSTALL

#replace .br domains with contents of listservers.txt
list="listservers.txt"
domain="localhost"
sed -e "s,$domain,$fqdn,g" listservers.txt > listserversnew.txt

mv listserversnew.txt listservers.txt

#remove old .br entries
awk '!/.br\|/' $oldlist > tmplist.php

#add new .br entries into whoissservers.php
cat tmplist.php $list > $oldlist

install -m 640 -o $httpuser -g $httpgroup Avail.php $docroot/
install -m 640 -o $httpuser -g $httpgroup brdomaincheck.php $docroot/


#remove tmpfile
rm tmplist.php

