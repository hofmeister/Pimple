<?php

class JavascriptTagLib extends TagLib {
	protected function tagInclude($attrs) {
		if ($attrs->local == 'false') {
			$base = Settings::get(Pimple::URL);
		} else {
			$base = Url::basePath();
		}
        return sprintf('<script type="text/javascript" src="%s"></script>',$base.$attrs->path)."\n";
	}
    protected function tagScript($attrs) {
        return sprintf('<script type="text/javascript">%s</script>',"\n${$this->body()}\n")."\n";
	}
    protected function tagSetting($attrs) {
        return sprintf('<script type="text/javascript">%s</script>',"\n".sprintf('Pimple.settings.%s = %s',$attrs->name,json_encode($attrs->value))."\n")."\n";
    }
    protected function tagExpose($attrs) {
        return sprintf('<script type="text/javascript">%s</script>',"\n".sprintf('%s = %s',$attrs->as,json_encode($attrs->value))."\n")."\n";
    }
    protected function tagJson($attrs) {
        return json_encode($attrs->value);
    }

}