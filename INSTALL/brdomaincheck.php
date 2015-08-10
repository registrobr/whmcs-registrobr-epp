<?php


# ${copyright}$
# $Id: brdomaincheck.php 70 2013-01-30 19:00:00Z rubens $

require "Avail.php";

function check_domain_availability($fqdn, $parameters) {
    $client = new AvailClient();
    $client->setParam($parameters);
    $response = $client->send_query($fqdn);
    return $response;
}

# tmp file name to store cookie_file
$tmpfname = sys_get_temp_dir() . "/whmcsavail_" . md5(mt_rand(0,20));

$attrib = array(
    "lang" => 1, # PT (EN = 0)
    "port" => 43,
    "cookie_file" => $tmpfname,
    "ip" => "",
    "suggest" => 0, 
    "server" => "registro.br"
);

# replace registro.br with beta.registro.br for beta testing     
$fqdn = $_GET["domain"];;
$domain_info = check_domain_availability($fqdn, $attrib);

# remove tmp cookie_file
if (file_exists($tmpfname)) {
    unlink($tmpfname);
}

echo "Status do dom&iacute;nio '{$fqdn}': <br /><br />"; 
echo nl2br($domain_info);


?>
