<?php
class RefBase {
    public $reader;
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
    public function getDocValue($name) {
        if (!is_array($this->doc[$name])) return array();
        return $this->doc[$name];
    }
    public function hasDocValue($name) {
        return is_array($this->doc[$name]) && count($this->doc[$name]) > 0;
    }
    public function getParms() {
        $out = array();

        $uses = $this->getDocValue('uses');

        foreach($uses as $use) {
            list($useClass,$useMethod) = explode('::',$use);

            $m = $this->reader->getMethod($useClass,$useMethod);
            //echo "<br/>USE $use: $m->name";
            Arrayutil::merge($out, $m->getParms());
        }

        foreach($this->getDocValue('param') as $parmStr) {
            preg_match('/([A-Z\\|,\\[\\]]+)\\s+([A-Z]+)\\s+(.*)/is', $parmStr,$matches);
            $parm = new RefParm();
            if (!$matches[1]) continue;
            $parm->name = $matches[2];
            $parm->type = implode(" | ",preg_split("/[\\|,]/",$matches[1]));
            $parm->description = trim($matches[3]," \t\n| ");
            $out[$parm->name] = $parm;
        }

        return $out;
    }
}

class RefParm {
    public $name;
    public $type;
    public $description;
}