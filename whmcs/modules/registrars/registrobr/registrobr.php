<?php

# Copyright (c) 2013-2023, NIC.br (R)
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



# Official Website for whmcs-registrobr-epp
# https://github.com/registrobr/whmcs-registrobr-epp


# More information on NIC.br(R) domain registration services, Registro.br(TM), can be found at http://registro.br
# Information for registrars available at http://registro.br/provedor/epp

# NIC.br(R) is a not-for-profit organization dedicated to domain registrations and fostering of the Internet in Brazil. No WHMCS services of any kind are available from NIC.br(R).


use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;



# Configuration array

$include_path = ROOTDIR . '/modules/registrars/registrobr';
set_include_path($include_path . PATH_SEPARATOR . get_include_path());

function registrobr_MetaData() {
    return array(
        'DisplayName' => 'Registro.br',
        'APIVersion' => '1.1',
        'NonLinearRegistrationPricing' => false,
    );
}


function registrobr_getConfigArray() {


    
    


    

    


    $results = localAPI('GetSupportDepartments', array());
    if (($results['result']=='success')&&($results['totalresults'] > 0)) {
        $numdepts = $results['totalresults'];
        foreach ($results['departments']['department'] as &$dept) {
            $deptoptions = $deptoptions . $dept['id'] . "," ;
            $deptnames = $deptnames . $dept['id'] . '-' . $dept['name'] . ",";
            
        }
        $deptoptions = rtrim($deptoptions,",");
        $deptnames = rtrim($deptnames,",");
        
    } else {
       $deptoptions = "1";
       $deptnames = "1 - Undefined";
    }
    
    $configarray = array(
        "BetaUsername" => array( "Type" => "text", "Size" => "16", "Description" => "Numerical Provider ID for Beta (OT&E)" ),
        "BetaPassword" => array( "Type" => "password", "Size" => "20", "Description" => "EPP Password for Beta (OT&E)" ),
        "ProdUsername" => array( "Type" => "text", "Size" => "16", "Description" => "Numerical Provider ID for Production" ),
        "ProdPassword" => array( "Type" => "password", "Size" => "20", "Description" => "EPP Password for Production" ),
        "ProdCertificate" => array( "Type" => "text", "Description" => "Path of production certificate .pem" ),
        "ProdPassphrase" => array( "Type" => "password", "Size" => "20", "Description" => "Passphrase to the production certificate file" ),
        "TestMode" => array( "Type" => "radio" , "Options" => "Beta,Prod", "Description" => "If Beta connects to beta.registro.br instead of production server", "Default" => "Beta"),
        "firstyearprice" => array ("Type" => "text", "Size" >= "16", "Description" => "1st year price for .br domain registrations", "Default" => "40.00"),
        "renewalprice" => array ("Type" => "text", "Size" >= "16", "Description" => "Renewal and additional years price .br for domain registrations", "Default" => "40.00"),
        "ReprovisionTLDs" => array ("Type" => "radio", "Options" => "Unconfigured,No,Yes", "Description" => "Force reprovision of .br TLDs (required to update pricing)", "Default" => "Unconfigured"),
        "TechC" => array( "FriendlyName" => "Tech Contact", "Type" => "text", "Size" => "20", "Description" => "Tech Contact used in new registrations; blank will make registrant the Tech contact" ),
        "TechDept" => array( "FriendlyName" => "Tech Department ID", "Type" => "dropdown", "Options" => $deptoptions, "Description" => $deptnames, "Default" => "1"),
        "FinanceDept" => array( "FriendlyName" => "Finance Department ID", "Type" => "dropdown", "Options" => $deptoptions, "Description" => $deptnames, "Default" => "1"),
        "Language" => array ( "Type" => "radio", "Options" => "English,Portuguese", "Description" => "Escolha Portuguese para mensagens em Portugu&ecircs", "Default" => "English"),
                         #"UnityTesting" => array ( "Type" => "radio", "Options" => "Normal,Case1,Case2,Case3","Description" => "Use only for code quality testing", "Default" => "Normal"),
                         #"UT-Domain" => array( "Type" => "text", "Description" => "Domain name for unity testing"),
                         #"UT-NameServer1" => array( "Type" => "text", "Description" => "Domain name server #1 for unity testing"),
                         #"UT-NameServer2" => array( "Type" => "text", "Description" => "Domain name server #2 for unity testing"),

        "FriendlyName" => array("Type" => "System", "Value"=>"Registro.br"),
        "Description" => array("Type" => "System", "Value"=>"https://registro.br/tecnologia/provedor-hospedagem.html?secao=epp"),


        );

    #$moduleparams = getregistrarconfigoptions('registrobr');

    #print_r($moduleparams);exit;
    
    # Remove # from the comments bellow to perform unity testing
    #if(($moduleparams['TestMode'] == 'on' )and ($moduleparams['UnityTesting'] != 'Normal')){

        //case1 => register a domain
        //case2 => check nameservers,contacts and delete the domain
        //case3 => check nameservers,contacts and renew the domain

        //Check few minutes later if the domain was correct registered (whois -hbeta.registro.br domain)
        //If the domain is ok, change testtype to 0 and load the url below again

        #require_once('RegistroEPP/RegistroEPPFactory.class.php');

        #$objRegistroEPPTest = RegistroEPPFactory::build('RegistroEPPTest');

        #//Register a new domain, with DNS OK
        #$objRegistroEPPTest->testCase($moduleparams);

    #}


    return $configarray;

}

