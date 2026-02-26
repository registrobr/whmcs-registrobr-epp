<?php

    /**
     * @package Net_EPP
     */
    final class Net_EPP_Frame_Hello extends Net_EPP_Frame
    {
        public function __construct()
        {
            parent::__construct('hello');
        }
    }
