jQuery.fn.checkers = function() {
    this.each(function() {
        var dom = $(this);
        var cb = dom.find('.form-checkbox');
        var input = cb.next();
        input[0].disabled = !cb[0].checked;
        cb.bind('change mouseup',function() {
            input[0].disabled = !cb[0].checked;
			if (input[0].disabled) {
				input.trigger('blur');
			}
        });
    });
};

Pimple.addBinding('.pw-checker','checkers');