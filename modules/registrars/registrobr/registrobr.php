<?php

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


# Configuration array
function registrobr_getConfigArray() {

    # Create version table if it doesn't exist
    $query = "CREATE TABLE IF NOT EXISTS `mod_registrobr_version` (
    `version` int(10) unsigned NOT NULL,
    PRIMARY KEY (`version`)
    ) ";
    mysql_query($query);
   
    $current_version = 1.01 ;
    $queryresult = mysql_query("SELECT version FROM mod_registrobr_version");
    $data = mysql_fetch_array($queryresult);
    
    $version=$data['version'];
    
    if ($version!=$current_version) {
        #include code to alter table mod_registrobr
        
        #only update version if alter table above succeeds
        mysql_query("UPDATE mod_registrobr_version SET version='".$current_version."'");
        if (mysql_affected_rows()==0) {
            mysql_query("insert into mod_registrobr_version (version) values ('".$current_version."')");
            mysql_query("ALTER TABLE mod_registrobr CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
        }
    }
  
    
    # Create auxiliary table if it doesn't exist
    $query = "CREATE TABLE IF NOT EXISTS `mod_registrobr` (
        `clID` varchar(16) COLLATE latin1_general_ci NOT NULL,
        `domainid` int(10) unsigned NOT NULL,
        `domain` varchar(200) COLLATE latin1_general_ci NOT NULL,
        `ticket` int(10) unsigned NOT NULL,
        PRIMARY KEY (`domainid`),
        UNIQUE KEY `ticket` (`ticket`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
    mysql_query($query);
    
	$configarray = array(
		"Username" => array( "Type" => "text", "Size" => "16", "Description" => "Provider ID(numerical)" ),
		"Password" => array( "Type" => "password", "Size" => "20", "Description" => "EPP Password" ),
		"TestMode" => array( "Type" => "yesno" , "Description" => "If active connects to beta.registro.br instead of production server"),
		"Certificate" => array( "Type" => "text", "Description" => "Path of certificate .pem" ),
		"Passphrase" => array( "Type" => "password", "Size" => "20", "Description" => "Passphrase to the certificate file" ),
		"CPF" => array( "Type" => "dropdown", "Options" => "1,2,3,4,5,6,7,8,9", "Description" => "Custom field index for individuals Tax Payer IDs", "Default" => "1"),
        "CNPJ" => array( "Type" => "dropdown", "Options" => "1,2,3,4,5,6,7,8,9", "Description" => "Custom field index for corporations Tax Payer IDs (can be same as above)", "Default" => "1"),
        "TechC" => array( "FriendlyName" => "Tech Contact", "Type" => "text", "Size" => "20", "Description" => "Tech Contact used in new registrations; blank will make registrant the Tech contact" ),
        "TechDept" => array( "FriendlyName" => "Tech Department ID", "Type" => "dropdown", "Options" => "1,2,3,4,5,6,7,8,9", "Description" => "Index for Tech Department ID within ticketing system", "Default" => "1"),
        "FinanceDept" => array( "FriendlyName" => "Finance Department ID", "Type" => "dropdown", "Options" => "1,2,3,4,5,6,7,8,9", "Description" => "Index for Finance Department ID within ticketing system (can be same as above)", "Default" => "1"),
        "Sender" => array( "FriendlyName" => "Sender Username", "Type" => "text", "Size" => "16", "Description" => "Sender of tickets (usually root)", "Default" => "root"),                  
        "Language" => array ( "Type" => "radio", "Options" => "English,Portuguese", "Description" => "Escolha Portuguese para mensagens em Portugu&ecircs", "Default" => "English"),
        "FriendlyName" => array("Type" => "System", "Value"=>"Registro.br"),
        "Description" => array("Type" => "System", "Value"=>"http://registro.br/provedor/epp/"),
        

	);
    return $configarray;

}


#Aux functions

#Pear error

function _registrobr_pear_error($client,$strerror){
    $client = _registrobr_set_encode($client);
    $values["error"]=_registrobr_lang($strerror).$client;
    logModuleCall("registrobr",$values["error"]);
    return $values;
}

#Parse xml response from epp server
function _registrobr_parse_response($response){

	$doc= new DOMDocument();
	$doc->loadXML($response);
	$atts = array();
	$atts['coderes'] = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
	$atts['msg'] = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
	$atts['reason'] = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
	$atts['id'] = $doc->getElementsByTagName('id')->item(0)->nodeValue;
	$atts['contact'] = $doc->getElementsByTagName('contact')->item(0)->nodeValue;
	$atts['doc'] = $doc;

	return $atts;         
}
#registro.br response error

function _registrobr_server_error($strerror,$coderes,$msg,$reason,$request,$response){

	$msg = _registrobr_set_encode($msg);
	$errormsg = _registrobr_lang($strerror).$coderes._registrobr_lang('msg').$msg."'";
	if (!empty($reason)) {
		$reason = _registrobr_set_encode($reason);
		$errormsg.= _registrobr_lang("reason").$reason."'";
	};
	logModuleCall("registrobr",$errormsg,$request,$response);
	$values["error"] = $errormsg;
	return $values;
}

    
# Function to return current nameservers

function registrobr_GetNameservers($params) {
    
    # We need pear for the error handling
    require_once "PEAR.php";

    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
    
    # Create new EPP client
    $client = _registrobr_Client();
    if (PEAR::isError($client)) {
        return _registrobr_pear_error($client,'getnsconnerror');
    }
   
    $domain = $params["sld"].".".$params["tld"];
    $ticket='';
    
    //external links requrires
    
	do {
        $request = '
        <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
                <info>
                    <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                        <domain:name hosts="all">'.$domain.'</domain:name>
                    </domain:info>
                </info>';
                if ($ticket!='') {
                    $request.='
                    <extension>
                        <brdomain:info xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0" 
                        xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0 
                        brdomain-1.0.xsd"> 
                            <brdomain:ticketNumber>'.$ticket.'</brdomain:ticketNumber>
                        </brdomain:info>
                    </extension>';
                    $ticket='';
                }    
                $request.='    
                <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';

        $response = $client->request($request);
  
        # Check results	
        $answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	
        if($coderes != '1000') {
            if ($coderes != '2303') {
                return _registrobr_server_error('getnserrorcode',$coderes,$msg,$reason,$request,$response);
            }
        $table = "mod_registrobr";
        $fields = "clID,domainid,domain,ticket";
        # incluir domainid ?
        $where = array("clID"=>$moduleparams['Username'],"domain"=>$domain);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        $ticket = $data['ticket'];
        }
    } while ($ticket!='');
    
    if ($coderes == '2303') {
        $values["error"] = _registrobr_lang('domainnotfound');
        return $values;
    }         
	
    # Parse XML
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->loadXML($response);
    $ns = $doc->getElementsByTagName('hostName');
    # Extract nameservers
    $i =0;
    $values = array();
    foreach ($ns as $nn) {
        $i++;
        if ($nn->nodeName=='domain:hostName') $values["ns{$i}"] = $nn->nodeValue;
    }
    return $values;
}

# Function to save set of nameservers

function registrobr_SaveNameservers($params) {
    
    # We need pear for the error handling
    require_once "PEAR.php";
    
    # Grab variables
    $domain = $params["sld"].".".$params["tld"];
    $moduleparams = getregistrarconfigoptions('registrobr');
    
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
        # Create new EPP client
        if (PEAR::isError($client)) {
	    return _registrobr_pear_error($client,'setnsconnerror');
        }
        # Create new EPP client
    
    $ticket='';
    $setticket='';
    do {
        $request = '
        <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
        <command>
        <info>
    <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
    <domain:name hosts="all">'.$domain.'</domain:name>
        </domain:info>
        </info>';
        if ($ticket!='') {
            $request.='
            <extension>
        <brdomain:info xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0"
        xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0
            brdomain-1.0.xsd">
        <brdomain:ticketNumber>'.$ticket.'</brdomain:ticketNumber>
            </brdomain:info>
            </extension>';
            $setticket=$ticket;
            $ticket='';
        }
        $request.='
        <clTRID>'.mt_rand().mt_rand().'</clTRID>
        </command>
        </epp>
        ';
        
        $response = $client->request($request);
        
        # Parse response	
        $answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	  
        # Check if result is ok
        if($coderes != '1000') {
            if ($coderes != '2303') {
                return _registrobr_server_error('setnserrorcode',$coderes,$msg,$reason,$request,$response);
            }
        
        $table = "mod_registrobr";
        $fields = "clID,domainid,domain,ticket";
        # incluir domainid ?
        $where = array("clID"=>$moduleparams['Username'],"domain"=>$domain);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        $ticket = $data['ticket'];
        }
        
    } while ($ticket!='');
    
    if ($coderes == '2303') {
        $values["error"] = _registrobr_lang('domainnotfound');
        return $values;
    }
    
    # Generate list of nameservers to remove
    $hostlist = $doc->getElementsByTagName('hostName');
    foreach ($hostlist as $host) {
        if ($host->nodeName=='domain:hostName') 
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
                                <domain:name>'.$domain.'</domain:name>';
                                if(!empty($add_hosts)) 
                                   $request .=' <domain:add>
                                   <domain:ns>'.$add_hosts.' </domain:ns>
                                   </domain:add>';
                                $request .='
                                <domain:rem>
                                    <domain:ns>'.$rem_hosts.'</domain:ns>
                                </domain:rem>
                            </domain:update>
                        </update>';
                        if ($setticket!='') {
                            $request.='
                            <extension>
                                <brdomain:update xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0"
                                xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0
                                brdomain-1.0.xsd">
                                    <brdomain:ticketNumber>'.$setticket.'</brdomain:ticketNumber>
                                </brdomain:update>
                            </extension>';
                        }
                        $request.='
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                    </command>
            </epp>
            ';

    # Make request
    $response = $client->request($request);

    # Parse results	
    $answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
    
    # Check results

    if($coderes != '1000') {
        return _registrobr_server_error('setnsupdateerrorcode',$coderes,$msg,$reason,$request,$response);
    }  
    
    return $values;
}
       
# Function to register domain

function registrobr_RegisterDomain($params) {

    # Setup include dir
    $include_path = ROOTDIR . '/modules/registrars/registrobr';
    set_include_path($include_path . PATH_SEPARATOR . get_include_path());

    # Include CPF and CNPJ stuff we need
    require_once 'isCnpjValid.php';
    require_once 'isCpfValid.php';
    
    # We need pear for the error handling
    require_once "PEAR.php";

    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
    $RegistrantTaxID = $params['customfields'.$moduleparams['CPF']];
    if (!isCpfValid($RegistrantTaxID)) {
                        $RegistrantTaxID = $params['customfields'.$moduleparams['CNPJ']] ;
        
                        if (!isCnpjValid($RegistrantTaxID)) {
                            $values["error"] =_registrobr_lang("cpfcnpjrequired");
                            logModuleCall("registrobr",$values["error"],$params);
                            return $values;
                        }
    }
  
    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);
    if (isCpfValid($RegistrantTaxIDDigits)==TRUE) {
                        $RegistrantTaxID = substr($RegistrantTaxIDDigits,0,3).".".substr($RegistrantTaxIDDigits,3,3).".".substr($RegistrantTaxIDDigits,6,3)."-".substr($RegistrantTaxIDDigits,9,2);
    } else {
                        $RegistrantTaxID = substr($RegistrantTaxIDDigits,0,2).".".substr($RegistrantTaxIDDigits,2,3).".".substr($RegistrantTaxIDDigits,5,3)."/".substr($RegistrantTaxIDDigits,8,4)."-".substr($RegistrantTaxIDDigits,12,2);
    }

    # Grab variaibles
    $tld = _registrobr_convert_to_punycode($params["original"]["tld"]);
    $sld = _registrobr_convert_to_punycode($params["original"]["sld"]);
    
    $domain_punycode = $sld.'.'.$tld;

    //print_r($domain_punycode);exit;
    
    
    $regperiod = $params["regperiod"];

    # Get registrant details	
    #$RegistrantFirstName = _registrobr_set_encode($params["firstname"],'ISO-8859-1');
    $RegistrantFirstName = $params["original"]["firstname"];
    $RegistrantLastName = $params["original"]["lastname"];
    $RegistrantContactName = $params["original"]["firstname"]." ".$params["original"]["lastname"];
    if (isCpfValid($RegistrantTaxIDDigits)==TRUE) {
                        $RegistrantOrgName = substr($RegistrantContactName,0,40);

    } else {
                        $RegistrantOrgName = substr($params["original"]["companyname"],0,50);
                        if (empty($RegistrantOrgName)) {
                            $values['error'] = _registrobr_lang("companynamerequired");
                            return $values;
                        }   
    }

    $RegistrantAddress1 = $params["original"]["address1"];
    $RegistrantAddress2 = $params["original"]["address2"];
    $RegistrantCity = $params["original"]["city"];
    $RegistrantStateProvince = _registrobr_StateProvince($params["original"]["state"]);
    $RegistrantPostalCode = $params["original"]["postcode"];
    $RegistrantCountry = $params["original"]["country"];
    $RegistrantEmailAddress = $params["original"]["email"];
    $RegistrantPhone = substr($params["original"]["fullphonenumber"],1);
    

    
    #Get an EPP connection
    $client = _registrobr_Client();
    # Create new EPP client
    if (PEAR::isError($client)) {
		return _registrobr_pear_error($client,'registerconnerror');
    }
    # Create new EPP client
    
    # Does the company or individual is already in the .br database ?
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
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                      </command>
    </epp>
    ';
 
    $response = $client->request($request);
    # Check results	
    $answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
    $doc = $answer['doc'];
    # Check results


    if($coderes == '1000') {
            # If it's already on the database, verify new domains can be registered 
            $orgprov = ltrim($doc->getElementsByTagName('clID')->item(0)->nodeValue,"0");
            if ($orgprov!=$moduleparams["Username"]) {
                        $values["error"]=_registrobr_lang("notallowed");
                        logModuleCall("registrobr",$values["error"],$request,$response);
                        return $values ;
            } 

    } elseif($coderes != '2303') {
		return _registrobr_server_error('registergetorgerrorcode',$coderes,$msg,$reason,$request,$response);
    } else {
        
                # Company or individual not in the database, proceed to org contact creation
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
                                        <contact:name>'.$RegistrantContactName.'</contact:name>
                                        <contact:addr>
                                            <contact:street>'.$RegistrantAddress1.'</contact:street>
                                            <contact:street>'.$RegistrantAddress2.'</contact:street>
                                            <contact:street>'.$RegistrantAddress3.'</contact:street>
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
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
                        </epp>';
		$request = _registrobr_set_encode($request,'ISO-8859-1');
     
		$response = $client->request($request);
			
		# Parse XML result
		# Check results	
		$answer = _registrobr_parse_response($response);
		$coderes = $answer['coderes'];
		$msg = $answer['msg'];
		$reason = $answer['reason'];
		$idt = $answer['id'];
		# Check results

        if($coderes != '1000') {
			return _registrobr_server_error('registercreateorgcontacterrorcode',$coderes,$msg,$reason,$request,$response);
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
                                            <contact:name>'.$RegistrantOrgName.'</contact:name>
                                            <contact:addr>
                                                <contact:street>'.$RegistrantAddress1.'</contact:street>
                                                <contact:street>'.$RegistrantAddress2.'</contact:street>
                                                <contact:street>'.$RegistrantAddress3.'</contact:street>
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
                                        <brorg:contact type="admin">'.$idt.'</brorg:contact>
                                    </brorg:create>
                                </extension>
                                <clTRID>'.mt_rand().mt_rand().'</clTRID>
                            </command>
                        </epp>';
        		$request = _registrobr_set_encode($request,'ISO-8859-1');
                $response = $client->request($request);

                # Parse XML result

				# Check results	
				$answer = _registrobr_parse_response($response);
				$coderes = $answer['coderes'];
				$msg = $answer['msg'];
				$reason = $answer['reason'];
				# Check results

        if($coderes != '1001') {
            return _registrobr_server_error('registercreateorgerrorcode',$coderes,$msg,$reason,$request,$response);
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

    # Carry on to domain registration

	
    $request = '
                 <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                 xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
                        <command>
                            <create>
                                <domain:create xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" 
                                xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                                    <domain:name>'.$domain_punycode.'</domain:name>
                                    <domain:period unit="y">'.$regperiod.'</domain:period>
                                    <domain:ns>'.$add_hosts.'</domain:ns>';
    
                                    # Valid .br contacts have 3 or more letters and/or numbers
                                    if (strlen($moduleparams['TechC'])>2) $request.=' <domain:contact type="tech">'.$moduleparams['TechC'].'</domain:contact>';
                                    $request.='
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
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
                    </epp>
                ';

    $response = $client->request($request);
    # Check results	
    $answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
    # Check results
    if($coderes != '1001') {
	return _registrobr_server_error('registererrorcode',$coderes,$msg,$reason,$request,$response);
    }
    
    $table = "mod_registrobr";
    $values = array("clID"=>$moduleparams['Username'],"domainid"=>$params['domainid'],"domain"=>$doc->getElementsByTagName('name')->item(0)->nodeValue,"ticket"=>$doc->getElementsByTagName('ticketNumber')->item(0)->nodeValue);
    $newid = insert_query($table,$values);
    return $values;
}
                                      
# Function to renew domain

function registrobr_RenewDomain($params) {
    
    # We need pear for the error handling
    require_once "PEAR.php";

	# Grab variables
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];

    # Get an EPP Connection                    
    $client = _registrobr_Client();
    # Create new EPP client
    if (PEAR::isError($client)) {
	return _registrobr_pear_error($client,'renewconnerror');
    }
    # Create new EPP client
                        
    $request='
            <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" 
            xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                        <command>
                            <info>
                                <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                                    <domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
                                </domain:info>
                            </info>
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
            </epp>
            ';

    $response = $client->request($request);
                                     
    # Parse XML result
    # Check results	
    $answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
    # Check results
    if($coderes != '1000') {
        return _registrobr_server_error('renewinfoerrorcode',$coderes,$msg,$reason,$request,$response);
    }
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
                            <domain:period unit="y">'.$regperiod.'</domain:period>
                        </domain:renew>
                    </renew>
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
                </command>
            </epp>
            ';
                                      
    $response = $client->request($request);
   
    # Check results	
    $answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
    # Check results

    if($coderes != '1000') {
		return _registrobr_server_error('renewerrorcode',$coderes,$msg,$reason,$request,$response);
    }
    return $values;

}

# Function to grab contact details

function registrobr_GetContactDetails($params) {

    # Setup include dir
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
	set_include_path($include_path . PATH_SEPARATOR . get_include_path());

	# Include CPF and CNPJ stuff we need
	require_once 'isCnpjValid.php';
	require_once 'isCpfValid.php';
    
    # We need pear for the error handling
    require_once "PEAR.php";
  
	# Grab variables	
	$tld = $params["tld"];
	$sld = $params["sld"];

	# Grab contact details
	$client = _registrobr_Client();
    # Create new EPP client
    if (PEAR::isError($client)) {
	return _registrobr_pear_error($client,'getcontactconnerror');

    }
    # Create new EPP client
    
    $request = '
        <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0"
		xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
                <info>
                    <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                        <domain:name hosts="all">'.$sld.'.'.$tld.'</domain:name>
                    </domain:info>
                </info>
            </command>
        </epp>
        ';

    $response = $client->request($request);
                                                              
	# Parse XML result		
	# Check results	
	$answer = _registrobr_parse_response($response);
	$coderes = $answer['coderes'];
	$msg = $answer['msg'];
	$reason = $answer['reason'];
	$doc = $answer['doc'];
	# Check results

    if($coderes != '1000') {
		return _registrobr_server_error('getcontacterrorcode',$coderes,$msg,$reason,$request,$response);

    }
    
    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
     
    # Verify provider
    $prov = ltrim($doc->getElementsByTagName('clID')->item(0)->nodeValue,"0");
    if ($prov!=$moduleparams["Username"]) {
        $values["error"] = _registrobr_lang("getcontactnotallowed");
        logModuleCall("registrobr",$values["error"],$request,$response);
        return $values;
    }
    
    $domaininfo=array();
    for ($i=0; $i<=2; $i++) $domaininfo[$doc->getElementsByTagName('contact')->item($i)->getAttribute('type')]=$doc->getElementsByTagName('contact')->item($i)->nodeValue;
    $Contacts["Admin"]=$domaininfo["admin"];
    $Contacts["Tech"]=$domaininfo["tech"];
    
    
    
    # Get TaxPayer ID for obtaining Reg Info
    $RegistrantTaxID=$doc->getElementsByTagName('organization')->item(0)->nodeValue;

    # Returned CNPJ has extra zero at left
    if(isCpfValid($RegistrantTaxID)!=TRUE) { $RegistrantTaxID=substr($RegistrantTaxID,1); };
    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);

	# Grab reg info
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
                        <clTRID>'.mt_rand().mt_rand().'</clTRID>
                    </command>
                </epp>
                ';

    $response = $client->request($request);
       
	# Parse XML result

        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results

    if($coderes != '1000') {
	return _registrobr_server_error('getcontactorginfoerrorcode',$coderes,$msg,$reason,$request,$response);
    }

    $Contacts["Registrant"]= $contact;
   
    
    # Companies have both company name and contact name, individuals only have their own name 
    if (isCnpjValid($RegistrantTaxIDDigits)==TRUE) {
        $values["Registrant"][_registrobr_lang("companynamefield")] = $doc->getElementsByTagName('name')->item(0)->nodeValue;
    } else { $values["Registrant"][_registrobr_lang("fullnamefield")] = $doc->getElementsByTagName('name')->item(0)->nodeValue; }

        
    #Get Org, Adm and Tech Contacts
    foreach ($Contacts as $type => $value) {
                    $request = '<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
                                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                                    <command>
                                        <info>
                                            <contact:info xmlns:contact="urn:ietf:params:xml:ns:contact-1.0"
                                            xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0
                                            contact-1.0.xsd">
                                                <contact:id>'.$value.'</contact:id>
                                            </contact:info>
                                        </info>
                                        <clTRID>'.mt_rand().mt_rand().'</clTRID>
                                    </command>
                            </epp>';

                    $response = $client->request($request);

                    # Parse XML result
        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	$doc = $answer['doc'];

	# Check results
                    if($coderes != '1000') {
			return _registrobr_server_error('getcontacttypeerrorcode',$coderes,$msg,$reason,$request,$response);
                    }
    
                    $values[$type][_registrobr_lang("fullnamefield")] = $doc->getElementsByTagName('name')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("streetnamefield")] = $doc->getElementsByTagName('street')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("streetnumberfield")] = $doc->getElementsByTagName('street')->item(1)->nodeValue;
                    $values[$type][_registrobr_lang("addresscomplementsfield")] = $doc->getElementsByTagName('street')->item(2)->nodeValue;
                    $values[$type][_registrobr_lang("citynamefield")] = $doc->getElementsByTagName('city')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("stateprovincefield")] = $doc->getElementsByTagName('sp')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("zipcodefield")] = $doc->getElementsByTagName('pc')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("countrycodefield")] = $doc->getElementsByTagName('cc')->item(0)->nodeValue;
                    $values[$type][_registrobr_lang("phonenumberfield")] = $doc->getElementsByTagName('voice')->item(0)->nodeValue;
                    $values[$type]["Email"] = $doc->getElementsByTagName('email')->item(0)->nodeValue;
                    }        
 	return $values;
}

