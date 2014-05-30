<?php
abstract class RegistroEPP {
    
	protected $id;
	protected $domain;
	protected $ticket;
	protected $username;
	protected $password;
	protected $language;
	protected $netClient;
	protected $coderes;
	protected $nameservers;
	protected $clID;
	protected $contacts;
	protected $organization;
	protected $contact; // must be change
	protected $cpf;
	protected $cnpj;
	
	// Força a classe que estende ClasseAbstrata a definir esse método
    abstract protected function getInfo();
    //abstract protected function create();
    
    
    public function validateCPF($cpf = null){
    	require_once ('isCpfValid.php');
    	
    	# Returned CNPJ has extra zero at left
    	if(isCpfValid($cpf)!=TRUE) {
    		$cpf=substr($cpf,1);
    	}
    	
    	$cpfDigits = preg_replace("/[^0-9]/","",$cpf);
    	$cpfformatdo = $cpf; //formatar cpf corretamente
    	
    	return array($cpfformatado,$cpfDigits);
    }
    public function validateCNPJ($cnpj = null){
    	require_once ('isCnpjValid.php');
    
    
    }
    
    public function get($key){
    	if (property_exists($this, $key)) {
    		return $this->$key;
    	}
    	else {
    		return false;
    	}
    }
    public function set($key,$value){
    	
    	if($key == 'domain'){
    		$value = $this->_convert_to_puny_code($value);
    	}
    	
    	if (property_exists($this, $key)) {
    		$this->$key = $value;
    		return $this;
    	}
    	else {
    		return false;
    	}
    }

    protected function _convert_to_puny_code($string){
    	
    	require_once('Idna/idna_convert.class.php');
    	
    	$IDN = new idna_convert(array('idn_version' => '2008'));
    	
    	$encoded = $IDN->encode($string);
    	
    	return $encoded;
    	
    }
    public function language($string){
    	
    	$this->set('language',$string);
    	
    }
    public function errorEPP($StringError,$ObjParser,$XmlRequest,$XmlResponse){
    	
    	//$msg = _registrobr_set_encode($msg);
    	$coderes = $ObjParser->get('coderes');
    	$msg = $ObjParser->get('msg');
    	$reason = $ObjParser->get('reason');
		$language = $this->get('language');
		
    	$errormsg = $this->_registrobr_lang($StringError)." ".$coderes.": ".$this->_registrobr_lang('msg').$msg."'";

    	if (!empty($reason)) {
    		#$reason = _registrobr_set_encode($reason);
    		$errormsg.= $this->_registrobr_lang("reason").$reason."'";
    	}
    	
    	logModuleCall("registrobr",$errormsg,$XmlRequest,$XmlResponse);
    	return $errormsg;	 
    }
    
    public function error($StringError,$param1,$errormsg){
    	 
		$translate = $this->_registrobr_lang($StringError);
		logModuleCall("registrobr",$translate,$param1,$errormsg);
		return $errormsg;
    }
    public function login($moduleparams){
    	
    	require_once('RegistroEPP/RegistroEPPFactory.class.php');
    	require_once('ParserResponse/ParserResponse.class.php');
    	require_once('Net/EPP/Client.php');
    	require_once('Net/EPP/Protocol.php');
    	require_once('PEAR.php');
    	
    	$username = $this->set('username',$moduleparams['Username']);
    	$password = $this->set('password',$moduleparams['Password']);
    	$language = $this->set('language',$moduleparams['Language']);
    	    	
    	# Grab module parameters
    	
    	if (!isset($moduleparams['TestMode']) && empty($moduleparams['Certificate'])) {
    			$errormsg = $this->_registrobr_lang("specifypath");
    			$msg = $this->error('configerr',$moduleparams,$errormsg);
    			throw new Exception($msg);
    	}
    	
    	if (!isset($moduleparams['TestMode']) && !file_exists($moduleparams['Certificate'])) {
    			$errormsg = $this->_registrobr_lang("invalidpath");
    			$msg = $this->error('configerr',$moduleparams,$errormsg);
    			throw new Exception($msg);
    	}
    	
    	if (!isset($moduleparams['TestMode']) && empty($moduleparams['Passphrase'])) {
    			$errormsg = $this->_registrobr_lang("specifypassphrase")  ;
    			$msg = $this->error('configerr',$moduleparams,$errormsg);
    			throw new Exception($msg);
    			 
    	}
    	
    	# Use OT&E if test mode is set
    	
    	$local_cert = dirname(__FILE__) . '/../test-client.pem';
    	
    	
    	if (!isset($moduleparams['TestMode'])) {
	    	$Server = 'epp.registro.br' ;
	    	$Options = array (
	    	'ssl' => array (
	    	'passphrase' => $moduleparams['Passphrase'],
	    	'local_cert' => $moduleparams['Certificate']));
    	} 
    	else {
	    	$Server = 'beta.registro.br' ;
	    	$Options = array (
	    	'ssl' => array (
	    			'local_cert' => $local_cert  ));
    	}
    	
    	# Create SSL context
    	$context = stream_context_create ($Options) ;
    	
    	# Create EPP client
    	$client = new Net_EPP_Client();
    	
    	# Connect
    	$Port = 700;
    	$use_ssl = true;
    	$res = $client->connect($Server, $Port, 3 , $use_ssl, $context);

    	# Check for error
    	if (PEAR::isError($res)) {
    		$param1 = "tls://".$Server.":".$Port;
    		$errormsg = $res->getMessage();
    		$msg = $this->error('eppconnect',$param1,$errormsg);
    		throw new Exception($msg);
    	}
    	$requestXML = $this->loginXML();
     	$responseXML = $client->request($requestXML);
     	$objParser = New ParserResponse();
     	$objParser->parse($responseXML);
     	
     	# Check results
     	$coderes = $objParser->get('coderes');
     	$this->set('netClient',$client); //set NET client to others requests
     	
     	
    	if($coderes != '1000') {
    		$msg = $this->errorEPP('epplogin',$objParser,$requestXML,$responseXML);
    		throw new Exception($msg);
    	}
    }
    
