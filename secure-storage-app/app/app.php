<?php
class App
{
    const pages = [
        'home',
        'upload',
        'download',
        'login',
        'logout'
    ];

    public function route(){
        $get = $_GET;
        $post = $_POST;
        if(empty($get) && empty($post)){
            $get = [];
            $get['page'] = "home";
        }
        if($this->whitelist_get($get)){
            $this->get($get);
        }elseif($this->whitelist_post($post)){
            $this->post($post);
        }else{
            $this->not_found();
        }
    }

    public function whitelist_get($get) : bool{
        if($get === null){
            return false;
        }elseif (is_array($get) === false) {
            return false;
        }elseif (empty($get) === true) {
            return false;
        }elseif(isset($get['page']) === false){
            return false;
        }elseif(in_array($get['page'], App::pages) === false){
            return false;
        }
        return true;
    }

    public function whitelist_post($post) : bool{
        if($post === null){
            return false;
        }elseif (is_array($post) === false) {
            return false;
        }elseif (empty($post) === true) {
            return false;
        }elseif(isset($post['upload']) === false && isset($post['download']) === false && isset($post['login']) === false){
            return false;
        }
        return true;
    }

    public function not_found(){
        require_once('views/not_found.php');
    }

    public function get($get){
        if($get['page'] === 'logout'){
            unset($_SESSION['isLogged']);
            session_destroy();
            $get['page'] = 'home';
        }
        require_once('views/nav.php');
        $nav = new Nav();
        require_once('views/' . $get['page'] . '.php');
    }

    public function post($post){
        if(isset($post['action']) && $post['action'] === 'upload' && isset($post['key'])){
            $this->upload($post);
        }elseif(isset($post['action']) && $post['action'] === 'download' && isset($post['file']) && isset($post['key'])){
            $this->download($post);
        }elseif(isset($post['action']) && $post['action'] === 'login' && isset($post['email']) && isset($post['password'])){
            $this->login($post);
        }
    }

    public function upload($post){
        require_once('views/nav.php');
        $nav = new Nav();
        $target_dir = "uploads/" . $_SESSION['isLogged'] . "/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $key = $post['key'];
        $status = null;
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $status = "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                $status = "File is not an image.";
                $uploadOk = 0;
            }
        }
        
        // Check if file already exists
        if (file_exists($target_file)) {
            $status = "Sorry, file already exists.";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            $status = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $status = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $status = "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                //open original uploaded file
                $original = fopen('./' . $target_file, "r") or die("Unable to open file!");
                //read contents of uploaded file into $plaintext
                $plaintext = fread($original,filesize('./' . $target_file));
                fclose($original);
                $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
                $iv = openssl_random_pseudo_bytes($ivlen);
                $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
                $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
                $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
                //delete original uploaded file
                //write $ciphertext into new file and name it as the original uploaded file
                $encrypted = fopen('./' . $target_file, "w") or die("Unable to open file!");
                fwrite($encrypted, $ciphertext);
                fclose($encrypted);
                $status = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded and encrypted.";
            } else {
                $status = "Sorry, there was an error uploading your file.";
            }
        }
        require_once('views/home.php');
        if(isset($status)){
            echo $status;
        }
    }

    public function download($post){
        require_once('views/nav.php');
        $nav = new Nav();
        $key = $post['key'];
        $encrypted = fopen('uploads/' . $_SESSION['isLogged'] . "/" . $post['file'], "r") or die("Unable to open file!");
        $ciphertext = fread($encrypted,filesize('uploads/' . $_SESSION['isLogged'] . "/" . $post['file']));
        fclose($encrypted);
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);        
        require_once('views/home.php');        
        if (hash_equals($hmac, $calcmac))// timing attack safe comparison
        {
            echo '<img src="data:image/jpeg;base64,' . base64_encode($original) .'" alt="">';
        }else{
            echo 'Decryption failed. Are you sure you got the correct key?';
        }
    }

    public function login($post){
        $email = $post['email'];
        $password = $post['password'];      
        require_once('database/database.php');
        $db = new Database();
        $user = $db->FindUser($email, $password);
        if($user === null){
            $db->CreateUser($email, $password);
        }
        $user = $db->FindUser($email, $password);
        if($user !== null){
            $_SESSION['isLogged'] = $user['id'];
        }
        if(file_exists("./uploads/" . $_SESSION['isLogged']) === false){
            mkdir("./uploads/" . $_SESSION['isLogged']);
        }
        require_once('views/nav.php');
        $nav = new Nav();
        require_once('views/home.php');  
    }
}
