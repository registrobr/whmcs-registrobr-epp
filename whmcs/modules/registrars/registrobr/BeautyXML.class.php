<?php

//==============================================================================
// This class is simple XML beautifer 
// it's very, very, very simple - feature version will be better :-)
// 
//
// IMPORTANT NOTE
// there is no warranty, implied or otherwise with this software.
// 
// version 0.1 | August 2004
//
// released under a LGPL licence.
//
// Slawomir Jasinski, 
// http://www.jasinski.us (polish only - my home page) 
// http://www.cgi.csd.pl (english & polish)
// contact me - sj@gex.pl
//==============================================================================

class BeautyXML {
    
    var $how_to_ident = "    "; // you can user also \t or more/less spaces
    var $wrap = false; // wrap long tekst ? 
    var $wrap_cont = 90; // where wrap words 
    
    var $colors = array('green', 'red', 'pink', 'yellow', 'gray');

    // gives ident to string
    function ident(&$str, $level) {
        $level--;
        for ($a = 0; $a < $level; $a++) 
            $spaces .= $this->how_to_ident;
        return $spaces .= $str;
    }
    

    // main funcion
    function format($str) {
        
        $str = preg_replace("/<\?[^>]+>/", "", $str);
        
        $tmp = explode("\n", $str); // extracting string into array
         
        // cleaning string from spaces and other stuff like \n \r \t
        for ($a = 0, $c = count($tmp); $a < $c; $a++) 
            $tmp[$a] = trim($tmp[$a]);
        
        // joining to string ;-)
        $newstr = join("", $tmp);

        $newstr = preg_replace("/>([\s]+)<\//", "></", $newstr);
        
        // adding \n lines where tags ar near 
        $newstr = str_replace("><", ">\n<", $newstr);
        
        // exploding - each line is one XML tag
        $tmp = explode("\n", $newstr);
        
        // preparing array for list of tags
        $stab = array('');
        
        // lets go :-)
        for ($a = 0, $c = count($tmp); $a <= $c; $a++) {
             
            $add = true;
             
            preg_match("/<([^\/\s>]+)/", $tmp[$a], $match);
            
            $lan = trim(strtr($match[0], "<>", "  "));
            
            $level = count($stab);
             
            if (in_array($lan, $stab) && substr_count($tmp[$a], "</$lan") == 1) {
                $level--;
                $s = array_pop($stab);
                $add = false;
            }
             
            if (substr_count($tmp[$a], "<$lan") == 1 && substr_count($tmp[$a], "</$lan") == 1) 
                $add = false;
                
            if (preg_match("/\/>$/", $tmp[$a], $match))
                $add = false;
                
            $tmp[$a] = $this->ident($tmp[$a], $level);
            
            if ($this->wrap) $tmp[$a] = wordwrap($tmp[$a], $this->wrap_cont, "\n" . $this->how_to_ident . $this->how_to_ident . $this->how_to_ident);
             
            if ($add && !@in_array($lan, $stab) && $lan != '') array_push($stab, $lan);
                 
        }
         
        return join("\n", $tmp);
    }
     
}