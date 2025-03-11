<?php
/*
dev: https://jahanbots
channel telegram : @jahanbots
*/
// این فایل ادیت نشود
//روی یک دقیقه کرون جاب شود


include "config.php"; 
//------------------------------------------------------//
error_reporting(0);
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
} // factweb
function Takhmin($fil){
if($fil <= 200 ){
return "2";
}else{ // @factweb
$besanie = $fil/200;
return ceil($besanie)+1;
}
} // 
//===================================================================
$settings = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$foall = $settings["forall"];
$sendall = $settings["sendall"];
$tedad = $settings["tedad"];
$text = $settings["text"]; 
$chat_id = $settings["chat_id"];
$msg_id = $settings["msg_id"];
$msg_id2 = $settings["msg_id2"];
$is_all = $settings["is_all"];
$users = mysqli_query($connect,"select id from user");
$fil = mysqli_num_rows($users); 
//=====================================================
if($tedad + 0.1 > $fil ){
$connect->query("UPDATE settings SET forall = 'false' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET sendall = 'false' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET tedad = '0' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET msg_id2 = 'no' WHERE botid = 'none' LIMIT 1");	
factweb('sendMessage',[
 'chat_id'=>$admins[0],
 'text'=>"✅ عملیات همگانی پایان یافت !",
 'parse_mode'=>"HTML",
  ]); 
  factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"✅ همگانی پایان یافت .",'callback_data'=>"none"]],
              ]
        ])
    		]);
    		$connect->query("UPDATE settings SET is_all = 'no' WHERE botid = '$botid' LIMIT 1");	
}
$settings = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$foall = $settings["forall"];
if($foall == "true"){
while($row = mysqli_fetch_assoc($users)){
     $ex[] = $row["id"];
}
$kobs2 = $tedad + 200;
for($z = $tedad;$z <= $kobs2;$z++){
		   		   factweb('ForwardMessage',[
		   		    'chat_id'=>$ex[$z],
		   		   	'from_chat_id'=>$chat_id,
	'message_id'=>$msg_id,
  ]); 
		}   
if($fil < 200.1 ){
$connect->query("UPDATE settings SET tedad = '$fil' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh); 
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]); 
}else{
if($kobs2 > $fil ){ 
$connect->query("UPDATE settings SET tedad = '$kobs2' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh);
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]);
}else{
$connect->query("UPDATE settings SET tedad = '$kobs2' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd; 
$min = Takhmin($tfrigh);
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all, 
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]);
}
}
} 
$settings = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$sendall = $settings["sendall"];
if($sendall == "true"){
while($row = mysqli_fetch_assoc($users)){
     $ex[] = $row["id"];
}
$kobs2 = $tedad + 200;
for($z = $tedad;$z <= $kobs2;$z++){
		   factweb('sendMessage',[
 'chat_id'=>$ex[$z],
 'text'=>$text, 
 'parse_mode'=>"HTML",
   'disable_web_page_preview'=>true,
  ]); 
		}    
if($fil < 200.1 ){ 
$connect->query("UPDATE settings SET tedad = '$fil' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh);
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]);
}else{ 
if($kobs2 > $fil ){
$connect->query("UPDATE settings SET tedad = '$kobs2' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh);
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]); 
}else{
$connect->query("UPDATE settings SET tedad = '$kobs2' WHERE botid = '$botid' LIMIT 1");	
$settings1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$tddd = $settings1['tedad'];
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh); 
factweb('editMessageReplyMarkup',[
    'chat_id'=>$is_all,
    'message_id'=>$msg_id2,
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]); 
}
}
}
?>