<?php
include 'elements_db_connection.php';
include 'functions.php';
include 'default.php';
    
/*
tabela zdjecia stworzona w phpmyadmin/sql

id
photo
title
alt
*/


?>


<!DOCTYPE html>
<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photos location</title>
    <!-- jquery-->
    <script src="js/jquery-3.2.1.min.js"> </script>
    <script src="js/default.js"> </script>
    <!--google maps api -->
    <script  defer src="<?php echo "https://maps.googleapis.com/maps/api/js?key=". $google_maps_api_key;?>"> </script>
    <!-- javascript -->
    <script src="js/app.js"> </script>
    <!-- css 
    <link rel="stylesheet" href="css/style.css">-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no" />
</head>
<body id ="register_form">
    <header>
        <div class="title"><h1>Zarejestruj nowe zdjęcie:</h1></div>
    </header>
    <form action='#' method = post enctype="multipart/form-data">
        <p>Wybierz zdjęcie</p>
        <input type=hidden name=size value='1000000'>
        <input type=file  name=image  >
        <p>Nadaj tytuł: </p><input type=text name=title >
        <p>Dodaj opis:</p> <textarea type=text name=alt col=40 row = 4></textarea><br>
        <input type=submit name = 'upload' value='upload'>
    </form>
    
<?php

       
    //uploading photo
                
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          if (  preg_match('/[.](jpg)$/', $_FILES['image']['name']) || preg_match('/[.](JPG)$/', $_FILES['image']['name']) || preg_match('/[.](png)$/', $_FILES['image']['name']) || preg_match('/[.](PNG)$/', $_FILES['image']['name']) ) {
          
            //submitted data
            $fileData = pathinfo(basename($_FILES["image"]["name"]));
            $image = uniqid() . '.' . $fileData['extension'];
            $filename = $image;
            $alt = $_POST["alt"];
            $title = $_POST["title"];
            //source and target to save at the server
            $source = $_FILES['image']['tmp_name'];
            $target = "images/".$image; 
                echo "source: ".$source;
              echo "target: ".$target;
              
            //send data to sql
            
            $sql = "INSERT INTO ". $table_name ." (".$table_var_photo.", ".$table_var_title.", ".$table_var_alt.") VALUES ('$image', '$title', '$alt')";          
            if ($conn->query($sql) === TRUE) {
                    echo "<p>Nowy wpis dodany</p>";
            } else {
                    echo "<p>Error: " . $sql . "<br>" . $conn->error."</p>";
            }
          
          //move uploaded image into the folder
          if (move_uploaded_file($source, $target)) {
              $msg = "<p>obrazek uploadowany</p>";
          }
          else {
              $msg = "<p class='error'>problem z uploadowaniem obrazka</p>";
          }
          echo '<br>';
          echo $msg;
              
          
              
              
          //create thumbnail  for map
          createThumbnail($filename); 
          }
          else {echo "<p class='error'>Obrazek nie zostal zalaczony. Obrazek musi byc w formacie jpg lub png. Sprobuj ponownie.</p>";}
    };
   
?>
    
     <div class="title"><h1>Mapa zdjęć:</h1></div>
        <div class="map" id="general-map"></div>
        <div class="title"><h1>Galeria zdjęć:</h1></div>

        

    
<?php
    
 
    //show the uploaded files
    $sql = "SELECT ".$table_var_title.", ".$table_var_photo.", ".$table_var_alt." FROM ". $table_name;
            $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        //get photo
                            $photo = "images/".$row['photo'];
                        //get  and show title
                            echo "<div class='gallery-box'>";
                            echo "<h3 class='title'>".$row['title']."</h3> ";
                              
                        //get to data meta data
                            $exif = exif_read_data($photo, 0, true);
                            echo $exif===false ? "<br>TECH INFO: No header data found.<br />\n" : "<br>TECH INFO: Image contains headers<br />";
                            
                            //echo var_dump($exif);
                        /*
                            foreach ($exif as $key => $section) {
                              //if($key == 'GPS'){
                                  foreach ($section as $name => $val) {
                                    echo "$key.$name: $val<br />\n";
                                        }
                                   // }
                                }
                        */
                            //check if image needs rotation    
                            $ort = $exif["IFD0"]["Orientation"];
                            echo "<p class='address'>Orientation: $ort</p>";
                            /* 
                            if ($ort >1){
                            
                            $imageResource = imagecreatefromjpeg($photo);  
                                
                             switch($ort) {
                                    case 3:
                                        $image = imagerotate($imageResource, 180, 0);
                                        break;
                                    case 6:
                                        $image = imagerotate($imageResource, -90, 0);
                                        break;
                                    case 8:
                                        $image = imagerotate($imageResource, 90, 0);
                                        break;
                                     default:
                                        $image = $imageResource;
                                }
                                
                             $photo = imagejpeg($image, $filename, 90);
                            }

                            */
                        
                        //saving  and showing date
                            $daytime = $exif["IFD0"]["DateTime"];
                            echo "<p class='time'> $daytime</p>" ;
                        //get  and show description
                            echo "<p class='desc'>".$row['alt']."</p>";
                        //showing image 
                        echo "<picture class='gallery'><img class ='gallery-image' src=$photo alt='".$row['alt']."'></picture>";
                        
                        //getting longitude and latitude
                            if(isset($exif["GPS"]["GPSLatitudeRef"])){
                                $LatM = 1; $LongM = 1;
                                if($exif["GPS"]["GPSLatitudeRef"] == 'S'){
                                    $LatM = -1;
                                }
                                if($exif["GPS"]["GPSLongitudeRef"] == 'W'){
                                    $LongM = -1;
                                }
                                //get the GPS data to arrays
                                $gps['LatDegree']=$exif["GPS"]["GPSLatitude"][0];
                                $gps['LatMinute']=$exif["GPS"]["GPSLatitude"][1];
                                $gps['LatgSeconds']=$exif["GPS"]["GPSLatitude"][2];
                                $gps['LongDegree']=$exif["GPS"]["GPSLongitude"][0];
                                $gps['LongMinute']=$exif["GPS"]["GPSLongitude"][1];
                                $gps['LongSeconds']=$exif["GPS"]["GPSLongitude"][2];

                                //convert strings to numbers
                                foreach($gps as $key => $value){
                                    $pos = strpos($value, '/');
                                    if($pos !== false){
                                        $temp = explode('/',$value);
                                        $gps[$key] = $temp[0] / $temp[1];
                                    }
                                }

                            //calculate the decimal degree to variables
                            $latitude  = $LatM * ($gps['LatDegree'] + ($gps['LatMinute'] / 60) + ($gps['LatgSeconds'] / 3600));
                            $longitude = $LongM * ($gps['LongDegree'] + ($gps['LongMinute'] / 60) + ($gps['LongSeconds'] / 3600));
                            echo "<div class='address-box'>";
                            echo "<p class='coord'>lat:</p><p class='coord lat'> $latitude </p>" ;
                            echo "<p class='coord'>lng:</p><p class='coord lng'>  $longitude</p>" ;

                                                
                            }
                        else {echo "<p class='coord'>Brak koordytat GPS dla tego zdjecia :( Nie mozna pokazac go na mapie.</p>";}
                        echo "</div> </div>";
                        }
                }else {
                            echo "<p>Nie ma jeszcze żadnych zdjęc, sprobuj dodac pierwsze!</p>";
                        }
    
    ?>

</body>
</html>
    
    
    
     
    