<?
# Copyright (c) 2012-2013, AllWorldIT and (c) 2013, NIC.br (R)
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# This module is a fork from whmcs-coza-epp (http://devlabs.linuxassist.net/projects/whmcs-coza-epp)
# whmcs-coza-epp developed by Nigel Kukard (nkukard@lbsd.net)


# Official Website for whmcs-registrobr-epp
# https://github.com/registrobr/whmcs-registrobr-epp


# More information on NIC.br(R) domain registration services, Registro.br(TM), can be found at http://registro.br
# Information for registrars available at http://registro.br/provedor/epp

# NIC.br(R) is a not-for-profit organization dedicated to domain registrations and fostering of the Internet in Brazil. No WHMCS services of any kind are available from NIC.br(R).


# WHMCS hosting, theming, module development, payment gateway 
# integration, customizations and consulting all available from 
# http://allworldit.com

# This cron job should only be used with versions up to 5.0.x ; 5.1.x will work with this file although it's not necessary, and 5.2.x and up won't work with this file
# For 5.1.x and later versions, use WHMCS crons/domainsync.php instead

# Constants, functions and registrar functions we need
require_once dirname(__FILE__) . '/../../../dbconnect.php';
require_once dirname(__FILE__) . '/../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../includes/registrarfunctions.php';

# Include EPP stuff we need
require_once dirname(__FILE__) . '/registrobr.php';

# We need pear for the error handling
require_once "PEAR.php";



# Get an EPP connection
$client = _registrobr_Client();
if (PEAR::isError($client)) {
    $values["error"] = _registrobr_lang("syncconnerror").$client;
    logModuleCall("registrobr",$values["error"]);
    return $values ;
}

#For every sync, also do a poll queue clean
_registrobr_Poll($client);

echo(_registrobr_lang("syncreport"));
echo(_registrobr_lang("syncreportdashes"));

# Pull list of domains which are registered using this module
$queryresult = mysql_query("SELECT domain FROM tbldomains WHERE registrar = 'registrobr'");
while($data = mysql_fetch_array($queryresult)) {
	$domains['domain'] = trim(strtolower($data['domain']));
    $domains['domainid'] = trim($data['domainid']);
}

# Loop with each one
foreach($domains as $domain) {	
	sleep(1);

	# Query domain
     $domainvalues=_registrobr_SyncRequest($client,$domain);
     if (!empty($domainvalues['error'])) {
        echo $domainvalues['error']."\n";
        continue;
     }
     
     
    # This is the template we are going to use below for our updates
	$querytemplate = "UPDATE tbldomains SET status = '%s', registrationdate = '%s', expirydate = '%s', nextduedate = '%s' WHERE domain = '%s'";

	# Check status and update
	if ($domainvalues['active'] == TRUE) {
		mysql_query(sprintf($querytemplate,"Active",
				mysql_real_escape_string($domainvalues['registrationdate']),
				mysql_real_escape_string($domainvalues['expirydate']),
				mysql_real_escape_string($domainvalues['expirydate']),
				mysql_real_escape_string($domain['domain'])
		));
		echo "Updated $domain expiry to $nextduedate\n";

    } elseif ($domainvalues['expired'] == TRUE) {
                mysql_query(sprintf($querytemplate,"Expired",
                mysql_real_escape_string($domainvalues['registrationdate']),
                mysql_real_escape_string($domainvalues['expirydate']),
                mysql_real_escape_string($domainvalues['expirydate']),
                mysql_real_escape_string($domain['domain'])
            ));
            echo "Domain $domain is EXPIRED (Registration: $createdate, Expiry: $nextduedate)\n";
                                }
        else {
                mysql_query(sprintf($querytemplate,"Pending",
                mysql_real_escape_string($domainvalues['registrationdate']),
                mysql_real_escape_string($domainvalues['expirydate']),
                mysql_real_escape_string($domainvalues['expirydate']),
                mysql_real_escape_string($domain['domain'])
		));
		echo _registrobr_lang("Domain").$domain._registrobr_lang("is")._registrobr_lang("domainstatusserverhold")._registrobr_lang("registration").$createdate._registrobr_lang("exp, Expiry: $nextduedate)\n");

	} 
}
                             
                               


?>
