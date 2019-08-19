<?php

header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
date_default_timezone_set(Europe/Rome);
ini_set("display_errors", "1");
error_reporting(E_ALL);

$servername = "localhost";
$username = "alessandrobasi";
$password = "nope";
$dbname = "database";

$cache_time = 604800; // 7 giorni in sec

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    //die("Connection failed: " . $conn->connect_error);
    die("Connection failed");
} 

$sql = "SELECT * FROM numero_dns ORDER BY id DESC LIMIT 1 ";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

//la data salvata + 7 gg (in sec) deve essere maggiore della data attuale
if( $row['date']+$cache_time < time() ) {
	
	$file = "https://data.iana.org/TLD/tlds-alpha-by-domain.txt" ; //basename($_SERVER['PHP_SELF']) 
	$no_of_lines = count(file($file)) - 1;
    $sql = "INSERT INTO numero_dns (date, number) VALUES ( '".time()."' , ".$no_of_lines." )";
	
    if ($conn->query($sql) === TRUE) {
        $row['number'] = $no_of_lines;
        $row['date'] = time();
    }
    else{
    	die("Error");
    }
}

$conn->close();


//creazione img
$immagine=imagecreatetruecolor(1080,420);

//trasparenza
imagealphablending($immagine, false);
$transparency = imagecolorallocatealpha($immagine, 255, 255, 255, 0); //127
imagefill($immagine, 0, 0, $transparency);
imagesavealpha($immagine, true);


//font
putenv('GDFONTPATH=' . realpath('.'));
$font1 = "arial.ttf";

//colore
$nero = imagecolorallocate($immagine, 0, 0, 0);

//testi

imagettftext($immagine, 50, 0, 10, 60, $nero, $font1, "Number of TLD registered: ".$row['number']."" );
imagettftext($immagine, 50, 0, 10, 140, $nero, $font1, "Update date ".date("d-m-Y H:i", $row['date'] ) );

imagettftext($immagine, 15, 0, 890, 400, $nero, $font1, "By alessandrobasi.it" );

header('content-type: image/png');

imagepng($immagine);
imagedestroy($immagine);
?>
