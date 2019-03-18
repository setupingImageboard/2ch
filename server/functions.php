<?php
function imgScale($num){    // Function for processing uploaded images and videos
  $finfoRes = finfo_open(FILEINFO_MIME_TYPE);
  $mimeType = finfo_file($finfoRes, $_FILES['threadImg']['tmp_name']);
  $exifType = exif_imagetype($_FILES['threadImg']['tmp_name']);
  preg_match('/\.[a-zA-Z0-9]+/', $_FILES['threadImg']['name'], $matches);
  $uplfilename = $num.$matches[0];
  if ($_FILES['threadImg']['tmp_name']){    // saving img/video
    if ($mimeType == 'video/mp4'||$mimeType == 'video/webm'){
            if($_FILES['threadImg']['size'] < 3000000){
              $fpath = '/var/www/html/0/threads/'.$num.'/temp/'.$uplfilename;
              move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);
              //Creating thumbnail for video
              $thumbpath = '/var/www/html/0/threads/'.$num.'/temp/thumbs/'.$uplfilename.'.jpg';
              exec("ffmpeg -i $fpath -vframes 1 -an -ss 1 -noaccurate_seek -r 1 -vcodec mjpeg -f mjpeg $thumbpath");
            }
        }
    else if ($mimeType == 'image/gif'||$mimeType == 'image/jpeg'||$mimeType == 'image/pjpeg'||$mimeType == 'image/png'){
      if ($exifType != false){
        if($_FILES['threadImg']['size'] < 3000000){
          $fpath = '/var/www/html/0/threads/'.$num.'/temp/'.$uplfilename;
            move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);
            $m_image = new Imagick($fpath);
            $m_imWidth = $m_image->getImageWidth();
              $m_imHeight = $m_image->getImageHeight();
              $m_image->thumbnailImage($m_imWidth*0.7, $m_imHeight*0.7);
              $m_image->writeImage($fpath);
              $coeff = 400/$m_imWidth;
              $thumbHeight = $coeff * $m_imHeight;
              $thumbWidth = $coeff * $m_imWidth;
              $m_image->thumbnailImage($thumbWidth, $thumbHeight);
              $thumbname = '/var/www/html/0/threads/'.$num.'/temp/thumbs/'.$uplfilename;
              $m_image->writeImage($thumbname);
        }
      }
    }
    else {
      finfo_close($finfoRes);
      $output = array(
        'uplfilename' => 'none',
      );
      return $output;
    }
  }

  finfo_close($finfoRes);
  $output = array(
    'mimeType' => $mimeType,
    'uplfilename' => $uplfilename,
  );
  return $output;
}

