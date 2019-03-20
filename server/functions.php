<?php
function newIndex($connection){    // function for creating new 0/index.php
  $header = file_get_contents('/var/www/html/0/indexData/threadhead');  //creating new 0/index.php
  $index = fopen('/var/www/html/0/index.php', w);
  fwrite($index, $header);

  $threads = mysqli_query($connection, 'SELECT * FROM thread0 ORDER BY ptime DESC');

  while($thread = mysqli_fetch_row($threads)){
    $indNum = $thread[0];
    $indTime = $trhead[1];
    $indBody = $thread[2];
    $indPass = $thread[3];
    $indFile = $thread[4];
    $indCount = $thread[5];

    $headpost = "
     <div id='threadBlock'>
      <div id='threadHead'>
      <ins>Аноним #$indNum</ins>&#8195;$indTime
        <span>
          <button onclick='hideThread($indNum)'>Скрыть</button>
          <button onclick='showFullText($indNum)'>Full</button>
          <button onclick='threadOpen($indNum)'>В тред</button>
        </span>
      </div>

      <div class='threadBody' id='$indNum'>
        <button onclick='showImage(\"$indFile\", $indNum)'>

        <img src='../0/threads/$indNum/temp/thumbs/$indFile' alt=''>
        </button>

        <article>$indBody</article>

        </div>

        <div id='mobileBottom'>
        <div>Постов: $indCount</div>
        <button onclick='hideThread($indNum)'>Скрыть</button>
        <button onclick='showFullText($indNum)'>Full</button>
        <button onclick='threadOpen($indNum)'>В тред</button>
        </div>
        </div>
    ";
    fwrite($index, $headpost);

    if($indCount != 0){
        $myQuery = 'SELECT * FROM post0 WHERE threadnum='.$indNum.' ORDER BY ptime ASC LIMIT 3';
        $result = mysqli_query($connection, $myQuery);

        while($row = mysqli_fetch_row($result)){
            $postNum = $row[1];
            $indTime = $row[2];
            $indBody = $row[3];
            $indFile = $row[4];
            $headpost = "
             <div id='threadBlock' style='width:900px; max-width:90vw;' name='$indNum'>
              <div id='threadHead'>
              <ins>Аноним #$postNum</ins>&#8195;$indTime
              </div>

              <div class='threadBody' id='$postNum'>
                ";

              if($indFile != 'none'){
                $headpost .= "
                <button onclick='showImage(\"$indFile\", $indNum)'>
                <img src='../0/threads/$indNum/temp/thumbs/$indFile' alt=''>
                </button>";
              }

             $headpost .="
                <article>$indBody</article>
                </div>
              </div>
            ";
            fwrite($index, $headpost);
          }
      }
      fwrite($index, "<div style='border-bottom: solid 1px #328ae1;'></div>");
  }
  fclose($index);
}
/*****************************************************************/

/************ function for checking file type  *******************/
function f_type(){
  $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['threadImg']['tmp_name']);
    if( $mimeType == 'image/gif' ||
        $mimeType == 'image/jpeg'||
        $mimeType == 'image/pjpeg'||
        $mimeType == 'image/png')
      {
        $mimeType = substr($mimeType, 6);   // returning filetype
        return $mimeType;
      }
  else {return false;}
}
/***************************************************************/

/***********  function for processing thread uploadeds   *******/
function threadPic($num, $f_type){
  $picname = $num.'.'.$f_type;      // creating name for new file

  // moving new file into thread directory
  $fpath = '/var/www/html/0/threads/'.$num.'/temp/'.$picname;
  move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);

  // handling file with imagick
  $m_image = new Imagick($fpath);

  $imageWidth = $m_image->getImageWidth();
  $imageHeight = $m_image->getImageHeight();
  $m_image->thumbnailImage($imageWidth*0.7, $imageHeight*0.7);

  $wr_orig = $m_image->writeImage($$thumbWidthfpath);  // writing original image

  $coeff = 400/$imageWidth;
  $m_image->thumbnailImage($imageWidth*$coeff, $imageHeight*$coeff);
  $fpath = '/var/www/html/0/threads/'.$num.'/temp/thumbs/'.$picname;

  $wr_thumb = $m_image->writeImage($fpath);  // writing thumbnail

  $pic_output = array($wr_thumb, $wr_orig, $picname);
  return $pic_output;
}
/****************************************************************/

