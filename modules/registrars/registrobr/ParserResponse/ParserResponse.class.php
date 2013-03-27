<?php
class ParserResponse {

	
	private $coderes;
	private $msg;
	private $reason;
	private $id;
	private $contact;
	private $doc;
	private $clID;
	private $name;
	private $street1;
	private $street2;
	private $street3;
	private $city;
	private $sp;
	private $pc;
	private $cc;
	private $voice;
	private $email;
	private $exDate;
	private $crDate;
	private $onHoldReason;
	
	private $msgQ;
	private $qDate;
	private $code;
	private $txt;
	private $ticket;
	private $objectId;
		
	#Parse xml response from epp server
	public function parse($response){
	
		$doc= new DOMDocument();
		$doc->loadXML($response);
		
		$createdate = substr($doc->getElementsByTagName('crDate')->item(0)->nodeValue,0,10);
		$exDate = substr($doc->getElementsByTagName('exDate')->item(0)->nodeValue,0,10);
		
		$holdreasons = array();
		$i = 0;
		
		$hreasons = $doc->getElementsByTagName('onHoldReason');
		foreach ($hreasons as $hr) {
			$holdreasons[$i] = $hr->nodeValue;
			$i++;
		}
		
		
		$this->set('coderes',$doc->getElementsByTagName('result')->item(0)->getAttribute('code'));
		$this->set('msg',$doc->getElementsByTagName('msg')->item(0)->nodeValue);
		$this->set('reason',$doc->getElementsByTagName('reason')->item(0)->nodeValue);
		$this->set('id',$doc->getElementsByTagName('id')->item(0)->nodeValue);
		$this->set('contact',$doc->getElementsByTagName('contact')->item(0)->nodeValue);
		$this->set('clID',$doc->getElementsByTagName('clID')->item(0)->nodeValue);
		$this->set('name',$doc->getElementsByTagName('name')->item(0)->nodeValue);
		$this->set('exDate',$exDate);
		$this->set('crDate',$createdate);
		$this->set('onHoldReason',$holdreasons);
		
		$this->set('name',$doc->getElementsByTagName('name')->item(0)->nodeValue);
		$this->set('ticket',$doc->getElementsByTagName('ticketNumber')->item(0)->nodeValue);
		
		
		$this->set('doc',$doc);
	}
	public function parsePoll($response){
		$doc= new DOMDocument();
		$doc->loadXML($response);

		/*
		 * 		
		<?xml version="1.0" encoding="UTF-8" standalone="no"?> 
		<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"      xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0      epp-1.0.xsd">  
		 <response>     
		 <result code="1301">
		 <msg lang="pt">Command completed successfully; ack to dequeue</msg>
		 </result>     
		 <msgQ count="110" id="12823">
		 <qDate>2011-07-05T14:42:14.0Z</qDate>
		 <msg lang='pt'>
		 	<code>301</code>
		 	<txt>Deposit notification.</txt>
		 	<depositValue>1000.00</depositValue>
		 	<creditBalance>1000.00</creditBalance>
		 </msg>
		 </msgQ>
		 <trID>
		 <clTRID>20997642941829377339</clTRID>
		 <svTRID>20130325152922-3671AABF-237-0002</svTRID>
		 </trID>
		 </response>
		 </epp>
		 */
		
		$msgQ = $doc->getElementsByTagName('msgQ')->item(0)->getAttribute('id');
		$qDate = $doc->getElementsByTagName('qDate')->item(0)->nodeValue;
		$code = $doc->getElementsByTagName('code')->item(0)->nodeValue;
		$txt = $doc->getElementsByTagName('txt')->item(0)->nodeValue;
		$reason = $doc->getElementsByTagName('reason')->item(0)->nodeValue;
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$ticket = $doc->getElementsByTagName('ticketNumber')->item(0)->nodeValue;
		$objectId = $doc->getElementsByTagName('objectId')->item(0)->nodeValue;

		$this->set('coderes',$coderes);
		$this->set('msgQ',$msgQ);
		$this->set('qDate',$qDate);
		$this->set('code',$code);
		$this->set('txt',$txt);
		$this->set('reason',$reason);
		$this->set('ticket',$ticket);
		$this->set('objectId',$objectId);
		$this->set('msg',$txt);
		$this->set('language',$language);
		
		
	}
	public function parseAck($response){
		/*<?xml version="1.0" encoding="UTF-8" standalone="no"?> 
		  <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0      epp-1.0.xsd">
		     <response>     
		     <result code="1000">
		     <msg lang="pt">Command completed successfully</msg>
		     </result>     
		     <msgQ count="101" id="24525"/>
               <trID>
               	<clTRID>14295320031113985087</clTRID>
               	<svTRID>20130327184845-2D7B794A-237-0003</svTRID>
               </trID>
		    </response> 
		  </epp>		*/
		$doc= new DOMDocument();
		$doc->loadXML($response);
		
		$msgQ = $doc->getElementsByTagName('msgQ')->item(0)->getAttribute('id');
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
				
		$this->set('coderes',$coderes);
		$this->set('msgQ',$msgQ);
	}
	public function parseBRorgInfo($response){
		$doc= new DOMDocument();
		$doc->loadXML($response);
		
		
		
		/* must be inherits from parser() */
		$this->set('coderes',$doc->getElementsByTagName('result')->item(0)->getAttribute('code'));
		$this->set('msg',$doc->getElementsByTagName('msg')->item(0)->nodeValue);
		$this->set('reason',$doc->getElementsByTagName('reason')->item(0)->nodeValue);
		$this->set('id',$doc->getElementsByTagName('id')->item(0)->nodeValue);
		$this->set('contact',$doc->getElementsByTagName('contact')->item(0)->nodeValue);
		$this->set('clID',$doc->getElementsByTagName('clID')->item(0)->nodeValue);
		$this->set('name',$doc->getElementsByTagName('name')->item(0)->nodeValue);
		$this->set('doc',$doc);
		/* must be inherits from parser() */
		
		$this->set('name',$doc->getElementsByTagName('name')->item(0)->nodeValue);
		$this->set('street1',$doc->getElementsByTagName('street')->item(0)->nodeValue);
		$this->set('street2',$doc->getElementsByTagName('street')->item(1)->nodeValue);
		$this->set('street3',$doc->getElementsByTagName('street')->item(2)->nodeValue);
		$this->set('city',$doc->getElementsByTagName('city')->item(0)->nodeValue);
		$this->set('sp',$doc->getElementsByTagName('sp')->item(0)->nodeValue);
		$this->set('pc',$doc->getElementsByTagName('pc')->item(0)->nodeValue);
		$this->set('cc',$doc->getElementsByTagName('cc')->item(0)->nodeValue);
		$this->set('voice',$doc->getElementsByTagName('voice')->item(0)->nodeValue);
		$this->set('email',$doc->getElementsByTagName('email')->item(0)->nodeValue);
		
	}
	