    private function loginXML(){
    
    
    	$username = $this->get('username');
    	$password = $this->get('password');
    	$language = $this->get('language');
    
    	if($language == "Portuguese"){
    		$lang = "pt";
    	}
    	else {
    		$lang = "en";
    	}
    
    	if(empty($username) || empty($password)){
    		throw new Exception('Username or password not set');
    	}
    
    	$request='
    	<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
    	<command>
    	<login>
    	<clID>'.$username.'</clID>
    	<pw>'.$password.'</pw>
    	<options>
    	<version>1.0</version>
    	<lang>'.$lang.'</lang>
    	</options>
    	<svcs>
    	<objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
    	<objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
    	<svcExtension>
    	<extURI>urn:ietf:params:xml:ns:brdomain-1.0</extURI>
    	<extURI>urn:ietf:params:xml:ns:brorg-1.0</extURI>
    	<extURI>urn:ietf:params:xml:ns:secDNS-1.0</extURI>
    	<extURI>urn:ietf:params:xml:ns:secDNS-1.1</extURI>
    	</svcExtension>
    	</svcs>
    	</login>
    	<clTRID>'.mt_rand().mt_rand().'</clTRID>
    	</command>
    	</epp>
    	';
    
    	return $request;
    }
    public function getMsgLang($string){
    	
    	return $this->_registrobr_lang($string);
    	
    }
	protected function _registrobr_lang($msgid) {
		
		
	
	    # Grab module parameters
	    $moduleparams = getregistrarconfigoptions('registrobr');
	    $msgs = array (
			"epplogin" => array ("Erro no login EPP código ","EPP login error code "),
			"msg" => array (" mensagem '"," message '"),
			"reason" => array (" motivo '"," reason '"),
			"eppconnect" => array ("Erro de conexão EPP","EPP connect error"),
			"configerr" => array ("Erro nas opções de configuração","Config options errorr"),
			"specifypath" => array ("Favor informar o caminho para o arquivo de certificado","Please specifity path to certificate file"),
			"invalidpath" => array ("Caminho para o arquivo de certificado inválido", "Invalid certificate file path"),
			"specifypassphrase" => array ("Favor especificar a frase secreta do certificado", "Please specifity certificate passphrase"),
			"deleteerrorcode" => array ("Erro na remoção de domíenio código ","Domain delete: error code "),
			"deleteconnerror" => array ("Falha na conexão EPP ao tentar remover domínio erro ","Domain delete: EPP connection error "),
			"getnsconnerror" => array ("Falha na conexão EPP ao tentar obter servidores DNS erro ", "get nameservers: EPP connection error "),
			"setnsconnerror" => array ("Falha na conexão EPP ao tentar alterar servidores DNS erro ", "set nameservers: EPP connection error "),
			"setnsgeterrorcode" => array ("Falha ao tentar obter servidores DNS atuais para alterar servidores DNS código ", "set nameservers: error getting nameservers code "),
			"setnsupdateerrorcode" => array ("Falha ao alterar servidores DNS código ","set nameservers: update servers error code "),
			"cpfcnpjrequired" => array ("Registro de domínios .br requer CPF ou CNPJ","register domain: .br registrations require valid CPF or CNPJ"),
			"companynamerequired" => array ("Registros com CNPJ requerem nome da empresa preenchido",".br registrations with CNPJ require Company Name to be filled in"),
			"registerconnerror" => array ("Falha na conexão EPP ao tentar registrar domínio erro ", "register domain: EPP connection error "),
			"notallowed" => array ("Entidade só pode registrar domínios por provedor atualmente designado.", "entity can only register domains through designated registrar."),
			"registergetorgerrorcode" => array ("Falha ao obter status de entidade para registrar domínio erro ","register domain: get org status error code "),
			"registercreateorgcontacterrorcode" => array ("Falha ao criar contato para entidade erro ","register domain: create org contact error code "),
			"registercreateorgerrorcode" => array ("Falha ao criar entidade para registrar domínio erro ","register domain: create org error code "),
			"registererrorcode" => array ("Falha ao registrar domínio erro ","register domain error code "),
			"renewconnerror" => array ("Falha na conexão EPP ao renovar domínio erro ", "renew domain: EPP connection error "),
			"renewinfoerrorcode" => array ("Falha ao obter informações de domínio ao renovar domínio erro ", "renew: domain info error code "),
			"renewerrorcode" => array ("Falha ao renovar domínio erro ","domain renew: error code "),
			"getcontactconnerror" => array ("Falha na conexão EPP ao obter dados de contato erro ","get contact details: EPP connection error "), 
			"getcontacterrorcode" => array ("Falha ao obter dados de contato erro ", "get contact details: domain info error code "),
			"getcontactnotallowed" => array ("Somente provedor designado pode obter dados deste domínio.","get contact details: domain is not designated to this registrar."),
			"getcontactorginfoerrorcode" => array ("Falha ao obter informações de entidade detentora de domínio erro ","get contact details: organization info error code "),
			"getcontacttypeerrorcode" => array ("Falha ao obter dados de contato do tipo ","get contact details: "),
			"getcontacterrorcode" => array ("código de erro ","contact info error code "),
			"savecontactconnerror" => array ("Falha na conexão EPP ao gravar contatos erro ", "save contact details: EPP connection error "),
			"savecontactdomaininfoerrorcode" => array ("Falha ao obter dados de domínio para gravar contatos erro ","set contact details: domain info error code"),
			"savecontactnotalloweed" => array ("Somente provedor designado pode alterar dados deste domínio.", "Set contact details: domain is not designated to this registrar."),
			"savecontacttypeerrorcode" => array ("Falha ao criar novo contato do tipo ","save contact details: "),
			"savecontacterrorcode" => array ("código de erro ","contact create error code "),
			"savecontactdomainupdateerrorcode" => array ("Falha ao atualizar domínio ao modificar contatos erro ","set contact: domain update error code "),
			"savecontactorginfoeerrorcode" => array ("Falha de obtenção de informações de entidade ao modificar contatos erro ","set contact: org info error code "),
			"savecontactorgupdateerrorcode" => array ("Falha ao atualizar entidade ao modificar contatos erro ","set contact: org update error code "),
			"testerror" => array("Testando ...","Testing..."),
	    	"domainnotfound" => array ("Domínio ainda não registrado.","Domain not yet registered"),
			"getnserrorcode" => array ("Falha ao obter dados de domínio erro ","get nameserver error code "),
			"syncconnerror" => array ("Falha na conexão EPP ao sincronizar domínio erro ","domain sync: EPP connection error "),
			"syncerrorcode" => array ("Falha ao tentar obter informação de domínio código ", "domain sync: error getting domain info code "),
			"syncdomainnotfound" => array ("não mais registrado."," no longer registered"),
			"syncdomainunknownstatus" => array(" apresentou status desconhecido: ","domain sync: unknown status code "),
			"Domain" => array ("Domínio ","Domain "),
			"domain" => array ("domínio ","domain "),
			"syncreport" => array("Relatorio de Sincronismo de Dominios Registro.br\n","Registro.br Domain Sync Report\n"),
			"syncreportdashes" => array ("------------------------------------------------\n","------------------------------\n"),
			"ERROR" => array ("ERRO: ","ERROR: "),
			"domainstatusok" => array ("Ativo","Active"),
			"domainstatusserverhold" => array ("CONGELADO","PENDING"),
			"domainstatusexpired" => array ("Vencido","Expired"),
	    	"domainpending" => array("Modificação de dados de contato de domínios que ainda não completaram o ciclo de registro não é permitida em domínios .br", "'Modify Contact Details' of domains that not finalized the complete registrars' process are not allowed"),	 
			"is" => array (" está "," is "),
			"registration" => array ("(Criação: ","(Registered: "),
			"epppollerror" => array ("Erro de ao fazer EPP Poll","EPP Polling error"),
			"Pollmsg" => array ("Mensagem de Poll relativa a dominios .br","Poll message about .br domains"),
			"pollackerrorcode" => array ("Falha ao dar recebimento de mensagem EPP Poll codigo ", "EPP Poll: error acknowledging a message error code "),
			"Date" => array ("Data ","Date "),
			"time" => array ("hora ","time "),
			"Code" => array ("Codigo ", "code "),
			"Text" => array ("Texto ","Text "),
			"FullXMLBelow" => array ("Mensagem XML completo abaixo:\n","Full XML message below:\n"),
	
			"companynamefield" => array ("Razao Social","Company Name"),
			"fullnamefield" => array ("Nome e Sobrenome","Full Name"),
			"streetnamefield" => array ("Logradouro","Street Name"),
			"streetnumberfield" => array ("Numero", "Street Number"),
			"addresscomplementsfield" => array ("Complemento", "Address Complements"),
			"citynamefield" => array ("Cidade","City"),
			"stateprovincefield" => array ("Estado","State or Province"),
			"zipcodefield" => array ("CEP","Zip code"),
			"countrycodefield" => array ("Pais","Country"),
			"phonenumberfield" => array ("Fone","Phone"),
    		"email" => array ("Email","Email")
	    		 
	    		 
			);                   
	
	    $language = $this->get('language');
	    
	    $langmsg = ($language=="Portuguese" ? $msgs["$msgid"][0] : $msgs["$msgid"][1] );
	    
	    #$langmsg = _registrobr_set_encode($langmsg);
	    return $langmsg;
	}
	
