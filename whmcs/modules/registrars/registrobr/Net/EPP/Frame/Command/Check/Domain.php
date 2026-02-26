<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_Frame_Command_Check_Domain extends Net_EPP_Frame_Command_Check
    {
        public function __construct()
        {
            parent::__construct('domain');
        }
    }
