<?php

class JavascriptTagLib extends TagLib {
	protected function tagInclude($attrs) {
        return sprintf('<script type="text/javascript" src="%s"></script>',Url::basePath().$attrs->path);
	}
    protected function tagScript($attrs) {
        return sprintf('<script type="text/javascript">%s</script>',"\n${$this->body()}\n");
	}
    protected function tagSetting($attrs) {
        return $this->tagScript(null,sprintf('Pimple.settings.%s = %s',$attrs->name,json_encode($attrs->value)));
    }
    protected function tagExpose($attrs) {
        return $this->tagScript(null,sprintf('%s = %s',$attrs->as,json_encode($attrs->value)));
    }
    protected function tagJson($attrs) {
        return json_encode($attrs->value);
    }

}