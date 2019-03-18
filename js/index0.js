var postFormStatus = false;  // Переменная для хранения состояния формы постинга
function postFormWrap(){     // Показать/скрыть форму постинга
  if(postFormStatus == false){
    document.getElementById('postDiv').style.display='block';
    postFormStatus = true;
    var psswd = document.getElementById("pppp");      // Adding random password
    psswd.value =  Math.floor(Math.random() * (99999999 - 999999 + 1)) + 999999;}
  else {
    document.getElementById('postDiv').style.display='none';
    postFormStatus = false;}
}

/******* Функция расставления разметки кнопками **********/

function textFormatting(button){
 let textarea = document.getElementsByName('threadText')[0]; // Получаем объект textarea, рядом с которым была нажата кнопка
 let startPos = textarea.selectionStart; // Начальная позиция выделения
 let endPos = textarea.selectionEnd; // Конечная позиция выделения

  if(startPos == endPos){
   var startMark = '['+button.getAttribute('data-mark')+'] '; // Формируем открывающий тег
   var endMark = '[/'+button.getAttribute('data-mark')+']';} // Закрывающий тег
  else{
  var startMark = '['+button.getAttribute('data-mark')+']'; // Формируем открывающий тег
  var endMark = '[/'+button.getAttribute('data-mark')+']'; }// Закрывающий тег

 // Заменяем содержимое, если ничего не выделено - просто добавляем теги
 textarea.value = textarea.value.substring(0, startPos) + startMark + textarea.value.substring(startPos, endPos) + endMark + textarea.value.substring(endPos, textarea.value.length);
 textarea.focus();
}

/********   Функция создания треда    *********/
function checkfilesize(butt){   // Submit function
  var file = document.getElementById('uplFile').files[0];
  if (typeof file !== 'undefined'){
    if (file.size > 3000000) alert('Слишком тяжело,  не могу загрузить');
    else {
      var shown = false;
      document.getElementById('thread').submit();
      document.getElementById('postDiv').style.display = 'none';
      postFormStatus = false;
      var loadDiv = document.getElementById('loading');
      setInterval(function(){
        if (shown == false){
          loadDiv.style.display = 'block';
          shown = true;}
        else {
          loadDiv.style.display = 'none';
          shown = false;
        }
      }, 1000);
    }
  }
}

/*********  Функция скрытия треда   ********/
function hideThread(id) {
  var cookies = document.cookie;    // Получаем куки
  cookies = cookies.toString();
  var cookieStr = id + 'hidden=';
  var ind = cookies.lastIndexOf(cookieStr); // Проверяем, есть ли уже записи

  if (ind > -1){                  // Если есть
    if (cookies[ind+14] == 1){    // Если тред скрыт - показываем
      document.getElementById(id).style.display = 'block';
      document.cookie = cookieStr + '0';
    }
    else{
      document.getElementById(id).style.display = 'none';
      document.cookie = cookieStr + '1';
    }
    return;
  }
  // Если кнопка нажимается в первый раз - скрываем и записываем куки
  else{
    document.getElementById(id).style.display = 'none';
    document.cookie = cookieStr + '1';
  }
}


/********  Функция перехода в тред    ****************/
function threadOpen(id){
  id = '../0/threads/'+id;
  window.open(id);
}

/********   Функция открытия треда в полный размер    *************/
var fullThreadText = false;      // Переменная для хранения текущего состояния текста
function showFullText(id){
  if (fullThreadText){
    var thread = document.getElementById(id);
    thread.style.maxHeight = '200px';
    var article = thread.childNodes;
    article = article[3];
    article.style.maxHeight = '160px';
    fullThreadText = false;
    return;
  }
  var thread = document.getElementById(id);
  thread.style.maxHeight = '9000px'
  var article = thread.childNodes;
  article = article[3];
  article.style.maxHeight = '9000px';
  fullThreadText = true;
}


/**********  Функция открытия картинки в полном размере     **********/
function showImage(id, thread) {
      var imgDiv = document.createElement('DIV');   // Создаем новые элементы
      var img = document.createElement('IMG');

      imgDiv.appendChild(img);  // Задаем img дочерним элементом divImg
      // Задаем всё это дочерним элементом BODY
      document.getElementById('nullBody').appendChild(imgDiv);

      // Описываем стили блока и изображения
      var styles = 'position:fixed; max-height:90vh; max-width:90vw; top:50%; left:50%; transform:translate(-50%, -50%); background-color:#353b41; border:solid 1px #328ae1; padding: 4px';
      imgDiv.setAttribute('style', styles);
      img.style.maxWidth = '88vw';
      img.style.maxHeight = '88vh';
      img.style.display = 'block';
      img.style.border = 'solid 1px #328ae1';

      imgDiv.id = id + 'img';   // Присваиваем блоку ID
      img.src = '../0/threads/' + thread + '/temp/' + id;   // Задаем источник изображения для нового блока


      // По первому клику скрываем все это
      imgDiv.onclick = function(){
        imgDiv.style.display = 'none';
      };
}
