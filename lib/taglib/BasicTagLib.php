<?php

class BasicTagLib extends TagLib {
	private static $uidCount = 0;
    private static $globalVars = array();
    private $lastIfOutcome = true;
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
        
        if (DataUtil::has($view->data,$key)) {
            return DataUtil::get($view->data,$key);
        } else {
            return DataUtil::get(self::$globalVars,$key);
        }
    }
    protected function tagSet($attrs,$body,$view) {
        $value = ($attrs->value) ? $attrs->value : $body ;
        if ($attrs->global == 'true') {
            DataUtil::set(self::$globalVars,$attrs->name,$value);
        } else {
            DataUtil::set($view->data,$attrs->name,$value);
            
        }
    }
    protected function tagInclude($attrs,$body,$view) {
        $file = Dir::concat(BASEDIR,'view').$attrs->file;
        $include = new View($file);
        $result = $include->render($view->data);
        
        $view->data = DataUtil::merge($view->data,$include->data);
        
        return $result;
    }

    protected function tagConstant($attrs,$body,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        return constant($key);
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
        $attrs->src = Dir::normalize(BASEURL).$attrs->src;
        return new HtmlElement('img',ArrayUtil::fromObject($attrs),false);
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
}