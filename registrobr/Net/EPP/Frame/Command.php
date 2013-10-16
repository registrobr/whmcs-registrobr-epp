<?php

	/**
	* @package Net_EPP
	*/
	class Net_EPP_Frame_Command extends Net_EPP_Frame {

		function __construct($command, $type) {
			$this->type = $type;
			$command = strtolower($command);
			if (!in_array($command, array('check', 'info', 'create', 'update', 'delete', 'renew', 'transfer', 'poll', 'login', 'logout'))) trigger_error("Invalid argument value '$command' for \$command", E_USER_ERROR);
			parent::__construct('command');

			$this->command = $this->createElement($command);
			$this->body->appendChild($this->command);

			$this->payload = $this->createElementNS(
				Net_EPP_ObjectSpec::xmlns($this->type),
				$this->type.':'.$command
			);

			$this->command->appendChild($this->payload);

			$this->clTRID = $this->createElement('clTRID');
			$this->clTRID->appendChild($this->createTextNode());
			$this->body->appendChild($this->clTRID);
		}

		function addObjectProperty($name, $value=NULL) {
			debug_log("%s::%s(%s, %s)", __CLASS__, __FUNCTION__, $name, $value);
			$element = $this->createObjectPropertyElement($name);
			$this->payload->appendChild($element);

			if ($value instanceof DomNode) {
				$element->appendChild($value);

			} elseif (isset($value)) {
				$element->appendChild($this->createTextNode($value));

			}
			return $element;
		}

		function createObjectPropertyElement($name) {
			return $this->createElementNS(
				Net_EPP_ObjectSpec::xmlns($this->type),
				$this->type.':'.$name
			);
		}

	}
?>
