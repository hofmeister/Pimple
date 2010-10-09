jQuery.fn.formitem = function() {
    this.each(function() {
        var dom = $(this);
        var formElm = dom.find('input,textarea,select');
        dom.bind('add',function(evt,elm) {
            //Catch event and fill it with value
            var val = formElm.val();
            evt.stopPropagation();
            if (formElm.validate(true)) {
                formElm.clearValidation();
                formElm.val('');
                dom.parent().trigger('add',[elm,val]);
                formElm.focus();
            }
        });
        dom.bind('value',function(evt,elm,val) {
            formElm.val(val);
        });
        
    });
};
Pimple.addBinding('.form-item','formitem');
