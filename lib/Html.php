<?php
require_once 'Xml.php';

interface HtmlNode extends XmlNode {
	
}

class HtmlElement extends XmlElement implements HtmlNode {

	public function isContainer() {
		switch (strtolower($this->getTag())) {
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

	public function __toString() {
		return $this->toHtml();
	}
    public function toHtml() {
        $str = "<";
		$tagName = '';
        if ($this->getNs() != '')
            $tagName .= $this->getNs().':';
		$tagName .= $this->getTag();

		$str .= $tagName;
		if (count($this->getAttrs()) > 0) {
			$str .= ' ';
			foreach ($this->getAttrs() as $name => $val) {
				$str .= sprintf('%s="%s" ', $name, $val);
			}
			$str = trim($str);
		}
		if ($this->isContainer()) {
			$str .= '>';
			$children = $this->getChildren();
			foreach ($children as $child) {
				$str .= $child->__toString();
			}
			$str .= "</$tagName>";
		} else {
			$str .= '/>';
		}
		return $str;
    }
}
class HtmlText extends XmlText implements HtmlNode {

    public function toHtml() {
        return $this->toXml();
    }
}