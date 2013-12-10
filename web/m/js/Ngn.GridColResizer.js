Ngn.GridColResizer = new Class({

  initialize: function(grid, colN, eHandler) {
    this.grid = grid;
    this.colN = colN;
    var resizer = this;
    this.drag = new Drag(new Element('div'), {
      handle: eHandler,
      onStart: function(el, e) {
        this.startPosition = new Event(e).page.x;
        this.startW = this.getElements()[0].getSize().x;
      }.bind(this),
      onDrag: function(el, e) {
        var delta = this.startPosition - new Event(e).page.x;
        var els = this.getElements();
        for (var i = 0; i < els.length; i++) {
          els[i].setStyle('width', (this.startW - delta) + 'px');
        }
      }.bind(this),
      onComplete: function() {
        Ngn.storage.set('gridColWidth' + this.grid.options.id + this.colN, this.getElements()[0].getStyle('width'));
      }.bind(this),
      snap: 0
    });
  },
  getElements: function() {
    var els = [];
    this.grid.eParent.getElements('tr').each(function(eTr) {
      eTr.getElements('td,th').each(function(el, n) {
        var offset = el.get('tag') == 'th' ? 1 : 0;
        if (n == this.colN - offset) els.push(el.getElement('.wr'));
      }.bind(this));
    }.bind(this));
    return els;
  }

});
