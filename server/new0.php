<?php
  include ('functions.php');
  session_start();

  if($_POST['captcha'] != $_SESSION['captcha']['code']){  // Checking captcha
    header("Location: http://www.2ch.ge/0/");
    exit();
  }

  if(!$_FILES['threadImg']['tmp_name']) {                 // Checking if pic is uploaded
    header("Location: http://www.2ch.ge/0/");
    exit();
  }

  $num = mt_rand(1000000, 9999999);     // Получаем случайное семизначное число для номера треда
  $body = textFormat();                 // получаем обработанный текст
  $pass = $_POST['pass'];

  $connection = mysqli_connect('host', 'name', 'passwd', 'database');   // Подключаемся к базе данных
  $myQuery = 'SELECT COUNT(*) FROM thread0 ORDER BY TIMESTAMP ASC';
  $threadCount = mysqli_query($connection, $myQuery);         // Узнаем количество записей
  $threadCount = mysqli_fetch_row($threadCount);

  if ($threadCount[0] > 49){       // Если лимит записей исчерпан, удаляем одну, самую старую
    $myQuery = 'SELECT * FROM thread0 ORDER BY ptime ASC LIMIT 1';  // Getting the oldest thread
    $deleteRow = mysqli_query($connection, $myQuery);
    $deleteRow = mysqli_fetch_row($deleteRow);
    $myQuery = 'DELETE FROM thread0 WHERE num='.$deleteRow[0];
    $deleteRow = mysqli_query($connection, $myQuery);   // Deleting the oldest thread/
    $myQuery = 'DELETE FROM post0 WHERE threadnum='.$deleteRow[0];
    $deleteRow = mysqli_query($connection, $myQuery);
    $myQuery = 'rm -r /var/www/html/0/'.$deleteRow[0];
    exec($myQuery);
  }

  $myQuery = 'INSERT INTO thread0 (num, pbody, ppass, postcount) VALUES ('.$num.',\''.$body.'\','.$pass.', 0)';
  mysqli_query($connection, $myQuery);  // Добавляем запись


  $myQuery = '/var/www/html/0/threads/'.$num; // Создаем папку для нового треда
  $oldmask = umask(0);
  mkdir($myQuery,0777);
  umask($oldmask);

  mkdir($myQuery.'/temp');          // Создаем папки для временных файлов
  mkdir($myQuery.'/temp/thumbs');

  $output = imgScale($num);   // Обрабатываем загруженные пикчи
  $myQuery = 'UPDATE thread0 SET uplfile="'.$output['uplfilename'].'" WHERE num='.$num;
  mysqli_query($connection, $myQuery);  // adding uplfile into mysql table

  newfiles($num, $output['mimeType'], $body, $output['uplfilename'], $connection, $threadCount[0]);   // Creating new index files

  mysqli_close($connection);  // Закрываем соединени
  header("Location: http://www.2ch.ge/0/threads/$num");
?>
