<?php
$nav->show();
?>
<h1>Updload</h1>
<form action="./index.php" method="post"  enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
    <label for="key">Encryption key:</label><input type="password" name="key">
    <input type="hidden" name="action" value="upload">
    <input type="submit" value="Upload File" name="upload">
</form>