<?php
use App\Autoloader;
use App\User;
use App\Router;
use App\App;
use App\Db;
use App\Image;
use App\Comment;
use App\Like;

session_start();

//Set the autoloader
require 'app/autoloader.class.php';
Autoloader::register();

Router::post('/sign', function() {
	if (isset($_SESSION['username'])){
		echo 'Already logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$user = new User($db);
	echo $user->register($_POST);
});
Router::post('/login', function() {
	if (isset($_SESSION['username'])){
		echo 'Already logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$user = new User($db);
	echo $user->login($_POST);
});
Router::get('/logout', function() {
	if (isset($_SESSION['username'])){
		$user = new User(null);
		$user->logout();
	}
	header('Location: http://localhost:8080/login');
});
Router::post('/upload', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	if (!is_writable('./public/upload/')){
		echo "Permission denied";
		return ;
	}
	if (!$_POST["data"] || !$_POST["filter"]){
		echo "Wrong data sent";
		return ;
	}
	$_POST["filter"] = json_decode($_POST["filter"]);
	$image = new Image($db);
	echo $image->upload($_POST);
});
Router::post('/upload_file', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	if (!is_writable('./public/upload/')){
		echo "Permission denied";
		return ;
	}
	if (!$_POST["data"]){
		echo "Wrong data sent";
		return ;
	}
	$image = new Image($db);
	echo $image->upload($_POST);
});
Router::post('/delete_photo', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	if (!is_writable('./public/upload/') || !is_writable('./public/upload/'.$_POST["name"])){
		echo "Permission denied";
		return ;
	}
	$image = new Image($db);
	echo $image->delete($_POST["name"], $_SESSION["id"]);
});
Router::post('/delete_comments', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	$comment = new Comment($db);
	$image = $image->selectAll($_SESSION["id"], $_POST["name"]);
	if (!$image || !is_writable('./public/upload/') || !is_writable('./public/upload/'.$_POST["name"].".png")){
		echo "Permission denied";
		return ;
	}
	echo $comment->delete($image[0]["id"]);
});
Router::post('/comments', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	if (($image = $image->selectAll(0, $_POST["name"])) === null){
		echo 'Server Error';
		return ;
	}
	header('Content-Type: application/json');
	$comments = new Comment($db);
	echo $comments->selectAll($image[0]["id"], $_POST["timestamp"]);
});
Router::post('/upload_comment', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	if (($image = $image->selectAll(0, $_POST["name"])) === null){
		echo 'Server Error';
		return ;
	}
	$comments = new Comment($db);
	echo $comments->insert($image[0]["id"], $_SESSION["id"], $_POST["content"]);
});
Router::post('/gallery', function() {
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	if (($image = $image->selectAll()) === null){
		echo 'Server Error';
		return ;
	}
	header('Content-Type: application/json');
	echo json_encode($image);
});
Router::post('/likes', function(){
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	if (($image = $image->selectAll(0, $_POST["name"])) === null){
		echo 'Server Error';
		return ;
	}
	$like = new Like($db);
	header('Content-Type: application/json');
	echo $like->selectAll($image[0]["id"]);
});
Router::post('/like', function(){
	if (!isset($_SESSION['username'])){
		echo 'Not logged in';
		return ;
	}
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	if (($image = $image->selectAll(0, $_POST["name"])) === null){
		echo 'Server Error';
		return ;
	}
	$like = new Like($db);
	$tab = json_decode($like->selectAll($image[0]["id"], $_SESSION["id"]));
	if ((count((array)$tab) - 1) > 0){
		if ($like->delete($image[0]["id"], $_SESSION["id"]) === "true")
			echo "You have disliked this picture";
	}
	else{
		if ($like->insert($image[0]["id"], $_SESSION["id"]) === "true")
			echo "You have liked this picture";
	}
});


//views
Router::get('/', function() {
	if (!isset($_SESSION['username']))
		header('Location: http://localhost:8080/login');
	if (!($db = Db::getDatabase())){
		echo 'Server Error';
		return ;
	}
	$image = new Image($db);
	$photos = $image->selectAll($_SESSION['id']);
	$found = false;
	$content = 'public/view/home.php';
	include('public/view/templates/template.php');
});
Router::get('/login', function() {
	if (isset($_SESSION['username']))
		header('Location: http://localhost:8080/');
	$content = 'public/view/login.php';
	include('public/view/templates/template.php');
});
Router::get('/sign', function() {
	if (isset($_SESSION['username']))
		header('Location: http://localhost:8080/');
	$content = 'public/view/sign.php';
	include('public/view/templates/template.php');
});
Router::get('/sign/:rand', function($params) {
	if (isset($_SESSION['username']))
		header('Location: http://localhost:8080/');
	$db = Db::getDatabase();
	$user = new user($db);
	$return = $user->verify($params["rand"]);
	($return === 'true') ? $valid = "Your account has been validated":
							$error = $return;
	$class = isset($error) ? 'error_display' : 'success_display';
	$content = 'public/view/sign.php';
	include('public/view/templates/template.php');
});
Router::get('/forgot', function() {
	if (isset($_SESSION['username']))
		header('Location: http://localhost:8080/');
	$content = 'public/view/forgot.php';
	include('public/view/templates/template.php');
});
Router::get('/comment/:name', function($params) {
	if (!isset($_SESSION['username']))
		header('Location: http://localhost:8080/login');
	$content = 'public/view/comment.php';
	include('public/view/templates/template.php');
});
Router::get('/gallery', function() {
	if (!isset($_SESSION['username']))
		header('Location: http://localhost:8080/login');
	$content = 'public/view/gallery.php';
	include('public/view/templates/template.php');
});
Db::closeDatabase();
Router::run();
?>
