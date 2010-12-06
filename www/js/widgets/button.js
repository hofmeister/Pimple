$(function() {
    $('.pw-button').live('click',function(evt) {
        var classes = $(this).attr('class').split(' ');
        for(var i = 0; i < classes.length;i++) {
            if (classes[i].substr(0,7) == 'pw-evt-') {
                var evtType = classes[i].substr(7);
                $(this).trigger(evtType,this);
            }
        }
    });
});