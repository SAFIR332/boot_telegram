<?php
/*
dev: https://jahanbots
channel telegram : @jahanbots
*/
// این فایل را ویرایش نکنید
// روی یک دقیقه کرون جاب کنید
include "config.php";
//------------------------------------------------------//

date_default_timezone_set('Asia/Tehran');
define('API_KEY',$API_KC); 
function factweb($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method; 
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
} 
$get = mysqli_query($connect, "SELECT * FROM dbremove");
sleep(20);  
 while($row = $get->fetch_assoc()) {
  factweb('deleteMessage',[
            'chat_id' => $row['id'],
            'message_id' => $row['message_id'],
  ]);
 $connect->query("DELETE FROM dbremove WHERE id = '{$row['id']}' and message_id = '{$row['message_id']}' LIMIT 1");
 }
 