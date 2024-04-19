<?php

if (!defined('WHMCS'))
    die('You cannot access this file directly.');

use WHMCS\Database\Capsule;

/**
 * Based on WHMCS SDK Sample Registrar Module Hooks File
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2016
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

add_hook('AdminHomeWidgets', 1, function() {
    return new registrobrModuleWidget();
});

add_hook('AfterCronJob', 1, function($vars) {
         
      

    require_once 'registrobr.php';
    require_once ROOTDIR . '/includes/functions.php';
    require_once ROOTDIR . '/includes/registrarfunctions.php';
    
        
    require_once 'RegistroEPP/RegistroEPPFactory.class.php' ;

    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');

    $objRegistroEPPPoll = RegistroEPPFactory::build('RegistroEPPPoll');

    try {
        $objRegistroEPPPoll->login($moduleparams);

    }
    catch (Exception $e){
         logModuleCall('registrobr', 'Poll processing login failure', $moduleparams, $e->getMessage());
    }
    $i = 0;

    do {

        try {
            $objRegistroEPPPoll->getMessages($moduleparams);

        }
        catch (Exception $e){
         logModuleCall('registrobr', 'Poll processing login failure', $moduleparams, $e->getMessage());
        }
        $coderes = $objRegistroEPPPoll->get('coderes');

        
        $last = 0;

        # This is the last one
        if ($coderes == 1300) {
            $last = 1;
        }
        else {
            

            $msgid = $objRegistroEPPPoll->get('msgQ');
            $reason = $objRegistroEPPPoll->get('reason');
            $code = $objRegistroEPPPoll->get('code');
            $content = $objRegistroEPPPoll->get('content');
            $objectId = $objRegistroEPPPoll->get('objectId');
            
            logModuleCall('registrobr', 'Poll debug', $moduleparams, "msgQ ".$msgid." reason ".$reason." code ".$code." content ".$content." objectId ".$objectId);
        
            $ok = _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll);

            if($ok){
                $objRegistroEPPPoll->sendAck();
            }
        }

        $i++;

    } while($last != 1 and $i < 100); //prevent inbox flooding

}

         );
         


add_hook('ClientAreaFooterOutput', 1, function ($domain) {
    if (strpos($domain['currentpagelinkback'], 'cart.php?a=confdomains') !== false) {
        // Formats the additionalfields (CPF ou CNPJ) in the client area page "/cart.php?a=confdomains".
        echo <<<HTML
        <script type="text/javascript">
            window.addEventListener("DOMContentLoaded", (event) => {
                const docInput = document.getElementById('cpf-cnpj-rgbr-formatter').parentElement.firstChild

                if (docInput) {
                    docInput.maxLength = 18
                    docInput.minLength = 14

                    docInput.addEventListener('input', e => {
                        // Source: https://gist.github.com/marceloneppel/dd9c17a01c1a8031c760b034dad0efd9
                        const rawValue = e.target.value.replace(/\D/g, '')

                        if (rawValue.length >= 11) {
                            if (rawValue.length === 11) {
                                e.target.value = rawValue.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "\$1.\$2.\$3-\$4")

                                return
                            }

                            e.target.value = rawValue.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, "\$1.\$2.\$3/\$4-\$5")

                            return
                        } else {
                            e.target.value = rawValue
                        }
                    })
                }
            });
        </script>
HTML;
    }
});

/**
 * Code based on WHMCS Sample Registrar Module Admin Dashboard Widget.
 *
 */
class registrobrModuleWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Registro.br';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData()
    {
	$include_path = ROOTDIR . '/modules/registrars/registrobr';
        set_include_path($include_path . PATH_SEPARATOR . get_include_path());

        require_once('TLDs.php');

	require_once ROOTDIR . '/includes/registrarfunctions.php';
	$moduleparams = getRegistrarConfigOptions('registrobr');

	if ($moduleparams["ReprovisionTLDs"] == "No") {
		return (array ('success' => true)); } ;
	
        $firstyearprice = $moduleparams['firstyearprice'];


        $renewalprice = $moduleparams['renewalprice'];
        
        $registerpricearray = array (1 => $firstyearprice, 2 => $firstyearprice + $renewalprice, 3 => $firstyearprice + 2 * $renewalprice, 4 => $firstyearprice + 3 * $renewalprice, 5 => $firstyearprice + 4 * $renewalprice, 6 => $firstyearprice + 5 * $renewalprice, 7 => $firstyearprice + 6 * $renewalprice, 8 => $firstyearprice + 7 * $renewalprice, 9 => $firstyearprice + 8 * $renewalprice, 10 => $firstyearprice + 9 * $renewalprice);
        
        $renewpricearray = array (1 => $renewalprice, 2 => 2 * $renewalprice, 3 =>  3 * $renewalprice, 4 => 4 * $renewalprice, 5 => 5 * $renewalprice, 6 => 6 * $renewalprice, 7 => 7 * $renewalprice, 8 => 8 * $renewalprice, 9 => 9 * $renewalprice);
        

        $success=true ;

	
        
        foreach ($registrobr_AllTLDs as &$registrobr_TLD) {
            $command = 'CreateOrUpdateTLD';
            $postData = array(
                'extension' => $registrobr_TLD,
                'id_protection' => false,
                'dns_management' => false,
                'email_forwarding' => false,
                'epp_required' => false,
                'auto_registrar' => 'registrobr',
                'currency_code' => 'BRL',
                'grace_period_days' => '104',
                'grace_period_fee' => '-1',
                'redemption_period_fee' => '0.00',
                'register' => $registerpricearray,
                'renew' => $renewpricearray,
                'transfer' => array(1 => '-1.00'),
            );
            
            $results = localAPI($command, $postData);
            if ($results['result'] != 'success') {
                $success=false ;
            }
	}        

	$moduleparams['ReprovisionTLDs'] = "No";

	$command = 'UpdateModuleConfiguration';
	$postData = array(
    'moduleType' => 'registrar',
    'moduleName' => 'registrobr',
    'parameters' => $moduleparams);
	$results = localAPI($command, $postData);
	if ($results['result'] != 'success') {
                $success=false ;
	}        
	return array('success' => $success);
    }

    public function generateOutput($data)
    {
	$message = ($data['success'] == true) ?  'ok' : 'failed' ;

        return <<<EOF
<div class="widget-content-padded">
    Registro.br TLDs $message
</div>
EOF;
    }
}



