$(document).on('submit', '#add-url-browser', function(e) {   
  e.preventDefault(); 

  var formData = new FormData(this); 
  formData.append("save_browser", true);  

  $.ajax({    
      type: "POST",
      url: "../../shortcut-url/shortcutclass.php",
      data: formData, 
      processData: false, 
      contentType: false,
      success: function(response) {    
          var res = JSON.parse(response);  
          if (res.status == 422) {  
              console.log(res);
          } else if (res.status == 200) {           
              $("#add-url-browser")[0].reset();
              $("#addShortcut").modal('hide');
              fetchUserAndShortcuts();  
          }
      },
      error: function(xhr, status, error) {
          console.error("Error:", error);
      }
  });
});

 