# Function to save contact details

function registrobr_SaveContactDetails($params) {

    # If nothing was changed, return
    if ($params["contactdetails"]==$params["original"]["contactdetails"]) {
        $values=array();
        return $values;
    }
    
    # Setup include dir
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
	set_include_path($include_path . PATH_SEPARATOR . get_include_path());
    
    # Include CPF and CNPJ stuff we need
    require_once 'isCnpjValid.php';
    require_once 'isCpfValid.php';
    
    # We need pear for the error handling
    require_once "PEAR.php";

    	
    $tld = _registrobr_convert_to_punycode($params["original"]["tld"]);
    $sld = _registrobr_convert_to_punycode($params["original"]["sld"]);
    	
    $domain_punycode = $sld.'.'.$tld;
    	

    # Grab domain, organization and contact details
    $client = _registrobr_Client();
    # Create new EPP client
    if (PEAR::isError($client)) {
	return _registrobr_pear_error($client,'savecontactconnerror');

    }
    # Create new EPP client
    
    $request = '
        <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0"
        xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
                <info>
                    <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                        <domain:name hosts="all">'.$domain_punycode.'</domain:name>
                    </domain:info>
                </info>
            </command>
        </epp>
        ';
  
    $response = $client->request($request);

	# Parse XML result		
	# Check results	
	$answer = _registrobr_parse_response($response);
	$coderes = $answer['coderes'];
	$msg = $answer['msg'];
	$reason = $answer['reason'];	
	$contact = $answer['contact'];
	$doc = $answer['doc'];
	# Check results
	
    if($coderes != '1000') {
		return _registrobr_server_error('savecontactdomaininfoerrorcode',$coderes,$msg,$reason,$request,$response);

    }
        
    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
    
    # Verify provider
    $prov = ltrim($doc->getElementsByTagName('clID')->item(0)->nodeValue,"0");

    if ($prov!=$moduleparams["Username"]) {
        $values["error"] = _registrobr_lang("savecontactnotalloweed");
        logModuleCall("registrobr",$values["error"],$request,$response);
        return $values;
    }
   
    # Grab Admin, Billing, Tech ID

    $Contacts=array();
    for ($i=0; $i<=2; $i++) {
    	$Contacts[ucfirst($doc->getElementsByTagName('contact')->item($i)->getAttribute('type'))]=$doc->getElementsByTagName('contact')->item($i)->nodeValue;
    }
    $NewContacts=$Contacts;

    # Get TaxPayer ID for obtaining Reg Info
    $RegistrantTaxID=$doc->getElementsByTagName('organization')->item(0)->nodeValue;

    # Returned CNPJ has extra zero at left
    if(isCpfValid($RegistrantTaxID)!=TRUE) { $RegistrantTaxID=substr($RegistrantTaxID,1); };
    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);


    # This flag will signal the need for doing a domain update or not
    $DomainUpdate = FALSE ; 

    # This flag will signal the need for doing a brorg update or not
    $OrgUpdate = FALSE ;
    
    # Verify which contacts need updating
    $ContactTypes = array ("Registrant","Admin","Tech");
    
    
    foreach ($ContactTypes as $type)  {
        if ($params["contactdetails"][$type]!=$params["original"][$type]) {
        	
			if (empty($params["contactdetails"][$type][_registrobr_lang("streetnamefield")])) {
				$contact_street1 = $params["contactdetails"][$type]["Address 1"];
				$contact_street2 = $params["contactdetails"][$type]["Address 2"];
			}
			else {
				$contact_street1 = $params["contactdetails"][$type][_registrobr_lang("streetnamefield")];
				$contact_street2 = '';
			}
			$contact_street3 = $params["contactdetails"][$type][_registrobr_lang("streetnumberfield")];
			$contact_street4 = $params["contactdetails"][$type][_registrobr_lang("addresscomplementsfield")];
			$contact_city = $params["contactdetails"][$type]["City"];//(empty($params["contactdetails"][$type][_registrobr_lang("citynamefield")]) ? $params["contactdetails"][$type]["City"] : $params["contactdetails"][$type][_registrobr_lang("citynamefield")]);
			$contact_province = (empty($params["contactdetails"][$type][_registrobr_lang("stateprovincefield")]) ? _registrobr_StateProvince($params["contactdetails"][$type]["State"]) : $params["contactdetails"][$type][_registrobr_lang("stateprovincefield")]);
			$contact_zip = (empty($params["contactdetails"][$type][_registrobr_lang("zipcodefield")]) ? $params["contactdetails"][$type]["Postcode"] : $params["contactdetails"][$type][_registrobr_lang("zipcodefield")]);
			$contact_details = (empty($params["contactdetails"][$type][_registrobr_lang("countrycodefield")]) ? $params["contactdetails"][$type]["Country"] : $params["contactdetails"][$type][_registrobr_lang("countrycodefield")]);
			
			# Start by creating a new contact with the updated information
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
	                                        <contact:name>'.(empty($params["contactdetails"][$type]["Nome e Sobrenome"]) ? $params["contactdetails"][$type]["Full Name"] : $params["contactdetails"][$type]["Nome e Sobrenome"]).'</contact:name>
	                                        <contact:addr>
													<contact:street>'.$contact_street1.'</contact:street>
													<contact:street>'.$contact_street2.'</contact:street>
													<contact:street>'.$contact_street3.'</contact:street>
	                                            	<contact:street>'.$contact_street4.'</contact:street>
	                                            <contact:city>'.$contact_city.'</contact:city>
	                                            <contact:sp>'.$contact_province.'</contact:sp>
	                                            <contact:pc>'.$contact_zip.'</contact:pc>
	                                            <contact:cc>'.$contact_details.'</contact:cc>
	                                        </contact:addr>
	                                    </contact:postalInfo>
	                                    <contact:voice>'.substr((empty($params["contactdetails"][$type][_registrobr_lang("phonenumberfield")]) ? $params["contactdetails"][$type]["Phone Number"] : $params["contactdetails"][$type][_registrobr_lang("phonenumberfield")]),1).'</contact:voice>
	                                    <contact:email>'.$params["contactdetails"][$type]["Email"].'</contact:email>
	                                    <contact:authInfo>
	                                                <contact:pw/>
	                                    </contact:authInfo>
	                                </contact:create>
	                            </create>
	                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
	                        </command>
	                </epp>';
	        $response = $client->request($request);
	
			# Check results	
			$answer = _registrobr_parse_response($response);
			$coderes = $answer['coderes'];
			$msg = $answer['msg'];
			$reason = $answer['reason'];
			$contact = $answer['contact'];
			$doc = $answer['doc'];
			# Check results
			
	        if($coderes != '1000') {
				return _registrobr_server_error('savecontacttypeerrorcode',$coderes,$msg,$reason,$request,$response);
	        }
	        $NewContacts[$type] = $doc->getElementsByTagName('id')->item(0)->nodeValue;

	        if ($type!="Registrant") {
	        		$DomainUpdate=TRUE;
	        }
	        else {
	            	$OrgUpdate=TRUE;
	            	$OrgContactXML=$request;
	        }   
        }
    }

    if ($DomainUpdate==TRUE) {

    	//print_r($NewContacts);print_r("XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX");print_r($Contacts);
        
    	$NewContacts["Billing"]=$NewContacts["Admin"];
        $request='
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
                <command>
                    <update>
                        <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" 
                        xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 
                        domain-1.0.xsd"> 
                            <domain:name>'.$domain_punycode.'</domain:name>
                            <domain:add>';
                            foreach ($NewContacts as $type => $id) if ($type!="Registrant") $request.='<domain:contact type="'.strtolower($type).'">'.$id.'</domain:contact>' ;
                            $request.='</domain:add>
                            <domain:rem>';
                            foreach ($Contacts as $type => $id) if ($type!="Registrant") $request.='<domain:contact type="'.strtolower($type).'">'.$id.'</domain:contact>' ;
                            $request.='
                            </domain:rem>
                        </domain:update>
                    </update>
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
                </command>
            </epp>';
                            
        $response = $client->request($request);
        # Check results	
		$answer = _registrobr_parse_response($response);
		$coderes = $answer['coderes'];
		$msg = $answer['msg'];	
		$reason = $answer['reason'];
		$contact = $answer['contact'];
		# Check results
        if($coderes != '1000') {
	    	return _registrobr_server_error('savecontactdomainupdateerrorcode',$coderes,$msg,$reason,$request,$response);
        }
        

    }
       
    # Has registrant information changed ?
    if ($OrgUpdate==TRUE) {
        # Grab reg info
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
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
                </command>
            </epp>
            ';

            $response = $client->request($request);
            
            # Parse XML result
			# Check results	
			$answer = _registrobr_parse_response($response);
			$coderes = $answer['coderes'];
			$msg = $answer['msg'];
			$reason = $answer['reason'];
			$contact = $answer['contact'];
			$doc = $answer['doc'];
			# Check results
            if($coderes != '1000') {
		    	return _registrobr_server_error('savecontactorginfoeerrorcode',$coderes,$msg,$reason,$request,$response);
            }

            # Get current org contact

            $Contacts["Registrant"]=$doc->getElementsByTagName('contact')->item(0)->nodeValue;
        
            # With current org contact we can now do an org update
        
            # Parse XML org contact request 
            $doc= new DOMDocument();
            $doc->loadXML($OrgContactXML);
            $request='<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
                        <command>
                            <update>
                                <contact:update xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" 
                                xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 
                                contact-1.0.xsd"> 
                                    <contact:id>'.$RegistrantTaxIDDigits.'</contact:id>
                                    <contact:chg>    
                                        <contact:postalInfo type="loc">
                                            <contact:name>';
                                            if (isCpfValid($RegistrantTaxIDDigits)==TRUE) { $request.=$doc->getElementsByTagName('name')->item(0)->nodeValue; }
                                            else { $request.=( empty($params["contactdetails"]["Registrant"][_registrobr_lang("companynamefield")]) ? $params["contactdetails"]["Registrant"]["Company Name"] : $params["contactdetails"]["Registrant"][_registrobr_lang("companynamefield")]);
                                            }
            
                                            $request.='
                                            </contact:name>
                                            <contact:addr>
                                                <contact:street>'.$doc->getElementsByTagName('street')->item(0)->nodeValue.'</contact:street>
                                                <contact:street>'.$doc->getElementsByTagName('street')->item(1)->nodeValue.'</contact:street>
                                                <contact:street>'.$doc->getElementsByTagName('street')->item(2)->nodeValue.'</contact:street>
                                                <contact:city>'.$doc->getElementsByTagName('city')->item(0)->nodeValue.'</contact:city>
                                                <contact:sp>'.$doc->getElementsByTagName('sp')->item(0)->nodeValue.'</contact:sp>
                                                <contact:pc>'.$doc->getElementsByTagName('pc')->item(0)->nodeValue.'</contact:pc>
                                                <contact:cc>'.$doc->getElementsByTagName('cc')->item(0)->nodeValue.'</contact:cc>
                                            </contact:addr>
                                        </contact:postalInfo>
                                        <contact:voice>'.$doc->getElementsByTagName('voice')->item(0)->nodeValue.'</contact:voice>
                                        <contact:email>'.$doc->getElementsByTagName('email')->item(0)->nodeValue.'</contact:email>
                                    </contact:chg>
                                </contact:update>
                            </update>
                            <extension>
                                <brorg:update xmlns:brorg="urn:ietf:params:xml:ns:brorg-1.0" 
                                xsi:schemaLocation="urn:ietf:params:xml:ns:brorg-1.0 
                                brorg-1.0.xsd"> 
                                    <brorg:organization>'.$RegistrantTaxID.'</brorg:organization>
                                    <brorg:add>
                                        <brorg:contact type="admin">'.$NewContacts["Registrant"].'</brorg:contact>
                                    </brorg:add>
                                    <brorg:rem>
                                        <brorg:contact type="admin">'.$Contacts["Registrant"].'</brorg:contact>
                                    </brorg:rem>
                                    <brorg:chg>';
                                        if (isCnpjValid($RegistrantTaxIDDigits)) $request.='<brorg:responsible>'.$doc->getElementsByTagName('name')->item(0)->nodeValue.'</brorg:responsible>';
                                        $request.='
                                    </brorg:chg>
                                </brorg:update>
                            </extension>
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
                </epp>';

            $response = $client->request($request);

            # Parse XML result

        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results
            if($coderes != '1000') {
		    return _registrobr_server_error('savecontactorgupdateeerrorcode',$coderes,$msg,$reason,$request,$response);
            }           

    }
    $values = array();
    return $values;
}

