<?php
# Copyright (c) 2012, AllWorldIT
#
# Select portions Copyright (c) 2013, NIC.br (R)
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

# This module is a fork from whmcs-coza-epp 

# Official Website for whmcs-registrobr-epp
# https://github.com/registrobr/whmcs-registrobr-epp

# Official Website for whmcs-coza-epp
# http://devlabs.linuxassist.net/projects/whmcs-coza-epp

# Lead developer: 
# Nigel Kukard <nkukard@lbsd.net>


# ! ! P L E A S E   N O T E  ! !

# * If you make changes to this file, please consider contributing 
#   anything useful back to the community. Don't be a sour prick.

# * If you find this module useful please consider making a 
#   donation to AllWorldIT to support modules like this.


# WHMCS hosting, theming, module development, payment gateway 
# integration, customizations and consulting all available from 
# http://allworldit.com



# Configuration array
function registrobr_getConfigArray() {
	$configarray = array(
		"Username" => array( "Type" => "text", "Size" => "4", "Description" => "Provider ID(numerical)" ),
		"Password" => array( "Type" => "password", "Size" => "20", "Description" => "EPP Password" ),
		"TestMode" => array( "Type" => "yesno" ),
		"Certificate" => array( "Type" => "text", "Description" => "Path of certificate .pem" ),
		"Passphrase" => array( "Type" => "password", "Size" => "20", "Description" => "Passphrase to the certificate file" ),
		"CPF" => array( "Type" => "text", "Size" => "20", "Description" => "Custom field for Tax Payer ID  (single field or non-corporations)" ),
		"CNPJ" => array( "Type" => "text", "Size" => "20", "Description" => "Custom field for Tax Payer ID  (corporations, leave blank if same as above)" ),
   	     	"TechC" => array( "Type" => "text", "Size" => "20", "Description" => "Tech Contact for new registrations" ),
        	"BillC" => array( "Type" => "text", "Size" => "20", "Description" => "Billing Contact for new registrations" ),
		"FriendlyName" => array("Type" => "System", "Value"=>"Registro.br")
	);
	return $configarray;
}

    

