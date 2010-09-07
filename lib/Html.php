<?php

interface HtmlNode {

	public function __toString();
}

class HtmlElement implements HtmlNode {

	private $tag;
	private $parent;
	private $attrs = array();
	private $children = array();

	public function __construct($tag = null, $attrs=array()) {
		$this->tag = $tag;
		$this->attrs = $attrs;
	}

	public function getAttrs() {
		return $this->attrs;
	}

	public function setAttrs($attrs) {
		$this->attrs = $attrs;
	}

	public function isContainer() {
		switch (strtolower($this->tag)) {
			case 'div':
			case 'span':
			case 'strong':
            case 'a':
			case 'b':
			case 'em':
			case 'i':
			case 'ul':
			case 'li':
			case 'ol':
			case 'dd':
			case 'dt':
			case 'dl':
			case 'table':
			case 'tr':
			case 'thead':
			case 'tbody':
			case 'tfoot':
			case 'td':
			case 'th':
			case 'script':
			case 'title':
			case 'head':
			case 'body':
			case 'textarea':
			case 'html':
			case 'pre':
			case 'code':
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'p':
			case 'blink':
				return true;
		}
		return false;
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

	public function setAttribute($name, $value) {
		$this->attrs[$name] = $value;
	}

	public function getAttribute($name) {
		return $this->attrs[$name];
	}

	public function __toString() {
		$str = "<";
		$tagName = '';
		$tagName .= $this->tag;
		$str .= $tagName;
		if (count($this->attrs) > 0) {
			$str .= ' ';
			foreach ($this->attrs as $name => $val) {
				$str .= sprintf('%s="%s" ', $name, $val);
			}
			$str = trim($str);
		}
		if ($this->isContainer()) {
			$str .= '>';
			foreach ($this->children as &$child) {
				$str .= $child->__toString();
			}
			$str .= "</$tagName>";
		} else {
			$str .= '/>';
		}
		return $str;
	}

}

class HtmlText implements HtmlNode {

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

}