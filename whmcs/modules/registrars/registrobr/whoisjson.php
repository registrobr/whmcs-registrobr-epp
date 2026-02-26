<?php

require_once ("TLDs.php");




$registrobr_OtherTLDs= array (".emp.br",".nom.br",".br",".am.br",".coop.br",".fm.br",".g12.br",".gov.br",".mil.br",".org.br",".psi.br",".b.br",".def.br",".jus.br",".leg.br",".mp.br",".tc.br");

$registrobr_brTLDs = array_merge ($registrobr_AllTLDs, $registrobr_OtherTLDs);

echo <<<HEADER
[
    {
        "extensions":
HEADER;
        
foreach ($registrobr_brTLDs as $tld) {
    echo " \"";
    echo $tld;
    echo "\",";
    }

echo <<<FOOTER

        "uri": "https://registro.br/v2/ajax/avail/raw/",
        "available": "\"status\":0"
    }
]
        
FOOTER;
        

