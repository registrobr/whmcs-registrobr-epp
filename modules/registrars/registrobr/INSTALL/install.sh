#!/bin/bash

WHMCSDIR="/tmp"

#find the whoisservers.php
OLDLIST=$WHMCSDIR/includes/whoisservers.php
NEWLIST=listservers.txt


#remove old .br entries
awk '!/.br/' $OLDLIST > tmplist.php

#add new .br entries
cat tmplist.php $NEWLIST > newlist.php

#generate new whoisservers.php
cp newlist.php $OLDLIST

#remove tmpfiles
rm tmplist.php
rm newlist.php

cp -r ../../* $WHMCSDIR/modules/registrars/
rm $WHMCSDIR/modules/registrars/registrobr/INSTALL/*
rmdir $WHMCSDIR/modules/registrars/registrobr/INSTALL

#colocar poll na crontab
