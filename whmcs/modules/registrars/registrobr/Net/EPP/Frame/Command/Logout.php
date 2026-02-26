<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_Frame_Command_Logout extends Net_EPP_Frame_Command
    {
        public function __construct()
        {
            parent::__construct('logout');
        }
    }
