<?php


# ${copyright}$
# $Id: brdomaincheck.php 70 2013-01-30 19:00:00Z rubens $


require "Avail.php";


function check_domain_availability($fqdn, $parameters) { $client = new AvailClient(); $client->setParam($parameters); $response = $client->send_query($fqdn); return $response; }


$attrib = array(
"lang" => 1, # PT (EN = 0)
"port" => 43,
"cookie_file" => "/tmp/isavail-cookie.txt", "ip" => "", "suggest" => 0, # No domain suggestions
);
          
$moduleparams = getregistrarconfigoptions('registrobr');

$attrib["server"] = (isset($moduleparams['TestMode'] ? "beta.registro.br" : "registro.br" );


$fqdn = $_GET["domain"];;
$domain_info = check_domain_availability($fqdn, $attrib);


echo "Status do dom&iacute;nio '{$fqdn}': <br /><br />"; echo nl2br($domain_info);


?>
