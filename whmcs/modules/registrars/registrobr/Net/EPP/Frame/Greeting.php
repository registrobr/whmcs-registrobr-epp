<?php

    /**
     * @package Net_EPP
     */
    final class Net_EPP_Frame_Greeting extends Net_EPP_Frame
    {
        public function __construct()
        {
            parent::__construct('greeting');

            $this->svID = $this->createElement('svID');
            $this->body->appendChild($this->svID);

            $this->svDate = $this->createElement('svDate');
            $this->body->appendChild($this->svDate);

            $this->svcMenu = $this->createElement('svcMenu');
            $this->body->appendChild($this->svcMenu);

            $this->dcp = $this->createElement('dcp');
            $this->body->appendChild($this->dcp);
        }
    }
