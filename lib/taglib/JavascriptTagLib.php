<?php
/**
 * Javascript tags
 * @namespace js
 */
class JavascriptTagLib extends TagLib {
    private static $jsScripts = '';
    /**
     *
     * @param mixed $attrs
     * @param View $view
     * @return string
     */
	protected function tagInclude($attrs,$view) {
        if (String::StartsWith($attrs->path,'http')) {

            self::$jsScripts .= sprintf('<script type="text/javascript" src="%s"></script>',$attrs->path)."\n";
            return;
        }
        if (isset($attrs->local) && $attrs->local == 'false') {
			$base = Settings::get(Pimple::URL);
            $view->addJsFile(Pimple::instance()->getBaseDir().'www/'.$attrs->path);
		} else {
            $view->addJsFile(Pimple::instance()->getSiteDir().$attrs->path);
			$base = Url::basePath();
		}

        if (Settings::get(Settings::DEBUG,false) && Request::get('__nominify',false)) {
            self::$jsScripts .= sprintf('<script type="text/javascript" src="%s"></script>',$base.$attrs->path)."\n";
        }
	}
    protected function tagCollect($attrs,$view) {
        if (Settings::get(Settings::DEBUG,false) && Request::get('__nominify',false)) {
            return self::$jsScripts;
        }
        Dir::ensure(Pimple::instance()->getSiteDir().'cache/js/');
        $cacheFile = Pimple::instance()->getSiteDir().'cache/js/counter.tmp';
        if (File::exists($cacheFile))
            $stamp = file_get_contents($cacheFile);
        else {
            $stamp = time();
            file_put_contents($cacheFile,$stamp);
        }
        $ctrl = Pimple::instance()->getControllerInstance();
        $skipLayout = 0;
        if ($ctrl && $ctrl->getSkipLayout()) {
            $skipLayout = 1;
        }
        return self::$jsScripts.sprintf('<script type="text/javascript" src="%s"></script>',Url::makeLink('pimple','javascript',array('view'=>Pimple::instance()->getViewFile(),'stamp'=>$stamp,'skipLayout'=>$skipLayout)))."\n";
    }
    protected function tagScript($attrs) {
        return sprintf('<script type="text/javascript">%s</script>',"\n${$this->body()}\n")."\n";
	}
    protected function tagSetting($attrs) {
        return sprintf('<script type="text/javascript">Pimple.init(function() {%s;})</script>',"\n".sprintf('Pimple.settings.%s = %s',$attrs->name,json_encode($attrs->value))."\n")."\n";
    }
    protected function tagExpose($attrs) {
        return sprintf('<script type="text/javascript">Pimple.init(function() {%s})</script>',"\n".sprintf('window.%s = %s;',$attrs->as,json_encode($attrs->value))."\n")."\n";
    }
    protected function tagJson($attrs) {
        require_once Pimple::instance()->getRessource('lib/Zend/Json.php');
        $value = $attrs->value;
        if ($attrs->striptags){
        	$value = ArrayUtil::stripValues($value);
        }
        $result = Zend_Json::encode($value);
        return $result;
    }

}