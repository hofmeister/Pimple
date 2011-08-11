<?php
/**
 * Core flow structures
 * @compiled
 * @namespace c
 */
class CoreTagLib extends TagLib {
    private static $uid = 0;
    public function __construct() {
        parent::__construct(true);
    }
    /**
     * Returns a incrementing uid (from page load). Not unique across requests
     */
    protected function uid() {
        self::$uid++;
        return self::$uid;
    }
    /**
     * if statement - my contain elseif and else statements - in addition to anything else.
     * @param php test | Any php condition  
     * @container true
     */
    protected function tagIf($attrs,$view) {
        return sprintf('<? if (%s) {?>%s<? } ?>',$attrs->test,$this->body());
    }
    /**
     * Else if - must be in the bottom of an if tag - anly followed by other elseif og else tags
     * @param php test | Any php condition  
     * @container true
     */
    protected function tagElseIf($attrs,$view) {
        return sprintf('<? } elseif (%s) {?>%s ',$attrs->test,$this->body());
    }
    /**
     * Else - must be in the bottom of an if tag
     * @container true
     */
    protected function tagElse($attrs,$view) {
        return sprintf('<? } else {?>%s',$this->body());
    }
    /**
     * Each creates a foreach loop over the object or array provided
     * @param object|array in | the object or array to traverse
     * @param string as | name of iterator variable - defaults to $it
     * @param string ix | name of key variable in foreach - defaults to $ix
     * @container true
     */
    protected function tagEach($attrs,$view) {
        if (!$attrs->as)
            $attrs->as = '$it';
        if (!$attrs->ix)
            $attrs->ix = '$ix';

        return sprintf('<? if (is_array(%1$s) || is_object(%1$s)) {foreach(%1$s as %5$s=>%2$s){$view->data["%3$s"] = %2$s;?>%4$s<?}} ?>',
                $attrs->in,
                $attrs->as,
                trim($attrs->as,'$'),
                $this->body(),
                $attrs->ix);
    }
    /**
     * Creates a for loop
     * @param int limit | limit of the for loop
     * @param int start | offset of the for loop - defaults to 0
     * @param phpvar it| name of iterator variable - defaults to $it
     * @param int increment | how much to increment after each loop - defaults to 1
     * @container true
     */
    protected function tagFor($attrs,$view) {
        if (!$attrs->it)
            $attrs->it = '$it';
        if (!$attrs->start)
            $attrs->start = 0;
        if (!$attrs->increment)
            $attrs->increment = 1;
        
        if (!$attrs->limit)
            throw new InvalidArgumentException('For tag requires limit attribute',E_ERROR);
        $limit = intval($attrs->limit);
        
        return sprintf('<? for(%1$s = %2$s; %1$s < %3$s; %1$s+=%4$s){$view->data["%5$s"] = %2$s;?>%6$s<?} ?>',
                        $attrs->it,
                        $attrs->start,
                        $attrs->limit,
                        $attrs->increment,
                        trim($attrs->it,'$'),
                        $this->body());
    }
    /**
     * Creates a while loop
     * @param php test | Any php condition
     * @container true
     */
    protected function tagWhile($attrs,$view) {
        return sprintf('<? while(%s){?>%s<?} ?>',$attrs->test,$this->body());
    }

    /**
     * Creates a switch - may only contain case elements
     * @param php value | Any php expression
     * @container true
     */
    protected function tagSwitch($attrs,$view) {
        return sprintf('<? switch(%s){?>%s<?} ?>',$attrs->value,$this->body());
    }
    /**
     * Creates a switch case - may only be created within switch elements
     * @param string value | The value of the case
     * @container true
     */
    protected function tagCase($attrs,$view) {
        return sprintf('<? case "%s":?>%s<?break;?>',$attrs->value,$this->body());
    }
}