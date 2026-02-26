<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_ObjectSpec
    {
        public static $_spec = array(
            'domain' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:domain-1.0',
                'id' => 'name',
                'schema' => 'urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd',
            ),
            'host' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:host-1.0',
                'id' => 'name',
                'schema' => 'urn:ietf:params:xml:ns:host-1.0 host-1.0.xsd',
            ),
            'contact' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:contact-1.0',
                'id' => 'id',
                'schema' => 'urn:ietf:params:xml:ns:contact-1.0 contact-1.0.xsd',
            ),
            'rgp' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:rgp-1.0',
                'id' => 'id',
                'schema' => 'urn:ietf:params:xml:ns:rgp-1.0 rgp-1.0.xsd',
            ),
            'launch' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:launch-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:launch-1.0 launch-1.0.xsd',
            ),
            'idn' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:idn-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:idn-1.0 idn-1.0.xsd',
            ),
            'tmch' => array(
                'xmlns' => 'urn:ar:params:xml:ns:tmch-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:tmch-1.0 tmch-1.0.xsd',
            ),
            'application' => array(
                'xmlns' => 'urn:ar:params:xml:ns:application-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:application-1.0 application-1.0.xsd',
            ),
            'price' => array(
                'xmlns' => 'urn:ar:params:xml:ns:price-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:price-1.0 price-1.0.xsd',
            ),
            'asia' => array(
                'xmlns' => 'urn:ar:params:xml:ns:asia-1.0',
                'id' => 'id',
                'schema' => 'urn:ar:params:xml:ns:asia-1.0 asia-1.0.xsd',
            ),
            'signedMark' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:signedMark-1.0',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:signedMark-1.0 signedMark-1.0.xsd',
            ),
            'fee' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:fee-0.5',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:fee-0.5 fee-0.5.xsd',
            ),
            'fee_23' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:fee-0.23',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:fee-0.23 fee-0.23.xsd',
            ),
            'allocationToken' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:allocationToken-1.0',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:allocationToken-1.0 allocationToken-1.0.xsd',
            ),
            'auxcontact' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:auxcontact-0.1',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:auxcontact-0.1 auxcontact-1.0.xsd',
            ),
            'secDNS' => array(
                'xmlns' => 'urn:ietf:params:xml:ns:secDNS-1.1',
                'id' => 'domain',
                'schema' => 'urn:ietf:params:xml:ns:secDNS-1.1 secDNS-1.1.xsd',
            ),
        );

        public static function id($object)
        {
            return self::$_spec[$object]['id'];
        }

        public static function xmlns($object)
        {
            return self::$_spec[$object]['xmlns'];
        }

        public static function schema($object)
        {
            return self::$_spec[$object]['schema'];
        }
    }
