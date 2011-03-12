<?php
class BasicTagLib extends TagLib {
	
    private static $globalVars = array();
    private $lastIfOutcome = true;
    private static $firstCssFound = false;
    protected function tagUid($attrs,$view) {
		return $this->uid();
	}
    protected function tagVar($attrs,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        
        if (DataUtil::has($view->data,$key)) {
            return DataUtil::get($view->data,$key);
        } else {
            return DataUtil::get(self::$globalVars,$key);
        }
    }
    protected function tagSet($attrs,$view) {
        $value = ($attrs->value) ? $attrs->value : $this->body() ;
        if ($attrs->global == 'true') {
            DataUtil::set(self::$globalVars,$attrs->name,$value);
        } else {
            DataUtil::set($view->data,$attrs->name,$value);
            
        }
    }
    protected function tagInclude($attrs,$view) {
        $include = new View($attrs->file);
        $result = $include->render($view->data);
        
        $view->data = DataUtil::merge($view->data,$include->data);
        
        return $result;
    }

    protected function tagConstant($attrs,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        return constant($key);
    }
    protected function tagRender($attrs,$view) {
        if ($attrs->template) {
            $innerView = new View($attrs->template);
        
            $attrs->body = $this->body();
            unset($attrs->template);
            if (isset($attrs->data) && is_object($attrs->data) || 
            	isset($attrs->data) && is_array($attrs->data)) {
                $attrs = $attrs->data;
            }
            return $innerView->render($attrs);
        }
        return $this->body();
        
    }
	protected function tagButtonGroup($attrs,$view) {
		return '<div class="horizontal line right buttongroup '.$attrs->class.'">'.chr(10).
                $this->body().chr(10).
            '</div>';
	}
    protected function tagTabPage($attrs,$view) {
        return '<div class="tabpage" id="'.$this->uid().'">'.chr(10).
                $this->body().chr(10).
            '</div>';
    }
    protected function tagPage($attrs,$view) {
        return '<div class="page" id="'.$this->uid().'">'.chr(10).
				$this->body().chr(10).
			'</div>';
    }
    protected function tagImg($attrs) {
        $attrs->src = Url::basePath().$attrs->src;
        return new HtmlElement('img',ArrayUtil::fromObject($attrs),false);
    }
    protected function tagBody($attrs,$view) {
        $attrs = new stdClass();
        $attrs->name = 'body';
        return $this->var($attrs,$this->body(),$view);
    }

	protected function tagLink($attrs,$view) {
        $linkAttrs = new stdClass();
        $tagAttrs = new stdClass();
        
        if (isset($attrs->parms)) {
            //If parms argument is found restrict url to parms, controller and action attributes
            $linkAttrs = $this->toObject($attrs->parms);
            if ($attrs->controller)
                $linkAttrs->controller =  $attrs->controller;
            if ($attrs->action)
                $linkAttrs->action = $attrs->action;
            $tagAttrs = $attrs;
            unset($tagAttrs->controller);
            unset($tagAttrs->action);
            unset($tagAttrs->parms);
        } else {
            //If parms argument not present - allow only "class","style" and "title" for tag - assume rest is for url
            $tagAttrs = new stdClass();
            if (isset($attrs->class))
                $tagAttrs->class = $attrs->class;
            if (isset($attrs->style))
                $tagAttrs->style = $attrs->style;
            if (isset($attrs->title))
                $tagAttrs->title = $attrs->title;
            if (isset($attrs->rel))
                $tagAttrs->rel = $attrs->rel;
            
            $linkAttrs = $attrs;
            unset($linkAttrs->class);
            unset($linkAttrs->style);
            unset($linkAttrs->title);
            unset($linkAttrs->rel);
        }



        $link = $this->url($linkAttrs,$this->body(),$view);
        if (!$this->body())
            $this->body($link);
        
        $tagAttrs->href = $link;
        $a = new HtmlElement('a', $tagAttrs);
        $a->addChild(new HtmlText($this->body()));
		return $a;
	}
    protected function tagUrl($attrs,$view) {
        $controller = (isset($attrs->action)) ? $attrs->controller : '';
        $action = (isset($attrs->action)) ? $attrs->action : '';
        $host = (isset($attrs->host)) ? $attrs->host : '';
        $id = (isset($attrs->id)) ? $attrs->id : '';
        unset($attrs->controller);
        unset($attrs->action);
        unset($attrs->host);

        return Url::makeLink($controller,$action,$attrs,$host);
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
	protected function tagStylesheet($attrs,$view) {
        if(isset($attrs->collect) && $attrs->collect == 'true') {
            if (Settings::get(Settings::DEBUG,false) && Request::get('__nominify',false))
                return '';
            Dir::ensure(Pimple::instance()->getSiteDir().'cache/css/');
            $cacheFile = Pimple::instance()->getSiteDir().'cache/css/counter.tmp';
            if (File::exists($cacheFile))
                $stamp = file_get_contents($cacheFile);
            else {
                $stamp = time();
                file_put_contents($cacheFile,$stamp);
            }
            return sprintf('<link href="%s" rel="stylesheet" type="text/css" />',Url::makeLink('pimple','css',array('view'=> Pimple::instance()->getViewFile(),'stamp'=>$stamp)))."\n";
        }

		if (isset($attrs->local) && $attrs->local == 'false') {
			$base = Settings::get(Pimple::URL);
            if ($view != null)
                $view->addCssFile(Pimple::instance()->getBaseDir().'www/'.$attrs->path);
		} else {
            if ($view != null)
                $view->addCssFile(Pimple::instance()->getSiteDir().$attrs->path);
			$base = Url::basePath();
		}
        
        $url = $base.$attrs->path;
        if (isset($attrs->inline) && $attrs->inline != 'true') {
            if ($view == null || Settings::get(Settings::DEBUG,false) && Request::get('__nominify',false)) {
                return sprintf('<link href="%s" rel="stylesheet" type="text/css" />',$url);
            } 
            return '';
        }
		//die(Pimple::instance()->getBaseDir());
        //$path = Dir::normalize(BASEDIR).$attrs->path;
        // FIXME: Der er ugler i mosen her aka. something is odd here.
        // On my windows pc, it seems to try to get css-files from skybox/ that resides in the pimple project.
        /*if(file_exists($path)) {
        	$css = file_get_contents($path);
        	$css = str_replace('url(../',"url($base",$css);
        	return sprintf('<style type="text/css">%s</style>',$css);
        }*/
        return '';
	}
	protected function tagJavascript($attrs) {
        if ($this->body())
            return sprintf('<script type="text/javascript">%s</script>',$this->body());
        else
            return sprintf('<script type="text/javascript" src="%s"></script>',Dir::normalize(BASEURL).$attrs->path);
	}
    protected function tagSitename($attrs) {
        return Pimple::instance()->getSiteName();
    }
    protected function tagLoggedin($attrs) {
        if (SessionHandler::isLoggedIn() == !(isset($attrs->not)) 
        	&& (isset($attrs->not) ? $attrs->not != 'true' : TRUE)) {
            return $this->body();
        }
    }
}