<?php

require_once("RegistroEPP.class.php");

class RegistroEPPBrorg extends RegistroEPP {
	
	protected $name;
	protected $contactID;
	protected $contactIDDigits;
	protected $street1;
	protected $street2;
	protected $street3;
	protected $city;
	protected $sp;
	protected $pc;
	protected $cc;
	protected $voice;
	protected $email;
	


	public function getInfo(){
		require_once('ParserResponse/ParserResponse.class.php');

		$client = $this->get('netClient');
		if(empty($client)){
			throw new Exception('net Client is not setted, check login before');
		}
		$requestXML = $this->_getXMLinfo();
		$responseXML = $client->request($requestXML);
		$objParser = New ParserResponse();
		$objParser->parseBRorgInfo($responseXML); // must be change to OO aproach
		

        $this->set('coderes',$objParser->get('coderes'));
        $this->set('contact',$objParser->get('contact'));
        $this->set('name',$objParser->get('name'));
        
        $this->set('street1',$objParser->get('street1'));
        $this->set('street2',$objParser->get('street2'));
        $this->set('street3',$objParser->get('street3'));
        $this->set('street4',$objParser->get('street4'));
        
        $this->set('city',$objParser->get('city'));
        $this->set('sp',$objParser->get('sp'));
        $this->set('pc',$objParser->get('pc'));
        $this->set('cc',$objParser->get('cc'));
        $this->set('voice',$objParser->get('voice'));
        $this->set('email',$objParser->get('email'));
        
        $coderes = $objParser->get('coderes');
        
        if($coderes != '1000') {
			$msg = $this->errorEPP('getcontactorginfoerrorcode',$objParser,$requestXML,$responseXML,$language);
			throw new Exception($msg);
		}
		
	}
	public function createData(){
		require_once('ParserResponse/ParserResponse.class.php');
		
		$client = $this->get('netClient');
		if(empty($client)){
			throw new Exception('net Client is not setted, check login before');
		}
		$requestXML = $this->_createXML();
		
		$responseXML = $client->request($requestXML);
		$objParser = New ParserResponse();
		$objParser->parse($responseXML); 
				
		$coderes = $objParser->get('coderes');
		$id = $objParser->get('id');
		
		$this->set('id',$id);
		$this->set('coderes',$coderes);
		
		if($coderes != '1000') {
			$msg = $this->errorEPP('savecontacttypeerrorcode',$objParser,$requestXML,$responseXML,$language);
			throw new Exception($msg);
		}
	}
	
	public function updateInfo($OldContacts,$NewContacts){
		require_once('ParserResponse/ParserResponse.class.php');
	
		$client = $this->get('netClient');
		if(empty($client)){
			throw new Exception('net Client is not setted, check login before');
		}

		if(empty($OldContacts) || empty($NewContacts)){
			throw new Exception('OldContacts and NewContacts params are required');
		}
		
		$requestXML = $this->_updateXMLinfo($OldContacts,$NewContacts);
		$responseXML = $client->request($requestXML);
		$objParser = New ParserResponse();
		$objParser->parse($responseXML);
	
		$coderes = $objParser->get('coderes');
		$id = $objParser->get('id');
	
		$this->set('id',$id);
		$this->set('coderes',$coderes);
	
		if($coderes != '1000') {
			$msg = $this->errorEPP('savecontactorgupdateeerrorcode',$objParser,$requestXML,$responseXML,$language);
			throw new Exception($msg);
		}
	}
	
	
	
