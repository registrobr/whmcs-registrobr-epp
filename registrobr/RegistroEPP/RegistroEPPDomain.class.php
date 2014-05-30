<?php

require_once("RegistroEPP.class.php");

class RegistroEPPDomain extends RegistroEPP {

    protected $tech;
    protected $regperiod;
    protected $contactIDDigits;
    protected $contactID;
    protected $exDate;
    protected $crDate;
    protected $onHoldReason;
    protected $name;
    protected $coderes;
    protected $ticket;
        

    public function getInfo(){
        $include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());
        
        require_once('ParserResponse/ParserResponse.class.php');

        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
        
        $requestXML = $this->_getXMLinfo();
        $responseXML = $client->request($requestXML);
        
        $objParser = New ParserResponse();        
        $objParser->parse($responseXML);
                
        $coderes = $objParser->get('coderes',$coderes);
        $this->set('coderes',$coderes);
                
        
        if ($coderes == '2303') {
            $msg = $this->errorEPP('domainnotfound',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }
        elseif ($coderes != '1000') {
            $msg = $this->errorEPP('getnserrorcode',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }

        $this->set('nameservers',$objParser->getNameServers());
        $this->set('clID',$objParser->get('clID'));
        $this->set('contacts',$objParser->getContacts());
        $this->set('organization',$objParser->getOrganization());
        $this->set('exDate',$objParser->get('exDate'));
        $this->set('crDate',$objParser->get('crDate'));
        $this->set('onHoldReason',$objParser->get('onHoldReason'));
        $this->set('ticket',$objParser->get('ticket'));
         
    }
    
    public function createDomain($Nameservers = null){
        require_once('ParserResponse/ParserResponse.class.php');
        
        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
        
        $requestXML = $this->_createXMLDomain($Nameservers);
        $responseXML = $client->request($requestXML);
        
        $objParser = New ParserResponse();
        $objParser->parse($responseXML);
        
        $this->set('ticket',$objParser->get('ticket'));
        $this->set('name',$objParser->get('name'));
        $this->set('coderes',$objParser->get('coderes'));
        
        $coderes = $objParser->get('coderes',$coderes);
        
        #$msg = $this->errorEPP('registererrorcode',$objParser,$requestXML,$responseXML);
        
        
        if ($coderes != '1001') {
            $msg = $this->errorEPP('registererrorcode',$objParser,$requestXML,$responseXML);                
            throw new Exception($msg);
        }
                 
    
    }
    public function updateNameServers($OldNameservers,$NewNameservers){
        require_once('ParserResponse/ParserResponse.class.php');
    
        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
    
        $requestXML = $this->_getXMLupdateNameserver($OldNameservers,$NewNameservers);
        $responseXML = $client->request($requestXML);
    
        $objParser = New ParserResponse();
        $objParser->parse($responseXML);
    
        $coderes = $objParser->get('coderes',$coderes);
        
        if ($coderes != '1000') {
            $msg = $this->errorEPP('setnsupdateerrorcode',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }
    
    
        $this->set('coderes',$coderes);

            
    }
    
    public function updateInfo($OldContacts,$NewContacts){
        
        require_once('ParserResponse/ParserResponse.class.php');
        
        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
        
        $requestXML = $this->_updateXMLinfo($OldContacts,$NewContacts);
        $responseXML = $client->request($requestXML);
        
        $objParser = New ParserResponse();
        $objParser->parse($responseXML);
        
        $coderes = $objParser->get('coderes',$coderes);
        
        if ($coderes != '1000') {
            $msg = $this->errorEPP('savecontactdomainupdateerrorcode',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }

    }
    
    public function deleteDomain(){
        
        require_once('ParserResponse/ParserResponse.class.php');
        
        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
        
        $requestXML = $this->_deleteXMLDomain();
        $responseXML = $client->request($requestXML);
        
        $objParser = New ParserResponse();
        $objParser->parse($responseXML);
        
        $coderes = $objParser->get('coderes',$coderes);
        $this->set('coderes',$coderes);
        
        if ($coderes != '1000' and $coderes != '2303') {
            $msg = $this->errorEPP('deleteerrorcode',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }    
    }

    public function renewDomain(){
    
        require_once('ParserResponse/ParserResponse.class.php');
    
        $client = $this->get('netClient');
        if(empty($client)){
            throw new Exception('net Client is not setted, check login before');
        }
    
        $requestXML = $this->_renewXMLDomain();
        $responseXML = $client->request($requestXML);
    
        $objParser = New ParserResponse();
        $objParser->parse($responseXML);
    
        $coderes = $objParser->get('coderes',$coderes);
        $this->set('coderes',$coderes);
        $this->set('exDate',$objParser->get('exDate'));
        $this->set('crDate',$objParser->get('crDate'));
        $this->set('onHoldReason',$objParser->get('onHoldReason'));
        $this->set('ticket',$objParser->get('ticket'));
        
        
        if ($coderes != '1000') {
            $msg = $this->errorEPP('renewerrorcode',$objParser,$requestXML,$responseXML);
            throw new Exception($msg);
        }
    }
    
    private function _renewXMLDomain(){
        
        $domain = $this->get('domain');
        $exDate = $this->get('exDate');
        $regperiod  = $this->get('regperiod');
        
        
        $request='
        <epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
            <command>
            <renew>
                <domain:renew>
                    <domain:name>'.$domain.'</domain:name>
                    <domain:curExpDate>'.$exDate.'</domain:curExpDate>
                    <domain:period unit="y">'.$regperiod.'</domain:period>
                </domain:renew>
            </renew>
            <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';
        
        return $request;
    }
    private function _deleteXMLDomain(){
        
        $domain = $this->get('domain');
        
        if(!$domain){
            throw new Exception("Domain not set ");
        }
        
        $request = '
        <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
            <delete>
                <domain:delete xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                    <domain:name>'.$domain.'</domain:name>
                </domain:delete>
            </delete>
            <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';
        
        return $request;
    }
    
    private function _updateXMLinfo($OldContacts,$NewContacts){
        
        $domain = $this->get('domain');
        
            
        if(!$domain){
            throw new Exception("Domain not set ");
        }
        
        $request='
        <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
                <update>
                    <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                    <domain:name>'.$domain.'</domain:name>
                    <domain:add>';
                    foreach ($NewContacts as $type => $id) {
                        if ($type!="Registrant") {
                            $request.='<domain:contact type="'.strtolower($type).'">'.$id.'</domain:contact>' ;
                        }
                    }
                    $request.='</domain:add>
                    <domain:rem>';
                    foreach ($OldContacts as $type => $id){
                        if ($type!="Registrant") {
                            $request.='<domain:contact type="'.strtolower($type).'">'.$id.'</domain:contact>' ;
                        }
                    }
                    $request.='
                    </domain:rem>
                    </domain:update>
                </update>
                <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>';
                    
        
        return $request;
        
    }
    
    private function _getXMLinfo(){

        $ticket = $this->get('ticket');
        $domain = $this->get('domain');

            
        if(!$domain){
            throw new Exception("Domain not set => $domain");
        }
        
        $request = '
        <epp xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
                <info>
                    <domain:info xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                        <domain:name hosts="all">'.$domain.'</domain:name>
                    </domain:info>
                </info>';
                if ($this->get('ticket')) {
                    $request.='
                    <extension>
                        <brdomain:info xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0" 
                        xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0 
                        brdomain-1.0.xsd"> 
                            <brdomain:ticketNumber>'.$ticket.'</brdomain:ticketNumber>
                        </brdomain:info>
                    </extension>';
                    $this->set('ticket','');
                }    
                $request.='    
                <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';

        return $request;
    }

    private function _createXMLDomain($Nameservers = null){
        
        $domain          = $this->get('domain');
        $regperiod          = $this->get('regperiod');
        $contactIDDigits = $this->get('contactIDDigits');
        $contactID          = $this->get('contactID');
        $tech            = $this->get('tech');
        
        $addhosts = '';
         
        foreach ($Nameservers as $key => $ns){
            # Generate XML for nameservers
            if (!empty($ns)) {
                $add_hosts.= '
                <domain:hostAttr>
                <domain:hostName>'.$ns.'</domain:hostName>
                </domain:hostAttr>
                ';
            }
        }

         
        
        $request = '
        <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
            <command>
            <create>
                <domain:create xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
                    <domain:name>'.$domain.'</domain:name>
                    <domain:period unit="y">'.$regperiod.'</domain:period>
                    <domain:ns>'.$add_hosts.'</domain:ns>';
                     
                    # Valid .br contacts have 3 or more letters and/or numbers
                    if (strlen($tech)>2) 
                        $request.=' <domain:contact type="tech">'.$tech.'</domain:contact>';
                    
                    $request.='
                    <domain:authInfo>
                    <domain:pw/>
                    </domain:authInfo>
                    </domain:create>
            </create>
            <extension>
                <brdomain:create xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0 brdomain-1.0.xsd">
                    <brdomain:organization>'.$contactID.'</brdomain:organization>
                </brdomain:create>
            </extension>
            <clTRID>'.mt_rand().mt_rand().'</clTRID>
            </command>
        </epp>
        ';
                    
        return $request;
    }
    
    private function _getXMLupdateNameserver($OldNameservers,$NewNameservers){
        

        $ticket = $this->get('ticket');
        $domain = $this->get('domain');
        
        $addhosts = '';
        $remhosts = '';
        
        foreach ($NewNameservers as $key => $ns){
            # Generate XML for nameservers
            if (!empty($ns)) {
                $add_hosts.= '
                    <domain:hostAttr>
                    <domain:hostName>'.$ns.'</domain:hostName>
                    </domain:hostAttr>
                ';
            }
        }
        
        
        foreach ($OldNameservers as $key => $ns) {
            if (!empty($ns)){
                $rem_hosts .= '
                    <domain:hostAttr>
                    <domain:hostName>'.$ns.'</domain:hostName>
                    </domain:hostAttr>
                ';
            }
        }
        
        
        # Build request
        $request='
        <epp xmlns:epp="urn:ietf:params:xml:ns:epp-1.0" xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <command>
        <update>
        <domain:update>
        <domain:name>'.$domain.'</domain:name>';
        if(!empty($add_hosts))
            $request .='
            <domain:add>
                <domain:ns>'.$add_hosts.' </domain:ns>
            </domain:add>';
            
            $request .='
            <domain:rem>
                <domain:ns>'.$rem_hosts.'</domain:ns>
            </domain:rem>
        </domain:update>
        </update>';
        if ($ticket!='') {
            $request.='
            <extension>
            <brdomain:update xmlns:brdomain="urn:ietf:params:xml:ns:brdomain-1.0"
            xsi:schemaLocation="urn:ietf:params:xml:ns:brdomain-1.0
            brdomain-1.0.xsd">
            <brdomain:ticketNumber>'.$ticket.'</brdomain:ticketNumber>
            </brdomain:update>
            </extension>';
        }
        $request.='
        <clTRID>'.mt_rand().mt_rand().'</clTRID>
        </command>
        </epp>
        ';
        
        return $request;
    }
    
    public function verifyProvider($prov,$prov_module){
        if($prov != $prov_module){ 
            $msg = $this->error('getcontactnotallowed',$prov,$prov_module);
            throw new Exception($msg);
        }
    }
    
 
}

?>
