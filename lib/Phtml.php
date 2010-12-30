<?php
require_once 'Html.php';

class Phtml {
    const NOTHING = 'NOTHING';
    const STRING = 'STRING';
    const TAG = 'TAG';
    const TAGEND = 'TAGEND';
    const DOCTYPE = 'DOCTYPE';
    const ATTR = 'ATTR';
    const SCRIPT = 'SCRIPT';
    const PHP = 'PHP';
    const P_EVAL = 'P_EVAL';
    const COMMENT = 'COMMENT';

    private static $IGNORELIST = array(self::PHP,self::COMMENT,self::STRING,self::P_EVAL,self::DOCTYPE);
    private static $IGNOREALLLIST = array(self::PHP,self::COMMENT,self::STRING,self::P_EVAL,self::DOCTYPE);
    private static $SCRIPTAGS = array('script','style','inline');

    private $withinStack = array();
    private $current = '';
    private $currentIgnore = '';
    private $node;
    private $lastChar,$nextChar,$char,$attrName;
    private $debug = false;
    private $stringStartChar = '';
    private $charCount = 0;
    private $lineCount = 0;
    private $phtmlRaw = '';
    private $debugTrace = '';
    private $ignoreNextChar = false;
    private $ignoreTags = false;
    private $ignoreChars = false;

