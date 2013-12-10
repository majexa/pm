Ngn.GridResizerWrappers = new Class({
  Implements: [Options, Events],

  initialize: function(grid, options) {
    this.setOptions(options);
    this.grid = grid;
    this.initWrappers();
    this.initHanderls();
  },

  initWrappers: function() {
    this.grid.eParent.getElements('tr').each(function(eTr) {
      eTr.getChildren('td,th').each(function(el, n) {
        if (!n) return;
        var html = el.get('html');
        el.set('html', '');
        new Element('div', {
          html: '<div class="cont">' + html + '</div>',
          'class': 'wr',
          styles: {
            width: this.getWrWidth(n) + 'px'
          }
        }).inject(el);
      }.bind(this));
    }.bind(this));
  },

  initHanderls: function() {
    this.grid.eParent.getElement('tr').getChildren('th,td').each(function(el, n) {
      if (n < 2) return;
      var eHandler = new Element('div', {
        'class': 'handler'
      }).inject(el, 'top');
      new Ngn.GridColResizer(grid, n, eHandler);
    });
  },

  getWrWidth: function(n) {
    c('');
    return Ngn.storage.get('gridColWidth' + this.grid.options.id + n) || 150;
  }

});