	public function getContacts(){
		
		$doc = $this->get('doc');
		$domaininfo=array();
		
		
		if(!empty($doc)){
			$domaininfo=array();
			for ($i=0; $i<=2; $i++) {
				$domaininfo[$doc->getElementsByTagName('contact')->item($i)->getAttribute('type')]=$doc->getElementsByTagName('contact')->item($i)->nodeValue;
			}
			return $domaininfo;
		}
		else {
			throw new Exception('Variable doc is not set, run parse method before');
		}
	}
	public function getOrganization(){
		$doc = $this->get('doc');
		
		if(!empty($doc)){
			# Get TaxPayer ID for obtaining Reg Info
			$RegistrantTaxID=$doc->getElementsByTagName('organization')->item(0)->nodeValue;
			return $RegistrantTaxID;
		}
		else {
			throw new Exception('Variable doc is not set, run parse method before');
		}		
	}
	public function getNameServers(){
		//after RegistroEPPDomainInfo();
		
		$doc = $this->get('doc');
		if(!empty($doc)){
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
		else {
			throw new Exception('Response XML doesnt exists');
			
		}
		
		
	}


    public function get($key){
    	if (property_exists($this, $key)) {
    		return $this->$key;
    	}
    	else {
    		return false;
    	}
    }
    public function set($key,$value){
    	if (property_exists($this, $key)) {
    		$this->$key = $value;
    		return $this;
    	}
    	else {
    		return false;
    	}
    }

}

?>
