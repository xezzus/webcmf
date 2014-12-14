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
            var anticache = new Date().getTime();
            if(response.js[i] != undefined){
              var head = document.getElementsByTagName('html')[0];
              var elm = document.createElement('script');
              elm.src = response.js[i]+'?'+anticache;
              elm.type = 'text/javascript';
              head.appendChild(elm);
              elm.parentNode.removeChild(elm);
            }
            if(response.css[i] != undefined){
              var head = document.getElementsByTagName('head')[0];
              var elm = document.createElement('link');
              elm.href = response.css[i]+'?'+anticache;
              elm.type = 'text/css';
              elm.rel = 'stylesheet';
              elm.media = 'all';
              head.appendChild(elm);
              //elm.parentNode.removeChild(elm);
            }
          }
        }
        // global
        if(response.src.css != ''){
          var head = document.getElementsByTagName('head')[0];
          var elm = document.createElement('link');
          elm.href = '/index.css'+response.src.css+'&'+anticache;
          elm.type = 'text/css';
          elm.rel = 'stylesheet';
          elm.media = 'all';
          head.appendChild(elm);
          //elm.parentNode.removeChild(elm);
        }
        if(response.src.js != ''){
          var head = document.getElementsByTagName('html')[0];
          var elm = document.createElement('script');
          elm.src = '/index.js'+response.src.js+'&'+anticache;
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
