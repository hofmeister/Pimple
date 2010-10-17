<?php
require_once 'Html.php';

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

    /**
     *
     * @param phtml $string
     * @return PhtmlNode
     */
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
                case ':':
                    if ($this->isWithin(self::ATTR)) {break;}
                case ' ':
                case "\t":
                case "\n":
                case "\r":
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
            $result = preg_replace ('/[^A-Z0-9_\-]/i','', $result);
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
                    $current = preg_replace ('/[^A-Z0-9_\-:]/i','',$this->getCurrent());
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
class PhtmlNode extends HtmlElement {
    private static $closureCount = 0;
    private static $append = '';
    private $ns;
    private $container = false;
    
    public static function getNextClosure() {
        self::$closureCount++;
        return "closure".md5(self::$closureCount + microtime(true));
    }
    public function getNs() {
        return $this->ns;
    }

    
    public function isContainer() {
        return $this->container;
    }

    public function setContainer($container) {
        $this->container = $container;
    }


    public function setNs($ns) {
        $this->ns = $ns;
    }

    public function __toString() {
        if ($this->getTag() == 'phtml') {
            return $this->getInnerString();
        }
		return parent::__toString();
    }

    public function getInnerString() {
        $str = '';
        foreach($this->getChildren() as $child) {
            $str .= $child->__toString();
        }
        return $str;
    }
    public function getInnerPHP() {
        $str = '';
        foreach($this->getChildren() as $child) {
            $str .= $child->toPHP();
        }
        return $str;
    }
    public function toPHP($filename = null) {
        
        if ($this->getTag() == 'phtml') {
            $result =  $this->getInnerPHP();
            if ($filename)
                file_put_contents($filename,$result);
            return $result;
        }
            

        $str = "<";
        $method = false;
        
        $tagName = '';


        if ($this->getNs()) {
            $method = true;
            $str = '<?=$'.$this->getNs().'->callTag("'.$this->getTag().'",';
        } else {
            $str .= $this->getTag();
        }

        
        if (count($this->getAttrs()) > 0) {
            if ($method)
                $str .= 'array(';
            else
                $str .= ' ';
            foreach($this->getAttrs() as $name=>$val) {
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
                if ($taglibs[$this->getNs()] && $taglibs[$this->getNs()]->isPreprocess()) {
                    $tag = $this->getTag();
                    $str = $taglibs[$this->ns]->callTag($tag,$this->getAttrs(),$body);
                } else {
                    
                        
                    if (preg_match('/<\?/',$body) && preg_match('/\$[A-Z]+\-\>[A-Z]+\(/is',$body)) {
                        //Body contains tags...
                        $closureName = self::getNextClosure();
                        $str .= sprintf('new %s($view),$view);?>',$closureName);
                        self::$append .= chr(10).sprintf('<?
                                        //%1$s closure start
                                        class %2$s extends PhtmlClosure {
                                        public function closure() {
                                        $view = $this->view;
                                        $data = $this->view->data;
                                        if (is_array($this->view->data)) {
                                            extract($this->view->data);
                                        }
                                        $libs = $this->view->taglibs;
                                        extract($libs);
                                        ?>%3$s<?
                                        }}
                                        //%1$s closure end
                                        ?>'.chr(10),$this->getTag(),$closureName,$body);
                    } else {
                        $str .= sprintf('ob_get_clean(),$view);?>',$closureName);
                        $str = '<? ob_start();?>'."\n$body\n".$str;
                    }
                }
                
            } else
                $str .= sprintf("</%s>",$this->getTag());
        } else {
            if ($method) {
                if ($taglibs[$this->getNs()] && $taglibs[$this->getNs()]->isPreprocess()) {
                    $tag = $this->getTag();
                    $str = $taglibs[$this->getNs()]->$tag($this->getAttrs(),null,null);
                } else {
                    $str .= 'null,$view);?>';
                }
            } else {
                $str .= '/>';
            }
        }
        if ($this->getParent() == null || $this->getParent()->getTag() == 'phtml') {
            $str .= self::$append;
            self::$append = '';
        }

        $str = $this->processEvals($str);

        if ($filename) {
            file_put_contents($filename,$str);
        }
        return $str;
    }
    private function processEvals($phtml) {
        return preg_replace('/%\{([^\}]*)\}/i','<?=$1?>',$phtml);
    }
    private function processAttrValue($val) {
        $val = preg_replace('/%\{([^\}]*)\}/i','".$1."','"'.$val.'"');
        $val = preg_replace('/(""\.|\."")/i','',$val);
        //$val = trim($val,'".');

        return $val;
    }
}

class PhtmlNodeText extends HtmlText {
    public function toPHP() {
        return $this->__toString();
    }
}
class PhtmlClosure {
    protected $view;
    public function __construct($view) {
        $this->view = $view;
    }
    public function closure() {
        ?><?
    }

    public function __toString() {
        try {
            ob_start();
            $this->closure();
            return ob_get_clean();
        } catch(Exception $e) {
            return $e->__toString();
        }
    }
}
