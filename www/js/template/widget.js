$p.Widget = function(template,container) {
	var newDate = new Date;
	this.guid = newDate.getTime();
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
	getPage: function(pageIndex) {
		this.setPage(pageIndex);
		this.render();
		return false;
	},
	setPage: function(pageIndex) {
		var start = this.data.totalRows*pageIndex;
		var end = ((start+this.data.rowsPerPage) > this.data.origTotalRows) ? this.data.origTotalRows : (start+this.data.rowsPerPage);
		var newRows = new Array();
		for(var i=start;i<end;i++) {
			newRows.push(this.rows[i]);
		}
		this.data.totalRows = newRows.length;
		this.data.currentPageIndex = parseInt(pageIndex);
		this.data.rows = newRows;
	},
	setSort: function() {
		// TODO: Make sorting...
	}
}