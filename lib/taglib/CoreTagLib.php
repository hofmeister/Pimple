<?php

class CoreTagLib extends TagLib {
	private static $uidCount = 0;
	protected function uid() {
		$uid = 'pimple-core-uid-'.(self::$uidCount);
		self::$uidCount++;
		return $uid;
	}
	protected function tagUid($attrs,$body,$view) {
		return $this->uid();
	}
    protected function tagVar($attrs,$body,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        if (is_array($view->data))
            return $view->data[$key];
        else
            return $view->data->$key;
    }
	protected function tagButtonGroup($attrs,$body,$view) {
		return '<div class="horizontal line right buttongroup '.$attrs->class.'">'.chr(10).
                $body.chr(10).
            '</div>';
	}
    protected function tagTabPage($attrs,$body,$view) {
        return '<div class="tabpage" id="'.$this->uid().'">'.chr(10).
                $body.chr(10).
            '</div>';
    }
    protected function tagPage($attrs,$body,$view) {
        return '<div class="page" id="'.$this->uid().'">'.chr(10).
				$body.chr(10).
			'</div>';
    }
    protected function tagImg($attrs) {
        return sprintf('<img src="%s" alt="%s">',Dir::normalize(BASEURL).$attrs->src,$attrs->alt);
    }
    protected function tagBody($attrs,$body,$view) {
        $attrs = new stdClass();
        $attrs->name = 'body';
        return $this->tagVar($attrs,$body,$view);
    }
	protected function tagMenu($attrs,$body,$view) {
		
	}

	protected function tagLink($attrs,$body,$view) {
        $controller = $attrs->controller;
        $action = $attrs->action;
        $id = $attrs->id;
        unset($attrs->controller);
        unset($attrs->action);
        unset($attrs->id);
        $link = Url::makeLink($controller,$action,$attrs);
        if (!$body)
            $body = $link;
		return sprintf('<a href="%s">%s</a>',$link,$body);
	}
    protected function tagMessages($attrs) {
        $msgs = MessageHandler::instance()->getMessages();
        MessageHandler::instance()->clear();
        
        $output = '<div class="pimple-messages">';
        foreach($msgs as $msg) {
            $class = ($msg->isError()) ? 'error' : 'success';
            if (!$msg->getField()) {
                $output .= sprintf('<div class="message %s">%s</div>',$class,$msg->getText());
            }
        }
        return $output.'</div>';
    }
	protected function tagStylesheet($attrs) {
        $baseurl = Dir::normalize(BASEURL);
        $url = $baseurl.$attrs->path;
        if ($attrs->inline != 'true')
            return sprintf('<link href="%s" rel="stylesheet" type="text/css" />',$url);

        $path = Dir::normalize(BASEDIR).$attrs->path;
        $dir = Dir::normalize(dirname($path));
        $css = file_get_contents($path);
        $host = $_SERVER['HTTP_HOST'];
        $css = str_replace('url(../',"url(http://$host$baseurl",$css);
        return sprintf('<style type="text/css">%s</style>',$css);

	}
	protected function tagJavascript($attrs) {
		return sprintf('<script src="%s"></script>',Dir::normalize(BASEURL).$attrs->path);
	}
    protected function tagSitename($attrs) {
        return Pimple::instance()->getSiteName();
    }
    protected function tagLoggedin($attrs,$body) {
        if (SessionHandler::isLoggedIn() == ($attrs->not != 'true'))
            return $body;
    }
    protected function tagPanel($attrs,$body) {
        return sprintf('<div class="panel %s"><h2>%s</h2>%s</div>',$attrs->class,$attrs->title,$body);
    }
}