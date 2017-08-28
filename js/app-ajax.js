$(function() {


  //reading data from json---------------------------------------
  //variables for main ul list
  var imagesDiv = $('#images-div');
  var button = $('input[type="submit"]');
  var form = $('form');
  console.log(form);
    


  //variables for url
  var apiUrl = 'http://localhost:3000/images/';


  //Insert elements to DOM  
  function insertContent(images) {
      //console.log(images);
      //clear what was there before - to avoid duplicates
      imagesDiv.empty();
      //add updated content
    $.each(images, function(index, element) {
        var galleryBox = $('<div>', {class: "gallery-box"});
        var h3 = $('<h3>', {class: "title"}).text(element.title);
        var p1 = $('<p>', {class: "time"}).text(element.date);
        var p2 = $('<p>', {class: "desc"}).text(element.alt);
        galleryBox.append(h3).append(p1).append(p2);
        imagesDiv.append(galleryBox);
        var imageBox = $('<div>', {class: "image-box"});
        var picture = $('<picture>', {class: "gallery"});
        var img = $('<img>', {class: "gallery-image"}).attr('src', element.photo);
        galleryBox.append(imageBox);
        imageBox.append(picture);
        picture.append(img);
        var addressBox = $('<div>', {class: "address-box"});
        var p3 = $('<p>', {class: "coord"}).text("lat: ");
        var p4 = $('<p>', {class: "coord"}).text(element.lat);
        var p5 = $('<p>', {class: "coord"}).text("lng: ");
        var p6 = $('<p>', {class: "coord"}).text(element.lng);
        var p7 = $('<p>', {class: "address"});
        var map = $('<div>', {class: "map"}).attr('id', element.id);
        addressBox.append(p3).append(p4).append(p5).append(p6).append(p7).append(map);
        galleryBox.append(addressBox);
    });
  }
    

  //Load movies and insert them into the DOM
  
  function loadImages() {
        $.ajax({
            	url: apiUrl
        }).done(function(response){
                  //console.log(response);
     		    insertContent(response);
    	 }).fail(function(error) {
           console.log(error);
       })
  }

  loadImages();
    
    
  //adding data to json---------------------------------------
    
    
    function addImage(){
        form.on('submit', function(event){
            //prevent default
            event.preventDefault();
            
            // save image via PHP
            var photoUrl = '#';
            var lat = '';
            var lng = '';
            var date = '';
            
             $.ajax({
                url: "upload-for-ajax.php", 
                type: "POST",             
                //dataType: 'text',
                data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false,       // The content type used when sending data to the server.
                cache: false,             // To unable request pages to be cached
                processData:false,        // To send DOMDocument or non processed data file it is set to false
                success: function(response) {  
                        //console.log(response);
                        photoUrl = response.split("||")[0];
                        lat = response.split("||")[2];
                        lng = response.split("||")[3];
                        date = response.split("||")[1];
                     
                        //retrieve post data
                        var title = $('input[name="title"]').val();
                        var desc = $('textarea').val();
                        //console.log(title);
                        //console.log(desc);
                    
                        //prepare json
                        var jsonData = {
                            title: title,
                            alt: desc,
                            photo: photoUrl,
                            lat: lat,
                            lng: lng,
                            date: date
                        };
                        //console.log(jsonData);
                
                        //send json
                        $.ajax({
                                url: apiUrl,
                                type: "POST",
                                dataType: "json",
                                data: jsonData
                        }).done(function(response){
                                //show images on the site
                                loadImages();
                         }).fail(function(error) {
                           console.log(error);
                        });
                
                
                }
                }); 
   
        });
    }
    
    addImage();
    

    
    //end---------------------------------

});



 