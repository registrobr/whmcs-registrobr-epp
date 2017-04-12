<?
# Copyright (c) 2013, NIC.br (R)
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.


# Official Website for whmcs-registrobr-epp
# https://github.com/registrobr/whmcs-registrobr-epp


# More information on NIC.br(R) domain registration services, Registro.br(TM), can be found at http://registro.br
# Information for registrars available at http://registro.br/provedor/epp

# NIC.br(R) is a not-for-profit organization dedicated to domain registrations and fostering of the Internet in Brazil. No WHMCS services of any kind are available from NIC.br(R).


#
#define('ROOTDIR','/var/www/whmcs');
#actual directory defined by install.sh script

# Include Registro.br stuff we need
define('ROOTDIR','WHMCSINSTALLDIRSCRIPTREPLACE');
require_once ROOTDIR . '/modules/registrar/registrobr/registrobr.php';
require_once ROOTDIR . '/dbconnect.php';
require_once ROOTDIR . '/includes/functions.php';
require_once ROOTDIR . '/includes/registrarfunctions.php';



# echo "Registro.br Poll" ;

registrobr_Poll();

#echo "Fim do Poll";



function registrobr_Poll(){

    $include_path = ROOTDIR . '/modules/registrar/registrobr';
    set_include_path($include_path . PATH_SEPARATOR . get_include_path());
    
    require_once('RegistroEPP/RegistroEPPFactory.class.php');

    # Grab module parameters
    $moduleparams = getregistrarconfigoptions('registrobr');

    $objRegistroEPPPoll = RegistroEPPFactory::build('RegistroEPPPoll');

    try {
        $objRegistroEPPPoll->login($moduleparams);

    }
    catch (Exception $e){
        echo $e->getMessage();
    }
    $i = 0;

    do {

        try {
            $objRegistroEPPPoll->getMessages($moduleparams);

        }
        catch (Exception $e){
            echo $e->getMessage();
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

            $ok = _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll);

            if($ok){
                $objRegistroEPPPoll->sendAck();
            }
        }

        $i++;

    } while($last != 1 and $i < 100); //prevent inbox flooding 

}

function _registrobr_whmcsTickets($code,$msgid,$reason,$content,$objRegistroEPPPoll){

    $moduleparams = getregistrarconfigoptions('registrobr');

    switch($code) {
        case '1': case '22': case '28': case '29':
            $ticket = $objRegistroEPPPoll->get('ticket');
            #no break, poll messages with ticketNumber also have domain in objectId
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
    else {
        $issue["priority"] = "Low" ;
        $issue["deptid"] = $moduleparams["TechDept"];
    }

    $issue["clientid"]=0;

    if (!empty($domain)) {
        $issue["domain"] =$domain;

        if (empty($ticket)) {
            $queryresult = mysql_query("SELECT domainid FROM mod_registrobr WHERE clID='".$moduleparams['Username']." domain='".$domain."'");
            $data = mysql_fetch_array($queryresult);

            # if there is only one domain with this name, we can match it to a domainid without a ticket
            if (count($data)==1) {
                $domainid = $data['domainid'];
            }
        }
        else {
            $queryresult = mysql_query("SELECT domainid FROM mod_registrobr WHERE clID='".$moduleparams['Username']." ticket='".$ticket."'");
            $data = mysql_fetch_array($queryresult);
            $domainid = $data['domainid'];
        }

        if (!empty($domainid)) {
            $issue["domainid"] = $domainid;
            $queryresult = mysql_query("SELECT userid FROM tbldomains WHERE id='".$domainid."'");
            $data = mysql_fetch_array($queryresult);
            $issue["clientid"]=$data['userid'];
        }
    }
    
    if (!empty($taxpayerID)&&($issue["clientid"]==0)) {
        $issue["clientid"] = "1";
    }

    $issue["subject"] = "Mensagem de Poll relativa a dominios .br";
    $issue["message"] = $content;
    $user = $moduleparams['Sender'];
    $queryresult = mysql_query("SELECT firstname,lastname,email FROM tbladmins WHERE username = '".$user."'");
    $data = mysql_fetch_array($queryresult);


    $issue["name"] = $data["firstname"]." ".$data["lastname"];
    $issue["email"] = $data["email"];

    $results = localAPI("openticket",$issue,$user);

    if ($results['result']!="success") {
        $msg = $objRegistroEPPPoll->error('epppollerror',$user,$results);
        return false;
    }
    else {
        return true;
    }

}

?>
