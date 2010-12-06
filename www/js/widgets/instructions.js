jQuery.fn.instructions = function() {
    this.each(function() {
        var dom = $(this);

        dom.find('.element').each(function() {
            var padding = 10;
            var instr = dom.find('.instructions');
            //Check to make sure instruction col is equal or bigger than input col
            //console.log("FIRST:"+(parseInt($(this).height()) + parseInt($(this).css('padding-top')) + parseInt($(this).css('padding-bottom')) + parseInt($(this).css('margin-top')) + parseInt($(this).css('margin-bottom'))));
            //console.log("OTHER:"+(parseInt($(instr).height()) + parseInt($(instr).css('padding-top')) + parseInt($(instr).css('padding-bottom')) + parseInt($(instr).css('margin-top')) + parseInt($(instr).css('margin-bottom'))));
            if (($(this).height()-padding) > instr.height()) {
                dom.find('.instructions').css('height',($(this).height()-padding));
            }
        });
        dom.find('input,textarea,select').focus(function() {
            dom.find('.instructions').css('visibility','visible');
        });
        dom.find('input,textarea,select').blur(function() {
            if (!dom.hasClass('error') && !dom.hasClass('valid'))
                dom.find('.instructions').css('visibility','hidden');
        });
    });
};
Pimple.addBinding('.form-item','instructions');