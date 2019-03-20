<?php
  include ('functions.php');
  session_start();

  /*********  initialising variables *************************/
    $num = mt_rand(1000000, 9999999);       // Number of thread
    $body = textFormat();                   // Text of thread
    $threadnum = $_POST['threadnum'];       // Number of parent thread
    # $f_type = type of uploaded pic
  /**********************************************************/

  /********** checking data for new post  ******************/
    if($_POST['captcha'] != $_SESSION['captcha']['code']){  // Checking captcha
      header("Location: http://www.2ch.ge/0/$threadnum");   // If invalid reloading page
      exit();                                               // and exiting
    }

    if($_FILES['threadImg']['tmp_name']) {                   // If file is uploaded
      $f_type = f_type();                                   // checking filetype
      if(!$f_type){                                         // If filetype is invalid
        header("Location: http://www.2ch.ge/0/$threadnum"); // Reloading page and exiting
        exit();
      }
    }

    if (strlen($_POST['threadText']) < 5){                        // If text of post is too short
      header("Location: http://www.2ch.ge/0/threads/$threadnum"); // reload page and exit
      exit();
    }
  /***********************************************************/

  /*********  handling uploaded pic  ***********************/
    if(isset($f_type)){
      $picname = postPic($threadnum, $num, $f_type);
      if(!$picname){                                                 // if something went wrong
        header("Location: http://www.2ch.ge/0/threads/$threadnum");  // reload page and exit
        exit();
      }
    }
    else $picname = 'none';
  /*********************************************************/

  $connection = post_mysql($num, $body, $threadnum, $picname); // adding new post into DB
  if(!$connection){                                              // if something went wrong
    header("Location: http://www.2ch.ge/0/threads/$threadnum");  // reload page and exit
    exit();
  }

  newPost($num, $threadnum, $body, $picname);   // appending the thread file
  mysqli_close($connection);

  header("Location: http://www.2ch.ge/0/threads/$threadnum");
?>
