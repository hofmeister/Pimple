<?php
class RefBase {
    public $file;
    public $offset;
    public $limit;
    public $source;
    public $name;
    public $doc = array();
    
    public function isDeprecated() {
        return count($this->doc['deprecated']) > 0;
    }
    public function getDescription() {
        return $this->doc['description'];
    }
    
    public function getParms() {
        $out = array();
        if (!is_array($this->doc['param'])) return $out;
        foreach($this->doc['param'] as $parmStr) {
            preg_match('/([A-Z]+)\\s+([A-Z]+)\\s+(.*)/is', $parmStr,$matches);
            $parm = new RefParm();
            if (!$matches[1]) continue;
            $parm->name = $matches[2];
            $parm->type = $matches[1];
            $parm->description = trim($matches[3]," \t\n| ");
            $out[] = $parm;
        }
        return $out;
    }
}

class RefParm {
    public $name;
    public $type;
    public $description;
}