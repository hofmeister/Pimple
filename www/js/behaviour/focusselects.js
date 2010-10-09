$(function() {
    $('.pb-focusselects').live('focus',function(evt) {
        var dom = $(this);
        if (dom.getSelection().length == 0) {
            dom.select();
        }
    });
    $('.pb-focusselects').live('blur',function(evt) {
        var dom = $(this);
        if (dom.getSelection().length > 0) {
            dom.selectRange(0,0);
        }
    });
});