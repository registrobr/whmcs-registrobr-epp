<?php

	/**
	* @package Net_EPP
	*/
	class Net_EPP_ObjectSpec {

		static $_spec = array(
			'domain' => array(
				'xmlns'		=> 'urn:ietf:params:xml:ns:domain-1.0',
				'id'		=> 'name',
				'schema'	=> 'urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd',
			),
			'host' => array(
				'xmlns'		=> 'urn:ietf:params:xml:ns:host-1.0',
				'id'		=> 'name',
				'schema'	=> 'urn:ietf:params:xml:ns:host-1.0 host-1.0.xsd',
			),
			'contact' => array(
				'xmlns'		=> 'urn:ietf:params:xml:ns:contact-1.0',
				'id'		=> 'id',
				'schema'	=> 'urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd',
			),
		);

		static function id($object) {
			return self::$_spec[$object]['id'];
		}

		static function xmlns($object) {
			return self::$_spec[$object]['xmlns'];
		}

		static function schema($object) {
			return self::$_spec[$object]['schema'];
		}
	}
?>
