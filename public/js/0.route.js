function route(callback){
  var obj = {};
  if(typeof callback == 'function') {
    window.route.callback = callback;
  }
  // работа с ссылками
  obj.getlink = function(callback){
    var get = document.getElementsByTagName('A');
    for(var i in get){
      var tag = get[i];
      if(tag.href == undefined) continue;
      tag.onclick = function(e){ if(this.target != ''){ return true; } obj.load(this); if(typeof window.route.callback == 'function') window.route.callback(e); return false; }
    }
  }
  // Работа с блоками
  obj.load = function(target){
    // url to address bar
    history.pushState({},'title',target.href);
    // get views
    var get = document.getElementsByTagName('div');
    var act = {};
    var view = {};
    for(i in get){
      var tag = get[i];
      if(tag.dataset == undefined || tag.dataset.view == undefined) continue;
      view[tag.dataset.view] = tag;
      if(target.dataset == undefined) target.dataset = {};
      if(target.dataset.viewLock != tag.dataset.view) act[tag.dataset.view] = null;
    }
    // xhr
    var xhr = new XMLHttpRequest;
    var open = window.location.pathname;
    var accept = 'application/view';
    xhr.open("POST", open, true);
    xhr.setRequestHeader('Accept',accept); // view/json, view/html
    xhr.send(JSON.stringify(act));
    xhr.onreadystatechange = function(){            
      if (xhr.readyState == 4){ 
        var response = JSON.parse(xhr.responseText);
        for(i in response.view){ 
          if(target.dataset.viewLock != i) {
            view[i].innerHTML = response.view[i];
            response.css.view.push(i);
            response.js.view.push(i);
          }
        }
        // anticache
        var anticache = new Date().getTime();
        // include css
        if(response.css != ''){
          var head = document.getElementsByTagName('head')[0];
          var elm = document.createElement('link');
          var cssapps = response.css.apps.join(',');
          var cssview = response.css.view.join(',');
          elm.href = '/index.css?apps='+cssapps+'&view='+cssview+'&local&'+anticache;
          elm.type = 'text/css';
          elm.rel = 'stylesheet';
          elm.media = 'all';
          head.appendChild(elm);
          //elm.parentNode.removeChild(elm);
        }
        // include js
        if(response.js != ''){
          var head = document.getElementsByTagName('html')[0];
          var elm = document.createElement('script');
          var jsapps = response.js.apps.join(',');
          var jsview = response.js.view.join(',');
          elm.src = '/index.js?apps='+jsapps+'&view='+jsview+'&local&'+anticache;
          elm.type = 'text/javascript';
          head.appendChild(elm);
          //elm.parentNode.removeChild(elm);
        }
        // start
        obj.getlink();
      }
    }
  }
  // start
  obj.getlink();
  return obj;
}