// Availability check
// Fall-back to WHOIS is provided
// Even so, for multi-TLD installations whois.json is preferred

function registrobr_CheckAvailability($params)
{
    // registrar configuration values

    
    require_once('TLDs.php');
    
    $target = ($params['TestMode'] == 'Beta') ?  'https://beta.registro.br/v2/ajax/avail/raw/' : 'https://registro.br/v2/ajax/avail/raw/' ;

    // availability check parameters
    $searchTerm = $params['searchTerm'];
    $tldsToInclude = $params['tldsToInclude'];
    
   
    if (empty($searchTerm)) {
        $searchTerm = $params['sld'];
    }
    
  
    $results = new ResultsList();

    
    foreach ($tldsToInclude as $tld) {
        $searchResult = new SearchResult($searchTerm, $tld);
        
        if (in_array($tld, $registrobr_AllTLDs)) {
            
            
            try {
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $target . $searchTerm . $tld);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $response = curl_exec($ch);
                
                if (curl_errno($ch)) {
                    throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
                }
                curl_close($ch);
                
                $result = json_decode($response, true);
                if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Bad response received from Ajax Avail');
                }
                
                                                               
                switch ($result['status']) {
                    case 0:
                        $searchResult->setStatus(SearchResult::STATUS_NOT_REGISTERED);
                        break;
                    case 2:
                        $searchResult->setStatus(SearchResult::STATUS_REGISTERED);
                        break;
                    case 1:
                    case 3:
                    case 5:
                    case 6:
                    case 7:
                    case 9:
                        $searchResult->setStatus(SearchResult::STATUS_RESERVED);
                        break;
                    case 4:
                    case 8:
                    default:
                        $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
                        break;
                        
                }
                
               
                    
               
                
                
            } catch (\Exception $e) {
                return array(
                             'error' => $e->getMessage(),
                             );
            }
        } else {
            
                $postData = array(
                'domain' => $searchTerm . $tld,
            );
           
            $whois = localAPI('DomainWhois', $postData);
            if ($whois['result']!=='success') {
                $searchResult->setStatus(SearchResult::STATUS_UNKNOWN);
                } elseif ($whois['status']=='unavailable') {
                    $searchResult->setStatus(SearchResult::STATUS_REGISTERED);
                } else {
                    $searchResult->setStatus(SearchResult::STATUS_NOT_REGISTERED);
                }
        }
        
        $results->append($searchResult);
        
        
    }
    
   
    return $results;
    
}

# Function to return current nameservers

function registrobr_GetNameservers($params) {

    /*
     $params example:

      Array
    (
    [domainid] => 54
    [sld] => toccos17
    [tld] => com.br
    [regperiod] => 1
    [registrar] => registrobr
    [regtype] => Register
    [Certificate] =>
    [CNPJ] => 1
    [CPF] => 1
    [FinanceDept] => 1
    [Language] => English
    [Passphrase] =>
    [Password] =>
    [TechC] =>
    [TechDept] => 1
    [TestMode] => on
    [Username] => 237
    )

     *
     */

    require_once('RegistroEPP/RegistroEPPFactory.class.php');


    $domain = $params["sld"].".".$params["tld"];


    # Grab module parameters
    $moduleparams = _registrobr_Selector();
    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPP->set('domain',$domain);
    $objRegistroEPP->set('language',$moduleparams['Language']);

    try {
        $objRegistroEPP->login($moduleparams);
    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }


    try {
            //Request domain info

            $objRegistroEPP->getInfo();

    } catch (Exception $e){
                $values["error"] = $e->getMessage();
                return $values;
            }
        

    $nameservers = $objRegistroEPP->get('nameservers');

    return $nameservers;

    /*
     Expected Output
     Array
    (
    [ns1] => dns1.stabletransit.com
    [ns2] => dns2.stabletransit.com
    )

     */

}

# Function to save set of nameservers

function registrobr_SaveNameservers($params) {

    /*
     Array
     (
     [domainid] => 54
     [sld] => toccos17
     [tld] => com.br
     [regperiod] => 1
     [registrar] => registrobr
     [regtype] => Register
     [ns1] => dns2.stabletransit.com
     [ns2] => dns1.stabletransit.com
     [ns3] =>
     [ns4] =>
     [ns5] =>
     [Certificate] =>
     [CNPJ] => 1
     [CPF] => 1
     [FinanceDept] => 1
     [Language] => English
     [Passphrase] =>
     [Password] =>
     [TechC] =>
     [TechDept] => 1
     [TestMode] => on
     [Username] => 237
     )

     */

    require_once('RegistroEPP/RegistroEPPFactory.class.php');



    $domain = $params["sld"].".".$params["tld"];


    # Grab module parameters
    $moduleparams = _registrobr_Selector();

    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPP->set('domain',$domain);
    $objRegistroEPP->set('language',$params['Language']);


    try {
        $objRegistroEPP->login($moduleparams);
    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }

    try {
        $objRegistroEPP->getInfo();
    }     catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }
    
    logModuleCall('registrobr', 'save nameservers debug',$params,$objRegistroEPP);
    
    $OldNameservers = registrobr_GetNameservers($params);

    $NewNameservers["ns1"] = $params["ns1"];
    $NewNameservers["ns2"] = $params["ns2"];
    $NewNameservers["ns3"] = $params["ns3"];
    $NewNameservers["ns4"] = $params["ns4"];
    $NewNameservers["ns5"] = $params["ns5"];

    try {
        $objRegistroEPP->updateNameServers($OldNameservers,$NewNameservers);
    }     catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
        
    }

    return array(
                 'success' => true,
                 );
}


