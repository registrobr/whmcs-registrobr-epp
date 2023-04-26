<?php

class dnsserver {
    var $driver = false;

    function __construct($moduleoptions) {

        if ($moduleoptions['dnsserver'] == "powerdns") {
            $this->driver = new dnsserver_powerdns(
                $moduleoptions['dnsserver_hostname'],
                $moduleoptions['dnsserver_database'],
                $moduleoptions['dnsserver_username'],
                $moduleoptions['dnsserver_password']
            );
        }    
    }

    function createZone($zone) {
        return $this->driver->createZone($zone);
    }
}

class dnsserver_powerdns {
    var $db;
    
    function __construct($hostname, $database, $username, $password) {
        $this->db = mysqli_connect($hostname, $username, $password, $database);

        if (!$this->db) {
            logModuleCall("registrobr","powerdns_mysql_connect", "", $this->db->error);
        }     
    }
    
    function createZone($zone) {
        $zone_safe = $this->db->real_escape_string($zone);    
        $sql = 'insert into domains (name, type) values ("' . $zone_safe . '", "MASTER")';
        $this->db->query($sql);

        if (!$this->db->error) {
            $zone_id = $this->db->insert_id;        
            if ($zone_id) {
                $sql = 'insert into records (domain_id, name, type, content, ttl) values (1, "' . $zone_safe . '", "SOA", "' . $zone_safe . ' postmaster.' . $zone_safe . ' 2014090800 28800 7200 604800 86400", 14400)';
                $this->db->query($sql);
            }
        }
    }

}

?>
