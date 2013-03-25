<?php

require_once("RegistroEPP.class.php");

class RegistroEPPTest extends RegistroEPP {

	protected $tech;

	public function getInfo(){
		
		
	}
	
	
	public function test($moduleparams,$type, $domaininfo, $debug) {
	
		$include_path = ROOTDIR . '/modules/registrars/registrobr';
		set_include_path($include_path . PATH_SEPARATOR . get_include_path());
	
		require_once('RegistroEPP/RegistroEPPFactory.class.php');
	
		$TESTUSER	    = $moduleparams['Username'];
		$TESTPASSWORD   = $moduleparams['Password'];
	
		
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
	
		if(empty($domaininfo['ns1']) || empty($domaininfo['ns2'])){
			$msg = "NS1 and NS2 are required, aborting... ";
			$objRegistroEPPDomain->error('testerror',$msg,$moduleparams);
			if($debug) echo $msg;
			return;
		}
		else {
			$TESTNS1 		= $domaininfo['ns1'];
			$TESTNS2 		= $domaininfo['ns2'];
		}
	
		$di = $domaininfo['domain'];
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
			$TESTDOMAIN 	= $di;
			$TESTTLD		= $dd[1].".".$dd[2];
			$TESTSLD		= $dd[0];
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
				'CNPJ' => 1,
				'CPF' => 1,
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
				'status' => 'Active'
		);
	
	
	
		$TESTORIGINAL = Array(
				'domainid' => '78',
				'sld' => $TESTSLD,
				'tld' => $TESTTLD,
				'registrar' => 'registrobr',
				'userid' => '22',
				'id' => '22',
				'firstname' => 'Joe',
				'lastname' => 'Doe',
				'companyname' => 'ACME',
				'email' => 'test@ciclanomail.com',
				'address1' => 'Rua Teste 1',
				'address2' => 'apt 1',
				'city' => 'Cidade 1',
				'state' => 'SP',
				'postcode' => '13148-133',
				'countrycode' => 'BR',
				'country' => 'BR',
				'countryname' => 'Brazil',
				'phonecc' => '55',
				'phonenumber' => '3334-3434',
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
				'adminfirstname' => 'Joe',
				'adminlastname' => 'Doe',
				'admincompanyname' => 'ACME',
				'adminemail' => 'fulano@ciclanomail.com',
				'adminaddress1' => 'Rua Do Norte, 1',
				'adminaddress2' => 'apt 1',
				'admincity' => 'Cidade',
				'adminfullstate' => 'SP',
				'adminstate' => 'SP',
				'adminpostcode' => '13148-133',
				'admincountry' => 'BR',
				'adminphonenumber' => '3334-3434',
				'fullphonenumber' => '+55.33343434',
				'adminfullphonenumber' => '+55.33343434',
				'ns1' => $TESTNS1,
				'ns2' => $TESTNS2,
				'ns3' => '',
				'ns4' => '',
				'ns5' => '',
	
		);
		$TESTREGISTRATION = Array
		(
				'domainid' => '78',
				'sld' => $TESTSLD,
				'tld' => $TESTTLD,
				'registrar' => 'registrobr',
				'userid' => '22',
				'id' => '22',
				'firstname' => 'Joe',
				'lastname' => 'Doe',
				'companyname' => 'ACME',
				'email' => 'test@ciclanomail.com',
				'address1' => 'Rua Teste 1',
				'address2' => 'apt 1',
				'city' => 'Cidade 1',
				'state' => 'SP',
				'postcode' => '13148-133',
				'countrycode' => 'BR',
				'country' => 'BR',
				'countryname' => 'Brazil',
				'phonecc' => '55',
				'phonenumber' => '3334-3434',
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
				'adminfirstname' => 'Joe',
				'adminlastname' => 'Doe',
				'admincompanyname' => 'ACME',
				'adminemail' => 'fulano@ciclanomail.com',
				'adminaddress1' => 'Rua Do Norte, 1',
				'adminaddress2' => 'apt 1',
				'admincity' => 'Cidade',
				'adminfullstate' => 'SP',
				'adminstate' => 'SP',
				'adminpostcode' => '13148-133',
				'admincountry' => 'BR',
				'adminphonenumber' => '3334-3434',
				'fullphonenumber' => '+55.33343434',
				'adminfullphonenumber' => '+55.33343434',
				'ns1' => $TESTNS1,
				'ns2' => $TESTNS2,
				'ns3' => '',
				'ns4' => '',
				'ns5' => '',
				'original' => $TESTORIGINAL,
	
				'Certificate' => '',
				'CNPJ' => '1',
				'CPF' => '1',
				'FinanceDept' => '1',
				'Language' => 'Portuguese',
				'Passphrase' => '',
				'Password' => $TESTPASSWORD,
				'Sender' => 'root',
				'TechC' => '',
				'TechDept' => '2',
				'TestMode' => 'on',
				'Username' => $TESTUSER
		);
	
	
		$objRegistroEPPDomain->set('language',$TESTREGISTRATION['Language']);
	
		
		## TESTING Poll
		if($type == 'Poll'){
			if(!$this->testPoll($TESTPARAMS)){
				return;
			}
			exit;
		}
	