function registrobr_RegisterDomain($params){


    require_once('RegistroEPP/RegistroEPPFactory.class.php');
    require_once('isCnpjValid.php');
    require_once('isCpfValid.php');
    
       

    $domain = $params["original"]["sld"].".".$params["original"]["tld"];

    # Grab module parameters
    $moduleparams = _registrobr_Selector();

    #################################################################
    #    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    #    $objRegistroEPP->set('domain',$domain);
    #    $objRegistroEPP->set('language',$params['Language']);
    #
    #    try {
    #            $objRegistroEPP->login($moduleparams);
    #    }
    #    catch (Exception $e){
    #            $values["error"] = $e->getMessage();
    #            return $values;
    #    }
	#################################################################

    $isCPF = FALSE ;
    $isCNPJ = FALSE ;

    if (!empty($params['additionalfields']['CPF'])) {
        $RegistrantTaxID = $params['additionalfields']['CPF'] ;
        $isCPF = isCpfValid($RegistrantTaxID) ;
    }

    if (!empty($params['additionalfields']['CNPJ'])) {
        $RegistrantTaxID = $params['additionalfields']['CNPJ'] ;
        $isCNPJ = isCnpjValid($RegistrantTaxID) ;
    }

    if (!empty($params['additionalfields']['CPF ou CNPJ'])) {
        $RegistrantTaxID = $params['additionalfields']['CPF ou CNPJ'] ;
        if (isCpfValid($RegistrantTaxID)) {
            $isCPF = TRUE ;
        }
        if (isCnpjValid($RegistrantTaxID)) {
            $isCNPJ = TRUE ;
        }
    }

    $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');

    if (($isCPF == FALSE) and ($isCNPJ == FALSE)) {
            $values["error"] =$objRegistroEPPBrorg->getMsgLang("cpfcnpjrequired");
            //logModuleCall("registrobr",$values["error"],$params);
            logModuleCall('registrobr', 'ErroCPFCNPJ',  $params,$values["error"],'','',array('BetaPassword','ProdPassword','ProdPassphrase'));

            return $values;
    }

    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);
    // if ($constantIsCPF) {
    //     $RegistrantTaxID = substr($RegistrantTaxIDDigits,0,3).".".substr($RegistrantTaxIDDigits,3,3).".".substr($RegistrantTaxIDDigits,6,3)."-".substr($RegistrantTaxIDDigits,9,2);
    // }
    // else {
    //     $RegistrantTaxID = substr($RegistrantTaxIDDigits,0,2).".".substr($RegistrantTaxIDDigits,2,3).".".substr($RegistrantTaxIDDigits,5,3)."/".substr($RegistrantTaxIDDigits,8,4)."-".substr($RegistrantTaxIDDigits,12,2);
    // }

    $regperiod = $params["regperiod"];


    # Get registrant details
    $name = $params["original"]["firstname"]." ".$params["original"]["lastname"];

    if ($isCPF) {
        $RegistrantOrgName = substr($RegistrantContactName,0,40);

    } else {
        $RegistrantOrgName = substr($params["original"]["companyname"],0,50);
        if (empty($RegistrantOrgName)) {
            $values['error'] = $objRegistroEPPBrorg->getMsgLang("companynamerequired");
            return $values;
        }
    }

    # Domain information and check provider

    $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
    $objRegistroEPPBrorg->set('language',$params['Language']);
    $objRegistroEPPBrorg->set('domain',$domain);

    $objRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
    $objRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);


    try {
        $objRegistroEPPBrorg->login($moduleparams);
        $objRegistroEPPBrorg->getInfo(true);

        $coderes = $objRegistroEPPBrorg->get('coderes');
        if($coderes == '1000'){
            # If it's already on the database, verify new domains can be registered
            $providerID = $objRegistroEPPBrorg->get('clID');
            $objRegistroEPPBrorg->verifyProvider($providerID,$moduleparams["Username"]);
        }
        else {
            # Company or individual not in the database, proceed to org contact creation
            $street1	= $params["original"]["address1"];
            $street2	= $params["original"]["address2"];
            $city	= $params["original"]["city"];
            $sp		= $objRegistroEPPBrorg->StateProvince($params["original"]["state"]);
            $pc		= $params["original"]["postcode"];
            $cc		= $params["original"]["country"];
            $email	= $params["original"]["email"];
            $voice	= substr($params["original"]["fullphonenumber"],1);

            $objRegistroEPPBrorg->set('domain',$domain);
            $objRegistroEPPBrorg->set('name',$name);
            $objRegistroEPPBrorg->set('street1',$street1);
            $objRegistroEPPBrorg->set('street2',$street2);
            $objRegistroEPPBrorg->set('street3',$street3);
            $objRegistroEPPBrorg->set('city',$city);
            $objRegistroEPPBrorg->set('sp',$sp);
            $objRegistroEPPBrorg->set('pc',$pc);
            $objRegistroEPPBrorg->set('cc',$cc);
            $objRegistroEPPBrorg->set('voice',$voice);
            $objRegistroEPPBrorg->set('email',$email);

            $objRegistroEPPBrorg->createData();


            $idt = $objRegistroEPPBrorg->get('id');

            # Create Org
            $objRegistroEPPRegistrant = RegistroEPPFactory::build('RegistroEPPBrorg');
            $objRegistroEPPRegistrant->set('language',$params['Language']);

            $objRegistroEPPRegistrant->set('netClient',$objRegistroEPPBrorg->get('netClient'));
            $objRegistroEPPRegistrant->set('domain',$domain);
            $objRegistroEPPRegistrant->set('contactID',$RegistrantTaxID);
            $objRegistroEPPRegistrant->set('contactIDDigits',$RegistrantTaxIDDigits);
            $objRegistroEPPRegistrant->set('idt',$idt);

            $objRegistroEPPRegistrant->set('name',$name);
            $objRegistroEPPRegistrant->set('street1',$street1);
            $objRegistroEPPRegistrant->set('street2',$street2);
            $objRegistroEPPRegistrant->set('street3',$street3);

            $objRegistroEPPRegistrant->set('city',$city);
            $objRegistroEPPRegistrant->set('sp',$sp);
            $objRegistroEPPRegistrant->set('pc',$pc);
            $objRegistroEPPRegistrant->set('cc',$cc);
            $objRegistroEPPRegistrant->set('voice',$voice);
            $objRegistroEPPRegistrant->set('email',$email);

            $objRegistroEPPRegistrant->createOrgData();
        }

    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }



        # Create domain


    $Nameservers["ns1"] = "a.auto.dns.br";
    $Nameservers["ns2"] = "b.auto.dns.br";
    
    $objRegistroEPPNewDomain = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPPNewDomain->set('language',$params['Language']);
    $objRegistroEPPNewDomain->set('netClient',$objRegistroEPPBrorg->get('netClient'));

    $objRegistroEPPNewDomain->set('domain',$domain);
    $objRegistroEPPNewDomain->set('regperiod',$regperiod);
    $objRegistroEPPNewDomain->set('contactIDDigits',$RegistrantTaxIDDigits);
    $objRegistroEPPNewDomain->set('contactID',$RegistrantTaxID);
    $objRegistroEPPNewDomain->set('tech',$moduleparams['TechC']);


    try {
        $objRegistroEPPNewDomain->createDomain($Nameservers);

    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }
    
    
    
    return array(
        'success' => true,
    );


}



