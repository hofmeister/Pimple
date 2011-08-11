<?php
/**
 * Basic tags
 * @namespace p
 */
class BasicTagLib extends TagLib {
	
    private static $globalVars = array();
    
    
    /**
     * Returns a guid 
     */
    protected function tagUid($attrs,$view) {
		return $this->uid();
	}
    /**
     * Return the value of a variable set on the view
     * @param string name | name of variable
     */
    protected function tagVar($attrs,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        
        if (DataUtil::has($view->data,$key)) {
            return DataUtil::get($view->data,$key);
        } else {
            return DataUtil::get(self::$globalVars,$key);
        }
    }
    /**
     * Set a variable on the current view
     * @param string name
     * @param mixed value
     * @param boolean global | set it globally or just this view - defaults to false
     */
    protected function tagSet($attrs,$view) {
        $value = ($attrs->value) ? $attrs->value : $this->body() ;
        if ($attrs->global == 'true') {
            DataUtil::set(self::$globalVars,$attrs->name,$value);
        } else {
            DataUtil::set($view->data,$attrs->name,$value);
            
        }
    }
    /**
     * include a template file - in the current template (transparently)
     * @param string file | path to the template file - relative from view/
     */
    protected function tagInclude($attrs,$view) {
        $include = new View($attrs->file);
        $result = $include->render($view->data);
        
        $view->data = DataUtil::merge($view->data,$include->data);
        
        return $result;
    }
    /**
     * Return the value of a PHP constant
     * @param string name | name of the constant
     */
    protected function tagConstant($attrs,$view) {
        $key = $attrs->name;
        if (!$key) return null;
        return constant($key);
    }
    /**
     * Render a template within this template
     * @param string template | path to template file - relative from view/
     * @param object|array data | data to pass to the template - defaults to all attributes given
     * @container true
     */
    protected function tagRender($attrs,$view) {
        if ($attrs->template) {
            $innerView = new View($attrs->template);
        
            $attrs->body = $this->body();
            unset($attrs->template);
            if (is_object($attrs->data) || is_array($attrs->data))
                $attrs = $attrs->data;
            return $innerView->render($attrs);
        }
        return $this->body();
    }
    /**
     * Renders a button group - button groups are simple containers that allow buttons to be properly places in 
     * regards to other content. Should contain only buttons
     * @param string class | additional css class - optional.
     * @container true
     */
	protected function tagButtonGroup($attrs,$view) {
		return '<div class="horizontal line right buttongroup '.$attrs->class.'">'.chr(10).
                $this->body().chr(10).
            '</div>';
	}
    /**
     * Renders a tabpage container - this can only contain page elements.
     * @container true
     */
    protected function tagTabPage($attrs,$view) {
        return '<div class="tabpage" id="'.$this->uid().'">'.chr(10).
                $this->body().chr(10).
            '</div>';
    }
    /**
     * Renders a page container - used in tabpages
     * @container true
     */
    protected function tagPage($attrs,$view) {
        return '<div class="page" id="'.$this->uid().'">'.chr(10).
				$this->body().chr(10).
			'</div>';
    }
    /**
     * Renders an img tag - src is relative to the basepath.
     * @param string src | path to image - relative to basepath of site
     */
    protected function tagImg($attrs) {
        $attrs->src = Url::basePath().$attrs->src;
        return new HtmlElement('img',ArrayUtil::fromObject($attrs),false);
    }
    /**
     * Renders the body of the current template - which is whatevers inside render tags or similar.
     * @container true
     */
    protected function tagBody($attrs,$view) {
        $attrs = new stdClass();
        $attrs->name = 'body';
        return $this->var($attrs,$this->body(),$view);
    }

    /**
     * Renders a anchor with a properly formatted url based on the parameters
     * @param string controller | the controller name - defaults to 'index'
     * @param string action | the action - defaults to 'index'
     * @param object parms | Parameters to append as GET parms to the url - defaults to most attributes on tag
     */
	protected function tagLink($attrs,$view) {
        $linkAttrs = new stdClass();
        $tagAttrs = new stdClass();
        
        if ($attrs->parms) {
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
            if ($attrs->class)
                $tagAttrs->class = $attrs->class;
            if ($attrs->style)
                $tagAttrs->style = $attrs->style;
            if ($attrs->title)
                $tagAttrs->title = $attrs->title;
            if ($attrs->rel)
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
        $a->addChild(new HtmlText(trim($this->body())));
		return $a;
	}
    /**
     * Returns a properly formatted url based on the parameters
     * @param string controller | the controller name - defaults to 'index'
     * @param string action | the action - defaults to 'index'
     * @param object parms | Parameters to append as GET parms to the url - defaults to most attributes on tag
     */
    protected function tagUrl($attrs,$view) {
        $controller = $attrs->controller;
        $action = $attrs->action;
        $host = $attrs->host;
        $id = $attrs->id;
        unset($attrs->controller);
        unset($attrs->action);
        unset($attrs->host);

        return Url::makeLink($controller,$action,$attrs,$host);
    }
    /**
     * Should be included at the bottom of the page - to enable pimple error|status|warning messages
     */
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
    /**
     * Include a style sheet
     * @param boolean collect | instead of including style sheets - output them here (other args are ignored)
     * @param boolean local | if false - assumes the stylesheet is found within pimple - defaults to true
     * @param string path | path to stylesheet - relative to site root or pimple/www/ (see local)
     */
	protected function tagStylesheet($attrs,$view) {
        if($attrs->collect == 'true') {
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

		if ($attrs->local == 'false') {
			$base = Settings::get(Pimple::URL);
            if ($view != null)
                $view->addCssFile(Pimple::instance()->getBaseDir().'www/'.$attrs->path);
		} else {
            if ($view != null)
                $view->addCssFile(Pimple::instance()->getSiteDir().$attrs->path);
			$base = Url::basePath();
		}

        $url = $base.$attrs->path;
        if ($attrs->inline != 'true') {
            if ($view == null || Settings::get(Settings::DEBUG,false) && Request::get('__nominify',false)) {
                return sprintf('<link href="%s" rel="stylesheet" type="text/css" />',$url);
            } 
            return '';
        }

        $path = Dir::normalize(BASEDIR).$attrs->path;
        $css = file_get_contents($path);
        
        $css = str_replace('url(../',"url($base",$css);
        return sprintf('<style type="text/css">%s</style>',$css);

	}
    /**
     * Outputs script tag
     * @param string path | if specified - outputs an include js script - else outputs a script tag with body
     * @container both
     * @deprecated
     * @See JavascriptTagLib
     */
	protected function tagJavascript($attrs) {
        if ($this->body())
            return sprintf('<script type="text/javascript">%s</script>',$this->body());
        else
            return sprintf('<script type="text/javascript" src="%s"></script>',Dir::normalize(BASEURL).$attrs->path);
	}
    /**
     * Outputs site name
     */
    protected function tagSitename($attrs) {
        return Pimple::instance()->getSiteName();
    }
    /**
     * Show only when the user is logged in - or logged out
     * @param boolean not | If true - show only contents when the user is logged out - defaults to false
     * @container true
     */
    protected function tagLoggedin($attrs) {
        if (SessionHandler::isLoggedIn() == ($attrs->not != 'true'))
            return $this->body();
    }
}