<?php

#  Copyright (C) 2013 Registro.br. All rights reserved.
# 
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are
# met:
# 1. Redistribution of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 
# THIS SOFTWARE IS PROVIDED BY REGISTRO.BR ``AS IS AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
# WARRANTIE OF FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
# EVENT SHALL REGISTRO.BR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
# OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
# ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
# TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
# USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
# DAMAGE.

# $Id: Avail.php 67 2012-04-11 18:14:25Z mendelson $

# File where the cookie is stored 
define('COOKIE_FILE', '/tmp/isavail-cookie.txt');

# Default Server Address and port 
define('SERVER_ADDR', '200.160.2.3');
define('SERVER_PORT', 43);

define('MAX_UDP_SIZE', 512);
define('DEFAULT_COOKIE', '00000000000000000000');

# Maximum retries and interval 
define('MAX_RETRIES', 3);
define('RETRY_TIMEOUT', 5);

                                                            #
##                                                         ##
##  Class responsible for parsing a Domain Check response  ##
##                                                         ##
                                                            #
class AvailResponseParser {
    
    var $status = -1;
    var $query_id = '';
    var $fqdn = '';
    var $fqdn_ace = '';
    var $expiration_date = '';
    var $publication_status = '';
    var $nameservers = '';
    var $tickets = '';
    var $release_process_dates = array();
    var $msg = '';
    var $cookie = '';
    var $response = '';
    var $suggestions = array();

    function __toString() {
        return $this-> str();
    }

    function str() {
        $message = '';
        $message .= "Query ID: $this->query_id\n";
        $message .= "Domain name: $this->fqdn\n";
        $message .= "Response Status: $this->status (";
    
        if ($this->status == 0) {
            $message .= "Available)\n";
    
        } else if ($this->status == 1) {
            $message .= "Available with active tickets)\n";
            $message .= "Tickets: \n";
            $message .= "  " . $this->tickets . "\n";
                            
        } else if ($this->status == 2) {
            $message .= "Registered)\n";
            $message .= 'Expiration Date: ';
            if ($this->expiration_date == '0') {
                $message .= "Exempt from payment\n";
            } else {
                $message .= $this->expiration_date . "\n";
            }
    
            $message .= "Publication Status: " . $this->publication_status . "\n";
            $message .= "Nameservers: \n";
            $message .= $this->nameservers;
            
            if (sizeof($this->suggestions) > 0) {
                $message .= "Suggestions:";
                foreach ($this->suggestions as $suggestion) {
                    $message .= " " . $suggestion;
                }
                $message .= "\n";
            }

        } else if ($this->status == 3) {
            $message .= "Unavailable)\n";
            $message .= "Additional Message: " . $this->msg . "\n";

            if (sizeof($this->suggestions) > 0) {
                $message .= "Suggestions:";
                foreach ($this->suggestions as $suggestion) {
                    $message .= " " . $suggestion;
                }
                $message .= "\n";
            }
    
        } else if ($this->status == 4) {
            $message .= "Invalid query)\n";
            $message .= "Additional Message: " . $this->msg . "\n";
                
        } else if ($this->status == 5) {
            $message .= "Release process waiting)\n";
    
        } else if ($this->status == 6) {
            $message .= "Release process in progress)\n";
            $message .= "Release Process:\n";
            $message .= "  Start date: " . $this->release_process_dates[0] . "\n";
            $message .= "  End date:   " . $this->release_process_dates[1] . "\n"; 
    
        } else if ($this->status == 7) {
            $message .= "Release process in progress with active tickets)\n";
            $message .= "Release Process:\n";
            $message .= "  Start date: " . $this->release_process_dates[0] . "\n";
            $message .= "  End date:   " . $this->release_process_dates[1] . "\n";
            $message .= "Tickets: \n";
            $message .= $this->tickets;
    
        } else if ($this->status == 8) {
            $message .= "Error)\n";
            $message .= "Additional Message: " . $this->msg . "\n";
                
        } else if ($this->response != '') {
            $message = $this->response;
    
        } else {
            $message = 'No response';
        }
                
        return $message;         
    }