# Function to return current nameservers
function registrobr_GetNameservers($params) {
		
	# Create new EPP client
	$client = _registrobr_Client();
	$request = '
    <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	<command>
		<info>
			<domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name hosts="all">'.$params["sld"].".".$params["tld"].'</domain:name>
			</domain:info>
		</info>
    <clTRID>'.mt_rand().'</clTRID>
	</command>
    </epp>
    ';
    $result = $client->request($request);
	# Check results	
    # Parse XML
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->loadXML($result);
        
     
	# Check results	
	if(!is_array($result)) {
		# Parse XML
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($result);
		$ns = $doc->getElementsByTagName('hostName');
		# Extract nameservers
		$i =0;
		$values = array();
		foreach ($ns as $nn) {
			$i++;
			$values["ns{$i}"] = $nn->nodeValue;
		}
  
	return $values;

} 
  

# Function to save set of nameservers
function registrobr_SaveNameservers($params) {
    logModuleCall("registrobr","modify name servers",$params);
	# Grab variables
	$tld = $params["tld"];
	$sld = $params["sld"];

	# Generate XML for nameservers
	if ($nameserver1 = $params["ns1"]) { 
		$add_hosts = '
<domain:hostAttr>
	<domain:hostName>'.$nameserver1.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver2 = $params["ns2"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver2.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver3 = $params["ns3"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver3.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver4 = $params["ns4"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver4.'</domain:hostName>
</domain:hostAttr>';
	}
	if ($nameserver5 = $params["ns5"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver5.'</domain:hostName>
</domain:hostAttr>';
	}
	
	# Grab list of current nameservers
	$client = _registrobr_Client();
	$request = '
<epp:epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" 
		xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	<epp:command>
		<epp:info>
			<domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
			</domain:info>
		</epp:info>
	</epp:command>
</epp:epp>
';
    $registrarinfo = $client->request($request);
    
	# Parse XML
	$doc= new DOMDocument();
	$doc->loadXML($registrarinfo);
    $coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
    $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
    $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
    
	if($coderes != '1000') {
            $errormsg = "set nameservers: error getting nameservers code ".$coderes." msg '".$msg."'";
            if (isset($reason)) {
                $errormsg = $errormsg." reason '".$reason."'";
                } ;
        
		$values["error"] = $errormsg ; 
        logModuleCall("registrobr",$errormsg,$request,$registrarinfo);
    }  
    else { 
            # Generate list of nameservers to remove
            $hostlist = $doc->getElementsByTagName('hostName');
            foreach ($hostlist as $host) {
                $rem_hosts .= '
            <domain:hostAttr>
            <domain:hostName>'.$host->nodeValue.'</domain:hostName>
                </domain:hostAttr>
                ';
            }
		# Build request
		$request='
                 <epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
                    <command>
                        <update>
                            <domain:update>
                                <domain:name>'.$sld.'.'.$tld.'</domain:name>
                                <domain:add>
                                    <domain:ns>'.$add_hosts.' </domain:ns>
                                </domain:add>								  
                                <domain:rem>
                                    <domain:ns>'.$rem_hosts.'</domain:ns>
                                </domain:rem>
                            </domain:update>
                        </update>
           <clTRID>'.mt_rand().'</clTRID>
                    </command>
                     </epp>
                ';
        # Make request
        $setnameservers = $client->request($request);

		# Parse XML result
		$doc= new DOMDocument();
		$doc->loadXML($setnameservers);
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
        $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
        
		if($coderes != '1001') {
            $errormsg = "set nameservers: update servers error code ".$coderes." msg '".$msg."'";
            if (isset($reason)) {
                $errormsg = $errormsg." reason '".$reason."'";
                } ;
        
            $values["error"] = $errormsg ; 
            logModuleCall("registrobr",$errormsg,$request,$setnameservers);
            } else { 
			$values['status'] = "Domain update Pending. Based on .br policy, the estimated time taken is up to 30 minutes.";
		}
	}

	return $values;
}
                 
                 
                 }

function registrobr_RegisterNameserver($params) {
    logModuleCall("registrobr","register name server",$params);
    return ;
}

function registrobr_DeleteNameserver($params) {
    logModuleCall("registrobr","delete name server",$params);
    return ;
}

function registrobr_ModifyNameserver($params) {
    logModuleCall("registrobr","modify name server",$params);
    return ;
}

        
# Function to register domain
function registrobr_RegisterDomain($params) {
	
	# Setup include dir
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
	set_include_path($include_path . PATH_SEPARATOR . get_include_path());
	# Include CPF and CNPJ stuff we need
	require_once 'isCnpjValid.php';
	require_once 'isCpfValid.php';

	# Grab module parameters
	$moduleparams = getregistrarconfigoptions('registrobr');
    
    
    
    
	if (isCpfValid($params[$moduleparams['CPF']])==TRUE) { $RegistrantTaxID = $params[$moduleparams['CPF']] ; }
    elseif (isCnpjValid($params[$moduleparams['CPF']])==TRUE) { $RegistrantTaxID = $params[$moduleparams['CPF']] ; } 
    elseif (isCnpjValid($params[$moduleparams['CNPJ']])==TRUE) { $RegistrantTaxID = $params[$moduleparams['CNPJ']] ; }
    else {
        $errormsg = ".br registrations require valid CPF or CNPJ";
     	logModuleCall("registrobr","register",$params,$errormsg);
        $values['error'] = $errormsg;
		return $values;
		}

    $RegistrantTaxIDDigits = preg_replace("[^0-9]","",$RegistrantTaxID);
		                                        
    
    # Grab varaibles
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
    
    # Get registrant details	
    $RegistrantFirstName = $params["firstname"];
    $RegistrantLastName = $params["lastname"];
    $RegistrantAddress1 = $params["address1"];
    $RegistrantAddress2 = $params["address2"];
    $RegistrantCity = $params["city"];
    $RegistrantStateProvince = $params["state"];
    $RegistrantPostalCode = $params["postcode"];
    $RegistrantCountry = $params["country"];
    $RegistrantEmailAddress = $params["email"];
    $RegistrantPhone = substr($params["fullphonenumber"],1);
                 
    
	# Get admin Details
	$AdminFirstName = $params["adminfirstname"];
	$AdminLastName = $params["adminlastname"];
	$AdminAddress1 = $params["adminaddress1"];
	$AdminAddress2 = $params["adminaddress2"];
	$AdminCity = $params["admincity"];
	$AdminStateProvince = $params["adminstate"];
	$AdminPostalCode = $params["adminpostcode"];
	$AdminCountry = $params["admincountry"];
	$AdminEmailAddress = $params["adminemail"];
	$AdminPhone = substr($params["fullphonenumber"],1);
                 
    logModuleCall("registrobr","numbers",$RegistrantPhone,$AdminPhone);
                               
	$client = _registrobr_Client();
                 
	$request = '
    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
    <command>
    <info>
    <contact:info xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" 
    xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 
    contact-1.0.xsd"> 
    <contact:id>'.$RegistrantTaxIDDigits.'</contact:id>
    </contact:info>
    </info>
    <extension>
    <brorg:info xmlns:brorg="urn:ietf:params:xml:ns:brorg-1.0" 
    xsi:schemaLocation="urn:ietf:params:xml:ns:brorg-1.0 
    brorg-1.0.xsd"> 
    <brorg:organization>'.$RegistrantTaxID.'</brorg:organization>
    </brorg:info>
    </extension>
       <clTRID>'.mt_rand().'</clTRID>
    </command>
    </epp>
    ';


	$provider = $client->request($request);

    # Parse XML result
    $doc= new DOMDocument();
    $doc->loadXML($provider);
    $coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
    if($coderes == '1000') {
            $orgprov = ltrim($doc->getElementsByTagName('clID')->item(0)->nodeValue,"0");
            if ($orgprov!=$moduleparams["Username"]) 
                               { $errormsg="entity can only register domains through designated registrar.";
                               logModuleCall("registrobr",$errormsg,$request,$provider);
                               $values["error"]=$errormsg;
                               return $values;
                               } 
    }
         
         elseif($coderes == '2303') { 
                               
                 # Org contact creation
                 $request='<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                        xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
                        <command>
                        <create>
                        <contact:create xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" 
                        xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 
                        contact-1.0.xsd"> 
                        <contact:id>dummy</contact:id>
                        <contact:postalInfo type="loc">
                            <contact:name>'.$RegistrantFirstName.' '.$RegistrantLastName.'</contact:name>
                            <contact:addr>
                                <contact:street>'.$RegistrantAddress1.'</contact:street>
                                <contact:street>'.$RegistrantAddress2.'</contact:street>
                                <contact:city>'.$RegistrantCity.'</contact:city>
                                <contact:sp>'.$RegistrantStateProvince.'</contact:sp>
                                <contact:pc>'.$RegistrantPostalCode.'</contact:pc>
                                <contact:cc>'.$RegistrantCountry.'</contact:cc>
                            </contact:addr>
                        </contact:postalInfo>
                    <contact:voice>'.$RegistrantPhone.'</contact:voice>
                    <contact:email>'.$RegistrantEmailAddress.'</contact:email>
                    <contact:authInfo>
                        <contact:pw/>
                        </contact:authInfo>
                    </contact:create>
                </create>
                <clTRID>'.mt_rand().'</clTRID>
                </command>
                </epp>';
                 
                 $contact = $client->request($request);

                 # Parse XML result
                 $doc= new DOMDocument();
                 $doc->loadXML($contact);
                 $coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
                 $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
                 $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
	
                 if($coderes != '1001') {
                        $errormsg = "register: organizational contact creation error  code ".$coderes." msg '".$msg."'";
                        if (isset($reason)) {
                            $errormsg = $errormsg." reason '".$reason."'";
                            }
                    logModuleCall("registrobr",$errormsg,$request,$contact);
                    $values["error"]=$errormsg;
                    return $values;
                    }                   
         
             # Org creation
             
             $request='<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
         xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
             <command>
             <create>
         <contact:create xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" 
         xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 
             contact-1.0.xsd"> 
         <contact:id>'.$RegistrantTaxIDDigits.'</contact:id>
         <contact:postalInfo type="loc">
         <contact:name>'.$RegistrantFirstName.' '.$RegistrantLastName.'</contact:name>
         <contact:addr>
         <contact:street>'.$RegistrantAddress1.'</contact:street>
         <contact:street>'.$RegistrantAddress2.'</contact:street>
         <contact:city>'.$RegistrantCity.'</contact:city>
         <contact:sp>'.$RegistrantStateProvince.'</contact:sp>
         <contact:pc>'.$RegistrantPostalCode.'</contact:pc>
         <contact:cc>'.$RegistrantCountry.'</contact:cc>
             </contact:addr>
             </contact:postalInfo>
         <contact:voice>'.$RegistrantPhone.'</contact:voice>
         <contact:email>'.$RegistrantEmailAddress.'</contact:email>
         <contact:authInfo>
         <contact:pw/>
             </contact:authInfo>
             </contact:create>
             </create>
             <extension>
         <brorg:create xmlns:brorg="urn:ietf:params:xml:ns:brorg-1.0" 
         xsi:schemaLocation="urn:ietf:params:xml:ns:brorg-1.0 
             brorg-1.0.xsd"> 
         <brorg:organization>'.$RegistrantTaxID.'</brorg:organization>
         <brorg:contact type="admin">'.$doc->getElementsByTagName('id')->item(0)->nodeValue.'</brorg:contact>
             </brorg:create>
             </extension>
                <clTRID>'.mt_rand().'</clTRID>
             </command>
             </epp>';
             $org = $client->request($request);
             
             # Parse XML result
             $doc= new DOMDocument();
             $doc->loadXML($org);
             $coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
             $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
             $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
             
             if($coderes != '1000') {
                 $errormsg = "register: organization creation error  code ".$coderes." msg '".$msg."'";
                 if (isset($reason)) {
                     $errormsg = $errormsg." reason '".$reason."'";
                 }
                 logModuleCall("registrobr",$errormsg,$request,$org);
                 $values["error"]=$errormsg;
                 return $values;
             }           
                 }       	
                 
    


	# Generate XML for namseverss
	if ($nameserver1 = $params["ns1"]) { 
		$add_hosts = '
<domain:hostAttr>
	<domain:hostName>'.$nameserver1.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver2 = $params["ns2"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver2.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver3 = $params["ns3"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver3.'</domain:hostName>
</domain:hostAttr>
';
	}
	if ($nameserver4 = $params["ns4"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver4.'</domain:hostName>
</domain:hostAttr>';
	}
	if ($nameserver5 = $params["ns5"]) { 
		$add_hosts .= '
<domain:hostAttr>
	<domain:hostName>'.$nameserver5.'</domain:hostName>
</domain:hostAttr>';
	}

	# Admin contact creation
    $request='<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
     xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
 <command>
  <create>
   <contact:create xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" 
                   xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 
                   contact-1.0.xsd"> 
    <contact:id>dummy</contact:id>
    <contact:postalInfo type="loc">
					<contact:name>'.$AdminFirstName.' '.$AdminLastName.'</contact:name>
					<contact:addr>
						<contact:street>'.$AdminAddress1.'</contact:street>
						<contact:street>'.$AdminAddress2.'</contact:street>
						<contact:city>'.$AdminCity.'</contact:city>
						<contact:sp>'.$AdminStateProvince.'</contact:sp>
						<contact:pc>'.$AdminPostalCode.'</contact:pc>
						<contact:cc>'.$AdminCountry.'</contact:cc>
					</contact:addr>
				</contact:postalInfo>
				<contact:voice>'.$AdminPhone.'</contact:voice>
				<contact:email>'.$AdminEmailAddress.'</contact:email>
				<contact:authInfo>
					<contact:pw/>
				</contact:authInfo>
			</contact:create>
		</create>
       <clTRID>'.mt_rand().'</clTRID>
	</command>
</epp>';
                 
    $contact = $client->request($request);

                 
	# Parse XML result
	$doc= new DOMDocument();
	$doc->loadXML($contact);
	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
    $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
    $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
    
	if($coderes == '1000') {
                 $admcontact = $doc->getElementsByTagName('id')->item(0)->nodeValue;
                 $values['contact'] = 'Contact Created';
	} else {

            $errormsg = "register: admin contact creation error code ".$coderes." msg '".$msg."'";
            if (isset($reason)) {
                $errormsg = $errormsg." reason '".$reason."'";
                }
            logModuleCall("registrobr",$errormsg,$request,$contact);
            $values["error"]=$errormsg;
            return $values;
	}
    

      	# If our result is success, carry on to domain registration

                 $request = '
                 <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                 xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
	<command>
		<create>
                 <domain:create xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" 
                 xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name>'.$sld.'.'.$tld.'</domain:name>
				<domain:ns>'.$add_hosts.'</domain:ns>
                 <domain:contact type="admin">'.$admcontact.'</domain:contact>';
                 if (isset($moduleparams['TechC'])) $request=$request.' <domain:contact type="tech">'.$moduleparams['TechC'].'</domain:contact>';
                 if (isset($moduleparams['BillC'])) $request=$request.' <domain:contact type="billing">'.$moduleparams['BillC'].'</domain:contact>';
                 $request=$request.'
                 <domain:authInfo>
				<domain:pw/>
			</domain:authInfo>
		</domain:create>
		</create>
                 <extension>
                 <brdomain:create xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0" 
                 xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0 
                 brdomain-1.0.xsd"> 
                 <brdomain:organization>'.$RegistrantTaxID.'</brdomain:organization>
                 </brdomain:create>
                 </extension>
    <clTRID>'.mt_rand().'</clTRID>
	</command>
</epp>
';
        $domaincreate = $client->request($request);
    logModuleCall("registrobr","debug",$request,$domaincreate);
		$doc= new DOMDocument();
		$doc->loadXML($domaincreate);
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
        $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
    
                               
                 if(($coderes != '1001')&&($coderes != '1000')) {
                                $errormsg = "register: domain creation error code ".$coderes." msg '".$msg."'";
                                if (isset($reason)) {
                                         $errormsg = $errormsg." reason '".$reason."'";
                                         }
                                logModuleCall("registrobr",$errormsg,$request,$domaincreate);
                                $values["error"]=$errormsg;
                                } else {
                                         $values["status"] = $msg;
		}					
	return $values;
}

                                         
                
                 
# Function to renew domain
function registrobr_RenewDomain($params) {
	# Grab variables
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];

	$client = _registrobr_Client();
	$request='
<epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" 
		xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	<command>
		<info>
			<domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
			</domain:info>
		</info>
    <clTRID>'.mt_rand().'</clTRID>
	</command>
</epp>
';
    $registrarinfo = $client->request($request);
                                      
	# Parse XML result
	$doc= new DOMDocument();
	$doc->loadXML($registrarinfo);
	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
    $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
    $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
                 
	if($coderes != '1000') {
                 $errormsg = "renew: domain info error code ".$coderes." msg '".$msg."'";
                 if (isset($reason)) {
                 $errormsg = $errormsg." reason '".$reason."'";
                 }
                 logModuleCall("registrobr",$errormsg,$request,$registrarinfo);
                 $values["error"]=$errormsg;
                 }
                 
		else { 
		# Sanitize expiry date
		$expdate = substr($doc->getElementsByTagName('exDate')->item(0)->nodeValue,0,10);

		# Send request to renew
		$request='
<epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
	<command>
		<renew>
			<domain:renew>
				<domain:name>'.$sld.'.'.$tld.'</domain:name>
				<domain:curExpDate>'.$expdate.'</domain:curExpDate>
			</domain:renew>
		</renew>
            <clTRID>'.mt_rand().'</clTRID>
	</command>
</epp>
';
                                        
        $domainrenew = $client->request($request);
                                        
		# Parse XML result	
		$doc= new DOMDocument();
		$doc->loadXML($domainrenew);
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
        $msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
        $reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
                 
		if($coderes != '1000') {
		         $errormsg = "renew: domain renew error code ".$coderes." msg '".$msg."'";
                 if (isset($reason)) {
                 $errormsg = $errormsg." reason '".$reason."'";
                 }
                 logModuleCall("registrobr",$errormsg,$request,$domainrenew);
                 $values["error"]=$errormsg;
                 }
                 else {
			$values["status"] = $msg;
		}
	}

	# If error, return the error message in the value below
	return $values;
}



# Function to grab contact details
function registrobr_GetContactDetails($params) {
	# Grab variables	
	$tld = $params["tld"];
	$sld = $params["sld"];

	# Grab contact details
	$client = _registrobr_Client();
	$registrarinfo = $client->request('
<epp:epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" 
		xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	<epp:command>
		<epp:info>
			<domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
			</domain:info>
		</epp:info>
	</epp:command>
</epp:epp>
');

	# Parse XML result		
	$doc= new DOMDocument();
	$doc->loadXML($registrarinfo);
	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
	$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;

	# Check result
	if($coderes != '1000') {
		$values["error"] = "Code (".$coderes.") ".$msg;
	} else { 

		# Grab contact info
		$registrant = $doc->getElementsByTagName('registrant')->item(0)->nodeValue;
		$domaininfo = $client->request('
<epp:epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:contact="urn:ietf:params:xml:ns:contact-1.0">
	<epp:command>
		<epp:info>
			<contact:info>
				<contact:id>'.$registrant.'</contact:id>
			</contact:info>
		</epp:info>
	</epp:command>
</epp:epp>
');
		# Parse XML result
		$doc= new DOMDocument();
		$doc->loadXML($domaininfo);
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;

		# Check result
		if($coderes != '1000') {
			$values["error"] = "Code (".$coderes.") ".$msg;
		} else { 
			# Setup return values
			$values["Registrant"]["Contact Name"] = $doc->getElementsByTagName('name')->item(0)->nodeValue;
			$values["Registrant"]["Organisation"] = $doc->getElementsByTagName('org')->item(0)->nodeValue;
			$values["Registrant"]["Address line 1"] = $doc->getElementsByTagName('street')->item(0)->nodeValue;
			$values["Registrant"]["Address line 2"] = $doc->getElementsByTagName('street')->item(1)->nodeValue;
			$values["Registrant"]["TownCity"] = $doc->getElementsByTagName('city')->item(0)->nodeValue;
			$values["Registrant"]["State"] = $doc->getElementsByTagName('sp')->item(0)->nodeValue;
			$values["Registrant"]["Zip code"] = $doc->getElementsByTagName('pc')->item(0)->nodeValue;
			$values["Registrant"]["Country Code"] = $doc->getElementsByTagName('cc')->item(0)->nodeValue;
			$values["Registrant"]["Phone"] = $doc->getElementsByTagName('voice')->item(0)->nodeValue;
			$values["Registrant"]["Email"] = $doc->getElementsByTagName('email')->item(0)->nodeValue;
		}
	}

	return $values;
}


                                       
# Function to save contact details
function registrobr_SaveContactDetails($params) {
	# Grab variables
	$tld = $params["tld"];
	$sld = $params["sld"];
	# Registrant Details
	$registrant_name = $params["contactdetails"]["Registrant"]["Contact Name"];
	$registrant_org = $params["contactdetails"]["Registrant"]["Organisation"];
	$registrant_address1 =  $params["contactdetails"]["Registrant"]["Address line 1"];
	$registrant_address2 = $params["contactdetails"]["Registrant"]["Address line 2"];
	$registrant_town = $params["contactdetails"]["Registrant"]["TownCity"];
	$registrant_state = $params["contactdetails"]["Registrant"]["State"];
	$registrant_zipcode = $params["contactdetails"]["Registrant"]["Zip code"];
	$registrant_countrycode = $params["contactdetails"]["Registrant"]["Country Code"];
	$registrant_phone = $params["contactdetails"]["Registrant"]["Phone"];
	$registrant_email = $params["contactdetails"]["Registrant"]["Email"];

	#Grab domain info
	$client = _registrobr_Client();
	$registrarinfo = $client->request('
<epp:epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" 
		xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	<epp:command>
		<epp:info>
			<domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
				<domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
			</domain:info>
		</epp:info>
	</epp:command>
</epp:epp>
');
	# Parse XML	result
	$doc= new DOMDocument();
	$doc->loadXML($registrarinfo);
	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
	if($coderes != '1000') {
		$values["error"] = "Code (".$coderes.") ".$msg;
	} else { 
		# Time to do the update
		$registrant = $doc->getElementsByTagName('registrant')->item(0)->nodeValue;
		$contact = $client->request('
<epp:epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:contact="urn:ietf:params:xml:ns:contact-1.0">
	<epp:command>
		<epp:update>
			<contact:update>
				<contact:id>'.$registrant.'</contact:id>
				<contact:chg>
					<contact:postalInfo type="loc">
						<contact:name>'.$registrant_name.'</contact:name>
						<contact:org>'.$registrant_org.'</contact:org>
						<contact:addr>
							<contact:street>'.$registrant_address1.'</contact:street>
							<contact:street>'.$registrant_address2.'</contact:street>
							<contact:city>'.$registrant_town.'</contact:city>
							<contact:sp>'.$registrant_state.'</contact:sp>
							<contact:pc>'.$registrant_zipcode.'</contact:pc>
							<contact:cc>'.$registrant_countrycode.'</contact:cc>
						</contact:addr>
						</contact:postalInfo>
						<contact:voice>'.$registrant_phone.'</contact:voice>
						<contact:fax></contact:fax>
						<contact:email>'.$registrant_email.'</contact:email>
				</contact:chg>
			</contact:update>
		</epp:update>
	</epp:command>
</epp:epp>
');

		# Parse XML result
		$doc= new DOMDocument();
		$doc->loadXML($contact);
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
		if($coderes == '1001') { 
			$values['status'] = $msg;
		} else { 
			$values["error"] = "Code (".$coderes.") ".$msg;
		}
	}

	return $values;
}

                                    
                                    
# Domain Delete (used in .br only for Add Grace Period)
function registrobr_RequestDelete($params) {
                                    
$client = _registrobr_Client();
                                    
$request = '<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
<command>
<delete>
<domain:delete xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" 
xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 
domain-1.0.xsd"> 
<domain:name>'.$params['sld'].'.'.$params['tld'].'</domain:name>
</domain:delete>
</delete>
                                    <clTRID>'.mt_rand().'</clTRID>
</command>
</epp>
';
$delete = $client->request($request);

# Parse XML
$doc= new DOMDocument();
$doc->loadXML($delete);
$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
$reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;

if($coderes != '1000') {
$errormsg = "domain delete: error code ".$coderes." msg '".$msg."'";
if (isset($reason)) {
$errormsg = $errormsg." reason '".$reason."'";
} ;

$values["error"] = $errormsg ; 
logModuleCall("registrobr",$errormsg,$request,$delete);
}
return $values ;
}
                                    
                                    


# Function to create internal .br EPP request
function _registrobr_Client() {
	# Setup include dir
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
	set_include_path($include_path . PATH_SEPARATOR . get_include_path());
	# Include EPP stuff we need
	require_once 'Net/EPP/Client.php';
	require_once 'Net/EPP/Protocol.php';


	# Grab module parameters
	$params = getregistrarconfigoptions('registrobr');


	if (!isset($params['TestMode']) && !isset($params['Certificate'])) {
		$errormsg =  "Please specifity path to certificate file"  ;
		logModuleCall ("registrobr","config options err",$errormsg);
		return $errormsg ;
		}

	if (!isset($params['TestMode']) && !file_exists($params['Certificate'])) {
		$errormsg =  "Invalid certificate file path"  ;
		logModuleCall ("registrobr","config options err",$params,$errormsg);
		return $errormsg ;
		}
	if (!isset($params['TestMode']) && !isset($params['Passphrase'])) {
		$errormsg =   "Please specifity certificate passphrase"  ;
		logModuleCall ("registrobr","config options err",$params,errormsg);
		return $errormsg ;
		}
 

	# Use OT&E if test mode is set
 	if (!isset($params['TestMode'])) {
	          $Server = 'epp.registro.br' ;
		  $Options = array (
			'ssl' => array (
				'passphrase' => $params['Passphrase'],
				'local_cert' => $params['Certificate']));

		} else {
		$Server = 'beta.registro.br' ;
		  $Options = array (
			'ssl' => array (
				'local_cert' =>  dirname(__FILE__) . '/test-client.pem' ));

                  }

	# Create SSL context
	$context = stream_context_create ($Options) ;

  

	# Create EPP client
	$client = new Net_EPP_Client();
	# Connect
	$Port = 700;
	$use_ssl = true;
	$res = $client->connect($Server, $Port, 3 , $use_ssl, $context);
	# Check for error
	if (PEAR::isError($res)) {
		logModuleCall("registrobr","epp connect error","tls://".$Server.":".$Port,$res);
		return $res;
	}

	# Perform login
	$result = $client->request('
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
	<command>
		<login>
			<clID>'.$params['Username'].'</clID>
			<pw>'.$params['Password'].'</pw>
			<options>
			<version>1.0</version>
			<lang>en</lang>
			</options>
			<svcs>
				<objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
				<objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
 				   <svcExtension>
     					<extURI>urn:ietf:params:xml:ns:brdomain-1.0</extURI>
     					<extURI>urn:ietf:params:xml:ns:brorg-1.0</extURI>
     					<extURI>urn:ietf:params:xml:ns:secDNS-1.0</extURI>
     					<extURI>urn:ietf:params:xml:ns:secDNS-1.1</extURI>
    				</svcExtension>
			</svcs>
		</login>
                               <clTRID>'.mt_rand().'</clTRID>
	</command>
</epp>
');

	return $client;


                               }
                               
?>
