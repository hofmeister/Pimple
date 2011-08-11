<?php
/**
 * Javascript template tags
 * @namespace jst
 */
class JSTemplateTagLib extends TagLib {
	protected $containers = array();
	private static $JS_WRAPPER_TAG = '';
	private static $JS_EXPRESSION = '/js{(.*?)}/';
	private static $JS_WIDGET_EXPRESSION = '/\\$self(.*?)}/';
	//private static $JS_WIDGET_EXPRESSION_OUTER = '/js{_w(.*?)}/';
	//private static $JS_WIDGET_EXPRESSION_REPLACEMENT = '';
	
	public function __construct() {
		parent::__construct(false, true);
		self::$JS_WRAPPER_TAG = $this->uid();
	}
	
	private function makeJsString($string) {
        return preg_replace('/[\n\r\t]\s*/', '', trim($string));
	}
    private function handleInline($string) {
        $string = String::RemoveSlashes($string);
        $parts = preg_split('/[;\n]{1,2}/s',$string);
        if (count($parts) <= 1)
            return "($string)";

        $result = "";
        for($i = 0; $i < count($parts);$i++) {
        	$result .= ($i ==  (count($parts)-1)) ? 'return '.$parts[$i].";" : $parts[$i].";";
        }

        return sprintf('(function(){%s})()',$result);
    }
	
	private function replaceJsExpressions($string) {
		$fixedExpressions=array();
		$expressionMatches=array();
		/* Change all widget expressions */
		$string = preg_replace(self::$JS_WIDGET_EXPRESSION, '$p.getWidget(\'"+g+"\')$1', $string);
		//$string = preg_replace(self::$JS_WIDGET_EXPRESSION_OUTER, '"+($p.getWidget(g)$1)+"', $string);
		preg_match_all(self::$JS_EXPRESSION, $string, $expressionMatches);
		if(count($expressionMatches) > 0) {
			/* Let's ensure that our js-expression don't get addslashed */
			foreach($expressionMatches[1] as $match) {
				$fixedExpressions[] = '"+'.$this->handleInline($match).'+"';
			}
            
			/* Now we replace the expression tags, with the fixed js expression */
			for($i=0;$i<count($expressionMatches[0]);$i++) {
				$string = str_replace($expressionMatches[0][$i], $fixedExpressions[$i], $string);
			}
		}
		return $string;
	}
	
	protected function tagContainer($attrs, $view) {
		$this->requireAttributes($attrs, array('id'));
		$output = sprintf('$.%1$s=function(d,g){var o="<%3$s>%2$s</%3$s>"; return o;};', $attrs->id, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
		$matches=array();
        
		preg_match_all('%<'.self::$JS_WRAPPER_TAG.'>(.*?)</'.self::$JS_WRAPPER_TAG.'>%', $output, $matches);
		if(isset($matches[1])) {
			foreach($matches[1] as $m) {
				$output = str_replace('<'.self::$JS_WRAPPER_TAG.'>'.$m.'</'.self::$JS_WRAPPER_TAG.'>', addslashes($m), $output);
			}
		}
		$this->containers[$attrs->id] = String::UTF8Encode($this->replaceJsExpressions($output));
        //if (Settings::get(Settings::DEBUG,false)) { @TODO: it sometimes breaks if this is not present - fix so it doesn't
            //Add some line breaks to make it easier to read
            $this->containers[$attrs->id] = str_replace('o+=',"\no+=",$this->containers[$attrs->id]);
            $this->containers[$attrs->id] = preg_replace('/";(\}else\{|for|if]switch)/i',"\";\n$1",$this->containers[$attrs->id]);

        //}
	}
	
	protected function tagIf($attrs, $view) {
		$this->requireAttributes($attrs, array('test'));
		return sprintf('</%3$s>";if(%1$s){o+="<%3$s>%2$s</%3$s>"; } o+="<%3$s>', $this->makeJsString($attrs->test), $this->body(), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagElse($attrs, $view) {
		return sprintf('</%2$s>";}else{o+="<%2$s>%s', $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagElseIf($attrs, $view) {
		$this->requireAttributes($attrs, array('test'));
		return sprintf('</%3$s>";}else if(%1$s){o+="<%3$s>%2$s', $attrs->test, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagWhile($attrs, $view) {
		$this->requireAttributes($attrs, array('test'));
		return sprintf('</%3$s>";while(%1$s){o+="<%3$s>%2$s</%3$s>";}o+="<%3$s>', $attrs->test, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagEach($attrs, $view) {
		$this->requireAttributes($attrs, array('in'));
		$row = (!isset($attrs->as)) ? 'row' : $attrs->as;
		if ($attrs->it)
			return sprintf('</%4$s>";(function() {var _tmparr = %1$s;var i = 0;for(var %5$s in _tmparr){var %2$s=%1$s[%5$s];o+="<%4$s>%3$s</%4$s>";i++;}})()o+="<%4$s>', 
					$attrs->in, $row, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG,$attrs->it);
		else
			return sprintf('</%4$s>";(function() {var _tmparr = %1$s; for(var i=0;i<_tmparr.length;i++){var %2$s=%1$s[i];o+="<%4$s>%3$s</%4$s>";}})()o+="<%4$s>', 
					$attrs->in, $row, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagFor($attrs, $view) {
		$this->requireAttributes($attrs, array('limit', 'start', 'it'));
		return sprintf('</%5$s>";for(var %1$s=%2$s;%1$s<%3$s;%1$s++){o+="<%5$s>%4$s</%5$s>";}o+="<%5$s>', $attrs->it, $attrs->start, $attrs->limit, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagCollect($attrs, $view) {
		$output = array('<!-- JSTaglib output start --><script type="text/javascript">');
		if($this->containers) {
			foreach($this->containers as $c) {
				$output[] = $c;
			}
		}
		$output[] = '</script><!-- JSTaglib end -->';
		return join('', $output);
	}
}