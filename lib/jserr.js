var page = require('webpage').create();
var args = require('system').args;
var fs = require('fs');
//console.log('The default user agent is ' + page.settings.userAgent);

page.open('http://' + args[2], function(status) {
  if (status !== 'success') {
    console.log('Unable to access network');
  } else {
    //var a = page.evaluate(function() {
    //  return document.getElementById('font1_dialog').textContent;
    //});
    //console.log(a);
  }
  phantom.exit();
});

page.onError = function(msg, trace) {
  fs.write(args[1], JSON.stringify([msg, trace]) + "\n", 'a');
};