# Domain Delete (used in .br only for Add Grace Period)
    
function registrobr_RequestDelete($params) {

    # We need pear for the error handling
    require_once "PEAR.php";
    
    # Create new EPP client
    $client = _registrobr_Client();
    if (PEAR::isError($client)) {
        return _registrobr_pear_error($client,'deleteconnerror');

    }

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
                        <clTRID>'.mt_rand().mt_rand().'</clTRID>
                    </command>
                </epp>
                ';

    $response = $client->request($request);

    # Parse XML
	$answer = _registrobr_parse_response($response);
    $coderes = $answer['coderes'];
    $msg = $answer['msg'];
    $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results
    if($coderes != '1000') {
        
        #If unknown domain, could be a ticket
        if($coderes == '2303') {
            $values=registrobr_Getnameservers($params);
                        
            # If no error, domain is still a ticket, so we remove the nameservers to prevent it becoming a domain
            if (empty($values["error"])) {
                $setparams=$params;
                $setparams["ns1"]='';
                $setparams["ns2"]='';
                $setparams["ns3"]='';
                $setparams["ns4"]='';
                $setparams["ns5"]='';
                
                $values=registrobr_SaveNameservers($setparams);
                if (empty($values["error"])) {
                    $values=array();
                    return $values ;
                }
                    
                return $values;
            }
        }
        return _registrobr_server_error('deleteerrorcode',$coderes,$msg,$reason,$request,$response);

    }

    return $values ;
}

