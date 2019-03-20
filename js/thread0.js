var postFormStatus = false;  // Переменная для хранения состояния формы постинга
function postFormWrap(){     // Показать/скрыть форму постинга
  if(postFormStatus == false){
    document.getElementById('postDiv').style.display='block';
    postFormStatus = true;
  }
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

/********   Функция создания поста    *********/
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

  else document.getElementById('thread').submit();
}


/**********  Функция открытия картинки в полном размере     **********/
var isOpen = false;  // Variable for checking, if there is any img opened
function showImage(id, post) {
      if (isOpen){
        var opened = document.getElementById('currentImgOpened');
        opened.parentElement.removeChild(opened);
      }

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

      imgDiv.id = 'currentImgOpened';   // Присваиваем блоку ID
      img.src = './temp/' + id;   // Задаем источник изображения для нового блока


      // Deleting imgDiv onclick
      imgDiv.onclick = function(){
        imgDiv.parentElement.removeChild(imgDiv);
        isOpen = false;
      };
}
