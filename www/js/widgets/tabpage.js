jQuery.fn.tabpage = function() {
    this.each(function() {
        var dom = $(this);
        var tabRow = dom.children('.pw-tabs');
        var panels = dom.children('.pw-tabpanel');
        tabRow.find('a').click(function(evt) {
            var panel = panels.filter('.active');
            var tab = tabRow.find('a.active');
            panel.removeClass('active').addClass('offscreen');
            tab.removeClass('active');
            tab = $(this);
            tab.addClass('active');
            var id = tab.attr('href');
            id = id.substr(id.indexOf('#'));
            panel = panels.filter(id).addClass('active').removeClass("offscreen");
            panel.trigger('active');
            dom.trigger("tabselected",panel);
            evt.preventDefault();
        });
        dom.bind('focus',function(evt) {
           var panel = $(evt.target).closest('.pw-tabpanel');
           if (panel.is('.active')) return;
           var id = panel.attr('id');
           tabRow.find('a[href=#'+id+"]").trigger('click');
        });
    });
};
Pimple.addBinding('.pw-tabpage','tabpage');