# Function to renew domain

function registrobr_RenewDomain($params){

    require_once('RegistroEPP/RegistroEPPFactory.class.php');

    $domain = $params["sld"].".".$params["tld"];
    $regperiod = $params["regperiod"];


    # Grab module parameters
    $moduleparams = _registrobr_Selector();
    
    
    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPP->set('language',$params['Language']);
    $objRegistroEPP->set('domain',$domain);

    try {
        $objRegistroEPP->login($moduleparams);
    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }

    try {
        //Request domain info
        $objRegistroEPP->getInfo();
        $objRegistroEPP->set('regperiod',$regperiod);
        $objRegistroEPP->renewDomain();
        $values['expirydate'] = $objRegistroEPP->get('exDate');

    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }


    return array(
        'success' => true,
    );

}


# Function to grab contact details

function registrobr_GetContactDetails($params) {


    /*
     Array
    (
    [domainid] => 54
    [sld] => toccos17
    [tld] => com.br
    [regperiod] => 1
    [registrar] => registrobr
    [Certificate] =>
    [CNPJ] => 1
    [CPF] => 1
    [FinanceDept] => 1
    [Language] => English
    [Passphrase] =>
    [Password] =>
    [TechC] =>
    [TechDept] => 1
    [TestMode] => on
    [Username] => 237
    )
     */

    # Include CPF and CNPJ stuff we need
    require_once 'isCnpjValid.php';
    require_once 'isCpfValid.php';

    require_once('RegistroEPP/RegistroEPPFactory.class.php');
    #require_once('ParserResponse/ParserResponse.class.php');

    # Grab module parameters
    $moduleparams = _registrobr_Selector();

    $domain = $params["sld"].".".$params["tld"];

    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPP->set('domain',$domain);
    $objRegistroEPP->set('language',$moduleparams['Language']);

    $ticket = _registrobr_getTickets($moduleparams['Username'],$params['domainid'],$domain);
    

  
    
        try {

            $objRegistroEPP->login($moduleparams);


            $objRegistroEPP->getInfo();
            $providerID = $objRegistroEPP->get('clID');
               $objRegistroEPP->verifyProvider($providerID,$moduleparams["Username"]);
         }
        catch (Exception $e){
            $coderes = $objRegistroEPP->get('coderes');
            if($coderes != '2303' and $coderes != '1000'){
                $values["error"] = $e->getMessage();
                return $values;
            }
        }
   

    $contacts = $objRegistroEPP->get('contacts');

    foreach ($contacts as $key => $value){
        $Contacts[ucfirst($key)] = $value;
    }


    $RegistrantTaxID = $objRegistroEPP->get('organization');
    # Returned CNPJ has extra zero at left
    if(isCpfValid($RegistrantTaxID)!=TRUE) {
        $RegistrantTaxID=substr($RegistrantTaxID,1);
    };
    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);

    try {
        #Get info about the brorg
        $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
        $objRegistroEPPBrorg->set('language',$params['Language']);

        $objRegistroEPPBrorg->set('netClient',$objRegistroEPP->get('netClient'));
        $objRegistroEPPBrorg->set('domain',$domain);
        $objRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
        $objRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);
        $objRegistroEPPBrorg->getInfo();
    }
    catch(Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }
    $Contacts["Registrant"]= $objRegistroEPPBrorg->get('contact');

    $Name = $objRegistroEPPBrorg->get('name');
    #Get Info about the brorg

    # Companies have both company name and contact name, individuals only have their own name
    if (isCnpjValid($RegistrantTaxIDDigits)==TRUE) {
        $values["Registrant"][$objRegistroEPPBrorg->getMsgLang("companynamefield")] = $Name;
    }
    else {
        $values["Registrant"][$objRegistroEPPBrorg->getMsgLang("fullnamefield")] = $Name;
    }

    #Get Org, Adm and Tech Contacts

    foreach ($Contacts as $key => $value) {

        if($key == 'Billing') continue;

        try {
            $objRegistroEPPBrorg->set('contactID','');
            $objRegistroEPPBrorg->set('contactIDDigits',$value);
            $objRegistroEPPBrorg->getInfo();
        }
        catch(Exception $e){
            $values["error"] = $e->getMessage();
            return $values;
        }

        $values[$key][$objRegistroEPPBrorg->getMsgLang("fullnamefield")] = $objRegistroEPPBrorg->get('name');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("streetnamefield")] = $objRegistroEPPBrorg->get('street1');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("streetnumberfield")] = $objRegistroEPPBrorg->get('street2');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("addresscomplementsfield")] = $objRegistroEPPBrorg->get('street3');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("citynamefield")] = $objRegistroEPPBrorg->get('city');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("stateprovincefield")] = $objRegistroEPPBrorg->get('sp');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("zipcodefield")] = $objRegistroEPPBrorg->get('pc');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("countrycodefield")] = $objRegistroEPPBrorg->get('cc');
        $values[$key][$objRegistroEPPBrorg->getMsgLang("phonenumberfield")] = $objRegistroEPPBrorg->get('voice');
        $values[$key]["Email"] = $objRegistroEPPBrorg->get('email');

    }
    /*

     Array
(
    [Registrant] => Array
        (
            [Full Name] => Flávio Novo Client Yanai
            [Street Name] => Av Nações Unidas, 333
            [Street Number] => 2222
            [Address Complements] =>
            [City] => São Paulo
            [State or Province] => SP
            [Zip code] => 03182-040
            [Country] => BR
            [Phone] => +55.33343434
            [Email] => flavio2.yanai@gmail.com
        )

    [Admin] => Array
        (
            [Full Name] => Flávio Novo Client Yanai
            [Street Name] => Av Nações Unidas, 333
            [Street Number] => 2222
            [Address Complements] =>
            [City] => São Paulo
            [State or Province] => SP
            [Zip code] => 03182-040
            [Country] => BR
            [Phone] => +55.33343434
            [Email] => flavio2.yanai@gmail.com
        )

    [Tech] => Array
        (
            [Full Name] => Flávio Novo Client Yanai
            [Street Name] => Av Nações Unidas, 333
            [Street Number] => 2222
            [Address Complements] =>
            [City] => São Paulo
            [State or Province] => SP
            [Zip code] => 03182-040
            [Country] => BR
            [Phone] => +55.33343434
            [Email] => flavio2.yanai@gmail.com
        )

)

     */

    return $values;
}

