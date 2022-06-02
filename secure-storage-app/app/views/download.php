<?php 
$files = array_diff(scandir('./uploads/' . $_SESSION['isLogged'] . '/'), array('.', '..'));
$nav->show();
?>
<h1>Download</h1>
<form action="./index.php" method="post">
    <label for="file">Choose a file:</label>
    <select name="file">
        <?php
            foreach ($files as $key => $file) {
                echo '<option value="' . $file . '">' . $file . '</option>';
            }
        ?>
    </select>
    <input type="hidden" name="action" value="download">
    <label for="key">Encryption key:</label><input type="password" name="key">
    <input type="submit" value="Download File" name="download">
</form>