function registrobr_Sync($params) {
    
    # We need pear for the error handling
    require_once "PEAR.php";
    
    # Get an EPP connection
    $client = _registrobr_Client();
    if (PEAR::isError($client)) {
        return _registrobr_pear_error($client,'syncconnerror');
    }
    
    #For every domain sync, also do a poll queue clean
    _registrobr_Poll($client);
    
    #Request a sync for the specified domain
    $values = _registrobr_SyncRequest($client,$params);
    return $values;
}
    
function _registrobr_SyncRequest($client,$params) {

    # Grab variables
    $domain = $params['domain'];
    $domainid = $params['domainid'];
    $moduleparams = getregistrarconfigoptions('registrobr');
    $table = "mod_registrobr";
    $fields = "clID,domainid,domain,ticket";
    $where = array("clID"=>$moduleparams['Username'],"domainid"=>$domainid,"domain"=>$domain);
    $result = select_query($table,$fields,$where);
    $data = mysql_fetch_array($result);
    $ticket = $data['ticket'];
    
    #Initialize return values
    $values=array();
    
    if(empty($ticket)) {
        $values["error"]=_registrobr_lang("syncdomainnevercreated");
        return $values;
    }

    $request = '
            <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0"
            xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                <command>
                    <info>
                        <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                            <domain:name hosts="all">'.$domain.'</domain:name>
                        </domain:info>
                    </info>
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
                </command>
            </epp>
            ';
    
    $response = $client->request($request);
    
	# Parse XML result		
        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results
    
    # Check if result is ok
	if($coderes != '1000') {
        	if ($coderes != '2303') {
			return _registrobr_server_error('syncerrorcode',$coderes,$msg,$reason,$request,$response);
        	}
        
    # See if domain not found is due to domain still being a ticket
    $request = '
            <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0"
            xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                <command>
                    <info>
                        <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                            <domain:name hosts="all">'.$domain.'</domain:name>
                        </domain:info>
                    </info>
                    <extension>
                        <brdomain:info xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0"
                        xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0
                        brdomain-1.0.xsd">
                            <brdomain:ticketNumber>'.$ticket.'</brdomain:ticketNumber>
                        </brdomain:info>
                    </extension>
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
                </command>
            </epp>
            ';
        
    $response = $client->request($request);
        
    # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	
    # Check results
    if($coderes == '1000') {
            #Guess: no info equals pending
            return $values;
    }
        
    if ($coderes != '2303') {
		return _registrobr_server_error('syncerrorcode',$coderes,$msg,$reason,$request,$response);
    }
    
    $values["error"] = _registrobr_lang('Domain').$domain._registrobr_lang('syncdomainnotfound');
    return $values;
    }
    
    $doc=$answer['doc'];
    $createdate = substr($doc->getElementsByTagName('crDate')->item(0)->nodeValue,0,10);
    $values['registrationdate'] = $createdate;
    $nextduedate = substr($doc->getElementsByTagName('exDate')->item(0)->nodeValue,0,10);
    $holdreasons = $doc->getElementsByTagName('onHoldReason');
    
    #if ticket number is different, this is actually a new domain with the same name
    if ($doc->getElementsByTagName('ticketNumber')->item(0)->nodeValue!=$ticket) {
        $values['expired'] = true ;
        $values['expirydate'] = $createdate;
    } elseif (!empty($holdreasons)) {
        if (array_search("billing",$holdreasons)!=FALSE) {
            $values['expired'] = true;
            $values['expirydate'] = $nextduedate;
        }
    } else {
        $values['active'] = true;
        $values['expirydate'] = $nextduedate;
        
    }
    return $values;
}

