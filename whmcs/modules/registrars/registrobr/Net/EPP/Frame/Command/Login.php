<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_Frame_Command_Login extends Net_EPP_Frame_Command
    {
        public function __construct()
        {
            parent::__construct('login');

            $this->clID = $this->createElement('clID');
            $this->command->appendChild($this->clID);

            $this->pw = $this->createElement('pw');
            $this->command->appendChild($this->pw);

            $this->options = $this->createElement('options');
            $this->command->appendChild($this->options);

            $this->eppVersion = $this->createElement('version');
            $this->options->appendChild($this->eppVersion);

            $this->eppLang = $this->createElement('lang');
            $this->options->appendChild($this->eppLang);

            $this->svcs = $this->createElement('svcs');
            $this->command->appendChild($this->svcs);
        }

        public function addExtension($exts)
        {
            $extensions = $this->createElement('svcExtension');
            foreach ($exts as $ext) {
                $ext_el = $this->createObjectPropertyElement('extURI');
                $ext_el->appendChild($this->createTextNode(Net_EPP_ObjectSpec::xmlns($ext)));
                $extensions->appendChild($ext_el);
            }
            $this->svcs->appendChild($extensions);
        }
    }