    /**
     *
     * @param phtml $string
     * @return PhtmlNode
     */
    public function read($string) {
        $string = String::normalize($string,false);
        $this->withinStack = array(self::NOTHING);
        $this->current = '';
        $this->debugTrace = '';
        $this->node = new PhtmlNode();
        $this->node->setContainer(true);
        $this->node->setTag('phtml');
        $this->phtmlRaw = $string;
        $this->lineCount = 1;
        $ignoredAscii = array(10,13,ord("\t"),ord(" "));
        
        for($i = 0; $i < mb_strlen($string);$i++) {
            $chr = mb_substr($string,$i,1);
            $this->charCount++;
            $this->prevChar = null;
            $this->nextChar = mb_substr($string,$i+1,1);
            $this->char = $chr;

            if ($this->char == "\n") {
                $this->lineCount++;
                $this->charCount = 0;
            }
            
            switch($chr) {
                case '<':
                    if (mb_substr($string,$i+1,3) == '!--') {
                        $this->pushWithin(self::COMMENT);
                        break;
                    }
                    if ($this->nextChar == '?') {
                        $this->pushWithin(self::PHP);
                        break;
                    } 
                    if (!$this->ignoreAll() && $this->nextChar == '/') {
                        $this->ignoreChars = true;
                        $this->pushWithin(self::TAGEND);
                        $this->getNode()->setContainer(true);
                        
                    } elseif ($this->nextChar == '!') {
                        $this->pushWithin(self::DOCTYPE);
                    } else {
                        $this->onTagStart();
                    }
                    
                    break;
                case '>':
                    if (mb_substr($string,$i-2,2) == '--' && $this->isWithin(self::COMMENT)) {
                        $this->popWithin();
                        break;
                    }
                    if ($this->lastChar == '?' && $this->isWithin(self::PHP)) {
                        $this->popWithin();
                    } elseif ($this->isWithin(self::TAGEND)) {
                        $this->checkEndTag();
                        $this->popWithin();
                        $this->onNodeEnd();
                        $this->ignoreChars = false;
                        $this->ignoreNextChar(1);
                    } elseif ($this->isWithin(self::DOCTYPE)) {
                        $this->popWithin();
                        $this->onWordEnd();
                    } elseif(!$this->ignoreTags()) {
                        $this->onWordEnd();
                        $this->onTagEnd();
                        $this->ignoreNextChar(2);
                    }
                    
                    break;
                case ':':
                    if ($this->isWithin(self::ATTR)) {break;}
                case '%':
                    if ($this->ignoreTags()) break;
                    if ($this->nextChar == '{') {
                        $this->pushWithin(self::P_EVAL);
                        break;
                    }
                case '}':
                    if ($this->isWithin(self::P_EVAL) && $this->lastChar != '\\') {
                        $this->popWithin();
                        break;
                    }
                case ' ':
                case "\t":
                case "\n":
                case "\r":
                case '/':
                case '=':
                    if ($this->ignoreTags() && !$this->isWithin(self::ATTR)) break;
                    $this->onWordEnd();
                    break;
                case '"':
                case '\'':
                    if (!$this->isWithin(self::TAG,true)) break;
                    if ($this->isWithin(self::STRING)) {
                        $this->onStringEnd();
                    } else {
                        $this->onStringStart();
                    }
                    break;
                default:
                    $this->onWordStart();
                    
                    break;
            }
            $ascii = ord($this->char);
            if (in_array($ascii,$ignoredAscii))
                $this->debug("CHR:chr($ascii)");
            else
                $this->debug("CHR:$this->char");
            $this->addChar($this->char);
            $this->lastChar = $this->char;
        }
        $text = $this->getCurrent();
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));

        $node = $this->getNode();
        $this->clear();
        if (Settings::get(Settings::DEBUG,false) && isset($_GET['__viewdebug'])) {
            throw new PhtmlException($this->phtmlRaw,$this->char,$this->lineCount,$this->charCount,$this->debugTrace);
        }
        return $node;
    }
    protected function ignoreAll() {
        return in_array($this->within(),self::$IGNOREALLLIST);
    }
    protected function ignoreTags() {
        return in_array($this->within(),self::$IGNORELIST);
    }
    protected function ignoreNextChar($debugKey) {
        $this->debug('IGNORING NEXT CHR ('.$debugKey.'): '.$this->char);
        $this->ignoreNextChar = true;//Used because the tag is marked ended before the char is added
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
        if (!$this->ignoreNextChar && !$this->ignoreChars)
            $this->current .= $chr;
        $this->ignoreNextChar = false;
        $this->currentIgnore .= $chr;
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
    protected function getCurrent($alphanum = false,$erase = true) {
        $result = $this->current;
        if ($alphanum)
            $result = preg_replace ('/[^A-Z0-9_\-]/i','', $result);
        if ($erase) {
            $this->clearCurrent();
        }
        return $result;
    }
    protected function clearCurrent() {
        $this->current = '';
        $this->currentIgnore = '';
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
        if ($this->char != ':')
            $this->currentIgnore = '';
        if (!$this->hasCurrent()) return;//ignore
        
        switch($this->within()) {
            case self::TAG:
                if ($this->ignoreTags()) return;
                $current = $this->getCurrent(true);
                if (!$current) return;
                if ($this->char == ':')
                    $this->getNode()->setNs($current);
                else {
                    $this->getNode()->setTag($current);
                    $this->debug("STARTING NODE: '".$this->getNode()->getTag()."'");
                    if ($this->isScriptTag($current)) {
                        $this->pushBefore(self::SCRIPT);
                    }
                }
                break;
            case self::ATTR:
                if (!$this->attrName) {
                    $current = $this->getCurrent(true);
                    if (!$current) return;
                    $this->attrName = $current;
                    $this->debug("ATTR FOUND: $this->attrName");
                } else {
                    $val = trim($this->getCurrent(),"\"'");
                    $this->debug("ATTR VAL FOUND FOR: $this->attrName ($val)");
                    $this->getNode()->setAttribute($this->attrName,$val);
                    $this->attrName = '';
                    $this->popWithin();
                }

                break;
        }
    }
    protected function isScriptTag($tag) {
        return in_array(strtolower($tag),self::$SCRIPTAGS);
    }
    protected function pushWithin($within) {
        array_push($this->withinStack,$within);
    }
    protected function pushBefore($within) {
        $oldWithin = $this->popWithin();
        $this->pushWithin($within);
        $this->pushWithin($oldWithin);
    }
    protected function popWithin() {
        return array_pop($this->withinStack);
    }
    protected function within() {
        return $this->withinStack[count($this->withinStack)-1];
    }
    protected function isWithin($within,$deep = false) {
        if ($deep) {
            return in_array($within,$this->withinStack);
        } else {
            return $this->within() == $within;
        }
    }

    protected function onTagStart() {
        if ($this->ignoreTags()) return;

        if ($this->ignoreTags()) return;
        $node = new PhtmlNode();
        $text = $this->getCurrent();
        $this->pushWithin(self::TAG);
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));
        $this->getNode()->addChild($node);
        //$this->getNode()->setTextBefore(substr($this->getCurrent(),1));
        $this->node = $node;

    }

    protected function onTagEnd() {
        if (!$this->isWithin(self::TAG)) return;
        if ($this->ignoreTags()) return;
        $this->debug("ENDING TAG: ".$this->getNode()->getTag());
        
        if ($this->lastChar == '/')
            $this->onNodeEnd();

        $this->popWithin();
    }
    protected function checkEndTag() {
        $endTag = trim($this->currentIgnore,"</> \t\n\r");
        if ($endTag) {
            $ns = '';
            if (!stristr($endTag,':'))
                $tag = $endTag;
            else
                list($ns,$tag) = explode(':',$endTag);;
            
            if (strtolower($this->getNode()->getTag()) != strtolower($tag) ||
                 strtolower($this->getNode()->getNs()) != strtolower($ns)) {
                 $this->debug("ERROR WRONG END TAG: $endTag ($ns:$tag) for ".$this->getNode()->getTag());
            }
        }
    }
    protected function onNodeEnd() {
        if ($this->ignoreAll()) return;
        $parent = $this->getNode()->getParent();
        
        $text = $this->getCurrent();
        if ($text)
            $this->getNode()->addChild(new PhtmlNodeText($text));
        $this->debug("ENDING NODE: ".$this->getNode()->getTag());
        
        if ($this->isScriptTag($this->getNode()->getTag())) {
            $this->popWithin();//Pop an extra time for the SCRIPT
        }
        $this->node = $parent;
    }
    protected function getNode() {
        if (!$this->node) throw new PhtmlException($this->phtmlRaw,$this->char,$this->lineCount,$this->charCount,$this->debugTrace);
        return $this->node;
    }
    protected function debug($msg) {
        $msg = htmlentities("[".implode(',',$this->withinStack)."] ".$msg);
        if ($this->debug)
            echo "\n$msg";
        else
            $this->debugTrace .= "\n$msg";
    }
    public function setDebug($debug) {
        $this->debug = $debug;
    }
}
class PhtmlNode extends HtmlElement {
    private static $closureCount = 0;
    private static $append = '';
    private static $prepend = '';
    private $ns;
    private $container = false;
    
