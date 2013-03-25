<?php

require_once("RegistroEPP.class.php");

class RegistroEPPPoll extends RegistroEPP {

	protected $tech;

	public function getInfo(){
		
		
	}
	public function getMessages($moduleparams){
		
		require_once('ParserResponse/ParserResponse.class.php');
		# We need XML beautifier for showing understable XML code
		require_once('BeautyXML.class.php');		
		# We need EPP stuff
		require_once('Net/EPP/Frame.php');
		require_once('Net/EPP/Frame/Command.php');
		require_once('Net/EPP/ObjectSpec.php');
		
		$client = $this->get('netClient');
		if(empty($client)){
			throw new Exception('net Client is not setted, check login before');
		}
		
		$requestXML = $this->_getXMLMessages();
		$responseXML = $client->request($requestXML);
		
		

		$objParser = New ParserResponse();		
		$objParser->parsePoll($responseXML);
		
		$this->set('msgQ',$objParser->get('msgQ'));
		$this->set('qDate',$objParser->get('qDate'));
		$this->set('code',$objParser->get('code'));
		$this->set('txt',$objParser->get('txt'));
		$this->set('reason',$objParser->get('reason'));
		$this->set('coderes',$objParser->get('coderes'));
				
		$coderes = $objParser->get('coderes',$coderes);
		
		if ($coderes == 'xxxx') {
			$msg = $this->errorEPP('domainnotfound',$objParser,$requestXML,$responseXML);
			throw new Exception($msg);
		}
		
		#build content
		
		$content = $this->_registrobr_lang("Date").substr($qDate,0,10)." ";
		$content .= $this->_registrobr_lang("Time").substr($qDate,11,10)." UTC\n";
		$code = $this->get('code');
		$txt = $this->get('txt');
		$content .= $this->_registrobr_lang("Code").$code."\n";
		$content .= $this->_registrobr_lang("Text").$txt;
		$reason = $this->get('reason');
		

		if (!empty($reason)) {
			$content .= $this->_registrobr_lang("Reason").$reason."\n";
		}
		
		
		$content .= $this->_registrobr_lang("FullXMLBelow");
		$bc = new BeautyXML();
		
		$content .= htmlentities($bc->format($response));
		
		$this->set('content',$content);
		
		$this->set('ticket',$objParser->get('ticket'));
		$this->set('objectId',$objParser->get('objectId'));
		
		
 		
	}
	
	public function sendAck(){

		require_once('ParserResponse/ParserResponse.class.php');

		$client = $this->get('netClient');
		if(empty($client)){
			throw new Exception('net Client is not setted, check login before');
		}

		$requestXML = $this->_getXMLMessages();
		$responseXML = $client->request($requestXML);

		$objParser = New ParserResponse();
		$objParser->parseAck($responseXML);

		$coderes = $objParser->get('coderes');
		
		if ($coderes != '1000') {
			$msg = $this->errorEPP('pollackerrorcode',$objParser,$requestXML,$responseXML);
			throw new Exception($msg);
		}
		
	}
	private function _getXMLAck(){
		
		$msgid = $this->get('msgQ');
		
		# Ack poll message
		$request='  <epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
		<command>
			<poll op="ack" msgID="'.$msgid.'"/>
			<clTRID>'.mt_rand().mt_rand().'</clTRID>
		</command>
		</epp>
		';
		
		return $request;

	}
	private function _getXMLMessages(){
		
		$request = '
		<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd">
			<command>
				<poll op="req"/>
				<clTRID>'.mt_rand().mt_rand().'</clTRID>
			</command>
		</epp>
		';
		
		return $request;
		
	}



}

?>
