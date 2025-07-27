<?php

    /**
     * @package Net_EPP
     */
    final class Net_EPP_Frame_Response extends Net_EPP_Frame
    {
        public function __construct()
        {
            parent::__construct('response');
        }

        public function code()
        {
            return $this->getElementsByTagName('result')->item(0)->getAttribute('code');
        }

        public function message()
        {
            return $this->getElementsByTagName('msg')->item(0)->firstChild->textContent;
        }
    }
