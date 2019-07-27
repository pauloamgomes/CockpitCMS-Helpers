App.Utils.renderer.collectionselect = function(v, field) {
  if (v.display && v.link && v._id) {
    var url = App.route('/collections/entry/' + v.link + '/' + v._id);
    return '<a target="_blank" class="uk-text-small" href="' + url + '"><i class="uk-icon-link uk-text-muted"></i> ' + v.display + '</a>';
  }

  return v.display || "n/a";
};