function _registrobr_Poll($client) {
          
  
    
    # We need pear for the error handling
    require_once "PEAR.php";
    
    # We need XML beautifier for showing understable XML code
    require_once dirname(__FILE__) . '/BeautyXML.class.php';
    
    
    # We need EPP stuff
    
    require_once dirname(__FILE__) . '/Net/EPP/Frame.php';
    require_once dirname(__FILE__) . '/Net/EPP/Frame/Command.php';
    require_once dirname(__FILE__) . '/Net/EPP/ObjectSpec.php';
    
    # Get module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
    
   
    
    # Loop with message queue
    while (!$last) {
          
        # Request messages
        $request = '
                    <epp xmlns="urn:ietf:params:xml:ns:epp-1.0"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                        <command>
                            <poll op="req"/>
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
                    </epp>
                    ';
        $response = $client->request($request);
          
        # Decode response
        
        $answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
        $contact = $answer['contact'];
        $doc = $answer['doc'];
        
        # Check results
        
        # This is the last one
        if ($coderes == 1300) {
            $last = 1;
        } else  {
            $msgid = $doc->getElementsByTagName('msgQ')->item(0)->getAttribute('id');
            $content = _registrobr_lang("Date").substr($doc->getElementsByTagName('qDate')->item(0)->nodeValue,0,10)." ";
            $content .= _registrobr_lang("Time").substr($doc->getElementsByTagName('qDate')->item(0)->nodeValue,11,10)." UTC\n";
            $code = $doc->getElementsByTagName('code')->item(0)->nodeValue;
            $content .= _registrobr_lang("Code").$code."\n";
            $content .= _registrobr_lang("Text").$doc->getElementsByTagName('txt')->item(0)->nodeValue."\n";
            $reason = $doc->getElementsByTagName('reason');
            if (!empty($reason)) $content .= _registrobr_lang("Reason").$doc->getElementsByTagName('reason')->item(0)->nodeValue."\n";
            $content .= _registrobr_lang("FullXMLBelow");
            $bc = new BeautyXML();
            
            $content .= htmlentities($bc->format($response));
            
            $ticket='';
            $domain='';
            $taxpayerID='';
            
            switch($code) {
                case '1': case '22': case '28': case '29':
                    $ticket = $doc->getElementsByTagName('ticketNumber')->item(0)->nodeValue;
                    
                    #no break, poll messages with ticketNumber also have domain in objectId
                    
                case '2': case '3': case '4': case '5': case '6': case '7': case '8': case '9':
                case '10': case '11': case '12': case '13': case '14': case '15': case '16': case '17': case '18':
                case '20':
                case '107': case '108':
                case '304': case '305':
                    
                    $domain = $doc->getElementsByTagName('objectId')->item(0)->nodeValue;
                    break;
                
                case '100': case '101': case '102': case '103': case '106':
                    
                    $taxpayerID = $doc->getElementsByTagName('objectId')->item(0)->nodeValue;
                    break;
            }
            
            $taxpayerID=preg_replace("/[^0-9]/","",$taxpayerID);
            
            if (in_array($code,array('300','302','303','305'))==TRUE) {
                            $issue["priority"] = "High";
                            $issue["deptid"] = $moduleparams["FinanceDept"];
            } elseif (in_array($code,array('301','304'))==TRUE) {
                            $issue["priority"] = "Low";
                            $issue["deptid"] = $moduleparams["FinanceDept"];
            }
            else {
                            $issue["priority"] = "Low" ;
                            $issue["deptid"] = $moduleparams["TechDept"];
                
            }
                    
            
            $issue["clientid"]=0;
            
            if (!empty($domain)) {
               
                $issue["domain"] =$domain;
                
                if (empty($ticket)) {
                    $queryresult = mysql_query("SELECT domainid FROM mod_registrobr WHERE clID='".$moduleparams['Username']." domain='".$domain."'");
                    $data = mysql_fetch_array($queryresult);
                    
                    # if there is only one domain with this name, we can match it to a domainid without a ticket
                    if (count($data)==1) {
                        $domainid = $data['domainid'];
                    }
                } else {
                    $queryresult = mysql_query("SELECT domainid FROM mod_registrobr WHERE clID='".$moduleparams['Username']." ticket='".$ticket."'");
                    $data = mysql_fetch_array($queryresult);
                    $domainid = $data['domainid'];
                }
                if (!empty($domainid)) {
                    $issue["domainid"] = $domainid;
                    $queryresult = mysql_query("SELECT userid FROM tbldomains WHERE id='".$domainid."'");
                    $data = mysql_fetch_array($queryresult);
                    $issue["clientid"]=$data['userid'];

                }
            }
            
            if (!empty($taxpayerID)&&($issue["clientid"]==0)) {
                $issue["clientid"] = "1";
                
            }
        
        
        $issue["subject"] = _registrobr_lang("Pollmsg");
        $issue["message"] = $content;
        $user = $moduleparams['Sender'];
        $queryresult = mysql_query("SELECT firstname,lastname,email FROM tbladmins WHERE username = '".$user."'");
        $data = mysql_fetch_array($queryresult);
                                         
        
        $issue["name"] = $data["firstname"]." ".$data["lasttname"];
        $issue["email"] = $data["email"];
            
            
        $results = localAPI("openticket",$issue,$user);
        if ($results['result']!="success") {
                logModuleCall("registrobr",_registrobr_lang("epppollerror"),$issue,$results);
                return;
            }
        

            
        # Ack poll message
        $request='  <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd"> 
                        <command>
                            <poll op="ack" msgID="'.$msgid.'"/>
                            <clTRID>'.mt_rand().mt_rand().'</clTRID>
                        </command>
                    </epp>
                    ';
        $response = $client->request($request);

        # Decipher XML
        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results
        


        # Check result
        if($coderes != '1000') {
		return _registrobr_server_error('pollackerrorcode',$coderes,$msg,$reason,$request,$response);
        }
    
    #brace below close msg if
    }

    #brace below close while(!last) loop
    }
        
    return;
}