# Function to save contact details

function registrobr_SaveContactDetails($params) {

    /*

     * Array
(
    [domainid] => 54
    [sld] => toccos17
    [tld] => com.br
    [regperiod] => 1
    [registrar] => registrobr
    [contactdetails] => Array
        (
            [Registrant] => Array
                (
                    [Full Name] => Flavio Newest Yanai
                    [Street Name] => Av Nacoes Unidas, 444
                    [Street Number] => 1111
                    [Address Complements] => 1111
                    [City] => Sao Paulo
                    [State or Province] => SP
                    [Zip code] => 03182-040
                    [Country] => BR
                    [Phone] => +55.33343434
                    [Email] => flavio2.yanai@gmail.com
                )

            [Admin] => Array
                (
                    [Full Name] => Flavio Novo Client Yanai2
                    [Street Name] => Av Nacoes Unidas, 2222
                    [Street Number] => 2222
                    [Address Complements] =>
                    [City] => Sao Paulo
                    [State or Province] => SP
                    [Zip code] => 03182-040
                    [Country] => BR
                    [Phone] => +55.33343434
                    [Email] => flavio2.yanai@gmail.com
                )

            [Tech] => Array
                (
                    [Full Name] => Flavio Novo Client Yanai
                    [Street Name] => Av Nacoes Unidas, 3333
                    [Street Number] => 3333
                    [Address Complements] =>
                    [City] => Sao Paulo
                    [State or Province] => SP
                    [Zip code] => 03182-040
                    [Country] => BR
                    [Phone] => +55.33343434
                    [Email] => flavio2.yanai@gmail.com
                )

        )

    [original] => Array
        (
            [domainid] => 54
            [sld] => toccos17
            [tld] => com.br
            [regperiod] => 1
            [registrar] => registrobr
            [contactdetails] => Array
                (
                    [Registrant] => Array
                        (
                            [Full Name] => Flávio Newest Yanai
                            [Street Name] => Av Nações Unidas, 444
                            [Street Number] => 1111
                            [Address Complements] => 1111
                            [City] => São Paulo
                            [State or Province] => SP
                            [Zip code] => 03182-040
                            [Country] => BR
                            [Phone] => +55.33343434
                            [Email] => flavio2.yanai@gmail.com
                        )

                    [Admin] => Array
                        (
                            [Full Name] => Flávio Novo Client Yanai2
                            [Street Name] => Av Nações Unidas, 2222
                            [Street Number] => 2222
                            [Address Complements] =>
                            [City] => São Paulo
                            [State or Province] => SP
                            [Zip code] => 03182-040
                            [Country] => BR
                            [Phone] => +55.33343434
                            [Email] => flavio2.yanai@gmail.com
                        )

                    [Tech] => Array
                        (
                            [Full Name] => Flávio Novo Client Yanai
                            [Street Name] => Av Nações Unidas, 3333
                            [Street Number] => 3333
                            [Address Complements] =>
                            [City] => São Paulo
                            [State or Province] => SP
                            [Zip code] => 03182-040
                            [Country] => BR
                            [Phone] => +55.33343434
                            [Email] => flavio2.yanai@gmail.com
                        )

                )

        )

    [Certificate] =>
    [CNPJ] => 1
    [CPF] => 1
    [FinanceDept] => 1
    [Language] => English
    [Passphrase] =>
    [Password] =>
    [TechC] =>
    [TechDept] => 1
    [TestMode] => on
    [Username] => 237
)
﻿
     */

    # If nothing was changed, return
    if ($params["contactdetails"]==$params["original"]["contactdetails"]) {
        $values=array();
        return $values;
    }

    # Include CPF and CNPJ stuff we need
    require_once 'isCnpjValid.php';
    require_once 'isCpfValid.php';

    require_once('RegistroEPP/RegistroEPPFactory.class.php');

    $domain = $params["original"]["sld"].".".$params["original"]["tld"];
    //must be used the original info

    # Grab module parameters
    $moduleparams = _registrobr_Selector();

    $objRegistroEPP = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPP->set('domain',$domain);
    $objRegistroEPP->set('language',$params['Language']);



        try {

            $objRegistroEPP->login($moduleparams);

            //Request domain info


            $objRegistroEPP->getInfo();

            $providerID = $objRegistroEPP->get('clID');
            $objRegistroEPP->verifyProvider($providerID,$moduleparams["Username"]);

        }
        catch (Exception $e){
            $coderes = $objRegistroEPP->get('coderes');
            if($coderes != '2303' and $coderes != '1000'){
                $values["error"] = $e->getMessage();
                return $values;
            }
        }
        # Check results
        $coderes = $objRegistroEPP->get('coderes');

        if ($coderes == '1000' and $ticket != '') {//Domain pending
            $values["error"] = $objRegistroEPP->getMsgLang("domainpending");
            return $values;
        }

    





    $contacts = $objRegistroEPP->get('contacts');

    $RegistrantTaxID = $objRegistroEPP->get('organization');

    foreach ($contacts as $key => $value){
        $Contacts[ucfirst($key)] = $value;
    }

    # Returned CNPJ has extra zero at left
    if(isCpfValid($RegistrantTaxID)!=TRUE) {
        $RegistrantTaxID=substr($RegistrantTaxID,1);
    };

    $RegistrantTaxIDDigits = preg_replace("/[^0-9]/","",$RegistrantTaxID);

    try {
        #Get info about the brorg
        $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
        $objRegistroEPPBrorg->set('language',$params['Language']);

        $objRegistroEPPBrorg->set('netClient',$objRegistroEPP->get('netClient'));
        $objRegistroEPPBrorg->set('domain',$domain);
        $objRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
        $objRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);
        $objRegistroEPPBrorg->getInfo();
    }
    catch(Exception $e) {
        $values["error"] = $e->getMessage();
        return $values;
    }
    $Contacts["Registrant"]= $objRegistroEPPBrorg->get('contact');

    $Name = $objRegistroEPPBrorg->get('name');
    #Get Info about the brorg

    # Companies have both company name and contact name, individuals only have their own name
    if (isCnpjValid($RegistrantTaxIDDigits)==TRUE) {
        $values["Registrant"][$objRegistroEPPBrorg->getMsgLang("companynamefield")] = $Name;
    }
    else {
        $values["Registrant"][$objRegistroEPPBrorg->getMsgLang("fullnamefield")] = $Name;
    }



    # This flag will signal the need for doing a domain update or not
    $DomainUpdate = FALSE ;

    # This flag will signal the need for doing a brorg update or not
    $OrgUpdate = FALSE ;

    # Verify which contacts need updating
    $ContactTypes = array ("Registrant","Admin","Tech");
    $NewContactsID = array();
    $objNewContacts = array();

    foreach ($ContactTypes as $type)  {
        /*
        [Full Name] => Flaavio Toccos Yanaica
        [Street Name] => Av. Nacoes Unidas. 333
        [Street Number] => 1111
        [Address Complements] => 2222
        [City] => Sao Paulo
        [State or Province] => SP
        [Zip code] => 03182-040
        [Country] => BR
        [Phone] => +55.38183343
        [Email] => fkyanai7@gmail.com
        )
        */
        $cdetails = $params["contactdetails"][$type];

        //work around  when WHMCS uses the owner contact details the indexes are different ...

        if(count($cdetails) > 10){

            $index_fullname = "Full Name";
            $index_company = "Company Name";
            $index_street1 = "Street";
            $index_street2 = "Address 1";
            $index_street3 = "Address 2";
            $index_city    = "City";
            $index_sp = "Region";
            $index_pc = "ZIP";
            $index_cc = "Country";
            $index_voice = "Phone";

        }
        else {
            $objRegistroEPPBrorg->set('language',$params['Language']);

            $index_fullname = $objRegistroEPPBrorg->getMsgLang("fullnamefield");
            $index_company  = $objRegistroEPPBrorg->getMsgLang("companynamefield");
            $index_street1  = $objRegistroEPPBrorg->getMsgLang("streetnamefield");
            $index_street2  = $objRegistroEPPBrorg->getMsgLang("streetnumberfield");
            $index_street3  = $objRegistroEPPBrorg->getMsgLang("addresscomplementsfield");
            $index_city     = $objRegistroEPPBrorg->getMsgLang("citynamefield");
            $index_sp       = $objRegistroEPPBrorg->getMsgLang("stateprovincefield");
            $index_pc       = $objRegistroEPPBrorg->getMsgLang("zipcodefield");
            $index_cc        = $objRegistroEPPBrorg->getMsgLang("countrycodefield");
            $index_voice    = $objRegistroEPPBrorg->getMsgLang("phonenumberfield");
        }
        //work around



        $name = !empty($cdetails[$index_fullname]) ? $cdetails[$index_fullname] : '';
        $street1 = !empty($cdetails[$index_street1]) ? $cdetails[$index_street1] : '';
        $street2 = !empty($cdetails[$index_street2]) ? $cdetails[$index_street2] : '';
        $street3 = !empty($cdetails[$index_street3]) ? $cdetails[$index_street3] : '';
        $city = !empty($cdetails[$index_city]) ? $cdetails[$index_city] : '';
        $sp = !empty($cdetails[$index_sp]) ? $cdetails[$index_sp] : '';
        $pc = !empty($cdetails[$index_pc]) ? $cdetails[$index_pc] : '';
        $cc = !empty($cdetails[$index_cc]) ? $cdetails[$index_cc] : '';
        $voice = !empty($cdetails[$index_voice]) ? $cdetails[$index_voice] : '';
        $email = !empty($cdetails["Email"]) ? $cdetails["Email"] : '';

        $sp    = $objRegistroEPPBrorg->StateProvince($sp);


        $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
        $objRegistroEPPBrorg->set('language',$params['Language']);

        $objRegistroEPPBrorg->set('netClient',$objRegistroEPP->get('netClient'));
        $objRegistroEPPBrorg->set('domain',$domain);
        $objRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
        $objRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);

        $objRegistroEPPBrorg->set('name',$name);
        $objRegistroEPPBrorg->set('street1',$street1);
        $objRegistroEPPBrorg->set('street2',$street2);
        $objRegistroEPPBrorg->set('street3',$street3);

        $objRegistroEPPBrorg->set('city',$city);
        $objRegistroEPPBrorg->set('sp',$sp);
        $objRegistroEPPBrorg->set('pc',$pc);
        $objRegistroEPPBrorg->set('cc',$cc);
        $objRegistroEPPBrorg->set('voice',$voice);
        $objRegistroEPPBrorg->set('email',$email);

        try {
            $objRegistroEPPBrorg->createData();
        }
        catch (Exception $e){
            $values["error"] = $e->getMessage();
            return $values;
        }
        $NewContactsID[$type] = $objRegistroEPPBrorg->get('id');
        $objNewContacts[$type] = $objRegistroEPPBrorg;

        if ($type!="Registrant") {
            $DomainUpdate=TRUE;
        }
        else {
            $OrgUpdate=TRUE;
            //$OrgContactXML=$request;
        }

    }

    if ($DomainUpdate == TRUE) {
        $NewContactsID["Billing"] = $NewContactsID["Admin"];

        try {
            //obj Domain
            $objRegistroEPP->updateInfo($Contacts,$NewContactsID);
        }
        catch(Exception $e){
            $values["error"] = $e->getMessage();
            return $values;
        }
    }

    if ($OrgUpdate == TRUE){
        try {
            #Get info about the brorg
            $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
            $objRegistroEPPBrorg->set('language',$params['Language']);

            $objRegistroEPPBrorg->set('netClient',$objRegistroEPP->get('netClient'));
            $objRegistroEPPBrorg->set('domain',$domain);
            $objRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
            $objRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);
            $objRegistroEPPBrorg->getInfo();
        }
        catch(Exception $e) {
            $values["error"] = $e->getMessage();
            return $values;
        }
        //Get current org contact
        $Contacts["Registrant"]= $objRegistroEPPBrorg->get('contact');

        if (isCpfValid($RegistrantTaxIDDigits)==TRUE) {
            $companyname = $objRegistroEPPBrorg->get('name');
        }
        else {
            $companyname =( empty($params["contactdetails"]["Registrant"][$objRegistroEPPBrorg->getMsgLang("companynamefield")]) ? $params["contactdetails"]["Registrant"]["Company Name"] : $params["contactdetails"]["Registrant"][$objRegistroEPPBrorg->getMsgLang("companynamefield")]);
        }

        if (isCnpjValid($RegistrantTaxIDDigits)) {
            $responsible = $objRegistroEPPBrorg->get('name');
        }

        $objReg = $objNewContacts["Registrant"];

        $objNewRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
        $objNewRegistroEPPBrorg->set('language',$params['Language']);

        $objNewRegistroEPPBrorg->set('netClient',$objRegistroEPP->get('netClient'));
        $objNewRegistroEPPBrorg->set('domain',$domain);
        $objNewRegistroEPPBrorg->set('contactID',$RegistrantTaxID);
        $objNewRegistroEPPBrorg->set('contactIDDigits',$RegistrantTaxIDDigits);
        $objNewRegistroEPPBrorg->set('name',$objReg->get('name'));
        $objNewRegistroEPPBrorg->set('street1',$objReg->get('street1'));
        $objNewRegistroEPPBrorg->set('street2',$objReg->get('street2'));
        $objNewRegistroEPPBrorg->set('street3',$objReg->get('street3'));
        $objNewRegistroEPPBrorg->set('city',$objReg->get('city'));
        $objNewRegistroEPPBrorg->set('sp',$objReg->get('sp'));
        $objNewRegistroEPPBrorg->set('pc',$objReg->get('pc'));
        $objNewRegistroEPPBrorg->set('cc',$objReg->get('cc'));
        $objNewRegistroEPPBrorg->set('voice',$objReg->get('voice'));
        $objNewRegistroEPPBrorg->set('email',$objReg->get('email'));
        $objNewRegistroEPPBrorg->set('responsible',$objReg->get('name'));


        try {
            $objNewRegistroEPPBrorg->updateInfo($Contacts,$NewContactsID);
        }
        catch(Exception $e){
            $values["error"] = $e->getMessage();
            return $values;
        }
    }

    $values = array();

    return $values;
}

