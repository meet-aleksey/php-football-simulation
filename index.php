<!DOCTYPE html>
<html>
  <head>
    <title>Football simulation</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="styles.css" />
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="client.js"></script>
  </head>
  <body>
    <?php
      if (file_exists('data.csv')) {
        echo '<input id="btnUseExample" type="button" value="Use included data" class="btn btn-primary" /> ';
      }
    ?>
    <input id="btnUploadOwnFile" type="button" value="Upload own data file" class="btn btn-primary" />
    <input id="btnStart" type="button" value="Simulate" class="btn btn-primary" />
    <input id="uploadFile" type="file" accept=".csv" class="hidden" />

    <div id="messages"></div>
    <div id="teams"></div>
    <div id="simulation"></div>
  </body>
</html>
