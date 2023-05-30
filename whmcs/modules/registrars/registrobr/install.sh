#!/bin/sh

#  install.sh
#  
#
#  (C) NIC.br 2023
#  
if [ "$(id -u)" -ne 0 ]; then echo "Please run as root." >&2; exit 1; fi

php whoisjson.php >> ../../../resources/domains/whois.json

cp additionalfields.php ../../../resources/domains/




