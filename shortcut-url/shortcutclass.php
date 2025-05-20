    <?php
    require_once './includes/config.php'; 

    class ShortCutClass { 
        private $conn;

        public function __construct($dbConn) {
            $this->conn = $dbConn;
        }

        public function CreateShortcut($sitename, $url, $user) {       
            $stmt = $this->conn->prepare("INSERT INTO shortcut_url (sitename, url, user_id) VALUES (?,?, ?)");
            $stmt->bind_param("sss",$sitename, $url, $user);

            if ($stmt->execute()) {
                echo json_encode(['status' => 200, 'message' => 'Shortcut URL added successfully']);
            } else {
                echo json_encode(['status' => 422, 'message' => 'Error in addding browser' ]);
            }
        }  
        
        public function GetShortcutByUser($user_id) {
            $stmt = $this->conn->prepare("SELECT *  FROM shortcut_url WHERE user_id = ?");
            $stmt->bind_param("i", $user_id); 
            $stmt->execute();  
        
            $result = $stmt->get_result(); 
        
            if ($result->num_rows > 0) { 
                $urls = [];
        
                while ($row = $result->fetch_assoc()) {
                    $urls[] = [
                        'url' => $row['url'],
                        'sitename' => $row['sitename'] 
                    ]; 
                }
        
                $stmt->close();
        
                return [
                    'status' => 200,
                    'url' => $urls
                ];
            } else {
                $stmt->close();
                return [
                    'status' => 404,
                    'message' => 'No URL found for this user.'
                ];
            }
        }
        
         
    }

    $shortcut = new ShortCutClass($conn); 
    if (isset($_POST['save_browser'])) {  
        $browserUrl = mysqli_real_escape_string($conn, $_POST['browserUrl']); 
        $browserName = mysqli_real_escape_string($conn, $_POST['browserName']); 
        if (empty($browserUrl)) {
            echo json_encode([
                'status' => 422, 
                'message' => "Empty Url"
            ]);
            return;
        } else {   
            $user_id = 1;
            $shortcut->CreateShortcut($browserName ,$browserUrl, $user_id);  
        }  
    } 

    if (isset($_POST['action']) && $_POST['action'] === "get_url") {   
        $user_id = $_POST['user-id-log']; 
        $user = mysqli_real_escape_string($conn, $user_id);  
    
        if (empty($user)) {
            echo json_encode([
                'status' => 422, 
                'message' => "Empty URL"
            ]);
            return;
        } else {   
            $result = $shortcut->GetShortcutByUser($user);  
            echo json_encode($result); 
        }  
    }
    
