<?php
class JSTagLib extends TagLib {
	protected $containers = array();
	private static $JS_WRAPPER_TAG = '';
	private static $JS_EXPRESSION = '/js{(.*?)}/';
	
	public function __construct() {
		parent::__construct(false, true);
		self::$JS_WRAPPER_TAG = $this->uid();
	}
	
	private function makeJsString($string) {
		return preg_replace('/[\n\t\r](\s)*/', '', trim($string));
	}
	
	private function replaceJsExpressions($string) {
		return preg_replace(self::$JS_EXPRESSION, '"+$1+"', $string);
	}
	
	private function outputJs($body) {
		$matches = array();
		$js = array();
		preg_match_all('%<'.self::$JS_WRAPPER_TAG.'\b[^>]*>(.*?)</'.self::$JS_WRAPPER_TAG.'>%', $body, $matches);
		$html = preg_split('%<'.self::$JS_WRAPPER_TAG.'\b[^>]*>(.*?)</'.self::$JS_WRAPPER_TAG.'>%', $body);
		if(isset($matches[1])) {
			foreach($matches[1] as $m) {
				$js[] = $m;
			}
		}
		$output = '';
		for($i=0;$i<count($js);$i++) {
			if($i==0) {
				$output .= sprintf('var o=%s;', $this->replaceJsExpressions(String::JsEncode(((isset($html[$i])) ? $this->makeJsString($html[$i]) : '')))) .chr(10) . chr(10);
			}
			$output .= $js[$i] .chr(10) . chr(10);
			$a = (isset($html[$i+1])) ? $this->makeJsString($html[$i+1]) : '';
			if(!empty($a)) {
				$output .= sprintf('o+=%s;', String::JsEncode($a)).chr(10) . chr(10);
			}
		}
		return $output;
	}
	
	protected function tagContainer($attrs, $view) {
		if(!isset($attrs->id)) {
			throw new ErrorException("TagContainer must have at least one parameter (id)");
		}
		$this->containers[$attrs->id] = sprintf('$.'.$attrs->id.' = function() { %s };', $this->outputJs($this->body()));
	}
	
	private function outputInnerTags($body) {
		// TODO: Argh... virker bare ikke john john
	}
	
	protected function tagIf($attrs, $view) {
		//$this->outputInnerTags($this->body());
		return sprintf("%sif (%s) { o += %s; }%s", '<'.self::$JS_WRAPPER_TAG.'>',
													$attrs->test,
													$this->outputInnerTags($this->body()),
													'</'.self::$JS_WRAPPER_TAG.'>');
	}
	
	protected function tagElse($attrs, $view) {
		return sprintf("%s} else { o += %s; } %s", '<'.self::$JS_WRAPPER_TAG.'>',
													$this->outputInnerTags($this->body()),
													'</'.self::$JS_WRAPPER_TAG.'>');
	}
	
	protected function tagElseIf($attrs, $view) {
		$body = $this->body();
		return sprintf("%s} else if (%s) { o += %s; %s", '<'.self::$JS_WRAPPER_TAG.'>',
													$attrs->test,
													$this->outputInnerTags($this->body()),
													'</'.self::$JS_WRAPPER_TAG.'>');
	}
	
	protected function tagWhile($attrs, $view) {
		
	}
	
	protected function tagFor($attrs, $view) {
		
	}
	
	protected function tagCollect($attrs, $view) {
		$output = array('<!-- JSTagLib output --><script type="text/javascript"> $(function() { ');
		if($this->containers) {
			$functionJs = array();
			foreach($this->containers as $c) {
				$output[] = $c;
			}
		}
		$output[] = '});</script>';
		return join('', $output);
	}
}