# Domain Delete (used in .br only for Add Grace Period)
function registrobr_RequestDelete($params) {
    require_once('RegistroEPP/RegistroEPPFactory.class.php');

    $domain = $params["sld"].".".$params["tld"];

    # Grab module parameters
    $moduleparams = _registrobr_Selector();

    $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
    $objRegistroEPPDomain->set('domain',$domain);
    $objRegistroEPPDomain->set('language',$params['Language']);


    try {
        $objRegistroEPPDomain->login($moduleparams);
        $objRegistroEPPDomain->deleteDomain();

        $coderes = $objRegistroEPPDomain->get('coderes');
    }
    catch (Exception $e){
        $values["error"] = $e->getMessage();
        return $values;
    }
    #If unknown domain, could be a ticket

    if($coderes == '2303') {
            $values = registrobr_Getnameservers($params);

            # If no error, domain is still a ticket, so we remove the nameservers to prevent it becoming a domain
            if (empty($values["error"])) {
                $setparams=$params;
                $setparams["ns1"]='';
                $setparams["ns2"]='';
                $setparams["ns3"]='';
                $setparams["ns4"]='';
                $setparams["ns5"]='';

                $values = registrobr_SaveNameservers($setparams);
                if (empty($values["error"])) {
                    $values=array();
                    return $values ;
                }
            }
    }

}

