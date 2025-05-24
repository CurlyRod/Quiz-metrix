
$(document).ready(function () {
    fetchUserAndShortcuts();
  });
  
  function fetchUserAndShortcuts() {
    $.ajax({
      url: "../../middleware/auth/ValidateUser.php",
      type: "POST",
      data: { action: "check-users" },
      dataType: "json",
      success: function (data) {
        if (data.userinfo) {
          const userEmail = data.userinfo[0];
          const userId = data.userinfo[1];
  
          $("#user-email-log").val(userEmail);
          $("#user-id-log").val(userId);
         
  
          fetchUserShortcuts(userId);
        } else {
          console.error("Invalid user info:", data);
        }
      },
      error: function (xhr, status, error) {
        console.error("User check error:", error);
      }
    });
  }
  
  function fetchUserShortcuts(userId) {
    $.ajax({
      url: "../../shortcut-url/shortcutclass.php",
      type: "POST",
      data: { action: "get_url", "user-id-log": userId },
      dataType: "json",
      success: function (response) {
        if (response.status === 200 && Array.isArray(response.url)) {
          buildShortcutList(response.url);
        } else {
          console.warn("No shortcuts found:", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Shortcut fetch error:", error);
      }
    });
  }
  


// check user if new login then will add password for manual login... -dor
$(document).on('submit', '#new-user-login' ,function(e)
{   
    e.preventDefault(); 
    var formData = new FormData(this); 
    formData.append("add_password", true);  

    $.ajax({    
       type: "POST" ,
       url:  "../../middleware/auth/UserAuthenticate.php",
       data: formData, 
       processData: false, 
       contentType:false,
       success: function(response)
       {    
         var res = JSON.parse(response);  
         if(res.status == 422)
         {  
           console.log(res);
           
         }else if(res.status == 200)
         {  
            console.log(res);             
         }
       }
    });
});   




  