# Function to create internal .br EPP request

function _registrobr_Client() {

	# Setup include dir
	$include_path = dirname(__FILE__);
	set_include_path($include_path . ':' . get_include_path());
    

	# Include EPP stuff we need
	require_once dirname(__FILE__) . '/Net/EPP/Client.php';
	require_once dirname(__FILE__) . '/Net/EPP/Protocol.php';
    
    # We need pear for the error handling
    require_once "PEAR.php";

	# Grab module parameters

	$moduleparams = getregistrarconfigoptions('registrobr');
    
	if (!isset($moduleparams['TestMode']) && empty($moduleparams['Certificate'])) {
		$errormsg =  _registrobr_lang("specifypath") ;
		logModuleCall ("registrobr",_registrobr_lang("configerr"),$moduleparams,$errormsg);
		return $errormsg ;
    }

    if (!isset($moduleparams['TestMode']) && !file_exists($moduleparams['Certificate'])) {
        $errormsg =  _registrobr_lang("invalidpath")  ;
        logModuleCall ("registrobr",_registrobr_lang("configerr"),$moduleparams,$errormsg);
        return $errormsg ;
    }

	if (!isset($moduleparams['TestMode']) && empty($moduleparams['Passphrase'])) {
        $errormsg =   _registrobr_lang("specifypassphrase")  ;
        logModuleCall ("registrobr",_registrobr_lang("configerr"),$moduleparams,$errormsg);
        return $errormsg ;
    }

    # Use OT&E if test mode is set

 	if (!isset($moduleparams['TestMode'])) {
          $Server = 'epp.registro.br' ;
		  $Options = array (
                            'ssl' => array (
                                            'passphrase' => $moduleparams['Passphrase'],
                                            'local_cert' => $moduleparams['Certificate']));

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
		logModuleCall("registrobr",_registrobr_lang("eppconnect"),"tls://".$Server.":".$Port,$res);
		return $res;

	}

	# Perform login
	$request='
            <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
                <command>
                    <login>
                        <clID>'.$moduleparams['Username'].'</clID>
                        <pw>'.$moduleparams['Password'].'</pw>
                        <options>
                            <version>1.0</version>
                            <lang>';
                            $request.=($moduleparams['Language']=='Portuguese' ? 'pt' : 'en' );
                            $request.='</lang>
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
                    <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';
    
   $response = $client->request($request);
        # Check results	
	$answer = _registrobr_parse_response($response);
        $coderes = $answer['coderes'];
        $msg = $answer['msg'];
        $reason = $answer['reason'];
	$contact = $answer['contact'];
	# Check results
    if($coderes != '1000') {
		return _registrobr_server_error('epplogin',$coderes,$msg,$reason,$request,$response);
		//before,the code wasn't returning the error
    }
    return $client;
}

    
function _registrobr_normaliza($string) {
        
    $string = str_replace('&nbsp;',' ',$string);
    $string = trim($string);
    $string = html_entity_decode($string,ENT_QUOTES,'UTF-8');
        
    //Instead of The Normalizer class ... requires (PHP 5 >= 5.3.0, PECL intl >= 1.0.0)
    $normalized_chars = array( 'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', ' ' => '');
    
    $string = strtr($string,$normalized_chars);
    $string = strtolower($string);
    return $string;
}
    