    public static function getNextClosure() {
        self::$closureCount++;
        $basis = self::$closureCount.microtime(true).rand(1,99999).rand(1,99999).rand(1,99999);
        $basis2 = String::GUID();
        //echo "\n$basis";
        return "closure".md5($basis.md5($basis2));
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
                    if (preg_match('/\%\{/',$body) || (preg_match('/<\?/',$body) && preg_match('/\$[A-Z]+\-\>[A-Z]+\(/is',$body))) {
                        //Body contains tags...
                        if (false && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                            //If in PHP 5.3 or higher - use closures
                            $closure = sprintf('function() use (&$view,&$data) {extract($view->taglibs);ob_start();?>%s<? return ob_get_clean();}',$body);;
                            $str .= sprintf('%s,$view);?>',$closure);
                        } else {
                            $closureName = self::getNextClosure();
                            $str .= sprintf('new %s($view),$view);?>',$closureName);
                            self::$prepend .= chr(10).sprintf('<?
                                            //%1$s closure start
                                            if (!class_exists("%2$s")) {
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
                                            }
                                            //%1$s closure end
                                            ?>'.chr(10),$this->getTag(),$closureName,$body);
                        }
                        
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
            $str = self::$prepend.$str.self::$append;
            self::$prepend = '';
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
        if (preg_match('/<\?\=(.*)\?>/i',$val)) {
            $val = preg_replace('/<\?\=(.*)\?>/i','$1',$val);
        } else {
            $val = preg_replace('/%\{([^\}]*)\}/i','".$1."','"'.$val.'"');
            $val = preg_replace('/(""\.|\."")/i','',$val);
        }
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
class PhtmlException extends Exception {
    private $phtml,$chr,$lineNum,$chrNum,$debugTrace;
    public function __construct($phtml,$chr,$lineNum,$chrNum,$debugTrace) {
        $this->phtml = $phtml;
        $this->char = $char;
        $this->lineNum = $lineNum;
        $this->chrNum = $chrNum;
        $this->debugTrace = $debugTrace;
        parent::__construct(sprintf('Failed parsing PHTML at line %s:%s - CHR: %s',$lineNum,$chrNum,$chr),E_ERROR);
    }
    public function __toString() {
        $lines = explode("\n",$this->phtml);
        $i = 1;
        foreach($lines as &$line) {
            $spaces = str_repeat(' ',3-strlen("$i"));
            $line = "<strong>$spaces$i:</strong> ".htmlentities($line,ENT_QUOTES,'UTF-8');
            $i++;
        }
        $phtml = implode("\n",$lines);
        return sprintf('<div><strong>%s</strong><pre>%s</pre></div>',
                    $this->getMessage(),
                    $phtml.chr(10).chr(10).$this->getTraceAsString().
                    chr(10).chr(10).'###### DEBUG TRACE ######'.
                    $this->debugTrace
                    );
    }
}