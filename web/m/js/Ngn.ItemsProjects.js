Ngn.ItemsProjects = new Class({
  Extends: Ngn.Grid,

  options: {
    isSorting: false
  },

  initToolActions: function() {
    this.parent();
    this.addBtnAction('.edit', function(id, eBtn, eItem) {
      new Ngn.Dialog.RequestForm({
        title: 'Редактирование проекта <b>' + eItem.get('data-domain') + '</b>',
        width: 500,
        url: Ngn.getPath(2) + '/json_edit?id=' + id,
        jsonSubmit: false,
        onSubmitSuccess: function() {
          eItem.removeClass('loading');
          window.location.reload();
        }
      });
    });
    this.addBtnAction('a.test', function(id) {
      new Ngn.Dialog.Loader.Request({
        title: 'Происходит копирование проекта',
        loaderUrl: Ngn.getPath(2) + '/ajax_copyToTest?id='+id,
        onLoaderComplete: function() {
          alert('Готово');
          windows.reload(true);
        }
      });
    });
    this.addBtnAction('a.versions', function(id, eBtn, eItem) {
      new Ngn.Dialog.Loader.Request({
        title: 'Происходит копирование проекта '+eItem.get('data-domain')+' в следующую версию',
        loaderUrl: Ngn.getPath(2) + '/ajax_copyToNextVersion?id='+id,
        onLoaderComplete: function() {
          alert('Готово');
          windows.reload(true);
        }
      });
    });
  }
  
});
