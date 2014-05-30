<?php

	/**
	* @package Net_EPP
	*/
	abstract class Net_EPP_Frame extends DomDocument {

		const EPP_URN		= 'urn:ietf:params:xml:ns:epp-1.0';
		const SCHEMA_URI	= 'http://www.w3.org/2001/XMLSchema-instance';
		const TEMPLATE		= '<epp xmlns="urn:ietf:params:xml:ns:epp-1.0"></epp>';

		function __construct($type) {
			parent::__construct('1.0', 'UTF-8');

			$this->loadXML(self::TEMPLATE);

			$type = strtolower($type);
			if (!in_array($type, array('hello', 'greeting', 'command', 'response'))) trigger_error("Invalid argument value '$type' for \$type", E_USER_ERROR);

			$this->epp = $this->firstChild;
			$this->body = $this->createElement($type);
			$this->epp->appendChild($this->body);
		}

		function friendly() {
			return str_replace('><', ">\n<", $this->saveXML());
		}
	}
?>
