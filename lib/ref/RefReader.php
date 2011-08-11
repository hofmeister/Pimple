<?php
include_once 'RefClass.php';
include_once 'RefController.php';
include_once 'RefTagLib.php';

class RefReader  {
    
    private $classes = array();
    private $RGX_ARG,$RGX_ARGS,$RGX_METHOD,$RGX_DOC,$RGX_DOCPARM,$RGX_CLASS;
    
    function __construct() {
        $this->RGX_ARG = '(?:\\$[A-Z][A-Z0-9_]*(?: *= *.*?)?)';
        $this->RGX_ARG_MATCH = '/(?:(\\$[A-Z][A-Z0-9_]*)(?: *= *([^,]+))?)/is';
        $this->RGX_ARGS = "(?:{$this->RGX_ARG}(?:\\s*,\\s*{$this->RGX_ARG})*)";
        $RGX_DOC_FRAGMENT = "\\/\\*\\* *(?:\n +\\*[^\n]*)+\\/";
        $this->RGX_DOC = "/{$RGX_DOC_FRAGMENT}/i";
        $this->RGX_DOCPARM = "/\\@([A-Z][A-Z0-9_]+)(\\s+[^\\@]*)?/is";
        
        $this->RGX_METHOD = "/({$RGX_DOC_FRAGMENT}[\n\\s]*\n\\s*)?(abstract\\s+)?(public|protected|private)(\\s+static)?\\s+function\\s+([A-Z][A-Z0-9_]+)\\s*\\(({$this->RGX_ARGS})?\\)/is";
        $this->RGX_CLASS = "/({$RGX_DOC_FRAGMENT}[\n\\s]*\n\\s*)?(abstract\\s+)?class\\s+([A-Z][A-Z0-9_]+)(?:\\s+extends\\s+([A-Z][A-Z0-9_]+))?(?:\\s+implements\\s+([A-Z][A-Z0-9_]+(?:[\n\\s]*,[\n\\s]*[A-Z][A-Z0-9_]+)*))?/is";
    }

    public function clear() {
        $this->classes = array();
    }
    public function getClasses() {
        return $this->classes;
    }
    /**
     *
     * @param type $path
     * @return type 
     */
    public function read($path,$classTypes = 'RefClass',$methodTypes = 'RefMethod') {
        if ($path[0] == '.') 
            return;
        //echo "<br>Path:$path";
        if (is_dir($path)) {
            $dh = opendir($path);
            while($file = readdir($dh)) {
                if ($file[0] != '.')
                    $this->read($path.$file,$classTypes,$methodTypes);
            }
            closedir($dh);
        } else if (is_file($path)) {
            $content = file_get_contents($path);
            $classes = $this->readClasses($path,$content,$classTypes,$methodTypes);
        }
        return $this->classes;
    }
    protected function readDoc($doc) {
        $doc = str_replace('/**','',$doc);
        $doc = str_replace('*/','',$doc);
        preg_match_all($this->RGX_DOCPARM,$doc,$matches,PREG_OFFSET_CAPTURE);
        $firstOffset = -1;
        $out = array();
        foreach($matches[0] as $i=>$match) {
            if ($firstOffset < 0)
                $firstOffset = intval($match[1]);
            $name = strtolower($matches[1][$i][0]);
            $val = trim($matches[2][$i][0],"\n \t*");
            if (!$out[$name])
                $out[$name] = array();
            $out[$name][] = empty($val) ? true : $val;
        }
        if ($firstOffset < 0)
            $firstOffset = strlen($doc);
        $text = substr($doc,0,$firstOffset);
        $lines = ArrayUtil::trimValues(preg_split("/\n/",$text),"\n \t*");
        
        $out['description'] = trim(implode("\n",$lines));
        return $out;
        
    }
    protected function readMethods(RefClass $class,$methodType) {
        preg_match_all($this->RGX_METHOD,$class->source,$matches,PREG_OFFSET_CAPTURE);
        $lastMethod = null;
        foreach($matches[0] as $i=>$match) {
            $method = new $methodType();
            $method->file = $class->file;
            $method->offset = $class->offset+intval($match[1]);
            $method->doc = $this->readDoc($matches[1][$i][0]);
            $method->isAbstract = $matches[2][$i][1] != -1;
            $method->access = Util::first($matches[3][$i][1],'public');
            $method->isStatic = $matches[4][$i][1] != -1;
            $method->name = trim($matches[5][$i][0]);
            $parmStr = trim($matches[6][$i][0]);
            
            if ($lastMethod != null) {
                $lastMethod->limit = $method->offset-1;
            }
            
            if ($parmStr)
                $method->parms = $this->readParms($parmStr);
            $class->methods[] = $method;
            $lastMethod = $method;
        }
        if ($lastMethod != null) {
            $lastMethod->limit = $class->limit-1;
        }
    }
    protected function readParms($parmStr) {
        preg_match_all($this->RGX_ARG_MATCH,$parmStr,$matches);
        $out = array();
        foreach($matches[1] as $i=>$match) {
            $parm = new RefMethodParm();
            $parm->name = $match;
            $parm->default = $matches[2][$i];
            $out[] = $parm;
        }
        return $out;
    }
    protected function readClasses($file,$contents,$classTypes,$methodTypes) {
        //echo "RGX:$this->RGX_CLASS<br/>";
        preg_match_all($this->RGX_CLASS,$contents, $matches,PREG_OFFSET_CAPTURE);
        $lastClass = null;
        $classes = array();
        foreach($matches[0] as $i=>$match) {
            $class = new $classTypes();
            $class->file = $file;
            $class->offset = intval($match[1]);
            $class->isAbstract= $matches[2][$i][1] != -1;
            $class->name = $matches[3][$i][0];
            $class->extends = $matches[4][$i][0];
            $class->interfaces = !empty($matches[5][$i][0]) ? ArrayUtil::trimValues(explode(',',$matches[5][$i][0])) : array();
            
            if ($lastClass != null) {
                $lastClass->limit = $class->offset-1;
            }
            
            $doc = $matches[1][$i][0];
            $class->doc = $this->readDoc($doc);
            $classes[] = $class;
            $lastClass = $class;
        }
        if ($lastClass != null) {
            $lastClass->limit = strlen(trim($contents));
        }
        foreach($classes as $class) {
            $class->source = substr($contents,$class->offset,$class->limit-$class->offset);
            $this->readMethods($class,$methodTypes);
            foreach($class->methods as $m) {
                $m->source = substr($contents,$m->offset,$m->limit-$m->offset);
            }
                    
        }
        
        ArrayUtil::append($this->classes,$classes);
    }
    protected function readControllers($contents) {
        
    }
    protected function readTagLibs($contents) {
        
    }
}