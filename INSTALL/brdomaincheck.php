<?php

define("REGISTROBR_SERVER_PRODUCAO", "registro.br");
define("REGISTROBR_SERVER_TESTE", "beta.registro.br");


# ${copyright}$
# $Id: brdomaincheck.php 70 2013-01-30 19:00:00Z rubens $

require "Avail.php";

function check_domain_availability($fqdn, $parameters) {
    $client = new AvailClient();
    $client->setParam($parameters);
    $response = $client->send_query($fqdn);
    return $response;
}

function get_server() {
    include "configuration.php";

    try {
        $link = mysqli_connect($db_host, $db_username, $db_password, $db_name);
        $query = "SELECT * FROM tblregistrars where registrar = 'registrobr' and setting = 'TestMode'";
        $result = $link->query($query); 

        # se existe testmode entao vamos pro servidor de teste
        if (mysqli_num_rows($result) > 0) {
            return REGISTROBR_SERVER_TESTE;
        }
    } catch (Exception $e) {
        die("error connection database");
    }

    return REGISTROBR_SERVER_PRODUCAO;
}

# tmp file name to store cookie_file
$tmpfname = sys_get_temp_dir() . "/whmcsavail_" . md5(mt_rand(0,20));

$attrib = array(
    "lang" => 1, # PT (EN = 0)
    "port" => 43,
    "cookie_file" => $tmpfname,
    "ip" => "",
    "suggest" => 0, 
    "server" => get_server()
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
