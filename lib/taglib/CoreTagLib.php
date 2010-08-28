<?php
class CoreTagLib {
	private static $uidCount = 0;
	public function uid() {
		$uid = 'pimple-core-uid-'.(self::$uidCount);
		self::$uidCount++;
		return $uid;
	}
	public function tagUid($attrs,$body,$view) {
		echo $this->uid();
	}
    public function tagVar($attrs,$body,$view) {
		echo $view->data[$attrs->name];
    }
	public function tagButtonGroup($attrs,$body,$view) {
		echo '<div class="buttongroup">',chr(10),
                $body,chr(10),
            '</div>';
	}
    public function tagTabPage($attrs,$body,$view) {
        echo '<div class="tabpage" id="'.$this->uid().'">',chr(10),
                $body,chr(10),
            '</div>';
    }
    public function tagPage($attrs,$body,$view) {
        echo '<div class="page" id="'.$this->uid().'">',chr(10),
				$body,chr(10),
			'</div>';
    }
    public function tagBody($attrs,$body,$view) {
        echo Pimple::instance()->getBody();
    }
	public function tagMenu($attrs,$body,$view) {
		
	}

	public function tagLink($attrs,$body,$view) {
        $controller = $attrs->controller;
        $action = $attrs->action;
        $id = $attrs->id;
        unset($attrs->controller);
        unset($attrs->action);
        unset($attrs->id);
        $link = Url::makeLink($controller,$action,$attrs);
		echo sprintf('<a href="%s">%s</a>',$link,$body);
	}
	public function tagStylesheet($attrs) {
		echo sprintf('<link href="%s" rel="stylesheet" type="text/css" />',Dir::normalize(BASEURL).$attrs->path);
	}
	public function tagJavascript($attrs) {
		echo sprintf('<script src="%s"></script>',Dir::normalize(BASEURL).$attrs->path);
	}
}