function _registrobr_StateProvince($sp) {
        
    if (strlen($sp)==2) return $sp;
    $estado = _registrobr_normaliza($sp);
    $map = array(
                "acre" => "AC",
                "alagoas" => "AL",
                "amazonas" => "AM",
                "amapa" => "AP",
                "bahia" => "BA",
                "baia" => "BA",
                "ceara" => "CE",
                "distritofederal" => "DF",
                "espiritosanto" => "ES",
                "espiritusanto" => "ES",
                "goias" => "GO",
                "goia" => "GO",
                "maranhao" => "MA",
                "matogrosso" => "MT",
                "matogroso" => "MT",
                "matogrossodosul" => "MS",
                "matogrossosul" => "MS",
                "matogrossodesul" => "MS",
                "minasgerais" => "MG",
                "minasgeral" => "MG",
                "para" => "PA",
                "paraiba" => "PB",
                "parana" => "PR",
                "pernambuco" => "PE",
                "pernanbuco" => "PE",
                "piaui" => "PI",
                "riodejaneiro" => "RJ",
                "rio" => "RJ",
                "riograndedonorte" => "RN",
                "riograndenorte" => "RN",
                "rondonia" => "RO",
                "riograndedosul" => "RS",
                "riograndedesul" => "RS",
                "riograndesul" => "RS",
                "roraima" => "RR",
                "santacatarina" => "SC",
                "sergipe" => "SE",
                "saopaulo" => "SP",
                "tocantins" => "TO"
                );
    if(!empty($map[$estado])){
            return $map[$estado];
        } else {
                return $sp;
        }
    }
                            

function _registrobr_identify_env_encode() {
	#Encoding default UTF-8


	if(!empty($CONFIG['Charset'])){
                
		return $CONFIG['Charset'];
	}
	else {
    		$table = "tblconfiguration";
    		$fields = "Charset";
    		$where = array();
    		$result = select_query($table,$fields,$where);
    		$data = mysql_fetch_array($result);

    		if($data['Charset']) {
			return $data['Charset'];
		}
		else {
			return 'UTF-8';
		}
	}

}
function _registrobr_convert_to_punycode($string){

	# Setup include dir
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
	set_include_path($include_path . PATH_SEPARATOR . get_include_path());
	
	require_once('Idna/idna_convert.class.php');
		
	$IDN = new idna_convert(array('idn_version' => '2008'));
	
	$encoded = $IDN->encode($string);
	
	return $encoded;
	
}
function _registrobr_detect_encode($text){
	$current_encoding = mb_detect_encoding($text, 'auto');
	if(empty($current_encoding)){
		return 'UTF-8';
	}
	else {
		return $current_encoding;
	}
	
}

function _registrobr_set_encode($text,$encode) {
	
	$current_encoding = _registrobr_detect_encode($text);
    if(empty($encode)){
    	$to_encode = _registrobr_identify_env_encode();
    }
    else {
    	$to_encode = $encode."//TRANSLIT";
    }

    $text = iconv($current_encoding, $to_encode, $text);
    return $text;
}

