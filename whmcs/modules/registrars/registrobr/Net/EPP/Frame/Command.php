<?php

    /**
     * @package Net_EPP
     */
    class Net_EPP_Frame_Command extends Net_EPP_Frame
    {
        public function __construct($command, $type = "")
        {
            $this->type = $type;
            $command = strtolower($command);
            if (!in_array($command, array('check', 'info', 'create', 'update', 'delete', 'renew', 'transfer', 'poll', 'login', 'logout'))) {
                trigger_error("Invalid argument value '$command' for \$command", E_USER_ERROR);
            }
            parent::__construct('command');

            $this->command = $this->createElement($command);
            $this->body->appendChild($this->command);

            if (!empty($this->type)) {
                $this->payload = $this->createElementNS(
                    Net_EPP_ObjectSpec::xmlns($this->type),
                    $this->type.':'.$command
                );

                $this->command->appendChild($this->payload);
            }
        }

        public function addclTRID($id)
        {
            $this->clTRID = $this->createElement('clTRID');
            $this->clTRID->appendChild($this->createTextNode($id));
            $this->body->appendChild($this->clTRID);
        }

        public function addObjectProperty($name, $value = null)
        {
            $element = $this->createObjectPropertyElement($name);
            $this->payload->appendChild($element);

            if ($value instanceof DomNode) {
                $element->appendChild($value);
            } elseif (isset($value)) {
                $element->appendChild($this->createTextNode($value));
            }
            return $element;
        }

        public function createObjectPropertyElement($name, $type = null)
        {
            $ns_type = isset($type) ? $type : $this->type;
            $ns = !empty($ns_type) ? $ns_type.':'.$name : $name;
            return $this->createElementNS(
                Net_EPP_ObjectSpec::xmlns($ns_type),
                $ns
            );
        }

        public function createExtension()
        {
            $this->extension = $this->createElement('extension');
            $this->body->appendChild($this->extension);
        }

        /**
         * Creates an extension element with the option of specifying a custom namespace
         * @param $ext
         * @param $command
         * @param null $version *
         */
        public function createExtensionElement($ext, $command, $version = null)
        {
            $this->extension->payload = $this->createElementNS(
                Net_EPP_ObjectSpec::xmlns($version !== null ? $version : $ext),
                $ext.':'.$command
            );
            $this->extension->appendChild($this->extension->payload);
        }
    }
