<?php
class Nav
{
    public function show(){
        echo "<a href='./index.php?page=home'>Home</a>";
        if(isset($_SESSION['isLogged']) === false){
            echo "<a href='./index.php?page=login'>Login or Register</a>";
        }
        else{
            echo "<a href='./index.php?page=logout'>Logout</a>";
            echo "<a href='./index.php?page=upload'>Upload</a>";
            echo "<a href='./index.php?page=download'>Download</a>";
        }
    }
}