		## TESTING RegisterDomain
	
		if($type == 'RegisterDomain'){
			if (!$this->TestRegisterDomain($TESTREGISTRATION)) {
				return;
			}
		}
		else {
		// Check if domain exists
		
			try {
				$objRegistroEPPDomain->login($moduleparams);
				$objRegistroEPPDomain->set('domain',$TESTDOMAIN);
				$objRegistroEPPDomain->getInfo();
		
			}
			catch (Exception $e){
				$msg = $e->getMessage();
				$objRegistroEPPDomain->error('testerror',$msg,$TESTPARAMS);
				return;
			}
		}
		## Testing RenewDomain
		if(!$this->TestRenewDomain($TESTREGISTRATION)){
			return;
		}
		## TESTING GetNameserver and SaveNameServer
		#For testing nameservers at least 2 servers are required.

		if (!$this->TestNameServers($TESTPARAMS)) {
			return;
		}
		
		## TESTING GetContactDetails and SaveContactDetails
		if (!$this->TestContactDetails($TESTPARAMS)) {
			return;
		}
		## Testing DeleteDomain
		
		if($this->TestDeleteDomain($TESTPARAMS)){
			return;
		}

	}
	public function testPoll($TESTPARAMS){
		
		$include_path = ROOTDIR . '/modules/registrars/registrobr';
		set_include_path($include_path . PATH_SEPARATOR . get_include_path());
		require_once('RegistroEPP/RegistroEPPFactory.class.php');
		
		$TESTDOMAIN = $TESTPARAMS['domain'];
		$objRegistroEPPPoll = RegistroEPPFactory::build('RegistroEPPPoll');
		
		$msg = "Testing Poll........$TESTDOMAIN";
		$objRegistroEPPPoll->error('testerror',$msg,$TESTREGISTRATION);
		

		registrobr_Poll($TESTPARAMS);
	}
	
	public function TestRegisterDomain($TESTREGISTRATION){
		
		$include_path = ROOTDIR . '/modules/registrars/registrobr';
		set_include_path($include_path . PATH_SEPARATOR . get_include_path());
		require_once('RegistroEPP/RegistroEPPFactory.class.php');
		
		$TESTDOMAIN = $TESTPARAMS['domain'];
		$objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
		
		$msg = "RegisterDomain........$TESTDOMAIN";
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
	public function TestContactDetails($TESTPARAMS){
		
		$include_path = ROOTDIR . '/modules/registrars/registrobr';
		set_include_path($include_path . PATH_SEPARATOR . get_include_path());
		require_once('RegistroEPP/RegistroEPPFactory.class.php');
		
		$TESTDOMAIN = $TESTPARAMS['domain'];
		$objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
		
		
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
	public function TestNameServers($TESTPARAMS){
		
		
		$include_path = ROOTDIR . '/modules/registrars/registrobr';
		set_include_path($include_path . PATH_SEPARATOR . get_include_path());
		require_once('RegistroEPP/RegistroEPPFactory.class.php');
		
		$objRegistroEPPDomain = RegistroEPPFactory::build('RegistroEPPDomain');
		
		$TESTDOMAIN = $TESTPARAMS['domain'];
		
		try {
			$nameservers = registrobr_GetNameservers($TESTPARAMS);
		}
		catch (Exception $e){
			$msg = $e->getMessage();
			$msg = "FAILED........$TESTDOMAIN => $msg";
			$objRegistroEPPDomain->error('testerror',$msg,$TESTREGISTRATION);
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
	
}

?>
