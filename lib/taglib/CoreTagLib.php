<?php
class CoreTagLib {
    public function tagVar($attrs,$view) {
		echo $view->data[$attrs->name];
    }
    public function tagTabPage($body,$attrs,$view) {
        echo '<div class="tabpage">',chr(10),
                $body,chr(10),
            '</div>';
    }
    public function tagPage($body,$attrs,$view) {
        echo '<div class="page">',chr(10),
				$body,chr(10),
			'</div>';
    }
    public function tagBody($attrs,$view) {
        echo Pimple::instance()->getBody();
    }
}