	public function normaliza($string) {
	
		$string = str_replace('&nbsp;',' ',$string);
		$string = trim($string);
		$string = html_entity_decode($string,ENT_QUOTES,'UTF-8');
	
		//Instead of The Normalizer class ... requires (PHP 5 >= 5.3.0, PECL intl >= 1.0.0)
		$normalized_chars = array( 'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', ' ' => '');
	
		$string = strtr($string,$normalized_chars);
		$string = strtolower($string);
		return $string;
	}
	
	public function StateProvince($sp) {
	
		if (strlen($sp)==2) return $sp;
		$estado = $this->normaliza($sp);
		$map = array(
				"acre" => "AC",
				"alagoas" => "AL",
				"amazonas" => "AM",
				"amapa" => "AP",
				"bahia" => "BA",
				"baia" => "BA",
				"ceara" => "CE",
				"distritofederal" => "DF",
				"espiritosanto" => "ES",
				"espiritusanto" => "ES",
				"goias" => "GO",
				"goia" => "GO",
				"maranhao" => "MA",
				"matogrosso" => "MT",
				"matogroso" => "MT",
				"matogrossodosul" => "MS",
				"matogrossosul" => "MS",
				"matogrossodesul" => "MS",
				"minasgerais" => "MG",
				"minasgeral" => "MG",
				"para" => "PA",
				"paraiba" => "PB",
				"parana" => "PR",
				"pernambuco" => "PE",
				"pernanbuco" => "PE",
				"piaui" => "PI",
				"riodejaneiro" => "RJ",
				"rio" => "RJ",
				"riograndedonorte" => "RN",
				"riograndenorte" => "RN",
				"rondonia" => "RO",
				"riograndedosul" => "RS",
				"riograndedesul" => "RS",
				"riograndesul" => "RS",
				"roraima" => "RR",
				"santacatarina" => "SC",
				"sergipe" => "SE",
				"saopaulo" => "SP",
				"tocantins" => "TO"
		);
		if(!empty($map[$estado])){
			return $map[$estado];
		}
		else {
			return $sp;
		}
	}
	
	
	private function _registrobr_identify_env_encode() {
		#Encoding default UTF-8
	
	
		if(!empty($CONFIG['Charset'])){
	
			return $CONFIG['Charset'];
		}
		else {
			$table = "tblconfiguration";
			$fields = "Charset";
			$where = array();
			$result = select_query($table,$fields,$where);
			$data = mysql_fetch_array($result);
		
			if($data['Charset']) {
				return $data['Charset'];
			}
			else {
				return 'UTF-8';
			}
		}
	}

}

?>
