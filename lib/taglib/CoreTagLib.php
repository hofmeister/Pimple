<?php
class CoreTagLib {
	private static $uidCount = 0;
	public function uid() {
		$uid = 'pimple-core-uid-'.(self::$uidCount);
		self::$uidCount++;
		return $uid;
	}
	public function tagUid() {
		echo $this->uid();
	}
    public function tagVar($attrs,$view) {
		echo $view->data[$attrs->name];
    }
    public function tagTabPage($body,$attrs,$view) {
        echo '<div class="tabpage" id="'.$this->uid().'">',chr(10),
                $body,chr(10),
            '</div>';
    }
    public function tagPage($body,$attrs,$view) {
        echo '<div class="page" id="'.$this->uid().'">',chr(10),
				$body,chr(10),
			'</div>';
    }
    public function tagBody($attrs,$view) {
        echo Pimple::instance()->getBody();
    }
}