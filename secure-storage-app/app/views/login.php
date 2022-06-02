<?php
$nav->show();
?>
<h1>Login or Register</h1>
<form action="./index.php" method="post">
    <label for="email">Email:</label><input type="text" name="email">
    <label for="password">Password:</label><input type="password" name="password">
    <input type="hidden" name="action" value="login">
    <input type="submit" value="Login or Register" name="login">
</form>