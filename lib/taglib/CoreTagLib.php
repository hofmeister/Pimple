<?php
class CoreTagLib {
    public function tagVar($attrs,$view) {
        echo $view->data[$attrs->name];
    }
    public function tagTabPage($body,$attrs,$view) {
        echo"TABPAGE START:
                $body
            TABPAGE END";
    }
    public function tagPage($body,$attrs,$view) {
        echo "PAGE:\n".$body;
    }
    public function tagBody($attrs,$view) {
        echo Pimple::instance()->getBody();
    }
}