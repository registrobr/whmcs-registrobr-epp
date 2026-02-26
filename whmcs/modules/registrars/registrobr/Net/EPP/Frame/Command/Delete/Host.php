<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_Frame_Command_Delete_Host extends Net_EPP_Frame_Command_Delete
    {
        public function __construct()
        {
            parent::__construct('host');
        }
    }
