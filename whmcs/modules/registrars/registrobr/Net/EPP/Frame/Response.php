<?php

	/**
	* @package Net_EPP
	*/
	class Net_EPP_Frame_Response extends Net_EPP_Frame {
		function __construct() {
			parent::__construct('response');
		}

		function code() {
			return $this->getElementsByTagName('result')->item(0)->getAttribute('code');
		}

		function message() {
			return $this->getElementsByTagName('msg')->item(0)->firstChild->textContent;
		}
	}
?>
