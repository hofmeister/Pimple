<?php
interface XmlNode {
	public function __toString();
}

class Xml {
	public static function toXml($data,$parent = 'root') {
		
		if (!($parent instanceof XmlNode)) {
			$parent = new XmlElement((string)$parent);
		}
		switch(true) {
			case is_array($data):
			case is_object($data):
				$parent->setAttribute('type','structure');
				foreach($data as $key=>$value) {
					if (is_int($key)) {
						$key = 'element';
					}
					$node = new XmlElement($key);
					$parent->addChild($node);
					Xml::toXml($value,$node);
				}
				break;
			case is_bool($data):
				$parent->setAttribute('type','boolean');
				$parent->addChild(new XmlText(($data) ? 'true' : 'false'));
				break;
			case is_int($data):
				$parent->setAttribute('type','integer');
				$parent->addChild(new XmlText($data));
				break;
			case is_string($data):
				$parent->setAttribute('type','string');
				$parent->addChild(new XmlText($data));
				break;
			case is_float($data):
				$parent->setAttribute('type','float');
				$parent->addChild(new XmlText($data));
				break;
			case is_double($data):
				$parent->setAttribute('type','double');
				$parent->addChild(new XmlText($data));
				break;
			case is_null($data):
				//break;
			default:
				$parent->addChild(new XmlText($data));
				break;
		}
		return $parent;
	}
}

class XmlElement implements XmlNode {

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
		$str = "";
		if (!$this->parent) {
			$str = '<?xml version="1.0" encoding="UTF-8" ?>'.chr(10);
		}
		$str .= "<";
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
		if (count($this->children) > 0) {
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

class XmlText implements XmlNode {
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