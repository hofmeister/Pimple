jQuery.fn.tabpage = function() {
    this.each(function() {
        var dom = $(this);
        
        dom.find('.pw-tabs').find('a').click(function(evt) {
            var current = dom.find('.pw-tabpanel').filter('.active');
            var currentA = dom.find('a.active');
            
            current.removeClass('active').hide();
            currentA.removeClass('active');
            var a = $(this);
            a.addClass('active');
            currentA = a;
            var id = a.attr('href');
            var current = dom.find(id).addClass('active').show();

            return false;
        });
    });
};
Pimple.addBinding('.pw-tabpage','tabpage');