function _registrobr_lang($msgid) {

    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');
    $msgs = array (
                    "epplogin" => array ("Erro no login EPP código ","EPP login error code "),
                    "msg" => array (" mensagem '"," message '"),
                    "reason" => array (" motivo '"," reason '"),
                    "eppconnect" => array ("Erro de conexão EPP","EPP connect error"),
                    "configerr" => array ("Erro nas opções de configuração","Config options errorr"),
                    "specifypath" => array ("Favor informar o caminho para o arquivo de certificado","Please specifity path to certificate file"),
                    "invalidpath" => array ("Caminho para o arquivo de certificado inválido", "Invalid certificate file path"),
                    "specifypassphrase" => array ("Favor especificar a frase secreta do certificado", "Please specifity certificate passphrase"),
                    "deleteerrorcode" => array ("Erro na remoção de domíenio código ","Domain delete: error code "),
                    "deleteconnerror" => array ("Falha na conexão EPP ao tentar remover domínio erro ","Domain delete: EPP connection error "),
                    "getnsconnerror" => array ("Falha na conexão EPP ao tentar obter servidores DNS erro ", "get nameservers: EPP connection error "),
                    "setnsconnerror" => array ("Falha na conexão EPP ao tentar alterar servidores DNS erro ", "set nameservers: EPP connection error "),
                    "setnsgeterrorcode" => array ("Falha ao tentar obter servidores DNS atuais para alterar servidores DNS código ", "set nameservers: error getting nameservers code "),
                    "setnsupdateerrorcode" => array ("Falha ao alterar servidores DNS código ","set nameservers: update servers error code "),
                    "cpfcnpjrequired" => array ("Registro de domínios .br requer CPF ou CNPJ","register domain: .br registrations require valid CPF or CNPJ"),
                    "companynamerequired" => array ("Registros com CNPJ requerem nome da empresa preenchido",".br registrations with CNPJ require Company Name to be filled in"),
                    "registerconnerror" => array ("Falha na conexão EPP ao tentar registrar domínio erro ", "register domain: EPP connection error "),
                    "notallowed" => array ("Entidade só pode registrar domínios por provedor atualmente designado.", "entity can only register domains through designated registrar."),
                    "registergetorgerrorcode" => array ("Falha ao obter status de entidade para registrar domínio erro ","register domain: get org status error code "),
                    "registercreateorgcontacterrorcode" => array ("Falha ao criar contato para entidade erro ","register domain: create org contact error code "),
                    "registercreateorgerrorcode" => array ("Falha ao criar entidade para registrar domínio erro ","register domain: create org error code "),
                    "registererrorcode" => array ("Falha ao registrar domínio erro ","register domain error code "),
                    "renewconnerror" => array ("Falha na conexão EPP ao renovar domínio erro ", "renew domain: EPP connection error "),
                    "renewinfoerrorcode" => array ("Falha ao obter informações de domínio ao renovar domínio erro ", "renew: domain info error code "),
                    "renewerrorcode" => array ("Falha ao renovar domínio erro ","domain renew: error code "),
                    "getcontactconnerror" => array ("Falha na conexão EPP ao obter dados de contato erro ","get contact details: EPP connection error "), 
                    "getcontacterrorcode" => array ("Falha ao obter dados de contato erro ", "get contact details: domain info error code "),
                    "getcontactnotallowed" => array ("Somente provedor designado pode obter dados deste domínio.","get contact details: domain is not designated to this registrar."),
                    "getcontactorginfoerrorcode" => array ("Falha ao obter informações de entidade detentora de domínio erro ","get contact details: organization info error code "),
                    "getcontacttypeerrorcode" => array ("Falha ao obter dados de contato do tipo ","get contact details: "),
                    "getcontacterrorcode" => array ("código de erro ","contact info error code "),
                    "savecontactconnerror" => array ("Falha na conexão EPP ao gravar contatos erro ", "save contact details: EPP connection error "),
                    "savecontactdomaininfoerrorcode" => array ("Falha ao obter dados de domínio para gravar contatos erro ","set contact details: domain info error code"),
                    "savecontactnotalloweed" => array ("Somente provedor designado pode alterar dados deste domínio.", "Set contact details: domain is not designated to this registrar."),
                    "savecontacttypeerrorcode" => array ("Falha ao criar novo contato do tipo ","save contact details: "),
                    "savecontacterrorcode" => array ("código de erro ","contact create error code "),
                    "savecontactdomainupdateerrorcode" => array ("Falha ao atualizar domínio ao modificar contatos erro ","set contact: domain update error code "),
                    "savecontactorginfoeerrorcode" => array ("Falha de obtenção de informações de entidade ao modificar contatos erro ","set contact: org info error code "),
                    "savecontactorgupdateerrorcode" => array ("Falha ao atualizar entidade ao modificar contatos erro ","set contact: org update error code "),
                    "domainnotfound" => array ("Domínio ainda não registrado.","Domain not yet registered"),
                    "getnserrorcode" => array ("Falha ao obter dados de domínio erro ","get nameserver error code "),
                    "syncconnerror" => array ("Falha na conexão EPP ao sincronizar domínio erro ","domain sync: EPP connection error "),
                    "syncerrorcode" => array ("Falha ao tentar obter informação de domínio código ", "domain sync: error getting domain info code "),
                    "syncdomainnotfound" => array ("não mais registrado."," no longer registered"),
                    "syncdomainunknownstatus" => array(" apresentou status desconhecido: ","domain sync: unknown status code "),
                    "Domain" => array ("Domínio ","Domain "),
                    "domain" => array ("domínio ","domain "),
                    "syncreport" => array("Relatorio de Sincronismo de Dominios Registro.br\n","Registro.br Domain Sync Report\n"),
                    "syncreportdashes" => array ("------------------------------------------------\n","------------------------------\n"),
                    "ERROR" => array ("ERRO: ","ERROR: "),
                    "domainstatusok" => array ("Ativo","Active"),
                    "domainstatusserverhold" => array ("CONGELADO","PENDING"),
                    "domainstatusexpired" => array ("Vencido","Expired"),
                    "is" => array (" está "," is "),
                    "registration" => array ("(Criação: ","(Registered: "),
                    "epppollerror" => array ("Erro de ao fazer EPP Poll","EPP Polling error"),
                    "Pollmsg" => array ("Mensagem de Poll relativa a dominios .br","Poll message about .br domains"),
                    "pollackerrorcode" => array ("Falha ao dar recebimento de mensagem EPP Poll codigo ", "EPP Poll: error acknowledging a message error code "),
                    "Date" => array ("Data ","Date "),
                   "time" => array ("hora ","time "),
                   "Code" => array ("Codigo ", "code "),
                   "Text" => array ("Texto ","Text "),
                   "FullXMLBelow" => array ("Mensagem XML completo abaixo:\n","Full XML message below:\n"),
                       
                    "companynamefield" => array ("Razao Social","Company Name"),
                    "fullnamefield" => array ("Nome e Sobrenome","Full Name"),
                    "streetnamefield" => array ("Logradouro","Street Name"),
                    "streetnumberfield" => array ("Numero", "Street Number"),
                    "addresscomplementsfield" => array ("Complemento", "Address Complements"),
                    "citynamefield" => array ("Cidade","City"),
                    "stateprovincefield" => array ("Estado","State or Province"),
                    "zipcodefield" => array ("CEP","Zip code"),
                    "countrycodefield" => array ("Pais","Country"),
                    "phonenumberfield" => array ("Fone","Phone"),
                    );                   
         
    $langmsg = ($moduleparams["Language"]=="Portuguese" ? $msgs["$msgid"][0] : $msgs["$msgid"][1] );
    $langmsg = _registrobr_set_encode($langmsg);
    return $langmsg;
}

?>
