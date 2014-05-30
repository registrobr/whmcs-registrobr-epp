<?php
class RegistroEPPFactory {
    
    
    public static function build($type) {
        
        $filename  = "$type.class.php";
        $classname = "$type";
        
        if (include_once($filename)) {
            return new $classname;
        } else {
            throw new Exception ('Classe não encontrada');
        }
    }
}


?>

