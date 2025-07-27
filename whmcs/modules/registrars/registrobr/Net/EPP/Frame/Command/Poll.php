<?php

    /**
     * @package Net_EPP
     */
    abstract class Net_EPP_Frame_Command_Poll extends Net_EPP_Frame_Command
    {
        public function __construct()
        {
            parent::__construct('poll', '');
        }

        public function setOp($op)
        {
            $this->command->setAttribute('op', $op);
        }
    }
