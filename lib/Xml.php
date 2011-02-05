<?php
interface XmlNode {
	public function __toString();
    /**
     * @param XmlNode $parent
     */
    public function setParent($parent);
    /**
     * @return XmlNode
     */
    public function getParent();
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
    private $ns;

	public function __construct($tag = null, $attrs=array(),$ns = "") {
		$this->tag = $tag;
		$this->attrs = $attrs;
        $this->ns = $ns;
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
    public function getNs() {
        return $this->ns;
    }
    public function setNs($ns) {
        $this->ns = $ns;
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
    public function setChildren($children) {
        $this->clear();
        $this->addChildren($children);
    }

	public function setAttribute($name, $value) {
		$this->attrs[$name] = $value;
	}

	public function getAttribute($name) {
		return $this->attrs[$name];
	}

	public function __toString() {
		return $this->toXml();
	}

    public function getIndex() {
        if (!$this->parent) return -1;
        return $this->getParent()->getChildIndex($this);
    }

    public function getChildIndex(XmlNode $node) {
        return array_search($node,$this->children);
    }
    public function setChildAt($i,XmlNode $node) {
        if ($i < 0 || $i > count($this->children))
            throw new Exception ("Child offset out of bounds: $i. Child count : ".count($this->children));
        unset($this->children[$i]);
        $this->children[intval($i)] = $node;
        ksort($this->children);
        return $this;
    }
    public function removeChildAt($i) {
        if ($i < 0 || $i > count($this->children))
            throw new Exception ("Child offset out of bounds: $i. Child count : ".count($this->children));
        unset($this->children[$i]);
        $this->children = array_values($this->children);
        return $this;
    }
    public function removeChild(XmlNode $node) {
        return $this->removeChildAt($this->getChildIndex($node));
    }
    public function addChildAt($offset,XmlNode $node) {
        if ($offset < 0)
            throw new Exception ("Child offset must be greater than -1".count($this->children));
        $result = array();
        if ($offset >= count($this->children)) {
            return $this->addChild($node);
        }
        for($i = 0; $i < count($this->children);$i++) {
            $result[] = $this->children[$i];
            if ($i == $offset) {
                $result[] = $node;
                $node->setParent($this);
            }
        }
        $this->children = $result;
        return $this;
    }
    public function addChildren($children) {
        foreach($children as $node) {
            $this->addChild($node);
        }
    }
    public function addChildrenAt($offset,$children) {
        $i = 0;
        foreach($children as $node) {
            $this->addChildAt($offset+$i,$node);
            $i++;
        }
    }
    public function detach() {
        $this->getParent()->removeChild($this);
    }
    /**
     * replace node with other node
     * @param PhtmlNode $otherNode
     */
    public function replace($otherNode) {
        $parent = $this->getParent();
        $i = $parent->getChildIndex($this);
        $this->detach();
        $parent->addChildAt($i,$otherNode);
    }
    public function clear() {
        foreach ($this->children as $child) {
            $child->setParent(null);
        }
        $this->children = array();
    }


    public function getElementsByTagNameNS($ns,$tagName) {
        $result = array();
        for($i = 0; $i < count($this->children);$i++) {
            if (!($this->children[$i] instanceof  XmlElement)) continue;
            if (strtolower($this->children[$i]->getNs()) == strtolower($ns)
                    && strtolower($this->children[$i]->getTag()) == strtolower($tagName)) {
                $result[] = $this->children[$i];
            }
            ArrayUtil::append($result, $this->children[$i]->getElementsByTagNameNS($ns,$tagName));
        }
        return $result;
    }
    public function toXml($makeParent = true) {
        $str = "";
		if (!$this->parent && $makeParent) {
			$str = '<?xml version="1.0" encoding="UTF-8" ?>'.chr(10);
		}
		$str .= "<";
		$tagName = '';
        if ($this->getNs() != '')
            $tagName .= $this->getNs().':';
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
			foreach ($this->children as $child) {
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
    public function toXml() {
        return $this->text;
    }

}