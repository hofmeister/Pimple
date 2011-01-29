jQuery.fn.tabpage = function() {
    this.each(function() {
        var dom = $(this);
        
        dom.find('.pw-tabs').find('a').click(function(evt) {
            var panel = dom.find('.pw-tabpanel').filter('.active');
            var tab = dom.find('a.active');
            panel.removeClass('active').addClass('offscreen');
            tab.removeClass('active');
            tab = $(this);
            tab.addClass('active');
            var id = tab.attr('href');
            panel = dom.find(id).addClass('active').removeClass("offscreen");
            panel.trigger('active');
            evt.preventDefault();
        });
        dom.bind('focus',function(evt) {
           var panel = $(evt.target).closest('.pw-tabpanel');
           if (panel.is('.active')) return;
           var id = panel.attr('id');
           dom.find('.pw-tabs').find('a[href=#'+id+"]").trigger('click');
        });
    });
};
Pimple.addBinding('.pw-tabpage','tabpage');