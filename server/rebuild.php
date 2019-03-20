<?php
  // set chown root:root and chmod 700 before use
    echo 'Rebuilding...';
    include('functions.php');
    $connection = mysqli_connect('localhost', 'www', '2ch', 'threadbase');
    newIndex($connection);
    mysqli_close($connection);
    header('Location: http://2ch.ge/0');
?>
