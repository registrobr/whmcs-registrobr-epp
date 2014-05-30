<?php

require_once("RegistroEPP.class.php");

class RegistroEPPTest extends RegistroEPP {

    protected $tech;

    public function getInfo(){
        
        
    }
    
    public function testCase($moduleparams) {
        
        //$cnpj = $this->cnpj();
        
        $info['firstname'] = 'Joe';
        $info['lastname'] = 'Doe';
        $info['companyname'] = 'ACME';
        $info['address1'] = 'Rua Acme';
        $info['address2'] = '555';
        $info['city'] = 'Sao Paulo';
        $info['state'] = 'SP';
        $info['postcode'] = '03182-040';
        $info['countrycode'] = 'BR';
        $info['country'] = 'BR';
        $info['countryname'] = 'Brasil';
        $info['phonecc'] = '55';
        $info['phonenumber'] = '3434-3434';
        $info['adminfirstname'] = 'Administrator';
        $info['adminlastname'] = 'Doe';
        $info['admincompanyname'] = 'ACME';
        $info['adminemail'] = 'acme@xcda.com';
        $info['adminaddress1'] = 'Rua Acme';
        $info['adminaddress2'] = '183';
        $info['admincity'] = 'Sao Paulo';
        $info['adminfullstate'] = 'Sao Paulo';
        $info['adminstate'] = 'SP'; 
        $info['adminpostcode'] = '03182-040'; 
        $info['admincountry'] = 'BR';
        $info['adminphonenumber'] = '3343-3434';
        $info['fullphonenumber'] = '+55.33433434'; 
        $info['adminfullphonenumber'] = '+55.33433434';
        $info['domain'] = $moduleparams['UT-Domain'];
        $info['ns1'] = $moduleparams['UT-NameServer1'];
        $info['ns2'] = $moduleparams['UT-NameServer2'];
        
        
        if($moduleparams['UnityTesting'] == 'Case1'){
            //Registra um dominio
                    
            $this->test($moduleparams,'RegisterDomain', $info, $debug);
        }
        elseif($moduleparams['UnityTesting'] == 'Case2'){
            // Testa tudo e remoção de dominio
            //Em um dominio existente e com o DNS cadastrado e sem tickets:
            
            //Testa o GetNameServer e o SetNameserver
            
            $res = $this->whois($info['domain']);
            $i = 1;
            foreach( $res['regrinfo']['domain']['nserver'] as $ns => $ip ){
                $info['nswhois'.$i] = $ns;
                $i++;
            }
            $this->test($moduleparams, 'GetNameServers', $info, $debug);

            $this->test($moduleparams, 'SetNameServers', $info, $debug);
            
            $this->test($moduleparams, 'GetContactDetails', $info, $debug);
            
            $this->test($moduleparams, 'SetContactDetails', $info, $debug);

            $this->test($moduleparams, 'Sync', $info, $debug);
            
            #$this->test($moduleparams, 'Poll', $info, $debug);
                
            $this->test($moduleparams,'DeleteDomain', $info, $debug);
        }
        elseif($moduleparams['UnityTesting'] == 'Case3'){
            //Testa renovação de dominio
            
            $res = $this->whois($info['domain']);
            $i = 1;
            foreach( $res['regrinfo']['domain']['nserver'] as $ns => $ip ){
                $info['nswhois'.$i] = $ns;
                $i++;
            }
            $this->test($moduleparams, 'GetNameServers', $info, $debug);
            
            $this->test($moduleparams, 'SetNameServers', $info, $debug);
                
            $this->test($moduleparams, 'GetContactDetails', $info, $debug);
                
            $this->test($moduleparams, 'SetContactDetails', $info, $debug);
            
            $this->test($moduleparams, 'Sync', $info, $debug);
                
            #$this->test($moduleparams, 'Poll', $info, $debug);
                        
            $this->test($moduleparams, 'RenewDomain', $info, $debug);
        }
        

    }
    
    
    protected function test($moduleparams,$type, $info, $debug) {
    
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
    
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
    
        $TESTUSER        = $moduleparams['Username'];
        $TESTPASSWORD   = $moduleparams['Password'];
        $CNPJ = $moduleparams['CNPJ'];
        $CPF = $moduleparams['CPF'];
    
        
        ##Checking parameters
    
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
    
        $msg = 'Initializing test...';
        if($debug) echo $msg;
        $objRegistroEPPDomain->error('testerror',$msg,$moduleparams);
    
        if(empty($TESTUSER) || empty($TESTPASSWORD)){
            $msg = "TESTUSER and TESTPASSWORD are required, aborting...";
            $objRegistroEPPDomain->error('testerror',$msg,$moduleparams);
            if($debug) echo $msg;
            return;
        }
    
        if(empty($info['ns1']) || empty($info['ns2'])){
            $msg = "NS1 and NS2 are required, aborting... ";
            $objRegistroEPPDomain->error('testerror',$msg,$moduleparams);
            if($debug) echo $msg;
            return;
        }
        else {
            $TESTNS1         = $info['ns1'];
            $TESTNS2         = $info['ns2'];
        }
    
        $di = $info['domain'];
        $dd = split("\.",$di);
    
        if(empty($di)){
            $msg = "Domain must be set in the url, aborting...";
            $objRegistroEPPDomain->error('testerror',$msg,$moduleparams);
            if($debug) echo $msg;
            return;
        }
    
        if(empty($dd[0]) || empty($dd[1]) || empty($dd[2]) || $dd[2] != 'br' || strlen($dd[1]) < 2 || strlen($dd[1]) > 3){
            $msg = "Domain must be a valid .br domain  => $di, aborting...";
            $objRegistroEPPDomain->error('testerror',$msg,$di);
            if($debug) echo $msg;
            return;
        }
        else {
            $TESTDOMAIN     = $di;
            $TESTTLD        = $dd[1].".".$dd[2];
            $TESTSLD        = $dd[0];
        }
        $pdomain['TESTDOMAIN'] = $TESTDOMAIN;
        $pdomain['TESTTLD'] = $TESTTLD;
        $pdomain['TESTSLD'] = $TESTSLD;
        $pdomain['TESTNS1'] = $TESTNS1;
        $pdomain['TESTNS2'] = $TESTNS2;
    
        $msg = "Parameters seems OK... ";
        $objRegistroEPPDomain->error('testerror',$msg,$pdomain);
        if($debug) echo $msg;

        
    
        $TESTPARAMS = Array
        (
                'Certificate' => '',
                'CNPJ' => $CNPJ,
                'CPF' => $CPF,
                'FinanceDept' => '1',
                'Language' => 'Portuguese',
                'Passphrase' => '',
                'Password' => $TESTPASSWORD,
                'Sender' => 'root',
                'TechC' => '',
                'TechDept' => '2',
                'TestMode' => 'on',
                'Username' => $TESTUSER,
                'domainid' => '78',
                'domain' => $TESTDOMAIN,
                'sld' => $TESTSLD,
                'tld' => $TESTTLD,
                'registrar' => 'registrobr',
                'status' => 'Active',
                'nswhois1' => $info['nswhois1'],
                'nswhois2' => $info['nswhois2'],
        );
    

        
        $TESTORIGINAL = Array(
                'domainid' => '78',
                'sld' => $TESTSLD,
                'tld' => $TESTTLD,
                'registrar' => 'registrobr',
                'userid' => '22',
                'id' => '22',
                'firstname' => $info['firstname'],
                'lastname' =>  $info['lastname'],
                'companyname' => $info['companyname'],
                'email' => $info['email'],
                'address1' => $info['address1'],
                'address2' => $info['address2'],
                'city' => $info['city'],
                'state' => $info['state'],
                'postcode' => $info['postcode'],
                'countrycode' => $info['countryocode'],
                'country' => $info['country'],
                'countryname' => $info['countryname'],
                'phonecc' => $info['phonecc'],
                'phonenumber' => $info['phonenumber'],
                'notes' => '',
                'password' => '9150351b52dbc22fec30b887d4661e1e:mtgPo',
                'currency' => '1',
                'defaultgateway' => '',
                'cctype' => '',
                'cclastfour' => '',
                'securityqid' => '0',
                'securityqans' => '',
                'groupid' => '0',
                'status' => 'Active',
                'credit' => '444.00',
                'taxexempt' => '',
                'latefeeoveride' => '',
                'overideduenotices' => '',
                'separateinvoices' => '',
                'disableautocc' => '',
                'language' => 'Portuguese-br',
                'lastlogin' => 'No Login Logged',
                'customfields1' => '64264445701',
                'customfields2' => 'Selecione',
                'customfields3' => '',
                'billingcid' => '0',
                'fullstate' => 'SP',
                'regperiod' => '1',
                'dnsmanagement' => '',
                'emailforwarding' => '',
                'idprotection' => '',
                'adminfirstname' => $info['adminfirstname'],
                'adminlastname' => $info['adminlastname'],
                'admincompanyname' => $info['admincompanyname'],
                'adminemail' => $info['adminemail'],
                'adminaddress1' => $info['adminaddress1'],
                'adminaddress2' => $info['adminaddress2'],
                'admincity' => $info['admincity'],
                'adminfullstate' => $info['adminfullstate'],
                'adminstate' => $info['adminstate'],
                'adminpostcode' => $info['adminpostcode'],
                'admincountry' => $info['admincountry'],
                'adminphonenumber' => $info['adminphonenumber'],
                'fullphonenumber' => $info['fullphonenumber'],
                'adminfullphonenumber' => $info['adminfullphonenumber'],
                'ns1' => $TESTNS1,
                'ns2' => $TESTNS2,
                'ns3' => '',
                'ns4' => '',
                'ns5' => '',
                'nswhois1' => $info['nswhois1'],
                'nswhois2' => $info['nswhois2'],
                'domain' => $TESTDOMAIN,
                
                
    
        );
        $TESTREGISTRATION = Array
        (
                'domainid' => '78',
                'sld' => $TESTSLD,
                'tld' => $TESTTLD,
                'registrar' => 'registrobr',
                'userid' => '22',
                'id' => '22',
                'firstname' => $info['firstname'],
                'lastname' =>  $info['lastname'],
                'companyname' => $info['companyname'],
                'email' => $info['email'],
                'address1' => $info['address1'],
                'address2' => $info['address2'],
                'city' => $info['city'],
                'state' => $info['state'],
                'postcode' => $info['postcode'],
                'countrycode' => $info['countryocode'],
                'country' => $info['country'],
                'countryname' => $info['countryname'],
                'phonecc' => $info['phonecc'],
                'phonenumber' => $info['phonenumber'],
                'notes' => '',
                'password' => '9150351b52dbc22fec30b887d4661e1e:mtgPo',
                'currency' => '1',
                'defaultgateway' => '',
                'cctype' => '',
                'cclastfour' => '',
                'securityqid' => '0',
                'securityqans' => '',
                'groupid' => '0',
                'status' => 'Active',
                'credit' => '444.00',
                'taxexempt' => '',
                'latefeeoveride' => '',
                'overideduenotices' => '',
                'separateinvoices' => '',
                'disableautocc' => '',
                'language' => 'Portuguese-br',
                'lastlogin' => 'No Login Logged',
                'customfields1' => '64264445701',
                'customfields2' => 'Selecione',
                'customfields3' => '',
                'billingcid' => '0',
                'fullstate' => 'SP',
                'regperiod' => '1',
                'dnsmanagement' => '',
                'emailforwarding' => '',
                'idprotection' => '',
                'adminfirstname' => $info['adminfirstname'],
                'adminlastname' => $info['adminlastname'],
                'admincompanyname' => $info['admincompanyname'],
                'adminemail' => $info['adminemail'],
                'adminaddress1' => $info['adminaddress1'],
                'adminaddress2' => $info['adminaddress2'],
                'admincity' => $info['admincity'],
                'adminfullstate' => $info['adminfullstate'],
                'adminstate' => $info['adminstate'],
                'adminpostcode' => $info['adminpostcode'],
                'admincountry' => $info['admincountry'],
                'adminphonenumber' => $info['adminphonenumber'],
                'fullphonenumber' => $info['fullphonenumber'],
                'adminfullphonenumber' => $info['adminfullphonenumber'],
                'ns1' => $TESTNS1,
                'ns2' => $TESTNS2,
                'ns3' => '',
                'ns4' => '',
                'ns5' => '',
                'nswhois1' => $info['nswhois1'],
                'nswhois2' => $info['nswhois2'],
                'original' => $TESTORIGINAL,
                'Certificate' => '',
                'CNPJ' => $CNPJ,
                'CPF' => $CPF,
                'FinanceDept' => '1',
                'Language' => 'Portuguese',
                'Passphrase' => '',
                'Password' => $TESTPASSWORD,
                'Sender' => 'root',
                'TechC' => '',
                'TechDept' => '2',
                'TestMode' => 'on',
                'Username' => $TESTUSER,
                'domain' => $TESTDOMAIN,
                
        );

        
        $email1 = $this->generateRandomString()."@nic.br";
        $email2 = $this->generateRandomString()."@nic.br";
        $email3 = $this->generateRandomString()."@nic.br";
        
        $name1 = "Registrant Joe Doe".$this->generateRandomString(2);
        $name2 = "Admin Joe Doe".$this->generateRandomString(2);
        $name3 = "Tech Joe Doe".$this->generateRandomString(2);
        
        $TESTSETCONTACT = Array(
                'domain' => $TESTDOMAIN,
                'domainid' => '78',
                'sld' => $TESTSLD,
                'tld' => $TESTTLD,
                'registrar' => 'registrobr',
                'contactdetails' => Array
                (
                        'Registrant' => Array
                        (
                                'Full Name' => $name1,
                                'Street Name' => 'Rua A',
                                'Street Number' => '1',
                                'Address Complements' => '2',
                                'City' => 'Sao Paulo',
                                'State or Province' => 'SP',
                                'Zip code' => '03182-040',
                                'Country' => 'BR',
                                'Phone' => '+55.33343434',
                                'Email' => $email1
                        ),
                        'Admin' => Array
                        (
                                'Full Name' => $name2,
                                'Street Name' => 'Rua A',
                                'Street Number' => '1',
                                'Address Complements' => '2',
                                'City' => 'Sao Paulo',
                                'State or Province' => 'SP',
                                'Zip code' => '03182-040',
                                'Country' => 'BR',
                                'Phone' => '+55.33343434',
                                'Email' => $email2
                        ),
                        'Tech' => Array
                        (
                                'Full Name' => $name3,
                                'Street Name' => 'Rua A',
                                'Street Number' => '1',
                                'Address Complements' => '2',
                                'City' => 'Sao Paulo',
                                'State or Province' => 'SP',
                                'Zip code' => '03182-040',
                                'Country' => 'BR',
                                'Phone' => '+55.33343434',
                                'Email' => $email3
                        )
                ),

                'original' => Array(
                        'domainid' => '78',
                        'sld' => $TESTSLD,
                        'tld' => $TESTTLD,
                        'registrar' => 'registrobr',
                        'contactdetails' => Array
                        (
                                'Registrant' => Array
                                (
                                        'Full Name' => 'Joe Doe Smith',
                                        'Street Name' => 'Rua A',
                                        'Street Number' => '1',
                                        'Address Complements' => '2',
                                        'City' => 'Sao Paulo',
                                        'State or Province' => 'SP',
                                        'Zip code' => '03182-040',
                                        'Country' => 'BR',
                                        'Phone' => '+55.33343434',
                                        'Email' => $email1
                                ),
                                'Admin' => Array
                                (
                                        'Full Name' => 'Joe Doe Smith',
                                        'Street Name' => 'Rua A',
                                        'Street Number' => '1',
                                        'Address Complements' => '2',
                                        'City' => 'Sao Paulo',
                                        'State or Province' => 'SP',
                                        'Zip code' => '03182-040',
                                        'Country' => 'BR',
                                        'Phone' => '+55.33343434',
                                        'Email' => $email2
                                ),
                                'Tech' => Array
                                (
                                        'Full Name' => 'Joe Doe Smith',
                                        'Street Name' => 'Rua A',
                                        'Street Number' => '1',
                                        'Address Complements' => '2',
                                        'City' => 'Sao Paulo',
                                        'State or Province' => 'SP',
                                        'Zip code' => '03182-040',
                                        'Country' => 'BR',
                                        'Phone' => '+55.33343434',
                                        'Email' => $email3
                                )
                        )
                    ),
                
                    'Certificate' => '',
                    'CNPJ' => $CNPJ,
                    'CPF' => $CPF,
                    'FinanceDept' => '1',
                    'Language' => 'English',
                    'Passphrase' => '',
                    'Password' => $TESTPASSWORD,
                    'TechC' => '',
                    'TechDept' => '1',
                    'TestMode' => 'on',
                    'Username' => $TESTUSER,
                
        );


        //TESTE
    
        $objRegistroEPPDomain->set('language',$TESTREGISTRATION['Language']);
    
        if($type == 'RegisterDomain'){
            ## Testing RegisterDomain
                    
            if (!$this->TestRegisterDomain($TESTREGISTRATION)) {
                return false;
            }
            else {
                return true;
            }
        }
        elseif($type == 'RenewDomain'){
            ## Testing RenewDomain
            if(!$this->TestRenewDomain($TESTREGISTRATION)){
                return false;
            }
            else {
                return true;
            }
        }
        elseif($type == 'GetNameServers'){
            ## TESTING GetNameserver and SaveNameServer
            #For testing nameservers at least 2 servers are required.
            if (!$this->TestGetNameServers($TESTPARAMS)) {
                return false;
            }
            else {
                return true;
            }
                
        }
        elseif($type == 'SetNameServers'){
            ## TESTING GetNameserver and SaveNameServer
            #For testing nameservers at least 2 servers are required.
            if (!$this->TestSetNameServers($TESTPARAMS)) {
            return false;
        }
        else {
            return true;
        }
        
        }
        elseif($type == 'GetContactDetails'){
            ## TESTING GetContactDetails and SaveContactDetails
            if (!$this->TestGetContactDetails($TESTPARAMS)) {
                return false;
            }
            else {
                return true;
            }
                
        }
        
        elseif($type == 'SetContactDetails'){
            ## TESTING SetContactDetails 
            if (!$this->TestSetContactDetails($TESTSETCONTACT)) {
                return false;
            }
            else {
                return true;
            }
        }
        elseif($type == "DeleteDomain"){
            ## Testing DeleteDomain    
            if($this->TestDeleteDomain($TESTPARAMS)){
                return false;
            }
            else {
                return true;
            }
                
        }
        elseif($type == 'Poll'){            
            ## TESTING Poll
            if(!$this->TestPoll($TESTPARAMS)){
                return false;
            }
            else {
                return true;
            }
                
        }
        elseif($type == 'Sync'){
            if(!$this->TestSync()){
                return;
            }
            else {
                return true;
            }
                
        }
    }
    public function TestSync(){
        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $objRegistroEPPPoll = RegistroEPPFactory::build('RegistroEPPPoll');
        
        $msg = "Testing Sync........$TESTDOMAIN";
        $objRegistroEPPPoll->error('testerror',$msg,$TESTREGISTRATION);
        
        registrobr_Sync($TESTPARAMS);
        
    }
    public function TestPoll($TESTPARAMS){
        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        require_once('registrobrpoll.php');
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $objRegistroEPPPoll = RegistroEPPFactory::build('RegistroEPPPoll');
        
        $msg = "Testing Poll........$TESTDOMAIN";
        $objRegistroEPPPoll->error('testerror',$msg,$TESTREGISTRATION);
        
        registrobr_Poll();
    }
    
