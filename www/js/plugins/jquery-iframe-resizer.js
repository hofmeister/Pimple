/**
 * @author Stephane Roucheray
 * @extends jquery
 *
 * Changed quite a lot sinze Stephane made something fairly odd... /Henrik Hofmeister
 */

jQuery.fn.iframeResize = function(options){
	var settings = jQuery.extend({
		width: "fill",
		height: "auto",
		autoUpdate : true,
        container:null,
        loader:null
	}, options);
	var filler = 30;

	this.each(function() {
        var frame = $(this);
        var body = frame.contents().find("body");
        var interval = null;

        frame.css('overflow','hidden');
        if (settings.container) {
            $(settings.container).css('position','absolute');
            $(settings.container).css('left','-9999px');
        }

        var resize = function() {
            frame.css("width",  settings.width  == "fill" ? "100%" : parseInt(settings.height));
            var autoheight = 0;
            try {
                body = frame.contents().find("body");
                autoheight = body.height() + filler;
                frame.css("height", settings.height == "auto" ? autoheight : parseInt(settings.height));
            } catch(e) {
                frame.css('overflow','auto');
                frame.css('height','400px');
                clearInterval(interval);
            }
        };
        frame.bind("load",resize);

		if (settings.autoUpdate) {
            frame.bind("load",function() {
                if (settings.loader) {
                    $(settings.loader).hide();
                }
                if (settings.container) {
                    $(settings.container).css('position','static');
                }
                if (interval) {
                    clearInterval(interval);
                    interval = null;
                }
                if ($.browser.msie) {
                    frame.attr("scrolling", "auto");
                }
                interval = setInterval(resize, 1000);
            });
			
		}
        resize();
    });
};
