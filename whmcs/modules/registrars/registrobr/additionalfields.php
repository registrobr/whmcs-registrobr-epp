<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$include_path = ROOTDIR . '/modules/registrars/registrobr';
set_include_path($include_path . PATH_SEPARATOR . get_include_path());

require_once('TLDs.php');

foreach ($registrobr_GenericTLDs as &$registrobr_TLD) {
    $additionaldomainfields[$registrobr_TLD][] = array (
                                             "Name" => "CPF ou CNPJ",
                                             "Type" => "text",
                                             "Size" => "18",
                                             "Description" => "<input type='hidden' id='cpf-cnpj-rgbr-formatter'><br /> Formato do CPF (11 d&iacutegitos) : NNN.NNN.NNN-NN <br /> Formato do CNPJ (14 d&iacutegitos) : NN.NNN.NNN/NNNN-NN",
                                             "Default" => "",
                                             "Required" => true);
    }

foreach ($registrobr_CPFTLDs as &$registrobr_TLD) {
    $additionaldomainfields[$registrobr_TLD][] = array(
     "Name" => "CPF",
     "Type" => "text",
     "Size" => "14",
     "Description" => "<input type='hidden' id='cpf-cnpj-rgbr-formatter'><br /> Formato do CPF (11 d&iacutegitos) : NNN.NNN.NNN-NN",
     "Default" => "",
     "Required" => true );
    }

foreach ($registrobr_CNPJTLDs as &$registrobr_TLD) {
    $additionaldomainfields[$registrobr_TLD][] = array(
                                                       "Name" => "CNPJ",
                                                       "Type" => "text",
                                                       "Size" => "18",
                                                       "Description" => "<input type='hidden' id='cpf-cnpj-rgbr-formatter'><br />  Formato do CNPJ (14 d&iacutegitos) : NN.NNN.NNN/NNNN-NN",
                                                       "Default" => "",
                                                       "Required" => true);
                                        }

foreach ($registrobr_AllTLDs as &$registrobr_TLD) {
    $additionaldomainfields[$registrobr_TLD][] = array(
                                                       "Name" => "Register Number",
                                                       "Remove" => true,
                                                       );
    }

