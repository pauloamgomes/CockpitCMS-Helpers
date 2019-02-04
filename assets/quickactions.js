var $this = this;

window.setTimeout(function() {
  App.$('.app-modulesbar').append(App.$('<li><cp-quickactions /></li>').addClass('quickactions'));
  riot.mount('cp-quickactions', {});
}, 500);
