function $id(id){
  return document.getElementById(id);
}

function $form(name){
  var form = document.getElementsByTagName('form')[name];
  var input = form.getElementsByTagName('input');
  var data = {};
  for(i = 0; i<=input.length-1; i++){
    if(input[i].name != '' && (input[i].type == 'text' || input[i].type == 'password' || input[i].type == 'hidden')){
      data[input[i].name] = input[i].value;
    }
  }
  return data;
}