	private function _updateXMLinfo($OldContacts,$NewContacts){
		
		$contactID = $this->get('contactID');
		$contactIDDigits = $this->get('contactIDDigits');
		
		
		if(!$contactID){
			throw new Exception('contactID is not set');
		}

		$name 	 = $this->get('name');
		$street1 = $this->get('street1');
		$street2 = $this->get('street2');
		$street3 = $this->get('street3');
		$city = $this->get('city');
		$sp = $this->get('sp');
		$pc = $this->get('pc');
		$cc = $this->get('cc');
		$voice = $this->get('voice');
		$email = $this->get('email');
		$responsible = $this->get('responsible');
		
		$request='<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
		<command>
			<update>
				<contact:update xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd">
				<contact:id>'.$contactIDDigits.'</contact:id>
				<contact:chg>
					<contact:postalInfo type="loc">
				    	<contact:name>'.$name.'</contact:name>
				    	<contact:addr>';
if(!empty($street1)) $request.= "<contact:street>$street1</contact:street>";
if(!empty($street2)) $request.= "<contact:street>$street2</contact:street>";
if(!empty($street3)) $request.= "<contact:street>$street3</contact:street>";
						$request.='
						<contact:city>'.$city.'</contact:city>
				    	<contact:sp>'.$sp.'</contact:sp>
				    	<contact:pc>'.$pc.'</contact:pc>
				    	<contact:cc>'.$cc.'</contact:cc>
			    		</contact:addr>
				    </contact:postalInfo>
			    	<contact:voice>'.$voice.'</contact:voice>
			    	<contact:email>'.$email.'</contact:email>
				</contact:chg>
				</contact:update>
			</update>
			<extension>
				<brorg:update xmlns:brorg="urn:ietf:params:xml:ns:brorg-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:brorg-1.0 brorg-1.0.xsd">
					<brorg:organization>'.$contactID.'</brorg:organization>
					<brorg:add>
						<brorg:contact type="admin">'.$NewContacts["Registrant"].'</brorg:contact>
					</brorg:add>
					<brorg:rem>
						<brorg:contact type="admin">'.$OldContacts["Registrant"].'</brorg:contact>
					</brorg:rem>
					<brorg:chg>
						<brorg:responsible>'.$responsible.'</brorg:responsible>
					</brorg:chg>
				</brorg:update>
			</extension>
			<clTRID>'.mt_rand().mt_rand().'</clTRID>
			</command>
			</epp>';
						
		
		return $request;		
		
	}

	private function _getXMLinfo(){

		
		$contactID = $this->get('contactID');
		$contactIDDigits = $this->get('contactIDDigits');
		

		if(!$contactIDDigits){
    		//throw new Exception('contactID is not set');
		}
		
        $request = '
                <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
                    <command>
                        <info>
                            <contact:info xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd">
                                <contact:id>'.$contactIDDigits.'</contact:id>
                            </contact:info>
                        </info>';
				        if($contactID){
				        	$request.= '
	        				<extension>
	                            <brorg:info xmlns:brorg="urn:ietf:params:xml:ns:brorg-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:brorg-1.0 brorg-1.0.xsd">
	                                <brorg:organization>'.$contactID.'</brorg:organization>
	                            </brorg:info>
	                        </extension>';
				        }
        $request.='<clTRID>'.mt_rand().mt_rand().'</clTRID>
                  </command>
                </epp>
        ';

		return $request;
    }
    
    private function _createXML(){
    	
    	$name  = $this->get('name');
    	$street1 = $this->get('street1');
    	$street2 = $this->get('street2');
    	$street3 = $this->get('street3');
    	$city = $this->get('city');
    	$sp = $this->get('sp');
    	$pc = $this->get('pc');
    	$cc = $this->get('cc');
    	$voice = $this->get('voice');
    	$email = $this->get('email');
    	
    	if(!$name){
    		throw new Exception('name must be set');
    	}   	 
    	//must check
    	
    	$request='
    	<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
	    	<command>
		    	<create>
			    	<contact:create xmlns:contact="urn:ietf:params:xml:ns:contact-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd">
				    	<contact:id>dummy</contact:id>
				    	<contact:postalInfo type="loc">
					    	<contact:name>'.$name.'</contact:name>
					    	<contact:addr>';
if(!empty($street1)) $request.= "<contact:street>$street1</contact:street>";
if(!empty($street2)) $request.= "<contact:street>$street2</contact:street>";
if(!empty($street3)) $request.= "<contact:street>$street3</contact:street>";
				     $request.= '
					    	<contact:city>'.$city.'</contact:city>
					    	<contact:sp>'.$sp.'</contact:sp>
					    	<contact:pc>'.$pc.'</contact:pc>
					    	<contact:cc>'.$cc.'</contact:cc>
				    		</contact:addr>
			    		</contact:postalInfo>
				    	<contact:voice>'.$voice.'</contact:voice>
				    	<contact:email>'.$email.'</contact:email>
				    	<contact:authInfo>
				    	<contact:pw/>
				    	</contact:authInfo>
			    	</contact:create>
		    	</create>
		    	<clTRID>'.mt_rand().mt_rand().'</clTRID>
	    	</command>
    	</epp>';
				     
				     print_r($request);
    	
    	return $request;
    }
    
    
    public function verifyProvider($prov,$prov_module){
    	$prov_module = trim($prov_module);
    	$prov = trim($prov);
    	
    	if($prov != $prov_module){ 
    		$msg = $this->error('getcontactnotallowed',$prov,$prov_module);
    		throw new Exception($msg);
    	}
    }
}

?>