/***********  function for processing post uploadeds   *******/
function postPic($threadnum, $num, $f_type){
  $picname = $num.'.'.$f_type;      // creating name for new file

  // moving new file into thread directory
  $fpath = '/var/www/html/0/threads/'.$threadnum.'/temp/'.$picname;
  move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);

  // handling file with imagick
  $m_image = new Imagick($fpath);

  $imageWidth = $m_image->getImageWidth();
  $imageHeight = $m_image->getImageHeight();
  $m_image->thumbnailImage($imageWidth*0.7, $imageHeight*0.7);

  $wr_orig = $m_image->writeImage($$thumbWidthfpath);  // writing original image

  $coeff = 400/$imageWidth;
  $m_image->thumbnailImage($imageWidth*$coeff, $imageHeight*$coeff);
  $fpath = '/var/www/html/0/threads/'.$threadnum.'/temp/thumbs/'.$picname;

  $wr_thumb = $m_image->writeImage($fpath);  // writing thumbnail

  if (!$wr_thumb || !$wr_orig){              // if something went wrong deleting pics
    $rem = 'rm /var/www/html/0/threads/'.$threadnum.'/temp/'.$picname;          exec($rem);
    $rem = 'rm /var/www/html/0/threads'.$threadnum.'/temp/thumbs/'.$picname;    exec($rem);
    return false;
  }
  return $picname;
}
/****************************************************************/

/************   function for processing uploaded text   *********/
function textFormat(){    // Функция обработки отправленного текста
  $body = strip_tags($_POST['threadText']);   // Получаем текст и очищаем от html-тегов
  if (strlen($body) > 7500){$body = substr($body, 0, 7300);}  // Если слишком длинный - обрезаем

  // При помощи регулярных выражений ищем и поправляем разметку в тексте

  $pattern = '/>[а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+/u';    // Quote
  preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
  $arr = count($matches);
  for($x=0;$x!=$arr;++$x){
    $str = $matches[$x][0];
    $rep = '<quot>'.$str.'</quot>';
    $body=str_replace($str, $rep, $body);
  }

  $pattern = '/\[b\][а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+\[\/b\]/u';    // Жирный текст
  preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
  $arr = count($matches);
  for($x=0;$x!=$arr;++$x){
    $str = $matches[$x][0];
    $rep = '<b>'.$str.'</b>';
    $rep = str_replace('[b]', '', $rep);
    $rep = str_replace('[/b]', '', $rep);
    $body=str_replace($str, $rep, $body);
  }

  $pattern = '/\[i\][а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+\[\/i\]/u';    // Курсив
  preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
  $arr = count($matches);
  for($x=0;$x!=$arr;++$x){
    $str = $matches[$x][0];
    $rep = '<i>'.$str.'</i>';
    $rep = str_replace('[i]', '', $rep);
    $rep = str_replace('[/i]', '', $rep);
    $body=str_replace($str, $rep, $body);
  }

  $pattern = '/\[u\][а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+\[\/u\]/u';    // Подчеркнутый текст
  preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
  $arr = count($matches);
  for($x=0;$x!=$arr;++$x){
    $str = $matches[$x][0];
    $rep = '<ins>'.$str.'</ins>';
    $rep = str_replace('[u]', '', $rep);
    $rep = str_replace('[/u]', '', $rep);
    $body=str_replace($str, $rep, $body);
  }

    $pattern = '/\[del\][а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+\[\/del\]/u';    // Зачеркнутый текст
    preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
    $arr = count($matches);
    for($x=0;$x!=$arr;++$x){
      $str = $matches[$x][0];
      $rep = '<del>'.$str.'</del>';
      $rep = str_replace('[del]', '', $rep);
      $rep = str_replace('[/del]', '', $rep);
      $body=str_replace($str, $rep, $body);
    }

    $pattern = '/\[sp\][а-яёА-ЯЁa-zA-Z0-9?!\'.,:%\- ]+\[\/sp\]/u';    // Скрытый текст
    preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);
    $arr = count($matches);
    for($x=0;$x!=$arr;++$x){
      $str = $matches[$x][0];
      $rep = '<sp>'.$str.'</sp>';
      $rep = str_replace('[sp]', '', $rep);
      $rep = str_replace('[/sp]', '', $rep);
      $body=str_replace($str, $rep, $body);
    }

    return $body;
}
/*****************************************************************/

