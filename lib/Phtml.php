<?php
class Phtml {
    const NOTHING = 'NOTHING';
    const STRING = 'STRING';
    const TAG = 'TAG';
    const TAGEND = 'TAGEND';
    const DOCTYPE = 'DOCTYPE';
    const ATTR = 'ATTR';

    private $withinStack = array();
    private $current = '';
    private $node;
    private $lastChar,$nextChar,$char,$attrName;
    private $debug = false;
    private $stringStartChar = '';

    public function read($string) {
        $this->withinStack = array(self::NOTHING);
        $this->current = '';
        $this->node = new PhtmlNode();
        $this->node->setContainer(true);
        $this->node->setTag('phtml');
        
        for($i = 0; $i < mb_strlen($string);$i++) {
            $chr = mb_substr($string,$i,1);
            $this->nextChar = mb_substr($string,$i+1,1);
            $this->char = $chr;
            switch($chr) {
                case '<':
                    if ($this->nextChar == '/') {
                        $this->pushWithin(self::TAGEND);
                        $this->getNode()->setContainer(true);
                        $this->onNodeEnd();
                    } elseif ($this->nextChar == '!') {
                        $this->pushWithin(self::DOCTYPE);
                    } else {
                        $this->onTagStart();
                    }
                        
                    break;
                case '>':
                    if ($this->isWithin(self::TAGEND)) {
                        $this->popWithin();
                        $this->clearCurrent();
                    } elseif ($this->isWithin(self::DOCTYPE)) {
                        $this->popWithin();
                        $this->onWordEnd();
                    } else {
                        $this->onWordEnd();
                        $this->onTagEnd();
                    }
                    break;
                case ' ':
                case "\t":
                case "\n":
                case "\r":
                case ':':
                case '/':
                case '=':
                    if ($this->isWithin(self::DOCTYPE)) break;
                    $this->onWordEnd();
                    break;
                case '"':
                case '\'':
                    if ($this->isWithin(self::DOCTYPE)) break;
                    if ($this->isWithin(self::STRING))
                        $this->onStringEnd();
                    else
                        $this->onStringStart();
                    break;
                default:
                    $this->onWordStart();
                    
                    break;
            }
            $this->debug("CHR:$this->char - ".$this->within());
            $this->addChar($this->char);
            $this->lastChar = $this->char;
        }
        $text = substr($this->getCurrent(),1);
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));

        $node = $this->getNode();
        $this->clear();
        return $node;

    }
    protected function clear() {
        $this->node = null;
        $this->withinStack = array();
        $this->current = '';
        $this->node;
        $this->lastChar = '';
        $this->nextChar = '';
        $this->char = '';
        $this->attrName = '';
    }
    protected function addChar($chr) {
        $this->current .= $chr;
    }
    protected function onStringStart() {
        if ($this->stringStartChar && $this->stringStartChar != $this->char) return;
        $this->stringStartChar = $this->char;
        $this->debug("STRING START");
        $this->pushWithin(self::STRING);
        $this->current = substr($this->current,1);
    }
    protected function onStringEnd() {
        if ($this->stringStartChar != $this->char || $this->lastChar == '\\') return;
        $this->stringStartChar = '';
        $this->debug("STRING END");
        $this->popWithin();
    }
    protected function getCurrent($alphanum = false) {
        $result = $this->current;
        if ($alphanum)
            $result = preg_replace ('/[^A-Z0-9_]/i','', $result);
        $this->current = '';
        return $result;
    }
    protected function clearCurrent() {
        $this->current = '';
    }
    protected function onWordStart() {
        if ($this->isWithin(self::STRING)) return;//ignore
        switch($this->within()) {
            case self::TAG:
                if ($this->getNode()->getTag()) {
                    $this->pushWithin(self::ATTR);
                }
                break;
        }
    }
    protected function hasCurrent($alphaNum = false) {
        return trim($this->current) != '';
    }

    protected function onWordEnd() {
        if ($this->isWithin(self::STRING) || !$this->hasCurrent()) return;//ignore
        switch($this->within()) {
            case self::TAG:
                $current = $this->getCurrent(true);
                if (!$current) return;
                if ($this->char == ':')
                    $this->getNode()->setNs($current);
                else {
                    $this->getNode()->setTag($current);
                    $this->debug("STARTING NODE: '".$this->getNode()->getTag()."'");
                }
                break;
            case self::ATTR:
                if (!$this->attrName) {
                    $current = $this->getCurrent(true);
                    if (!$current) return;
                    $this->attrName = $current;
                    $this->debug("ATTR FOUND: $this->attrName");
                } else {
                    $this->debug("ATTR VAL FOUND FOR: $this->attrName");
                    $this->getNode()->setAttribute($this->attrName,trim($this->getCurrent(),"\"'"));
                    $this->attrName = '';
                    $this->popWithin();
                }

                break;
        }
    }
    protected function pushWithin($within) {
        array_push($this->withinStack,$within);
    }
    protected function popWithin() {
        array_pop($this->withinStack);
    }
    protected function within() {
        return $this->withinStack[count($this->withinStack)-1];
    }
    protected function isWithin($within) {
        return $this->within() == $within;
    }

    protected function onTagStart() {
        if (!$this->isWithin(self::NOTHING)) return;
        
        $node = new PhtmlNode();
        $text = $this->getCurrent();
        if (!strstr($text,'!DOCTYPE'))
            $text = substr($text,1);
        $this->pushWithin(self::TAG);
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));
        $this->getNode()->addChild($node);
        //$this->getNode()->setTextBefore(substr($this->getCurrent(),1));
        $this->node = $node;

    }

    protected function onTagEnd() {
        if (!$this->isWithin(self::TAG)) return;
        $this->debug("ENDING TAG: ".$this->getNode()->getTag());
        
        if ($this->lastChar == '/')
            $this->onNodeEnd();

        $this->popWithin();
    }
    protected function onNodeEnd() {
        $parent = $this->getNode()->getParent();
        $text = substr($this->getCurrent(),1);
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));
        $this->debug("ENDING NODE: ".$this->getNode()->getTag());
        $this->node = $parent;
    }
    protected function getNode() {
        return $this->node;
    }
    protected function debug($msg) {
        if ($this->debug)
            echo "\n$msg";
    }
    public function setDebug($debug) {
        $this->debug = $debug;
    }
}
class PhtmlNode {
    private $ns;
    private  $tag;
    private $parent;
    private $attrs = array();
    private $children = array();
    private $container = false;