    # Parse a string response
    function parse_response($response) {
        $this->response = $response;
        $buffer = split("\n", $this->response);

        while (42) {
            if (count($buffer) == 0) { break; }
            $line = trim(array_shift($buffer));
    
            # Ignore blank lines at the beginning
            if (strlen($line) == 0) { continue; }
    
            # Ignore comments
            if (substr($line, 0, 1) == "%") { continue; }
    
            # Get the status of the response, or cookie
            if ((substr($line, 0, 3) == "CK ") || 
                (substr($line, 0, 3) == "ST ")) {
                $items = split(" ", $line);

                # New cookie
                if ($items[0] == "CK") {
                    $this->cookie = substr($items[1], 0, 20);
                    $this->query_id = $items[2];
                    return 0;
                }
    
                if (count('items') == 0) { return -1; }
    
                # Get the response status
                $this->status = $items[1];
                
                # Status 8: Error
                if ($this->status == 8) {
                    $this->msg = trim(array_shift($buffer));
                    return 0;
                }
                
                $this->query_id = $items[2];
            }
    
            # Get the fqdn and fqdn_ace
            $line = trim(array_shift($buffer));
            $words = split('\|', $line);
            $this->fqdn = $words[0];
            if (count($words) > 1) {
                $this->fqdn_ace = $words[1];
            } 
                
            # Domain available or waiting release process
            if (($this->status == 0) || ($this->status == 5)) { 
                return 0; 
            }
    
            # Read a new line from the buffer
            $line = trim(array_shift($buffer));
    
            # Domain available with ticket: Get the list of active tickets
            if ($this->status == 1) {
                $tickets = split('\|', $line);
                foreach ($tickets as $t) {
                    $this->tickets .= " $t\n";
                }
                return 0;
    
            # Domain already registered
            } else if ($this->status == 2) {
                $words = split('\|', $line);
                if (count($words) < 2) { return -1; }
    
                $this->expiration_date = $words[0];
                $this->publication_status = $words[1];
                for ($i = 2; $i < count($words); $i++) {
                    $this->nameservers .= "  " . $words[$i] . "\n";
                }

                # Check if there's any suggestion
                $line = trim(array_shift($buffer));
                if ($line == "") {
                    return 0;
                }

                $this->suggestions = split('\|', $line);
                for ($i = 0; $i < sizeof($this->suggestions); $i++) {
                    $this->suggestions[$i] = $this->suggestions[$i] . ".br";
                }

                return 0;
     
            # Domain unavailable or invalid or release process
            } else if ($this->status == 3 || $this->status == 4) {
                # Just get the message
                $this->msg = $line;

                if ($this->status == 3) {
                    # Check if there's any suggestion
                    $line = trim(array_shift($buffer));
                    if ($line == "") {
                        return 0;
                    }

                    $this->suggestions = split('\|', $line);
                    for ($i = 0; $i < sizeof($this->suggestions); $i++) {
                      $this->suggestions[$i] = $this->suggestions[$i] . ".br";
                    }
                }

                return 0;
     
            # Release process
            } else if ($this->status == 6 || $this->status == 7) {
                # Get the release process dates
                $this->release_process_dates = split('\|', $line);
                if (count($this->release_process_dates) < 2) {
                    return -1;
                }
    
                # Get the tickets (status 7)
                if ($this->status == 7) {
                    $line = trim(array_shift($buffer));
                    $tickets = split('\|', $line);
                    foreach ($tickets as $t) {
                        $this->tickets .= "  " . $t . "\n";
                    }
                }
                return 0;
            }
    
            # Error
            return -1;
        }
    }

}

                                                            
##                                                        ##
## Class responsible for sending a query thru the network ##
##                                                        ##
                                                            
class AvailClient {

    var $lang = 0;
    var $ip = '';
    var $cookie = DEFAULT_COOKIE;
    var $cookie_file = COOKIE_FILE;
    var $version = 1;
    var $server = SERVER_ADDR;
    var $port = SERVER_PORT;
    var $suggest = 1;

    function setParam($arg) {
        $this->lang        = $arg["lang"];
        $this->ip          = $arg["ip"];
        $this->cookie_file = $arg["cookie_file"];
        $this->server      = $arg["server"];
        $this->port        = $arg["port"];
        $this->suggest     = $arg["suggest"];

        if (!file_exists($this->cookie_file) || !is_readable($this->cookie_file)) {
            # Send a query with an invalid cookie
            $this->send_query('registro.br');
        } else {
            $COOKIE = fopen($this->cookie_file, "r");
            $this->cookie = fread($COOKIE, filesize($this->cookie_file));
            fclose($COOKIE);
        }
    }
    
    function send_query($fqdn) {
        $query = '';
        if ($this->ip != '') {
            $query .= "[" . $this->ip . "] ";
        }
    
        # Create a random 10 digit query ID (2^32)
        $query_id = rand(1000000000, 4294967296);
    
        # Form the query
        $query .= $this->version . " " . $this->cookie . " " .
                  $this->lang . " " . $query_id . " " . trim($fqdn);
    
        if ($this->version > 0) {
            $query .= " " . $this->suggest;
        }

        # Create a new socket
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!(@socket_connect($sock, $this->server, $this->port))) {
            print "\nConnection Failed!\n";
            exit(1);
        }

        # Send the query and wait for a response
        $timeout = 0;
        $retries = 0;
        $resend = true;
    
        # Response parser
        $parser = new AvailResponseParser();
        while (42) {
            # Check the need to (re)send the query
            if ($resend == true) {
                $resend = false;
                $retries++;
                if ($retries > MAX_RETRIES) {
                    break;
                }
               
                # Send the query
                socket_write($sock, $query, strlen($query));
            }
           
            # Set the timeout
            $timeout += RETRY_TIMEOUT;
            socket_set_option($sock, 
                          SOL_SOCKET,  // socket level
                          SO_RCVTIMEO, // timeout option
                          array(
                                "sec"  => $timeout, // Timeout in seconds
                                "usec" => 0  // I assume timeout in microseconds
                               ) );

            $response = @socket_read($sock, MAX_UDP_SIZE);
            if (empty($response)) {
                $resend = true;
                continue;
            }
    
            # Response received. Call the parser
            $parser->parse_response($response);
    
            # Check the query ID
            if (($parser->query_id != $query_id) &&
                ($parser->status != 8)) {
                # Wrong query ID. Just wait for another response
                $resend = false;
                continue;
            }            
            
            # Check if the cookie was invalid
            if ($parser->cookie != "") {
                # Save the new cookie
                $cookie = $this->cookie;
                $this->cookie = $parser->cookie;
    
                if ($COOKIE = fopen($this->cookie_file, "w")) {
                    fwrite($COOKIE, $this->cookie);
                    fclose($COOKIE);
                }
    
                if ($cookie == DEFAULT_COOKIE) {
                    # Nothing else to do
                    break;
                } else {
                    # Resend query. Now we should have the right cookie
                    $parser = $this->send_query($fqdn);
                    break;
                }
    
            }
            break;
        }        
        
        # Return the filled ResponseParser object
        return $parser;
    }
}

?>