/*********** function for creating new thread index file  ********/
function newThread($num, $body, $file){
  $header = file_get_contents('/var/www/html/0/indexData/posthead');
  $path = '/var/www/html/0/threads/'.$num.'/index.php';
  $index = fopen($path, w);
  fwrite($index, $header);
  $header = "
      <input type='hidden' value='$num' name='threadnum'>
      </div>

      <?php 	// Putting captcha img path into html
        echo '<img src=\"'.\$_SESSION['captcha']['image_src'].'\">';
      ?>

      <div id='captchas'>
        <input type='text' placeholder='CAPTCHA' name='captcha'>
        <input type='button' value='Отправить' onclick='checkfilesize(this)'>
      </div>
    </form>

    </div>
    </div>

    <div></div>

    <div id='loading'>
    Loading
    </div>";
  fwrite($index, $header);

  $posttime = date('d.m.Y H:i:s');
  $headpost = "
   <div id='threadBlock'>
    <div id='threadHead'>
    <a name='>>$num'></a>
    <ins>Аноним #$num</ins>&#8195;$posttime
      <span>
      </span>
    </div>

    <div class='threadBody' id='$num' style='max-height:9000px !important;'>
      <button onclick='showImage(\"$file\", $num)'>
      <img src='../../../0/threads/$num/temp/thumbs/$file' alt=''>
      </button>

      <article style='max-height:9000px !important;'>$body</article>
    </div>

      </div>
    <div style='border-bottom: solid 1px #328ae1;'></div>";

  fwrite($index, $headpost);
  fclose($index);
}
/*****************************************************************/

/*********** function for adding new post into thread ************/
function newPost($num, $threadnum, $body, $picname){
  $posttime = date('d.m.Y H:i:s');
  $query = '../0/threads/'.$threadnum.'/index.php';
  $index = fopen($query, a);
  $headpost = "
   <div id='threadBlock' class='post'>
    <div id='threadHead'>
    <a name='>>$num'></a>
    <ins>Аноним #$num</ins>&#8195;$posttime
      <span>
      </span>
    </div>

    <div class='threadBody' id='$num'>  ";

    if($picname != 'none'){
      $headpost .= "<button onclick='showImage(\"$picname\", $num)'>
      <img src='../../../0/threads/$threadnum/temp/thumbs/$picname' alt=''>
      </button>";
    }

    $headpost .= "<article>$body</article>

      </div>
      </div>";

  fwrite($index, $headpost);
  fclose($index);
}
/*****************************************************************/

/*********** function for adding post into DB ********************/
function post_mysql($num, $body, $threadnum, $file){
  $connection = mysqli_connect('localhost', 'www', '2ch', 'threadbase');  // establishing connection with DB

  // Getting amount of posts in this thread
  $query = 'SELECT postcount FROM thread0 where num='.$threadnum;
  $postCount = mysqli_query($connection, $query);
  $postCount = mysqli_fetch_row($postCount);
  $postCount = $postCount[0];

  // If limit is exceeded exiting
  if($postCount > 249){ return false; }

  // Bumping thread
  $myQuery = 'UPDATE thread0 SET ptime=CURRENT_TIMESTAMP() WHERE num='.$threadnum;
  mysqli_query($connection, $myQuery);
  $myQuery = 'UPDATE thread0 SET postcount = postcount+1 WHERE num='.$threadnum;  // Увеличиваем счетчик постов треда
  mysqli_query($connection, $myQuery);

  // Adding new row into DB
  $myQuery = 'INSERT INTO post0 (threadnum, num, pbody, uplfile) VALUES ('.$threadnum.','.$num.',\''.$body.'\',\''.$file.'\')';
  mysqli_query($connection, $myQuery);

  return $connection;
}

/*****************************************************************/

/*********** function for adding thread into DB ******************/
function thread_mysql($num, $body, $pass, $file){
  $connection = mysqli_connect('localhost', 'www', '2ch', 'threadbase');  // establishing connection with DB

  // Getting amount of threads already created
  $threadCount = mysqli_query($connection, 'SELECT COUNT(*) FROM thread0');
  $threadCount = mysqli_fetch_row($threadCount);
  $threadCount = $threadCount[0];

  // If limit is exceeded deleting oldest thread
  if($threadCount > 49){
    $deleteThread = mysqli_query($connection, 'SELECT num FROM thread0 ORDER BY ptime ASC LIMIT 1');
    $deleteThread = mysqli_fetch_row($deleteThread);
    $deleteThreadNum = $deleteThread[0];
    $deleteThread = 'DELETE FROM thread0 where num='.$deleteThreadNum;
    mysqli_query($connection, $deleteThread);
    $deleteThread = 'DELETE FROM post0 WHERE threadnum='.$deleteThreadNum;
    mysqli_query($connection, $deleteThread);
    $deleteThread = 'rm -r /var/www/html/0/'.$deleteThreadNum;
    exec($deleteThread);
  }

  // Adding new row into DB
  $myQuery = 'INSERT INTO thread0 (num, pbody, ppass, postcount, uplfile) VALUES ('.$num.',\''.$body.'\','.$pass.', 0, \''.$file.'\')';
  mysqli_query($connection, $myQuery);

  return $connection;
}
?>