    public function getAttrs() {
        return $this->attrs;
    }

    public function setAttrs($attrs) {
        $this->attrs = $attrs;
    }

    public function isContainer() {
        return $this->container;
    }

    public function setContainer($container) {
        $this->container = $container;
    }

        public function getNs() {
        return $this->ns;
    }

    public function setNs($ns) {
        $this->ns = $ns;
    }

    public function getTag() {
        return $this->tag;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    public function addChild($node) {
        $this->children[] = $node;
        $node->setParent($this);
    }
    public function getChildren() {
        return $this->children;
    }

    public function setAttribute($name,$value) {
        $this->attrs[$name] = $value;
    }
    public function getAttribute($name) {
        return $this->attrs[$name];
    }

    public function getOuterString() {

    }
    public function __toString() {
        if ($this->tag == 'phtml') {
            return $this->getInnerString();
        }
        $str = "<";
        $tagName = '';
        if ($this->ns) {
            $tagName .= $this->ns.':';
        }
        $tagName .= $this->tag;
        $str .= $tagName;
        if (count($this->attrs) > 0) {
            $str .= ' ';
            foreach($this->attrs as $name=>$val) {
                $str .= sprintf('%s="%s" ',$name,$val);
            }
            $str = trim($str);
        }
        if ($this->isContainer()) {
            $str .= '>';
            foreach($this->children as &$child) {
                $str .= $child->__toString();
            }
            $str .= "</$tagName>";
        } else {
            $str .= '/>';
        }
        return $str;
    }

    public function getInnerString() {
        $str = '';
        foreach($this->children as &$child) {
            $str .= $child->__toString();
        }
        return $str;
    }
    public function getInnerPHP() {
        $str = '';
        foreach($this->children as &$child) {
            $str .= $child->toPHP();
        }
        return $str;
    }
    public function toPHP($filename = null) {
        
        if ($this->tag == 'phtml') {
            $result =  $this->getInnerPHP();
            if ($filename)
                file_put_contents($filename,$result);
            return $result;
        }
            

        $str = "<";
        $method = false;
        
        $tagName = '';


        if ($this->ns) {
            $method = true;
            
            $str = '<?=$'.$this->ns.'->'.$this->tag.'(';
        } else {
            $str .= $this->tag;
        }

        
        if (count($this->attrs) > 0) {
            if ($method)
                $str .= 'array(';
            else
                $str .= ' ';
            foreach($this->attrs as $name=>$val) {
                if ($method)
                    $str .= sprintf('"%s"=>%s,',$name,$this->processAttrValue($val));
                else
                    $str .= sprintf('%s="%s" ',$name,$val);
            }
            if ($method)
                $str = trim($str,',').'),';
            else
                $str = trim($str);
        } else if ($method) {
            $str .= 'array(),';
        }
        if ($this->isContainer()) {
            if (!$method)
                $str .= '>';
            else
                $body = '';
            
            if ($method)
                $body .= $this->getInnerPHP();
            else
                $str .= $this->getInnerPHP();
            
            if ($method) {
                $taglibs = Pimple::instance()->getTagLibs();
                if ($taglibs[$this->ns] && $taglibs[$this->ns]->isPreprocess()) {
                    $tag = $this->tag;
                    $str = $taglibs[$this->ns]->$tag($this->attrs,$body,null);
                } else {
                    $str .= 'ob_get_clean(),$this);?>';
                    $str = sprintf("\n<?ob_start();//%s start\n?>",$this->tag).chr(10).$body.chr(10).$str;
                }
                
            } else
                $str .= "</$this->tag>";
        } else {
            if ($method) {
                if ($taglibs[$this->ns] && $taglibs[$this->ns]->isPreprocess()) {
                    $tag = $this->tag;
                    $str = $taglibs[$this->ns]->$tag($this->attrs,null,null);
                } else {
                    $str .= 'null,$this);?>';
                }
            } else {
                $str .= '/>';
            }
        }
        if ($filename)
            file_put_contents($filename, $str);
        return $str;
    }
    private function processAttrValue($val) {
        $val = preg_replace('/%\{([^\}]*)\}/i','".$1."','"'.$val.'"');
        $val = preg_replace('/(""\.|\."")/i','',$val);
        //$val = trim($val,'".');

        return $val;
    }
}

class PhtmlNodeText {
    private $parent;
    private $text;
    function __construct($text) {
        $this->text = $text;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }
    public function __toString() {
        return $this->text;
    }
    public function toPHP() {
        return $this->__toString();
    }
}