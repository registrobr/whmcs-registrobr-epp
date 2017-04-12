<?php

/*	EPP Client class for PHP, Copyright 2013 CentralNic Ltd
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once dirname(__FILE__).'/Exception.php';
require_once dirname(__FILE__).'/Client.php';
require_once dirname(__FILE__).'/Frame.php';

/**
* @package Net_EPP_Simple
* @author CentralNic <https://www.centralnic.com>
*/
class Net_EPP_Simple extends Net_EPP_Client {

	private $connected;
	private $logged_in;
	private $user;

	var $debug;

	/**
	* @var DOMDocument
	*/
	var $greeting;

	/**
	* @param string $host
	* @param string $user
	* @param string $pass
	* @param boolean $debug
	* @param integer $port
	* @param integer timeout
	* @param boolean $ssl
	* @param resource $context
	* @throws Net_EPP_Exception
	*/
	function __construct($host=NULL, $user=NULL, $pass=NULL, $debug=false, $port=700, $timeout=1, $ssl=true, $context=NULL) {
		$this->connected	= false;
		$this->logged_in	= false;
		$this->debug		= $debug;
		$this->user		= $user;

		if ($host) $this->connect($host, $port, $timeout, $ssl, $context);
		if ($user && $pass) $this->login($user, $pass);
	}

	/**
	* @param string $user
	* @param string $pass
	* @throws Net_EPP_Exception
	*/
	function login($user, $pass) {
		if (!$this->connected) throw new Net_EPP_Exception("Not connected");
		$frame = new Net_EPP_Frame_Command_Login;
		$frame->clID->appendChild($frame->createTextNode($user));
		$frame->pw->appendChild($frame->createTextNode($pass));

		$frame->eppVersion->appendChild($frame->createTextNode($this->greeting->getElementsByTagNameNS(Net_EPP_Frame::EPP_URN, 'version')->item(0)->textContent));
		$frame->eppLang->appendChild($frame->createTextNode($this->greeting->getElementsByTagNameNS(Net_EPP_Frame::EPP_URN, 'lang')->item(0)->textContent));

		$els = $this->greeting->getElementsByTagNameNS(Net_EPP_Frame::EPP_URN, 'objURI');
		for ($i = 0 ; $i < $els->length ; $i++) $frame->svcs->appendChild($frame->importNode($els->item($i), true));

		$els = $this->greeting->getElementsByTagNameNS(Net_EPP_Frame::EPP_URN, 'svcExtension');
		if (1 == $els->length) $frame->svcs->appendChild($frame->importNode($els->item(0), true));

		$this->request($frame);
	}

	function checkDomain($domain) {
	}

	function checkDomains($domains) {
	}
	
	function checkHost($host) {
	}
	
	function checkHosts($host) {
	}
	
	function checkContact($contact) {
	}
	
	function checkContacts($contacts) {
	}
	
	function domainInfo($domain, $authInfo=NULL) {
	}
	
	function hostInfo($host, $authInfo=NULL) {
	}
	
	function contactInfo($contact, $authInfo=NULL) {
	}
	
	function domainTransferQuery($domain) {
	}
	
	function domainTransferCancel($domain) {
	}
	
	function domainTransferRequest($domain, $authInfo, $period, $unit='y') {
	}
	
	function domainTransferApprove($domain) {
	}
	
	function domainTransferReject($domain) {
	}
	
	function contactTransferQuery($contact) {
	}
	
	function contactTransferCancel($contact) {
	}
	
	function contactTransferRequest($contact, $authInfo) {
	}
	
	function contactTransferApprove($contact) {
	}
	
	function contactTransferReject($contact) {
	}
	
	function createDomain($domain) {
	}
	
	function createHost($host) {
	}
	
	function createContact($contact) {
	}
	
	function updateDomain($domain, $add, $rem, $chg) {
	}
	
	function updateContact($contact, $add, $rem, $chg) {
	}
	
	function updateHost($host, $add, $rem, $chg) {
	}
	
	function deleteDomain($domain) {
	}
	
	function deleteHost($host) {
	}
	
	function deleteContact($contact) {
	}
	
	function renewDomain($domain, $period, $unit='y') {
	}
	
	function ping() {
	}

	private function _check($type, $objects) {
		$class = 'Net_EPP_Frame_Command_Check_'.ucfirst($type);
		$frame = new $class;

		foreach ($objects as $object) $frame->addObject($object);
		$frame->clTRID->appendChild($frame->createTextNode($this->clTRID()));

		$result = $this->request($frame);
	}

	/*
	* send a frame to the server and get the response
	* @param DOMDocument|string $frame
	* @return DOMDocument
	* @throws Net_EPP_Exception
	*/
	function request($frame) {
		$this->sendFrame($frame);
		$response = $this->getFrame();
		return $response;
	}
	
	/*
	* send a <logout> to the server
	* @throws Net_EPP_Exception
	*/
	function logout() {
		$this->debug("logging out");
		$frame = new Net_EPP_Frame_Command_Logout;
		$frame->clTRID->appendChild($frame->createTextNode($this->clTRID()));
		$this->request($frame);
	}

	/*
	* connect to the server
	* @param string $host
	* @param integer $port
	* @param integer timeout
	* @param boolean $ssl
	* @param resource $context
	* @throws Net_EPP_Exception
	* @return DOMDocument the <greeting> frame recived from the server
	*/
	function connect($host, $port, $timeout, $ssl, $context) {
		$this->debug("attempting to connect to %s:%d", $host, $port);
		$dom = parent::connect($host, $port, $timeout, $ssl, $context);
		$this->debug("connected OK");
		$this->connected = true;
		$this->greeting = new Net_EPP_Frame_Greeting;
		$this->greeting->loadXML($dom->saveXML());
		return $this->greeting;
	}

	/*
	* get a frame from the server
	* @return DOMDocument
	* @throws Net_EPP_Exception
	*/
	function getFrame() {
		$xml = parent::getFrame();
		foreach (explode("\n", str_replace('><', ">\n<", trim($xml))) as $line) $this->debug("S: %s", $line);
		return DOMDocument::loadXML($xml);
	}

	/*
	* send a frame to the server
	* @param DOMDocument|string $xml
	* @return integer number of btyes sent
	* @throws Net_EPP_Exception
	*/
	function sendFrame($xml) {
		if ($xml instanceof DOMDocument) $xml = $xml->saveXML();
		foreach (explode("\n", str_replace('><', ">\n<", trim($xml))) as $line) $this->debug("C: %s", $line);
		return parent::sendFrame($xml);
	}

	/*
	* write a debug messge to the error log - is silent unless
	* debugging is enabled
	* @param string $format
	* @param one or more variables
	* @return boolean
	*/
	protected function debug() {
		if (!$this->debug) return true;
		$args = func_get_args();
		error_log(vsprintf(array_shift($args), $args));
	}

	/*
	* destructor - cleanly disconnect from the server
	* @throws Net_EPP_Exception
	*/
	function __destruct() {
		if ($this->logged_in) $this->logout();
		$this->debug("disconnecting from server");
		$this->disconnect();
	}

	/*
	* generate a client transaction ID
	* @return string
	*/
	function clTRID() {
		$clTRID = base_convert(
			hash('sha256', ($this->user ? $this->user.'-' : '').uniqid()),
			16,
			36
		);
	}
}