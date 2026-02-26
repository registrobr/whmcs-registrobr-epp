<?php

    /**
     * @package Net_EPP
     */
    abstract class Net_EPP_Frame_Command_Info extends Net_EPP_Frame_Command
    {
        public function __construct($type)
        {
            parent::__construct('info', $type);
        }

        public function setObject($object)
        {
            $type = strtolower(str_replace(__CLASS__.'_', '', get_class($this)));
            foreach ($this->payload->childNodes as $child) {
                $this->payload->removeChild($child);
            }
            $this->payload->appendChild($this->createElementNS(
                Net_EPP_ObjectSpec::xmlns($type),
                $type.':'.Net_EPP_ObjectSpec::id($type),
                $object
            ));
        }

        public function setAuthInfo($authInfo)
        {
            $el = $this->createObjectPropertyElement('authInfo');
            $el->appendChild($this->createObjectPropertyElement('pw'));
            $el->firstChild->appendChild($this->createTextNode($authInfo));
            $this->payload->appendChild($el);
        }
    }