    public function TestRegisterDomain($TESTREGISTRATION){
        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $msg = "Testing RegisterDomain........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
        
        try {
            $return = registrobr_RegisterDomain($TESTREGISTRATION);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
            return false;
        }
        
        if(empty($return['clID'])){
            $msg = "FAILED........$TESTDOMAIN - registrobr_RegisterDomain FAILED, check the TESTREGISTRATION params are OK";
        }
        else {
            $msg = "OK............$TESTDOMAIN - registrobr_RegisterDomain OK, domain $TESTDOMAIN created";
        }
        $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
        
        if($debug) echo "Register process finalized, check the module logs";
        
        return true;
    }
    
    
    public function TestRenewDomain($TESTREGISTRATION){
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');

        $TESTDOMAIN = $TESTREGISTRATION['domain'];
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $msg = "Testing RenewDomain........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
        
        try {
            $values = registrobr_RenewDomain($TESTREGISTRATION);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........registrobr_RenewDomain => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
        //[expirydate] => 2016-03-20
        
        if(empty($values['expirydate'])){
            $msg = "FAILED........couldn't renew the domain $TESTDOMAIN";            
        }
        else {
            $msg = "OK............domain was renewed until ".$values['expirydate'];
        }
        if($debug) echo "$msg, check the modules logs for more details";
        $objRegistroEPPDomain->error('testerror',$msg,$values);
        
        return true;        
    }
    public function TestGetContactDetails($TESTPARAMS){
        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $msg = "Testing GetContactDetails........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
        
        
        try {
            $contactdetails = registrobr_GetContactDetails($TESTPARAMS);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
        
        $objRegistroEPPBrorg = RegistroEPPFactory::build('RegistroEPPBrorg');
        $objRegistroEPPBrorg->set('language',$TESTPARAMS['Language']);
        
        
        $types = array('Registrant','Admin','Tech');
        
        foreach ($types as $key => $value) {
            $index_fullname = $objRegistroEPPBrorg->getMsgLang("fullnamefield");
            $index_email    = $objRegistroEPPBrorg->getMsgLang("Email");
        
            if(empty($contactdetails[$value][$index_fullname])){
                $msg = "FAILED........$TESTDOMAIN - registrobr_GetContactDetails FAILED, check contact details of $index_fullname $type in $domain";
            }
            else {
                $msg = "OK............$TESTDOMAIN - registrobr_GetContactDetails it seems OK for $value in $TESTDOMAIN";
            }
            $objRegistroEPPDomain->error('testerror',$msg,$contactdetails);
            if($debug) echo "$msg , check the modules logs for more details";
        }
        return true;
    }
    
    public function TestSetContactDetails($TESTSETCONTACT){

        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $TESTDOMAIN = $TESTSETCONTACT['domain'];
        
        $msg = "Testing SetContactDetails........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
        
        try {
            registrobr_SaveContactDetails($TESTSETCONTACT);
        }
        catch (Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
        
        
        $paramTech = $TESTSETCONTACT['contactdetails']['Tech']["Full Name"];
        $paramAdmin = $TESTSETCONTACT['contactdetails']['Admin']["Full Name"];
        $paramBilling = $TESTSETCONTACT['contactdetails']['Admin']["Full Name"];
        $paramOwner = $TESTSETCONTACT['contactdetails']['Registrant']["Full Name"];
        
        $results = $this->whois($TESTDOMAIN);
        
        $nameTech = $results['regrinfo']['tech']['name'];
        $nameAdmin = $results['regrinfo']['admin']['name'];
        $nameBilling = $results['regrinfo']['billing']['name'];
        $nameOwner = $results['regrinfo']['owner']['name'];
    
        $ok = 1;
        
        if ($nameTech == $paramTech){
            $msg = "OK...........$TESTDOMAIN,Set Tech contact is OK";
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
        }
        else {
            $msg = "FAILED...........$TESTDOMAIN, Set Tech contact failed";
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            $ok = 0;
        }
        if($nameAdmin == $paramAdmin){
            $msg = "OK...........$TESTDOMAIN,Set Admin contact is OK";
            if($debug) echo "$msg , check the modules logs for more details";
        }
        else {
            $msg = "FAILED...........$TESTDOMAIN, Set Admin contact failed";
            if($debug) echo "$msg , check the modules logs for more details";

            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            $ok = 0;
        }

        
        if($nameBilling == $paramBilling){
            $msg = "OK...........$TESTDOMAIN,Set Billing contact is OK";
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            
        }
        else {
            $msg = "FAILED...........$TESTDOMAIN, Set Billing contact failed";
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            $ok = 0;
        }
        
        if($nameOwner == $paramOwner){
            $msg = "OK...........$TESTDOMAIN,Set Owner contact is OK";
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
        }
        else {
            $msg = "FAILED...........$TESTDOMAIN, Set Owner failed";
            if($debug) echo "$msg , check the modules logs for more details";                
            $objRegistroEPPDomain->error('testerror',$msg,$TESTSETCONTACT);
            $ok = 0;
        }
        
        if ($ok) {
            return true;
        }
        else {
            return false;
        }
            
    }
    
    public function TestGetNameServers($TESTPARAMS){
    
    
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
    
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
    
        $msg = "Testing GetSetNameservers........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
    
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $NSWHOIS1 = $TESTPARAMS['nswhois1'];
        $NSWHOIS2 = $TESTPARAMS['nswhois2'];
        
    
        try {
            $nameservers = registrobr_GetNameservers($TESTPARAMS);
        }
        catch (Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
    
        if(empty($nameservers['ns1']) || $nameservers['ns1'] != $NSWHOIS1 || $nameservers['ns2'] != $NSWHOIS2){
            $msg = "FAILED........$TESTDOMAIN - registrobr_GetNamservers FAILED, check nameservers for domain";
        }
        else {
            $ns = $nameservers['ns1'];
            $msg = "OK............$TESTDOMAIN - registrobr_GetNamservers it seems OK. $TESTDOMAIN - $ns";
        }
        if($debug) echo "$msg , check the modules logs for more details";
    
        $objRegistroEPPDomain->error('testerror',$msg,$nameservers);
        
        return true;
    
    }
    
    public function TestSetNameServers($TESTPARAMS){
        
        
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $msg = "Testing GetSetNameservers........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        
        try {
            $nameservers = registrobr_GetNameservers($TESTPARAMS);

        }
        catch (Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
        
        if(empty($nameservers['ns1'])){
            $msg = "FAILED........$TESTDOMAIN - registrobr_GetNamservers FAILED, check nameservers for domain";
        }
        else {
            $ns = $nameservers['ns1'];
            $msg = "OK............$TESTDOMAIN - registrobr_GetNamservers it seems OK. $TESTDOMAIN - $ns";
        }
        if($debug) echo "$msg , check the modules logs for more details";
        
        $objRegistroEPPDomain->error('testerror',$msg,$nameservers);
        
        
        if(count($nameservers) < 2){
            $msg = "FAILED........$TESTDOMAIN - registrobr_SaveNamservers FAILED, at least 2 nameservers are required.";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
        
        }
        //exchange the nameservers
        $TESTPARAMS['ns1'] = $nameservers['ns2'];
        $TESTPARAMS['ns2'] = $nameservers['ns1'];
        
        try {
            registrobr_SaveNameservers($TESTPARAMS);
            $change_nameservers = registrobr_GetNameservers($TESTPARAMS);
                
            if($change_nameservers['ns1'] == $nameservers['ns2']){
                // exchange again the nameservers
                $TESTPARAMS['ns1'] = $nameservers['ns1'];
                $TESTPARAMS['ns2'] = $nameservers['ns2'];
                registrobr_SaveNameservers($TESTPARAMS);
                $msg = "OK............$TESTDOMAIN - registrobr_saveNameservers it seems OK ";
            }
            else {
                $msg = "FAILED........$TESTDOMAIN - registrobr_saveNameservers FAILED";
        
            }
            if($debug) echo "$msg , check the modules logs for more details";
            $objRegistroEPPDomain->error('testerror',$msg,$change_nameservers);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }    
        return true;
    }
    
    public function TestDeleteDomain($TESTPARAMS){
        
        $TESTDOMAIN = $TESTPARAMS['domain'];
        $objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
        
        $msg = "Testing DeleteDomain........$TESTDOMAIN";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
        
        
        try {
            $error = registrobr_RequestDelete($TESTPARAMS);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            $msg = "FAILED........$TESTDOMAIN => $msg";
            $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
            if($debug) echo "$msg , check the modules logs for more details";
            return false;
        }
        
        if(empty($error)){
            $msg = "OK............$TESTDOMAIN - registrobr_RequestDelete it seems OK";
        }
        else {
            $msg = "FAILED........$TESTDOMAIN - registrobr_RequestDelete FAILED, $error";
        }
        if($debug) echo "$msg , check the modules logs for more details";
        if($debug) echo "Test Finalized";
        $objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
        $objRegistroEPPDomain->error('testerror','Test Finalized !','');
        return true;
        
    }
    
    function whois($domain,$ticket = null){
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        require_once('RegistroEPP/RegistroEPPFactory.class.php');
        
        require_once('PhpWhois/whois.main.php');
        /*
         * 
         [regrinfo][registered] => yes or no
         [regrinfo][owner]
         [regrinfo][admin]
         [regrinfo][tech]
         [regrinfo][billing]
                      [handle] => DDD1
                     [name] =>  ddddd
                     [email] => dddd@dddd.com
                     [created] => 2003-11-19
                     [changed] => 2011-11-17
                     [organization] => DDDD DDDD DDDD 
         
                     [domain] => Array
                        (
                            [name] => domain
                            [ownerid] => CPF or CNPJ
                            [country] => BR
                            [nserver] => Array
                                (
                                    [ns1.nameserver.com] => 127.0.0.1
                                    [ns2.nameserver.com] => 127.0.0.1
                                )
        
                            [created] => 2010-09-20
                            [expires] => 2011-09-20
                            [changed] => 2010-09-20
                            [status] => published
                        )         
         * 
         * ['regrinfo']['domain']['nserver']
         */
        $whois = new Whois();
        $whois->UseServer('br','beta.registro.br');
        
        $query = $domain;
        
        $result = $whois->Lookup($query,false);

        return $result;
        
    }

    //Aux functions
    
    public function mod($dividendo,$divisor){
        return round($dividendo - (floor($dividendo/$divisor)*$divisor));
    }
    
    public function cpf($compontos) {
        $n1 = rand(0,9);
        $n2 = rand(0,9);
        $n3 = rand(0,9);
        $n4 = rand(0,9);
        $n5 = rand(0,9);
        $n6 = rand(0,9);
        $n7 = rand(0,9);
        $n8 = rand(0,9);
        $n9 = rand(0,9);
        $d1 = $n9*2+$n8*3+$n7*4+$n6*5+$n5*6+$n4*7+$n3*8+$n2*9+$n1*10;
        $d1 = 11 - ( mod($d1,11) );
        if ( $d1 >= 10 )
        {
            $d1 = 0 ;
        }
        $d2 = $d1*2+$n9*3+$n8*4+$n7*5+$n6*6+$n5*7+$n4*8+$n3*9+$n2*10+$n1*11;
        $d2 = 11 - ( mod($d2,11) );
        if ($d2>=10) {
            $d2 = 0 ;
        }
        $retorno = '';
        if ($compontos==1) {
            $retorno = ''.$n1.$n2.$n3.".".$n4.$n5.$n6.".".$n7.$n8.$n9."-".$d1.$d2;
        }
        else {$retorno = ''.$n1.$n2.$n3.$n4.$n5.$n6.$n7.$n8.$n9.$d1.$d2;
        }
        return $retorno;
    }
    
    public function cnpj($compontos) {
        $n1 = rand(0,9);
        $n2 = rand(0,9);
        $n3 = rand(0,9);
        $n4 = rand(0,9);
        $n5 = rand(0,9);
        $n6 = rand(0,9);
        $n7 = rand(0,9);
        $n8 = rand(0,9);
        $n9 = 0;
        $n10= 0;
        $n11= 0;
        $n12= 1;
        $d1 = $n12*2+$n11*3+$n10*4+$n9*5+$n8*6+$n7*7+$n6*8+$n5*9+$n4*2+$n3*3+$n2*4+$n1*5;
        $d1 = 11 - ( mod($d1,11) );
        if ( $d1 >= 10 )
        {
            $d1 = 0 ;
        }
        $d2 = $d1*2+$n12*3+$n11*4+$n10*5+$n9*6+$n8*7+$n7*8+$n6*9+$n5*2+$n4*3+$n3*4+$n2*5+$n1*6;
        $d2 = 11 - ( mod($d2,11) );
        if ($d2>=10) {
            $d2 = 0 ;
        }
        $retorno = '';
        if ($compontos==1) {
            $retorno = ''.$n1.$n2.".".$n3.$n4.$n5.".".$n6.$n7.$n8."/".$n9.$n10.$n11.$n12."-".$d1.$d2;
        }
        else {$retorno = ''.$n1.$n2.$n3.$n4.$n5.$n6.$n7.$n8.$n9.$n10.$n11.$n12.$d1.$d2;
        }
        return $retorno;
    }
    function generateRandomString($length = 4) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

?>
