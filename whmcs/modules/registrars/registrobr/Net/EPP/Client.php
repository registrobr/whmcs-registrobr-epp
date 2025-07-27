<?php

    /*  EPP Client class for PHP, Copyright 2005 CentralNic Ltd
        This program is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 2 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    */

    /**
     * A simple client class for the Extensible Provisioning Protocol (EPP)
     * @package Net_EPP
     * @version 0.0.4
     * @author Gavin Brown <gavin.brown@nospam.centralnic.com>
     * @revision $Id: Client.php,v 1.13 2010/10/21 11:55:07 gavin Exp $
     */
    require_once('Protocol.php');

    $GLOBALS['Net_EPP_Client_Version'] = '0.0.6';

    /**
     * A simple client class for the Extensible Provisioning Protocol (EPP)
     * @package Net_EPP
     */
    class Net_EPP_Client
    {

        /**
         * @var resource the socket resource, once connected
         */
        public $socket;
        /**
         * @var bool do output more debug messages
         */
        public $debug;

        /**
         * @var integer timeout to wait on commands
         */
        public $timeout;

        /**
         * constructor set initialize various objects
         * @param boolean set debugging on
         */
        public function __construct($debug = false)
        {
            $this->debug = $debug;
            $GLOBALS['debug'] = $debug;
            $this->socket = null;
        }

        /**
         * Establishes a connect to the server
         * This method establishes the connection to the server. If the connection was
         * established, then this method will call getFrame() and return the EPP <greeting>
         * frame which is sent by the server upon connection. If connection fails, then
         * an exception with a message explaining the error will be thrown and handled
         * in the calling code.
         * @param string   $host    the hostname
         * @param integer  $port    the TCP port
         * @param integer  $timeout the timeout in seconds
         * @param boolean  $ssl     whether to connect using SSL
         * @param resource $context a stream resource to use when setting up the socket connection
         *@param string $protocol whether to use TLS or SSL
         * @throws Exception on connection errors
         * @return a         string containing the server <greeting>
         */
        public function connect($host, $port = 700, $timeout = 1, $ssl = true, $context = null, $protocol = 'tls')
        {
            if ($this->debug) {
                syslog(LOG_INFO, "in connect");
            }
            
            $target = sprintf('%s://%s:%d', ($ssl === true ? $protocol : 'tcp'), $host, $port);
            if ($this->debug) {
                syslog(LOG_INFO, "connecting to {$target}");
            }
            
            if (is_resource($context)) {
                if ($this->debug) {
                    syslog(LOG_INFO, "using your provided context resource");
                }
                $result = stream_socket_client($target, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
            } else {
                $result = stream_socket_client($target, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
            }
            if (is_resource($result)) {
                if ($errno == 0) {
                    if ($this->debug) {
                        $socketmeta = stream_get_meta_data($result);
                        if (isset($socketmeta['crypto'])) {
                            syslog(LOG_NOTICE, "socket opened with protocol ".$socketmeta['crypto']['protocol'].", cipher ".$socketmeta['crypto']['cipher_name'].", ".$socketmeta['crypto']['cipher_bits']." bits ".$socketmeta['crypto']['cipher_version']);
                        } else {
                            syslog(LOG_INFO, "socket opened without crypt");
                        }
                    }
                    // Set our socket
                    $this->socket = $result;
                } else {
                    throw new Exception("non errono 0 retrieved from socket connection: {$errno}");
                }
            } else {
                if ($result === false && $errno == 0) {
                    throw new Exception("Connection could not be opened due to socket problem.  Reasons can be unmatched peer name, tls not supported...");
                } else {
                    throw new Exception("Connection could not be opened: $errno $errstr");
                }
            }

            // Set stream timeout
            if (!stream_set_timeout($this->socket, $timeout)) {
                throw new Exception("Failed to set timeout on socket: $errstr (code $errno)");
            }
            $this->timeout = $timeout;
            $GLOBALS['timeout'] = $timeout;
            
            // Set blocking
            if (!stream_set_blocking($this->socket, 0)) {
                throw new Exception("Failed to set blocking on socket: $errstr (code $errno)");
            }
            if ($this->debug) {
                syslog(LOG_INFO, "trying to get frame from server");
            }
            return $this->getFrame();
        }

        /**
         * Get an EPP frame from the server.
         * This retrieves a frame from the server. Since the connection is blocking, this
         * method will wait until one becomes available. If the connection has been broken,
         * this method will return a string containing the XML from the server
         * @throws Exception on frame errors
         * @return a         string containing the frame
         */
        public function getFrame()
        {
            return Net_EPP_Protocol::getFrame($this->socket);
        }

        /**
         * Send an XML frame to the server.
         * This method sends an EPP frame to the server.
         * @param string the XML data to send
         * @throws Exception when it doesn't complete the write to the socket
         * @return boolean   the result of the fwrite() operation
         */
        public function sendFrame($xml)
        {
            return Net_EPP_Protocol::sendFrame($this->socket, $xml);
        }

        /**
         * a wrapper around sendFrame() and getFrame()
         * @param  string    $xml the frame to send to the server
         * @throws Exception when it doesn't complete the write to the socket
         * @return string    the frame returned by the server, or an error object
         */
        public function request($xml)
        {
            $res = $this->sendFrame($xml);
            return $this->getFrame();
        }

        /**
         * Close the connection.
         * This method closes the connection to the server. Note that the
         * EPP specification indicates that clients should send a <logout>
         * command before ending the session.
         * @return boolean the result of the fclose() operation
         */
        public function disconnect()
        {
            return @fclose($this->socket);
        }

        /**
         * ping the connection to check that it's up
         * @return boolean
         */
        public function ping()
        {
            return (!is_resource($this->socket) || feof($this->socket) ? false : true);
        }
    }
