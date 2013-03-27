<?php


# ${copyright}$
# $Id: brdomaincheck.php 70 2013-01-13 21:44:00Z rubens $


require "Avail.php";


function check_domain_availability($fqdn, $parameters) { $client = new AvailClient(); $client->setParam($parameters); $response = $client->send_query($fqdn); return $response; }


$atrib = array(
"lang" => 1, # PT (EN = 0)
"server" => "registro.br",
"port" => 43,
"cookie_file" => "/tmp/isavail-cookie.txt", "ip" => "", "suggest" => 0, # No domain suggestions );


$fqdn = $_GET["domain"];;
$domain_info = check_domain_availability($fqdn, $atrib);


echo "Status do dom&iacute;nio '{$fqdn}': <br /><br />"; echo nl2br($domain_info);


?>
