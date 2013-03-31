#!/bin/bash

whmcsdir=$1
whmcscrons=$2
whmcswebdomain=$3

: ${whmcsdir:="/var/www/whmcs"}
: ${whmcscrons="/var/www/whmcs/crons"}
: ${whmcswebdomain:=$(hostname --long)}



if [[ $EUID -ne 0 ]]; then
echo "This script must be run as root" 1>&2
exit 1
fi

if [ ! -d "$whmcsdir" ]; then
echo "WHMCS directory $whmcsdir doesn't exist"
exit 1
fi

if [ ! -d "$whmcscrons" ]; then
echo "WHMCS crons directory $whmcscrons doesn't exist"
exit 1
fi


#location of whoisservers.php
oldlist=$whmcsdir/includes/whoisservers.php

if [ -w $oldlist]
else
echo "Couldn't config WHMCS domain availabity, please check $whmcsdir"
exit 1
fi


#replace .br domains with contents of listservers.txt
list="listservers.txt"
domain="localhost"
sed -e "s,$domain,$whmcswebdomain,g" listservers.txt > listserversnew.txt

mv listserversnew.txt listservers.txt

#remove old .br entries
awk '!/.br|/' $oldlist > tmplist.php

#add new .br entries into whoissservers.php
cat tmplist.php $list > $oldlist

cp Avail.php  brdomaincheck.php $whmcsdir/
cp -r registrobr  $whmcsdir/modules/registrars/
mv $whmcsdir/modules/registrars/registrobrpoll.php $whmcscrons

#add poll process to crontab
poll="$whmcscrons/registrobrpoll.php"
crontab -l 2>/dev/null > crontabtmp.txt

grep -q -c "registrobrpoll" crontabtmp.txt
if [ $? eq 0 ]
then
$minute= $(RANDOM%60)
printf "$minute * * * * $poll" >> crontabtmp.txt
fi

#if domain sync is available, add it to crontab as well
if [ -e "$whmcscrons/domainsync.php"]
    grep -q -c "domainsync" crontabtmp.txt
    if [ $? eq 0 ]
    then
    $minute= $(RANDOM%60)
    printf "$minute */4 * * * $whmcscrons/domainsync.php" >> crontabtmp.txt
    fi
fi

crontab crontabtmp.txt

#remove tmpfiles
rm tmplist.php
rm crontabtmp.txt 
