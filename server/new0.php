<?php
  include ('functions.php');
  session_start();

/********** checking data for new thread  *************/
  if($_POST['captcha'] != $_SESSION['captcha']['code']){  // Checking captcha
    header("Location: http://www.2ch.ge/0/");             // If invalid reloading page
    exit();                                               // and exiting
  }

  if(!$_FILES['threadImg']['tmp_name']) {                 // If file is not uploaded
    header("Location: http://www.2ch.ge/0/");             // Reloading page and exiting
    exit();
  }
  else {                                                  // Else checking filetype
    $f_type = f_type();
    if(!$f_type){                                         // If filetype is invalid
      header("Location: http://www.2ch.ge/0/");           // Reloading page and exiting
      exit();
    }
  }

  if (strlen($_POST['threadText']) < 5){                            // If text of post is too short
    header("Location: http://www.2ch.ge/0/threads/$threadnum");     // reload page and exit
    exit();
  }
/***********************************************************/

/*********  initialising variables *************************/
  $num = mt_rand(1000000, 9999999);       // Number of thread
  $body = textFormat();                   // Text of thread
  $pass = $_POST['pass'];                 // Password of thread
  # $f_type = type of uploaded pic
/**********************************************************/

/*********  creating directories for new thread ***********/
  $myQuery = '/var/www/html/0/threads/'.$num;   // Creating dir for new thread
  $oldmask = umask(0);
  mkdir($myQuery, 0766);
  umask($oldmask);

  mkdir($myQuery.'/temp', 0766);          // Creating dir for pics
  mkdir($myQuery.'/temp/thumbs', 0766);   // Creating dir for thumbnails
/********************************************************/

/*********  handling uploaded pic  ***********************/
  $output = threadPic($num, $f_type);
  if (!$output[0] || !$output[1]){              // if something went wrong
    $myQuery = 'rm -r /var/www/html/0/threads/'.$num;
    exec($myQuery);                             // deleting thread
    header("Location: http://www.2ch.ge/0/");   // reloading page
    exit();                                     // and exiting
  }
/*********************************************************/

$connection = thread_mysql($num, $body, $pass, $output[2]);   // adding new thread into DB
newThread($num, $body, $output[2]);                           // creating index file for new thread
newIndex($connection);                                        // creating new 0/index.php

mysqli_close($connection);
header("Location: http://www.2ch.ge/0/threads/$num");
?>
