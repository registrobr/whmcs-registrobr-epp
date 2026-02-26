<?php

    /**
     * @package Net_EPP
     */
    abstract class Net_EPP_Frame_Command_Renew extends Net_EPP_Frame_Command
    {
        public function __construct($type)
        {
            parent::__construct('renew', $type);
        }

        public function addObject($object)
        {
            $type = strtolower(str_replace(__CLASS__.'_', '', get_class($this)));
            $this->payload->appendChild($this->createElementNS(
                Net_EPP_ObjectSpec::xmlns($type),
                $type.':'.Net_EPP_ObjectSpec::id($type),
                $object
            ));
        }
    }
