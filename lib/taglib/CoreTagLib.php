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
    protected function tagIf($attrs,$view) {
        return sprintf('<? if (%s) {?>%s<? } ?>',$attrs->test,$this->body());
    }
    protected function tagElseIf($attrs,$view) {
        return sprintf('<? } elseif (%s) {?>%s ',$attrs->test,$this->body());
    }
    protected function tagElse($attrs,$view) {
        return sprintf('<? } else {?>%s',$this->body());
    }
    protected function tagEach($attrs,$view) {
        if (!$attrs->as)
            $attrs->as = '$it';
        return sprintf('<? if (is_array(%1$s)) {foreach(%1$s as %2$s){$view->data["%3$s"] = %2$s;?>%4$s<?}} ?>',$attrs->in,$attrs->as,trim($attrs->as,'$'),$this->body());
    }
    protected function tagWhile($attrs,$view) {
        return sprintf('<? while(%s){?>%s<?} ?>',$attrs->test,$this->body());
    }

    protected function tagSwitch($attrs,$view) {
        return sprintf('<? switch(%s){?>%s<?} ?>',$attrs->value,$this->body());
    }
    protected function tagCase($attrs,$view) {
        return sprintf('<? case "%s":?>%s<?break;?>',$attrs->value,$this->body());
    }
}