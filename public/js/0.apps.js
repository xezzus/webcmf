function apps(cmd,callback){
  // act
  var act = {};
  if(typeof cmd == 'string'){ act[cmd] = null }
  else act = cmd;
  // xhr
  var xhr = new XMLHttpRequest;
  var accept = 'application/apps';
  xhr.open("POST", window.location.pathname, true);
  xhr.setRequestHeader('Accept',accept); // view/json, view/html
  xhr.send(JSON.stringify(act));
  xhr.onreadystatechange = function(){
    if (xhr.readyState == 4){
      var response = JSON.parse(xhr.responseText);
      callback(response);
    }
  }
}