function textFormat(){    // Функция обработки отправленного текста
  $body = strip_tags($_POST['threadText']);   // Получаем текст и очищаем от html-тегов
  if (strlen($body) < 10){header("Location: http://www.2ch.ge/0/threads/$threadnum");exit();}          // Если тред слишком короткий не печатаем его
  if (strlen($body) > 7500){$body = substr($body, 0, 7300);}  // Если слишком длинный - обрезаем

  // При помощи регулярных выражений ищем и поправляем разметку в тексте

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

function newfiles($num, $mimeType, $body, $uplfilename, $connection, $threadCount){   // Function for creating new thread files
  $headerData = file_get_contents('/var/www/html/0/indexData/threadhead');  //creating new 0/index.php
  $index0 = fopen('/var/www/html/0/index.php', w);
  $myQuery = 'SELECT * FROM thread0 ORDER BY ptime DESC';
  $result = mysqli_query($connection, $myQuery);
  fwrite($index0, $headerData);
    if($threadCount > 30){
      $counter = 0;
      for ($counter == 0; $counter != 30; ++$counter){
        $row = mysqli_fetch_row($result);
        $indNum = $row[0];
        $indTime = $row[1];
        $indBody = $row[2];
        $indPass = $row[3];
        $indFile = $row[4];
        $indCount = $row[5];

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
        fwrite($index0, $headpost);

        if($indCount != 0){
            $myQuery = 'SELECT * FROM post0 WHERE threadnum='.$indNum.' ORDER BY ptime ASC LIMIT 3';
            $result = mysqli_query($connection, $myQuery);
            while ($row = mysqli_fetch_row($result)){
              $postNum = $row[1];
              $indTime = $row[2];
              $indBody = $row[3];
              $indFile = $row[4];
              $headpost = "
               <div id='threadBlock' style='width:900px; max-width:90vw;'>
                <div id='threadHead'>
                <ins>Аноним #$postNum</ins>&#8195;$indTime
                </div>

                <div class='threadBody' id='$indNum'>
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
            }
          fwrite($index0, $headpost);
        }
      }

      $index2 = fopen('/var/www/html/0/index2.php', w);
      while($row = mysqli_fetch_row($result)){
        $indNum = $row[0];
        $indTime = $row[1];
        $indBody = $row[2];
        $indPass = $row[3];
        $indFile = $row[4];
        $indCount = $row[5];

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

        fwrite($index2, $headpost);

        if($indCount != 0){
            $myQuery = 'SELECT * FROM post0 WHERE threadnum='.$indNum.' ORDER BY ptime ASC LIMIT 3';
            $result = mysqli_query($connection, $myQuery);
            while ($row = mysqli_fetch_row($result)){
              $postNum = $row[1];
              $indTime = $row[2];
              $indBody = $row[3];
              $indFile = $row[4];
              $headpost = "
               <div id='threadBlock' style='width:900px; max-width:90vw;'>
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
            }
          fwrite($index2, $headpost);
        }
      }
        fclose($index2);
    }

  else{
    while($row = mysqli_fetch_row($result)){
      $indNum = $row[0];
      $indTime = $row[1];
      $indBody = $row[2];
      $indPass = $row[3];
      $indFile = $row[4];
      $indCount = $row[5];

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

      fwrite($index0, $headpost);

      if($indCount != 0){
          $myQuery = 'SELECT * FROM post0 WHERE threadnum='.$indNum.' ORDER BY ptime ASC LIMIT 3';
          $result = mysqli_query($connection, $myQuery);
          while ($row = mysqli_fetch_row($result)){
            $postNum = $row[1];
            $indTime = $row[2];
            $indBody = $row[3];
            $indFile = $row[4];
            $headpost = "
             <div id='threadBlock' style='width:900px; max-width:90vw;'>
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
            fwrite($index0, $headpost);
          }
      }
    }
  }
  fclose($index0);

  $headerData = file_get_contents('/var/www/html/0/indexData/posthead');
  $myQuery = '/var/www/html/0/threads/'.$num.'/index.php';
  $index1 = fopen($myQuery, w);
  fwrite($index1, $headerData);
  $txt = "<input type='hidden' value='$num' name='threadnum'>";
  fwrite($index1, $txt);
  $txt = "
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
  fwrite($index1, $txt);

  $posttime = date('d.m.Y H:i:s');
  $headpost = "
   <div id='threadBlock'>
    <div id='threadHead'>
    <a name='>>$num'></a>
    <ins>Аноним #$num</ins>&#8195;$posttime
      <span>
      </span>
    </div>

    <div class='threadBody' id='$num' style='max-height:9000px;'>
      <button onclick='showImage(\"$uplfilename\", $num)'>
      <img src='../../../0/threads/$num/temp/thumbs/$uplfilename' alt=''>
      </button>

      <article>$body</article>
    </div>

      </div>";

  fwrite($index1, $headpost);
  fclose($index1);
}

function newPost($num, $threadnum, $body, $uplfilename){
  $posttime = date('d.m.Y H:i:s');
  $myQuery = '../0/threads/'.$threadnum.'/index.php';
  $index = fopen($myQuery, a);
  $headpost = "
   <div id='threadBlock' class='post'>
    <div id='threadHead'>
    <a name='>>$num'></a>
    <ins>Аноним #$num</ins>&#8195;$posttime
      <span>
      </span>
    </div>

    <div class='threadBody' id='$num'>  ";

    if($uplfilename != 'none'){
      $headpost .= "<button onclick='showImage(\"$uplfilename\", $num)'>
      <img src='../../../0/threads/$threadnum/temp/thumbs/$uplfilename' alt=''>
      </button>";
    }

    $headpost .= "<article>$body</article>

      </div>
      </div>";

  fwrite($index, $headpost);
  fclose($index);
}

function postImgScale($num, $threadnum){
  $finfoRes = finfo_open(FILEINFO_MIME_TYPE);
  $mimeType = finfo_file($finfoRes, $_FILES['threadImg']['tmp_name']);
  $exifType = exif_imagetype($_FILES['threadImg']['tmp_name']);
  preg_match('/\.[a-zA-Z0-9]+/', $_FILES['threadImg']['name'], $matches);
  $uplfilename = $num.$matches[0];
  if ($_FILES['threadImg']['tmp_name']){    // saving img/video
    if ($mimeType == 'video/mp4'||$mimeType == 'video/webm'){
            if($_FILES['threadImg']['size'] < 3000000){
              $fpath = '/var/www/html/0/threads/'.$threadnum.'/temp/'.$uplfilename;
              move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);
              //Creating thumbnail for video
              $thumbpath = '/var/www/html/0/threads/'.$threadnum.'/temp/thumbs/'.$uplfilename.'.jpg';
              exec("ffmpeg -i $fpath -vframes 1 -an -ss 1 -noaccurate_seek -r 1 -vcodec mjpeg -f mjpeg $thumbpath");
            }
        }
        else if ($mimeType == 'image/gif'||$mimeType == 'image/jpeg'||$mimeType == 'image/pjpeg'||$mimeType == 'image/png'){
          if ($exifType != false){
            if($_FILES['threadImg']['size'] < 3000000){
              $fpath = '/var/www/html/0/threads/'.$threadnum.'/temp/'.$uplfilename;
                move_uploaded_file($_FILES['threadImg']['tmp_name'], $fpath);
                $m_image = new Imagick($fpath);
                $m_imWidth = $m_image->getImageWidth();
                  $m_imHeight = $m_image->getImageHeight();
                  $m_image->thumbnailImage($m_imWidth*0.7, $m_imHeight*0.7);
                  $m_image->writeImage($fpath);
                  $coeff = 400/$m_imWidth;
                  $thumbHeight = $coeff * $m_imHeight;
                  $thumbWidth = $coeff * $m_imWidth;
                  $m_image->thumbnailImage($thumbWidth, $thumbHeight);
                  $thumbname = '/var/www/html/0/threads/'.$threadnum.'/temp/thumbs/'.$uplfilename;
                  $m_image->writeImage($thumbname);
            }
          }
        }

        else{
            finfo_close($finfoRes);
            $output = array('uplfilename' => 'none',);
            return $output;
        }
      }

      else{
          finfo_close($finfoRes);
          $output = array('uplfilename' => 'none',);
          return $output;
      }

      finfo_close($finfoRes);
      $output = array(
        'mimeType' => $mimeType,
        'uplfilename' => $uplfilename,
      );
      return $output;
}
?>
