<?php
class JSTagLib extends TagLib {
	protected $containers = array();
	private static $JS_WRAPPER_TAG = '';
	private static $JS_EXPRESSION = '/js{(.*?)}/';
	private static $JS_WIDGET_EXPRESSION = '/js{_widget(.*?)}/';
	//private static $JS_WIDGET_EXPRESSION_OUTER = '/js{_w(.*?)}/';
	private static $JS_WIDGET_EXPRESSION_REPLACEMENT = '';
	
	public function __construct() {
		parent::__construct(false, true);
		self::$JS_WRAPPER_TAG = $this->uid();
	}
	
	private function makeJsString($string) {
		return preg_replace('/[\n\t\r](\s)*/', '', trim($string));
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
				$fixedExpressions[] = '"+'.String::RemoveSlashes($match).'+"';
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
	}
	
	protected function tagIf($attrs, $view) {
		$this->requireAttributes($attrs, array('test'));
		return sprintf('</%3$s>";if(%1$s){o+="<%3$s>%2$s</%3$s>"; } o += "<%3$s>', $this->makeJsString($attrs->test), $this->body(), self::$JS_WRAPPER_TAG);
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
		return sprintf('</%4$s>";for(var i=0;i<%1$s.length;i++){var %2$s=%1$s[i];o+="<%4$s>%3$s</%4$s>";}o+="<%4$s>', $attrs->in, $row, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagFor($attrs, $view) {
		$this->requireAttributes($attrs, array('test'));
		return sprintf('</%3$s>";for(var i=0;%1$s;i++){o+="<%3$s>%2$s</%3$s>";}o+="<%3$s>', $attrs->test, $this->makeJsString($this->body()), self::$JS_WRAPPER_TAG);
	}
	
	protected function tagCollect($attrs, $view) {
		$output = array('<!-- JSTaglib output start --><script type="text/javascript">$(function(){');
		if($this->containers) {
			$functionJs = array();
			foreach($this->containers as $c) {
				$output[] = $c;
			}
		}
		$output[] = '});</script><!-- JSTaglib end -->';
		return join('', $output);
	}
}