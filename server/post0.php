<?php
  include ('functions.php');
  session_start();

  $threadnum = $_POST['threadnum'];

  if($_POST['captcha'] != $_SESSION['captcha']['code']){  // Checking captcha
    header("Location: http://www.2ch.ge/0/threads/$threadnum");
    exit();
  }

  $num = mt_rand(1000000, 9999999);     // Получаем случайное семизначное число для номера треда
  $body = textFormat();                 // получаем обработанный текст
  $connection = mysqli_connect('localhost', 'www', '2ch', 'threadbase');   // Подключаемся к базе данных

  $myQuery = 'SELECT COUNT(*) FROM post0 WHERE threadnum='.$threadnum;
  $result = mysqli_query($connection, $myQuery);
  $count = mysqli_fetch_row($result);
  if($count[0] > 250){    // Если количество постов в треде больше 250 - постинг запрещен
    header("Location: http://www.2ch.ge/0/threads/$threadnum");
    exit();
  }
  $myQuery = 'UPDATE thread0 SET ptime=CURRENT_TIMESTAMP() WHERE num='.$threadnum;  // Бампаем тред
  mysqli_query($connection, $myQuery);
  $myQuery = 'UPDATE thread0 SET postcount = postcount+1 WHERE num='.$threadnum;  // Увеличиваем счетчик постов треда
  mysqli_query($connection, $myQuery);
  
  $output = postImgScale($num, $threadnum);   // processing uploaded files

  // adding post into database
  $myQuery = 'INSERT INTO post0(threadnum, num, pbody, uplfile) VALUES('.$threadnum.', '.$num.', "'.$body.'", "'.$output['uplfilename'].'")';
  mysqli_query($connection, $myQuery);

  newPost($num, $threadnum, $body, $output['uplfilename']);   // appending the thread file

  header("Location: http://www.2ch.ge/0/threads/$threadnum");
?>
