#!/bin/bash

whmcsdir="/tmp"
crontabfile="/var/spool/cron/crontabs/root"
whmcswebdomain="parcelafacil.com.br"

#find the whoisservers.php
oldlist=$whmcsdir/includes/whoisservers.php

#replace domain in the listservers.txt
list="listservers.txt"
domain="localhost"
sed -e "s,$domain,$whmcswebdomain,g" listservers.txt > listserversnew.txt

mv listserversnew.txt listservers.txt

#remove old .br entries
awk '!/.br/' $oldlist > tmplist.php

#add new .br entries
cat tmplist.php $list > newlist.php

#generate new whoisservers.php
cp newlist.php $oldlist

#remove tmpfiles
rm tmplist.php
rm newlist.php

cp Avail.php  brdomaincheck.php $whmcsdir/
cp -r ../../* $whmcsdir/modules/registrars/
rm $whmcsdir/modules/registrars/registrobr/INSTALL/*
rmdir $whmcsdir/modules/registrars/registrobr/INSTALL

#colocar poll na crontab
sync="$whmcsdir/modules/registrars/registrobr/registrobrsync.php"
path="PATH"
sed -e "s,$path,$sync,g" crontab.txt > crontabnew.txt

cat $crontabfile crontabnew.txt > crontabtmp.txt
cp crontabtmp.txt $crontabfile

rm crontabnew.txt
rm crontabtmp.txt 