function registrobr_Sync($params) {

    /*
     *
     Array
(
    [Certificate] =>
    [CNPJ] => 1
    [CPF] => 1
    [FinanceDept] => 1
    [Language] => Portuguese
    [Passphrase] =>
    [Password] =>
    [Sender] => root
    [TechC] =>
    [TechDept] => 2
    [TestMode] => on
    [Username] => 237
    [domainid] => 78
    [domain] => toccos28.com.br
    [sld] => toccos28
    [tld] => com.br
    [registrar] => registrobr
    [status] => Active
)
     */



    require_once('RegistroEPP/RegistroEPPFactory.class.php');


    # Grab variables
    $domain = $params['domain'];
    $domainid = $params['domainid'];
    $moduleparams = _registrobr_Selector();
    
    

    #if($TESTMODE){
    #_registrobr_test($domainid,$domain,$moduleparams);
    #}


    $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');


    $objRegistroEPPDomain->set('domain',$domain);
    $objRegistroEPPDomain->set('language',$params['Language']);

    try {
        $objRegistroEPPDomain->login($moduleparams);
        $objRegistroEPPDomain->getInfo();

    }
    catch (Exception $e){

        $values["error"] = $e->getMessage();
        $error = $e->getMessage();
        $objRegistroEPPDomain->error('syncdomainnevercreated',$error,'');

        return $values;
    }

    $createdate = $objRegistroEPPDomain->get('crDate');
    $values['registrationdate'] = $createdate;


    $nextduedate = $objRegistroEPPDomain->get('exDate');
    $holdreasons = $objRegistroEPPDomain->get('onHoldReason');


    if (count($holdreasons) > 0) {
        foreach ($holdreasons as $hr){
            if (array_search("billing",$hr)!=FALSE) {
                $values['expired'] = true;
                $values['expirydate'] = $nextduedate;
            }
        }
    }
    else {
        $values['active'] = true;
        $values['expirydate'] = $nextduedate;
    }


    return $values;

}







function _registrobr_Selector(){

    $params = getregistrarconfigoptions('registrobr');
    $output = $params;

    if ($params["TestMode"] == "Beta") {
        $output["Server"] = "beta.registro.br" ;
        $output["Certificate"] = ROOTDIR . '/modules/registrars/registrobr/client-pwd.pem';
        $output["Username"] = $params["BetaUsername"];
        $output["Password"] = $params["BetaPassword"];
        $output["Passphrase"] = "shepp";
    }
    else {
        $output["Server"] = "epp.registro.br" ;
        $output["Certificate"] = $params["ProdCertificate"];
        $output["Username"] = $params["ProdUsername"];
        $output["Password"] = $params["ProdPassword"];
        $output["Passphrase"] = $params["ProdPassphrase"];
    }

    return $output;
}

?>
