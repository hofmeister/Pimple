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
		this.rows = this.data.rows;
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
		this.data.rowsPerPage = rowsPerPage;
		this.data.totalRows = (this.data.origTotalRows > rowsPerPage) ? rowsPerPage : this.data.origTotalRows;
		this.data.currentPageIndex = 0;
	},
	getPageIndex: function() {
		return this.data.currentPageIndex;
	},
	getPage: function(pageIndex) {
		var start = this.data.totalRows*pageIndex;
		var end = ((start+this.data.rowsPerPage) > this.data.origTotalRows) ? this.data.origTotalRows : (start+this.data.rowsPerPage);
		var newRows = new Array();
		for(var i=start;i<end;i++) {
			newRows.push(this.rows[i]);
		}
		this.data.totalRows = newRows.length;
		this.data.currentPageIndex = pageIndex;
		this.data.rows = newRows;
		this.render();
		return false;
		/*
		2 rows per side
		1 pageindex.
		0 < 2
		
		*/
		
		/*this.setPage(pageIndex);
		this.render();*/
	},
	setPage: function(pageIndex) {
		var p = parseInt(pageIndex);
		if(p <= this.data.totalPages) {
			this.data.currentPageIndex = pageIndex;
		}
	}
}