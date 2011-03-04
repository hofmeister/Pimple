$p.Widget = function(template,container) {
	this.guid = 'testgui';
	$p.Widget.windows[this.guid] = this;
	this.template = template;
	this.container = $(container);
	this.data = {};
};

$p.Widget.windows = {};

$p.getWidget = function(g) {
	return $p.Widget.windows[g];
}

$p.Widget.prototype = {
	setData: function(data) {
		this.data = data;
	},
	setJSON: function(url) {
		var c = this;
		$.ajax({
		    type: 'GET',
		    url: url,
		    dataType: 'json',
		    success: function(d) {
		    	c.setData(d);
		    },
		    async: false
		});
	},
	render: function() {
		this.container.html(this.template(this.data, this.guid));
	},
	setPaging: function(currentPage, rowsPerPage) {
		this.data.totalPages = Math.ceil(this.data.origTotalRows/rowsPerPage);
		this.data.totalRows = (this.data.origTotalRows > rowsPerPage) ? rowsPerPage : this.data.origTotalRows;
		this.data.currentPage = 0;
	},
	getPage: function() {
		return this.data.currentPage;
	},
	setPage: function(pageIndex) {
		
	}
}

/*p.Widget = {
	data:'',
	template:'',
	container:'',
	init: function(template,container) {
		this.template = template;
		this.container = $(container);
		this.data = '';
	},
	setData: function(data) {
		data = data;
	},
	setJSON: function(url) {
		$.getJSON(url, function(d) {
			data = d;
		});
	},
	render: function() {
		container.html(template(data));
	}
};


$(function() {
	var template;
	var container;
	var data;
	
	$.Widget.init = function(t,c) {
		template = t;
		container = $(c);
		data = '';
	};
	
	$.Widget.setJSON = function(url) {
		
		$.ajax({
		    type: 'GET',
		    url: url,
		    dataType: 'json',
		    success: function(d) {
		    	data = d;
		    },
		    async: false
		});
	};
	
	$.Widget.render = function() {
		alert(data.length);
		container.html(template(data));
	};
	
});
*/



/*$p.Widget = {
	data:'',
	template:'',
	container:'',
	init: function(template,container) {
		template = template;
		container = $(container);
		data = '';
	},
	setData: function(data) {
		data = data;
	},
	setJSON: function(url) {
		$.getJSON(url, function(d) {
			data = d;
		});
	},
	render: function() {
		container.html(template(data));
	}
};*/

/*$(function() {
	var Pimple.Widget = function(template,container) {
		this.template = template;
		this.container = $(container);
		this.data = {};
	};
	var Pimple.Widget.prototype.setData = function(data) {
		this.data = data;
	};
	var Pimple.Widget.prototype.render = function() {
		this.container.html(this.template(this.data));
	};
});*/