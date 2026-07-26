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
    $pollTicketsEnabled = !isset($moduleparams['PollTicketsEnabled']) || $moduleparams['PollTicketsEnabled'] !== 'No';
    $pollTicketsEmailEnabled = !isset($moduleparams['PollTicketsEmailEnabled']) || $moduleparams['PollTicketsEmailEnabled'] !== 'No';

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

            // Injeção de Monitoramento Preventivo de Saldo (Poll)
            $strCode = (string)$code;
            $monitorBalance = $moduleparams['MonitorBalance'] ?? '';
            
            if (in_array($monitorBalance, ['on', 'Yes'], true) && in_array($strCode, ['300', '301', '302', '303'])) {
                // 300 = Low Balance | 301, 302, 303 = Depósitos/Ajustes que restauram o saldo
                $statusValue = ($strCode === '300') ? 'LOW' : 'OK';
                \WHMCS\Database\Capsule::table('tblconfiguration')->updateOrInsert(
                    ['setting' => 'RegistrobrBalanceStatus'],
                    ['value' => $statusValue]
                );
            }

            if ($pollTicketsEnabled) {
                $ok = _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll,$moduleparams,$pollTicketsEmailEnabled);
            }
            else {
                logModuleCall('registrobr', 'Poll ticket creation disabled', $moduleparams, "msgQ " . $msgid . " code " . $code . " objectId " . $objectId);
                $ok = true;
            }

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

        // WHMCS pode salvar checkbox como 'on' ou 'Yes'
        $monitorBalance = $moduleparams['MonitorBalance'] ?? '';
        $isMonitorActive = ($monitorBalance === 'on' || $monitorBalance === 'Yes');
        
        // Dados do Pix
        $pixCode = $moduleparams['PixCopiaCola'] ?? '';
        $qrExternal = $moduleparams['PixQRCodeExternal'] ?? '';
        $isQrExternalActive = ($qrExternal === 'on' || $qrExternal === 'Yes');
        
        $balanceState = 'OK';
        
        if ($isMonitorActive) {
            $dbState = \WHMCS\Database\Capsule::table('tblconfiguration')
                ->where('setting', 'RegistrobrBalanceStatus')
                ->value('value');
            if ($dbState) {
                $balanceState = $dbState;
            }
        }

        // Se NÃO precisar reprovisionar, retorna imediatamente incluindo nossos dados!
        if ($moduleparams["ReprovisionTLDs"] == "No") {
            return array(
                'success' => true,
                'monitor' => $isMonitorActive,
                'balanceState' => $balanceState,
                'pixCode' => $pixCode,
                'pixQrExternal' => $isQrExternalActive
            );
        }

        // --- ABAIXO É O CÓDIGO ORIGINAL DE REPROVISIONAMENTO DO MÓDULO ---
        $firstyearprice = $moduleparams['firstyearprice'];
        $renewalprice = $moduleparams['renewalprice'];
        
        $registerpricearray = array (1 => $firstyearprice, 2 => $firstyearprice + $renewalprice, 3 => $firstyearprice + 2 * $renewalprice, 4 => $firstyearprice + 3 * $renewalprice, 5 => $firstyearprice + 4 * $renewalprice, 6 => $firstyearprice + 5 * $renewalprice, 7 => $firstyearprice + 6 * $renewalprice, 8 => $firstyearprice + 7 * $renewalprice, 9 => $firstyearprice + 8 * $renewalprice, 10 => $firstyearprice + 9 * $renewalprice);
        
        // (RFCs de EPP) o tempo total de validade de um domínio não pode exceder 10 anos
        $renewpricearray = array (1 => $renewalprice, 2 => 2 * $renewalprice, 3 =>  3 * $renewalprice, 4 => 4 * $renewalprice, 5 => 5 * $renewalprice, 6 => 6 * $renewalprice, 7 => 7 * $renewalprice, 8 => 8 * $renewalprice, 9 => 9 * $renewalprice);        

        $success = true;
        
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
                $success = false;
                logModuleCall('registrobr', 'Create or Update TLD failure', $command . json_encode($postData), $results);
            }
        }        

        $moduleparams['ReprovisionTLDs'] = "No";

        $command = 'UpdateModuleConfiguration';
        $postData = array(
            'moduleType' => 'registrar',
            'moduleName' => 'registrobr',
            'parameters' => $moduleparams
        );
        $results = localAPI($command, $postData);
        if ($results['result'] != 'success') {
            $success = false;
        }        
        
        // Retorna o sucesso original E os nossos dados de saldo inseridos!
        return array(
            'success' => $success,
            'monitor' => $isMonitorActive,
            'balanceState' => $balanceState,
            'pixCode' => $pixCode,
            'pixQrExternal' => $isQrExternalActive
        );
    }


    public function generateOutput($data)
    {
        $html = '<div class="widget-content-padded" style="font-size: 14px;">';
        
        if ($data['success'] == true) {
            $html .= '<span style="color: #5cb85c; display: block; margin-bottom: 5px;"><i class="fas fa-check"></i> <strong>Sincronização de TLDs OK</strong></span>';
        } else {
            $html .= '<span style="color: #d9534f; display: block; margin-bottom: 5px;"><i class="fas fa-times"></i> <strong>Falha na Sincronização de TLDs</strong></span>';
        }
        
        if (isset($data['monitor']) && $data['monitor']) {
            $html .= '<hr style="margin: 10px 0; border-color: #eee;">';
            $state = (string)($data['balanceState'] ?? 'OK');
            $needsRefill = false;
            
            if (str_starts_with($state, 'EMPTY')) {
                $html .= '<span style="color: #d9534f; display: block;"><i class="fas fa-exclamation-triangle"></i> <strong>SALDO ESGOTADO</strong></span>';
                $html .= '<small style="color: #777; display: block; margin-top: 2px;">Falha em registros/renovações recentes.</small>';
                $needsRefill = true;
            } elseif (str_starts_with($state, 'LOW')) {
                $html .= '<span style="color: #f0ad4e; display: block;"><i class="fas fa-exclamation-circle"></i> <strong>SALDO BAIXO</strong></span>';
                $html .= '<small style="color: #777; display: block; margin-top: 2px;">O limite de alerta foi atingido.</small>';
                $needsRefill = true;
            } else {
                $html .= '<span style="color: #5cb85c; display: block;"><i class="fas fa-check-circle"></i> <strong>SALDO REGULAR</strong></span>';
                $html .= '<small style="color: #777; display: block; margin-top: 2px;">Operações transacionando normalmente.</small>';
            }

            // Lógica do PIX: Exibe se precisar de recarga E se o administrador configurou o código
            if ($needsRefill && !empty($data['pixCode'] ?? '')) {
                $pixString = htmlspecialchars($data['pixCode'] ?? '', ENT_QUOTES, 'UTF-8');
                
                if (isset($data['pixQrExternal']) && $data['pixQrExternal']) {
                    // OPÇÃO 1: Renderização COM a Imagem (Via API Externa)
                    $qrCodeUrl = 'https://quickchart.io/qr?size=130&margin=1&text=' . urlencode($data['pixCode']);
                    
                    $html .= '<div style="margin-top: 15px; background: #f9f9f9; border: 1px dashed #ccc; padding: 10px; border-radius: 4px; display: flex; align-items: center; gap: 15px;">';
                    $html .= '  <div>';
                    $html .= '      <img src="' . $qrCodeUrl . '" alt="QR Code Pix" style="width: 110px; height: 110px; border-radius: 4px; border: 1px solid #eee;">';
                    $html .= '  </div>';
                    $html .= '  <div style="flex: 1;">';
                    $html .= '      <strong style="font-size: 13px; color: #333; display: block; margin-bottom: 5px;"><i class="fab fa-pix" style="color: #32bcad;"></i> Recarga via Pix</strong>';
                    $html .= '      <span style="font-size: 11px; color: #666; display: block; margin-bottom: 8px;">Escaneie o código ao lado ou copie a chave abaixo:</span>';
                    $html .= '      <div style="display: flex;">';
                    $html .= '          <input type="text" id="rgbPixCode" value="' . $pixString . '" readonly style="flex: 1; font-size: 11px; padding: 5px 8px; border: 1px solid #ddd; border-right: 0; border-radius: 3px 0 0 3px; color: #555; background: #fff;">';
                    $html .= '          <button onclick="navigator.clipboard.writeText(document.getElementById(\'rgbPixCode\').value).then(()=> { this.innerHTML=\'Copiado!\'; setTimeout(()=> this.innerHTML=\'Copiar\', 2000); })" style="padding: 5px 12px; border: 1px solid #ddd; background: #eee; cursor: pointer; border-radius: 0 3px 3px 0; font-size: 11px; font-weight: bold; color: #333;">Copiar</button>';
                    $html .= '      </div>';
                    $html .= '  </div>';
                    $html .= '</div>';
                } else {
                    // OPÇÃO 2: Renderização APENAS Copiar e Colar (100% Local / Seguro)
                    $html .= '<div style="margin-top: 15px; background: #f9f9f9; border: 1px dashed #ccc; padding: 10px; border-radius: 4px;">';
                    $html .= '  <strong style="font-size: 13px; color: #333; display: block; margin-bottom: 5px;"><i class="fab fa-pix" style="color: #32bcad;"></i> Recarga via Pix</strong>';
                    $html .= '  <span style="font-size: 11px; color: #666; display: block; margin-bottom: 8px;">Copie a chave abaixo e use a opção "Pix Copia e Cola" do seu banco:</span>';
                    $html .= '  <div style="display: flex;">';
                    $html .= '      <input type="text" id="rgbPixCode" value="' . $pixString . '" readonly style="flex: 1; font-size: 11px; padding: 5px 8px; border: 1px solid #ddd; border-right: 0; border-radius: 3px 0 0 3px; color: #555; background: #fff;">';
                    $html .= '      <button onclick="navigator.clipboard.writeText(document.getElementById(\'rgbPixCode\').value).then(()=> { this.innerHTML=\'Copiado!\'; setTimeout(()=> this.innerHTML=\'Copiar\', 2000); })" style="padding: 5px 12px; border: 1px solid #ddd; background: #eee; cursor: pointer; border-radius: 0 3px 3px 0; font-size: 11px; font-weight: bold; color: #333;">Copiar</button>';
                    $html .= '  </div>';
                    $html .= '</div>';
                }
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
}



function _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll,$moduleparams = null,$pollTicketsEmailEnabled = true){

    if ($moduleparams === null) {
        $moduleparams = getregistrarconfigoptions('registrobr');
    }
    
    $automation = false;
    
    

    switch($code) {
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

        if (!$pollTicketsEmailEnabled) {
            $issue["noemail"] = true;
            $issue["noEmail"] = true;
            $issue["noAutoEmail"] = true;
        }
   
        $results = localAPI("OpenTicket",$issue);

        if ($results['result']!="success") {
                logModuleCall("registrobr","failed to open ticket",$issue,$results);
                return false;
            } else {
                return true;
            }
    }
        
}

