// WINDOW UUID
window.uuid = new Date().getTime();

// CARET
var caret = {
  get:function(){
    range= window.getSelection().getRangeAt(0);
    start = range.startOffset;
    end = range.endOffset;
    return {start:start,end:end};
  },
  set:function(element,pos){
    var range = document.createRange();
    var sel = window.getSelection();
    if(pos == 'start') { range.setStart(element, 0); }
    else if(pos == 'end') { range.setStart(element, 1); }
    else { range.setStart(elelement.childNodes[0], pos); }
    range.collapse(true);
    sel.removeAllRanges();
    sel.addRange(range);
  }
}

// MICROTIME
function microtime(get_as_float) {
  var now = new Date().getTime() / 1000;
  var s = parseInt(now, 10);
  return (get_as_float) ? now : (Math.round((now - s) * 1000) / 1000) + ' ' + s;
}

// COOKIE
var cookie = {
  set:function(name,value,sec){ 
    var exdate=new Date(); 
    var sec=exdate.getSeconds()+sec; 
    exdate.setSeconds(sec); 
    document.cookie=name+ "=" +escape(value)+((sec==null) ? "" : ";expires="+exdate.toGMTString()+"; path=/;");
  },
  get:function(name){ 
    var start=document.cookie;
    var start = start.split(";");
    for(var key in start){
      var check = start[key].split('=');
      check[0] = check[0].replace(" ","");
      if(name==check[0]) {
        return unescape(check[1]);
      }
    }
    return '';
  },
  del:function(name){
    document.cookie = name+"=''; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;";
  }
}

// URL
//<scheme name> : <path> [ ? <query> ] [ # <fragment> ]
var url = {
  path:function(num,match){ 
    var tmp = window.location.pathname.split('.')[0]; 
    var tmp = tmp.split('/');
    var newtmp = [];
    var x = 0;
    for(i in tmp){
      if(tmp[i] == '') { continue; }
      newtmp[x] = tmp[i];
      x++;
    }
    delete tmp;
    if(typeof num == 'number'){ 
      var newtmp = newtmp[num]; 
    } else {
      var newtmp = newtmp.join('/');
    }
    if(newtmp == undefined) { var newtmp = ''; }
    if(match !== undefined){ var newtmp = newtmp.match(match); }
    return newtmp;
  },
  query:function(){
    var GET = location.search;
    var GET = GET.split('?');
    var GET = GET[1];
    var obj = {};
    if(GET != undefined){
      var GET = GET.replace(/&/,'&amp;').split('&amp;');
      for(i in GET){
        var get = GET[i].split('=');
        if(get[1] == undefined) get[1] = '';
        obj[get[0]] = get[1];
      }
    }
    return obj;
  },
  fragment:function(){
    var tmp = window.location.hash;
    if(tmp != ''){
      var tmp = tmp.split('#');
      var tmp = tmp[1];
    }
    return tmp;
  }
}

// DATE
function date(time){
  var time = parseInt(time)*1000;
  if(isNaN(time)) var d = new Date();
  else var d = new Date(time);
  this.y = d.getFullYear();
  this.m = ('0'+(d.getMonth()-0+1)).substr(-2,2);
  this.d = ('0'+d.getDate()).substr(-2,2);
  this.h = ('0'+d.getHours()).substr(-2,2);
  this.i = ('0'+d.getMinutes()).substr(-2,2);
  this.s = ('0'+d.getSeconds()).substr(-2,2);
};

// VIEW
function view(url,callback){
  var xhr = new XMLHttpRequest;
  xhr.open("GET", url, true);
  xhr.setRequestHeader('Content-View','true');
  xhr.send();
  xhr.onreadystatechange = function(){            
    if (xhr.readyState == 4){ 
      callback(xhr.responseText);
    }
  };
}

// WEBSOCKET
var ws = {
  url:'localhost',
  act:{},
  open:function(){
    var $this = this;
    $this.webSocket = new WebSocket('ws://'+this.url);
    $this.webSocket.onclose = function(){
    };
    $this.webSocket.onmessage = function(ev){
      var response = JSON.parse(ev.data);
        if(typeof response != 'object') { var response = {}; } 
        if(('act' in response) === true) var response = [response];
        for(i in response){
          var line = response[i];
          var act = line.act;
          if(typeof act == 'object' && 'name' in act) act = act.name;
          delete line.act;
          var fun = $this.act[act];
          if(act in $this.act && typeof fun  == 'function') fun(line);
        }
    }
    $this.webSocket.onopen = function(){
      var session = cookie.get('session');
      if(session != '') $this.send({act:'session',session:session});
    }
  },
  send:function(data){
    var $this = this;
    if(typeof data == 'string') var data = JSON.parse(data);
    if((0 in data) == false) { var data = [data]; }
    if(this.webSocket.readyState == 1) { 
      this.webSocket.send(JSON.stringify(data)); 
    } 
  }
}

// API
var api = {
  url:'/',
  act:{},
  xhr:{},
  send:function(data,name){
    var $this = this;
    var stamp = new Date().getTime();
    if(typeof data == 'string') var data = JSON.parse(data);
    if((0 in data) === false) { var data = [data]; }
    $this.xhr[stamp] = new XMLHttpRequest;
    $this.xhr[stamp].open("POST", $this.url, true);
    $this.xhr[stamp].setRequestHeader('Content-Language',cookie.get('session')+''+window.uuid);
    $this.xhr[stamp].send(JSON.stringify(data));
    $this.xhr[stamp].onreadystatechange = function(){            
      if ($this.xhr[stamp].readyState == 4){ 
        var response = JSON.parse($this.xhr[stamp].responseText);
        if(typeof response != 'object') { var response = {}; } 
        if('act' in response) var response = [response];
        for(i in response){
          var line = response[i];
          var act = line.act;
          if(typeof act == 'object' && 'name' in act) act = act.name;
          delete line.act;
          var fun = $this.act[act];
          if(act in $this.act && typeof fun  == 'function') fun(line);
        }
      }
    }
  }
}
