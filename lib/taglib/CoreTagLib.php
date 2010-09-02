<?php
class CoreTagLib extends TagLib {
    private static $uid = 0;
    public function __construct() {
        parent::__construct(true);
    }
    protected function uid() {
        self::$uid++;
        return self::$uid;
    }
    protected function tagIf($attrs,$body,$view) {
        return sprintf('<? if (%s) {?>%s<? } ?>',$attrs->test,$body);
    }
    protected function tagElse($attrs,$body,$view) {
        return sprintf('<? else {?>%s<?} ?>',$body);
    }
    protected function tagEach($attrs,$body,$view) {
        if (!$attrs->as)
            $attrs->as = '$it';
        return sprintf('<? foreach(%s as %s){?>%s<?} ?>',$attrs->in,$attrs->as,$body);
    }
    protected function tagWhile($attrs,$body,$view) {
        return sprintf('<? while(%s){?>%s<?} ?>',$attrs->test,$body);
    }

    protected function tagSwitch($attrs,$body,$view) {
        return sprintf('<? switch(%s){?>%s<?} ?>',$attrs->value,$body);
    }
    protected function tagCase($attrs,$body,$view) {
        return sprintf('<? case "%s":?>%s<?break;?>',$attrs->value,$body);
    }
}