function _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll){

    $moduleparams = getregistrarconfigoptions('registrobr');
    
    $automation = false;
    
    

    switch($code) {
        case '1': case '22': case '28': case '29':
            $ticket = $objRegistroEPPPoll->get('ticket');
            #no break, poll messages with ticketNumber also have domain in objectId
        case '1':
            $automation = true ; // DOMAIN_CREATE_PAN handled by code
            #no break, domain_create_pan also has domain in objectId
        case '2': case '3': case '4': case '5': case '6': case '7': case '8': case '9': case '10': case '11': case '12': case '13': case '14': case '15': case '16': case '17': case '18': case '20': case '107': case '108': case '304': case '305':
            $domain = $objRegistroEPPPoll->get('objectId');
            break;
        case '100': case '101': case '102': case '103': case '106':
            $taxpayerID = $objRegistroEPPPoll->get('objectId');
            break;
    }
    $taxpayerID=preg_replace("/[^0-9]/","",$taxpayerID);

    if (in_array($code,array('300','302','303','305'))==TRUE) {
        $issue["priority"] = "High";
        $issue["deptid"] = $moduleparams["FinanceDept"];
    }
    elseif (in_array($code,array('301','304'))==TRUE) {
        $issue["priority"] = "Low";
        $issue["deptid"] = $moduleparams["FinanceDept"];
    }
    elseif (!$automation) {
        $issue["priority"] = "Low" ;
        $issue["deptid"] = $moduleparams["TechDept"];
    }

    if (!empty($domain)) {
        $issue["domain"] =$domain;

        if (empty($ticket)) {
            $data = Capsule::table('mod_registrobr')
                ->where(clID,"=",$moduleparams['Username'])
                ->where(domain,"=",$domain)
                ->get();
            
            
            # if there is only one domain with this name, we can match it to a domainid without a ticket
            if (count($data)==1) {
                $domainid = $data['domainid'];
            }
        }
        else {
            $domainid = Capsule::table('mod_registrobr')
                ->where(clID,"=",$moduleparams['Username'])
                ->where(ticket,"=",$ticket)
                ->value('domainid');
            
        }

        // Refactor opportunity: changing this code to use Domain Model instead of tbldomains
        if (!empty($domainid)) {
            $issue["domainid"] = $domainid;
            $data = Capsule::table('tbldomains')
                ->where(id,"=",$domainid)
                ->get();

            $issue["clientid"]=$data['userid'];
        }
    }
    
    if ($automation) {
        switch($code) {
                case '1': // DOMAIN_CREATE_PAN
                try {
                    Capsule::table('mod_registrobr')
                    ->where('domainid', $domainid)
                    ->update(
                             [
                             'registered' => true,
                             ]
                             );
                } catch (Exception $e) {
                    logModuleCall('registrobr', 'Failed to update mod_registrobr in poll processing',  "Capsule table where domainid ". $domainid . " update registered true " ,$e->getMessage());
                }
                break;
          }
               
                
        
    } else {
        
        if (!isset($issue["clientid"])) {
            $issue["email"]='noreply@registro.br';
            $issue["name"]='Registro.br EPP';
        }
        
        $issue["subject"] = "Mensagem de Poll relativa a dominios .br";
        $issue["message"] = $content;
        $issue["admin"] = true;
   
        $results = localAPI("OpenTicket",$issue);

        if ($results['result']!="success") {
                logModuleCall("registrobr","failed to open ticket",$issue,$results);
                return false;
            } else {
                return true;
            }
    }
        
}

