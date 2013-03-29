#!/bin/bash

whmcsdir=$1
crontabfile=$2
whmcswebdomain=$3

: ${whmcsdir:="/var/www/whmcs"}
: ${crontabfile:="/var/spool/cron/crontabs/root"}
: ${whmcswebdomain:=$(hostname --long)}



#location of whoisservers.php
oldlist=$whmcsdir/includes/whoisservers.php

#replace .br domains with contents of listservers.txt
list="listservers.txt"
domain="localhost"
sed -e "s,$domain,$whmcswebdomain,g" listservers.txt > listserversnew.txt

mv listserversnew.txt listservers.txt

#remove old .br entries
awk '!/.br|/' $oldlist > tmplist.php

#add new .br entries
cat tmplist.php $list > newlist.php

#generate new whoisservers.php
cp newlist.php $oldlist

#remove tmpfiles
rm tmplist.php
rm newlist.php

cp Avail.php  brdomaincheck.php $whmcsdir/
cp -r registrobr  $whmcsdir/modules/registrars/

#add poll process to crontab
poll="$whmcsdir/modules/registrars/registrobr/registrobrpoll.php"
path="PATH"
sed -e "s,$path,$poll,g" crontab.txt > crontabnew.txt

#if domain sync is available, add it to crontab as well


cat $crontabfile crontabnew.txt > crontabtmp.txt
cp crontabtmp.txt $crontabfile

rm crontabnew.txt
rm crontabtmp.txt 
