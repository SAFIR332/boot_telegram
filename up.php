<?php 
/*
dev: https://factweb.ir
channel telegram : @factwebir
*/
//-----------------------------------------------------------------------------------------------
$telegram_ip_ranges = [
['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    // literally 91.108.4.0/22
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok=false;
foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok=true;
}
if (!$ok) die("No Way"); 
//-----------------------------------------------------------------------------------------------
date_default_timezone_set('Asia/Tehran');
include "config.php";
include "jdf.php"; 
define('API_KEY',$API_KC);
//-----------------------------------------------------------------------------------------------
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
//-----------------------------------------------------------------------------------------------
$update = json_decode(file_get_contents('php://input'));
$data = $update->callback_query->data;
$message = $update->message;
$text = $message->text;
$tc = $update->message->chat->type;
$first_name = $message->from->first_name;
$username = $message->from->username;
//----------------------------------------------------------------------
if(isset($data)){
$chat_id = $update->callback_query->message->chat->id;
$from_id = $update->callback_query->from->id;
$message_id = $update->callback_query->message->message_id;
} 
if(isset($message->from)){
$chat_id = $message->chat->id;
$from_id  = $message->from->id;
$message_id  = $message->message_id;
} 
//-----------------------------------------------------------------------------------------------
$user = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM user WHERE id = '$from_id' LIMIT 1"));
$settings = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
$seennow = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM seen LIMIT 1"));
$reactnow=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM reaction LIMIT 1"));
$adspost=mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM adspost LIMIT 1"));
//-------------------------------------------------------------
$seencheck=$seennow['checkseen'];
$seenchannel=$seennow['channelseen'];
$seenadad=$seennow['adadseen'];
$timefake=$seennow['timefake'];

$reactcheck=$reactnow['checkreact'];
$reactchannel=$reactnow['channelreact'];
$reactadad=$reactnow['reacttedad'];
$reacttimefake=$reactnow['timefakereact'];

$checkads=$adspost['checkads'];
$textads=$adspost['textads'];
//-----------------------------------------------------------------------------------------------
$bot_mode=$settings['bot_mode'];
$chupl=$settings['chupl'];
$is_all=$settings['is_all'];
$factwebir=$settings['factwebir']; 

$topdlbut=$settings['topdlbut']; 
$newdlbut=$settings['newdlbut']; 
$supbut=$settings['supportbut']; 
$sendbut=$settings['sendbut']; 
$starttext=$settings['starttext']; 
//-----------------------------------------------------------------------------------------------
$time = jdate('H:i:s'); 
$ToDay = jdate('l'); 
$date = gregorian_to_jalali(date('Y'), date('m'), date('d'), '/');
$ToDayen = date('l'); 
$dateen = date("Y-m-d");
$timeen = date('H:i:s'); 
//-----------------------------------------------------------------------------------------------
function bot_user($id,$what){ 
  $bye = factweb('getChat',[
  'chat_id'=>$id,
  ]);
  return $bye->result->$what;
} 
function convert($size){
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.['', 'K', 'M', 'G', 'T', 'P'][$i].'B';
}
function decrypt($string){
  $p = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29];
  $res = [];
  $string = str_split($string, 10);
  foreach($string as $r){
    $r = str_split($r);
    $c = 0;
    $m = 1;
    for($i = 0; isset($r[$i]); ++$i){
      $r[$i] = base_convert($r[$i], 36, 10);
      while((41 * $c) % $p[$i] != $r[$i])
        $c += $m;
      $m *= $p[$i];
    }
    $res[] = $c;
  }
  $res = pack("V*", ...$res);
  return $res;
}
function doc($name) {
    if ($name == "document") {
        return "فایل";
    }
    if ($name == "video") {
        return "ویدیو";
    }
    if ($name == "photo") {
        return "عکس";
    }
    if ($name == "voice") {
        return "ویس";
    }
    if ($name == "audio") {
        return "موزیک";
    }
    if ($name == "sticker") {
        return "استیکر";
    } 
}
function Takhmin($fil){
if($fil <= 200 ){
return "2";
}else{
$besanie = $fil/200;
return ceil($besanie)+1;
}
} 
function getChatstats($chat_id,$token) {
  $url = 'https://api.telegram.org/bot'.$token.'/getChatAdministrators?chat_id='.$chat_id;
  $result = file_get_contents($url);
  $result = json_decode ($result);
  $result = $result->ok;
  return $result;
}
function IsJoined($token,$User, array $Channels) {
    $AcceptedRoles = ['administrator', 'creator', 'member'];
    foreach($Channels as $iterator){
        $Req = file_get_contents('https://api.telegram.org/bot'.$token.'/getChatMember?chat_id='.$iterator.'&user_id='.$User);
        yield in_array(json_decode($Req)->result->status, $AcceptedRoles);
    }
}
function CanSendRequest($results){
    $ok = true;
    foreach($results as $result)
        if($result == false)
            $ok = false;
    return $ok;
}
function is_join($from_id,$Channel){
$forchaneel = factweb('getChatMember',[
'chat_id'=>$Channel,
'user_id'=>$from_id]);
$tch = $forchaneel->result->status;
if($tch != 'member' && $tch != 'creator' && $tch != 'administrator' ){
return false;
}else{
return true; 
   }
}
//==========================================================================
if($user["spam"] < time()){ 
$tt = time() + 0.8;
$connect->query("UPDATE user SET spam = '$tt' WHERE id = '$from_id' LIMIT 1");	
//==========================================================================

$keymenu = json_encode([
    'keyboard' => [
 [['text' => ($newdlbut == "on") ? '🆕 جدیدترین ها' : ''],['text' => ($topdlbut == "on") ? '🆒 پردانلودترین ها' : '']], 
 [['text' => ($sendbut == "on") ? '📤 ارسال رسانه' : ''],['text' => ($supbut == "on") ? '👨🏼‍💻 پشتیبانی' : '']]
    ],
    'resize_keyboard' => true
]);
#===========================================================================
if($bot_mode == "off" && !in_array($from_id,$admins)) {
if(isset($message->from)){
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"⭕️ ربات فعلا خاموش میباشد .",
'parse_mode'=>"HTML",
    		]); 
    		} 
    if(isset($data)){
    	factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"⭕️ ربات فعلا خاموش میباشد .",
 'parse_mode'=>"HTML",
    		]); 
    }
}else{
if($user['step'] == "ban") {
if(isset($message->from)){
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"📛 شما از ربات مسدود هستید .",
'parse_mode'=>"HTML",
    		]); 
    		}
    if(isset($data)){
    	factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"📛 شما از ربات مسدود هستید .",
 'parse_mode'=>"HTML",
    		]); 
    } 
}else{
if($text == "/start" or $text == "🏠 برگشت به منو"){ 
if($user['id'] == null ){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"$starttext

🆔@$bottag",
'parse_mode'=>"HTML",
  'reply_markup'=>$keymenu
    		]);
 $connect->query("INSERT INTO user (id , step , step2 , step3 , step4 , step5 , spam,timejoin) VALUES ('$from_id', 'none', 'none', 'none', 'none', 'none', '0','$dateen')");
}else{ 
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"$starttext

🆔@$bottag",
'parse_mode'=>"HTML",
  'reply_markup'=>$keymenu
    		]); 
    	$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = 'none' WHERE id = '$from_id'");  
  }
  } 
  if(strpos($text,"/start dl_") !== false && $text != "/start" && $tc == "private"){
  if($user['id'] == null ){
   $connect->query("INSERT INTO user (id , step , step2 , step3 , step4 , step5 , spam,timejoin) VALUES ('$from_id', 'none', 'none', 'none', 'none', 'none', '0','$dateen')");
   }
$edit = str_replace("/start dl_","",$text);
$chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
if($files['ghfl_ch'] == "on" && $fil != 0 && CanSendRequest(IsJoined(API_KEY,$from_id,$ar)) == false ){
for ($i=0; $i <= $fil; $i++){

$by = $i + 1;
$okk = $ar[$i];
$ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$okk' LIMIT 1"));
$link = $ch['link'];
if($link != null ){
if(is_join($from_id,$okk) == false ){
$d4[] = [['text'=>"عضویت در کانال $by",'url'=>$link]];
}
}  
} 
$d4[] = [['text'=>"✅ عضو شدم",'callback_data'=>"taid_$edit"]];
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ به ربات <b> آپلودر مکس فکت وب </b> خوش آمدید؛

🔻جهت دریافت فایل ابتدا باید در کانال های زیر عضو شوید.

⭕️ بعد از عضویت در همه چنل ها روی 'تایید عضویت' کلیک کنید تا برای شما ارسال شود",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
           'inline_keyboard'=>$d4
              ])
    		]); 
    		}else{
        		    if($reactcheck=="on"){
        $timebegirr=time()+$reacttimefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و برای $reactadad پست آخر ری اکشن بزنید .
🔹سپس روی دکمه 'ری اکشن زدم' کلیک کرده تا فایل برای شما نمایش داده شود.",  
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$reactchannel"]],
    [['text' => "👌🏻 ری اکشن زدم", 'callback_data' => "ireact_$edit"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegirr' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{
        		    if($seencheck=="on"){
        $timebegir=time()+$timefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
               'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و $seenadad پست آخر را سین کنید .
🔹سپس روی دکمه 'مشاهده کردم' کلیک کرده تا پست برای شما نمایش داده شود.", 
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$seenchannel"]],
    [['text' => "👁 مشاهده کردم", 'callback_data' => "boin_$edit"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegir' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
 if($files['file_id'] != null ){
 if($files['pass'] == 'none' ){
 if($files['mahdodl'] == 'none' ){
   $file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $file_size2 = $files["file_size"];  
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }
    
    $showButton = false;
  }
   
    	
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b> حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        		if($files['dl'] != $files['mahdodl'] && $files['dl'] + 0.1 < $files['mahdodl']){
        	$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
   
   #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }
	
    $showButton = false;
  }

}

        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{  
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ این فایل به حداکثر دانلود رسیده است .",
'parse_mode'=>"HTML",
 'reply_markup'=>$keymenu
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}
        	} 
        	}else{
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔐 این محتوا حاوی پسورد است !

- لطفا رمز دسترسی را وارد کنید:",
'parse_mode'=>"HTML",
    		]);
    			$connect->query("UPDATE user SET step = 'khiiipassz_$edit' WHERE id = '$from_id' LIMIT 1");	
        	}
        	
        	
        }
        
        }
        
}}}
#======================================
elseif(strpos($data,"taid_") !== false ){ 
$ok = str_replace("taid_",null,$data);
$chs = mysqli_query($connect,"select idoruser from channels");
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
$fil = mysqli_num_rows($chs);
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['ghfl_ch'] == "on" && $fil != 0 && CanSendRequest(IsJoined(API_KEY,$from_id,$ar)) == false ){
factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "هنوز در چنل جوین نشده اید !",
        'show_alert' => false
    ]);
}else{
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"✅ عضویت شما تایید شد .",
 'parse_mode'=>"HTML",
    		]);  
        		    if($reactcheck=="on"){
        $timebegirr=time()+$reacttimefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و برای $reactadad پست آخر ری اکشن بزنید .
🔹سپس روی دکمه 'ری اکشن زدم' کلیک کرده تا فایل برای شما نمایش داده شود.",  
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$reactchannel"]],
    [['text' => "👌🏻 ری اکشن زدم", 'callback_data' => "ireact_$ok"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegirr' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{
        		    if($seencheck=="on"){
        $timebegir=time()+$timefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
               'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و $seenadad پست آخر را سین کنید .
🔹سپس روی دکمه 'مشاهده کردم' کلیک کرده تا پست برای شما نمایش داده شود.", 
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$seenchannel"]],
    [['text' => "👁 مشاهده کردم", 'callback_data' => "boin_$ok"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegir' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{		
    		$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
    		 if($files['pass'] == 'none' ){
 if($files['mahdodl'] == 'none' ){
    		$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
   $file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
      #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$ok' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	 	}else{
  	if($files['dl'] != $files['mahdodl'] && $files['dl'] + 0.1 < $files['mahdodl']){
        	$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
      #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$ok' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ این فایل به حداکثر دانلود رسیده است .",
'parse_mode'=>"HTML",
 'reply_markup'=>$keymenu
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}
        	}
        	}else{  
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔐 این محتوا حاوی پسورد است !

- لطفا رمز دسترسی را وارد کنید:",
'parse_mode'=>"HTML",
    		]);
    			$connect->query("UPDATE user SET step = 'khiiipassz_$ok' WHERE id = '$from_id' LIMIT 1");	
        	}
} 
}}}
elseif(strpos($user['step'],"khiiipassz_") !== false && strpos($text,"start") === false ){
$ok = str_replace("khiiipassz_",null,$user['step']);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['pass'] != 'none'){
if($text == $files['pass']){
if($files['mahdodl'] == "none"){
$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
    #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$ok' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        	if($files['dl'] != $files['mahdodl'] && $files['dl'] + 0.1 < $files['mahdodl']){
        	$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
     #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'reply_to_message_id' => $message_id,
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$ok' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ این فایل به حداکثر دانلود رسیده است .",
'parse_mode'=>"HTML",
 'reply_markup'=>$keymenu
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}
        	}
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ پسورد نامعتبر است !

❗️ لطفا پسورد را بدرستی ارسال کنید:",
'parse_mode'=>"HTML",
    		]);
} 
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"⭕️ این فایل دیگر پسورد ندارد.

لطفا یکبار دیگر با لینک وارد شوید:

https://telegram.me/$bottag?start=dl_$ok",
'parse_mode'=>"HTML",
    		]);
}
}
#======================================
elseif(strpos($data, "boin_") !== false) {
	$edit = str_replace("boin_", null, $data);
        $info = $user['step'];
        if(time()>$info){
        $chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
if($files['ghfl_ch'] == "on" && $fil != 0 && CanSendRequest(IsJoined(API_KEY,$from_id,$ar)) == false ){
for ($i=0; $i <= $fil; $i++){

$by = $i + 1;
$okk = $ar[$i];
$ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$okk' LIMIT 1"));
$link = $ch['link'];
if($link != null ){
if(is_join($from_id,$okk) == false ){
$d4[] = [['text'=>"عضویت در کانال $by",'url'=>$link]];
}
}  
} 
$d4[] = [['text'=>"✅ عضو شدم",'callback_data'=>"taid_$edit"]];
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ به ربات <b> آپلودر مکس فکت وب </b> خوش آمدید؛

🔻جهت دریافت فایل ابتدا باید در کانال های زیر عضو شوید.

⭕️ بعد از عضویت در همه چنل ها روی 'تایید عضویت' کلیک کنید تا برای شما ارسال شود",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
           'inline_keyboard'=>$d4
              ])
    		]); 
    		}else{
    		            		    if($reactcheck=="on"){
        $timebegirr=time()+$reacttimefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و برای $reactadad پست آخر ری اکشن بزنید .
🔹سپس روی دکمه 'ری اکشن زدم' کلیک کرده تا فایل برای شما نمایش داده شود.",  
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$reactchannel"]],
    [['text' => "👌🏻 ری اکشن زدم", 'callback_data' => "ireact_$edit"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegirr' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
 if($files['file_id'] != null ){
 if($files['pass'] == 'none' ){
 if($files['mahdodl'] == 'none' ){
   $file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        		if($files['dl'] != $files['mahdodl'] && $files['dl'] + 0.1 < $files['mahdodl']){
        	$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
   
   #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{  
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ این فایل به حداکثر دانلود رسیده است .",
'parse_mode'=>"HTML",
 'reply_markup'=>$keymenu
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}
        	} 
        	}else{
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔐 این محتوا حاوی پسورد است !

- لطفا رمز دسترسی را وارد کنید:",
'parse_mode'=>"HTML",
    		]);
    			$connect->query("UPDATE user SET step = 'khiiipassz_$edit' WHERE id = '$from_id' LIMIT 1");	
        	}
        	
        	
        }
        
        }
    }}
    else{
         factweb('answercallbackquery', [
              'callback_query_id' => $update->callback_query->id,
            'text' => "❌ هنوز پست های کانال را مشاهده نکرده اید", 
            'message_id' => $message_id,
            'show_alert' => false
        ]); 
    }
    
}
#======================================
elseif(strpos($data, "ireact_") !== false) {
	$edit = str_replace("ireact_", null, $data);
        $info = $user['step'];
        if(time()>$info){
        $chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
if($files['ghfl_ch'] == "on" && $fil != 0 && CanSendRequest(IsJoined(API_KEY,$from_id,$ar)) == false ){
for ($i=0; $i <= $fil; $i++){

$by = $i + 1;
$okk = $ar[$i];
$ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$okk' LIMIT 1"));
$link = $ch['link'];
if($link != null ){
if(is_join($from_id,$okk) == false ){
$d4[] = [['text'=>"عضویت در کانال $by",'url'=>$link]];
}
}  
} 
$d4[] = [['text'=>"✅ عضو شدم",'callback_data'=>"taid_$edit"]];
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"✔️ به ربات <b> آپلودر مکس فکت وب </b> خوش آمدید؛

🔻جهت دریافت فایل ابتدا باید در کانال های زیر عضو شوید.

⭕️ بعد از عضویت در همه چنل ها روی 'تایید عضویت' کلیک کنید تا برای شما ارسال شود",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
           'inline_keyboard'=>$d4
              ])
    		]); 
    		}else{
        		    if($seencheck=="on"){
        $timebegir=time()+$timefake;
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
               'text' => "🔻برای مشاهده این محتوا ابتدا به کانال رفته و $seenadad پست آخر را سین کنید .
🔹سپس روی دکمه 'مشاهده کردم' کلیک کرده تا پست برای شما نمایش داده شود.", 
        'reply_to_message_id' => $message_id,
        'reply_markup' => json_encode(['inline_keyboard' => [
    [['text' => "🆔 ورود به کانال و مشاهده پست ها", 'url' => "t.me/$seenchannel"]],
    [['text' => "👁 مشاهده کردم", 'callback_data' => "boin_$edit"]],
    ]
])
    ]); 
    	$connect->query("UPDATE user SET step = '$timebegir' WHERE id = '$from_id' LIMIT 1");	
        		    }
        		    else{
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$edit' LIMIT 1"));
 if($files['file_id'] != null ){
 if($files['pass'] == 'none' ){
 if($files['mahdodl'] == 'none' ){
   $file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
    		     if($checkads=="on"){
        	    factweb('sendmessage', [
        'chat_id' => $chat_id,
'text' => "$textads",
'parse_mode'=>"HTML",
    ]); 
        		    }
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{
        		if($files['dl'] != $files['mahdodl'] && $files['dl'] + 0.1 < $files['mahdodl']){
        	$file_size = $files['file_size'];
   $file_id = $files['file_id'];
   $file_type = $files['file_type'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   $type = doc($file_type);
   $bash = $dl + 1;
   
   #========
$file_size2 = $files['file_size2'];
$file_size3 = $files['file_size3'];
$file_size4= $files['file_size4'];
$file_size5 = $files['file_size5'];
$file_id2 = $files['file_id2'];
$file_id3 = $files['file_id3'];
$file_id4 = $files['file_id4'];
$file_id5 = $files['file_id5'];
$file_type2 = $files['file_type2'];
$file_type3 = $files['file_type3'];
$file_type4 = $files['file_type4'];
$file_type5 = $files['file_type5'];
$tozihat2 = $files['tozihat2'];
$tozihat3 = $files['tozihat3'];
$tozihat4 = $files['tozihat4'];
$tozihat5 = $files['tozihat5'];
$likefile = $files['likes'];
$dislikefile = $files['dislikes'];
#=====
$showButton = true;

for ($i = 0; $i <= 5; $i++) {
  if ($i == 0) {
    $file_id = $files['file_id'];
    $file_type = $files['file_type'];
    $file_size = $files['file_size'];
    $tozihat = $files['tozihat'];
  } else {
    $file_id = $files["file_id$i"];
    $file_type = $files["file_type$i"];
    $file_size = $files["file_size$i"];
    $tozihat = $files["tozihat$i"];
  }

  if ($file_id) {
    $id = factweb('send'.$file_type, [
      'chat_id' => $chat_id,
      "$file_type" => $file_id,
      'caption' => "$tozihat\n\n📥 تعداد دانلود : $bash\n\n🆔@$bottag",
      'parse_mode' => "HTML",
      'reply_markup' => ($showButton ? json_encode([
        'inline_keyboard' => [
          [['text' => "👍🏻 $likefile", 'callback_data' => "like_$edit"], ['text' => "👎🏻 $dislikefile", 'callback_data' => "disliuke_$edit"]]
        ]
      ]) : null)
    ])->result;

    $msg_iddd = $id->message_id;
    if ($files['zd_filter'] == "on") {
      $connect->query("INSERT INTO dbremove (id, message_id, time) VALUES ('{$from_id}', '$msg_iddd', '".strtotime("+{$settings['factwebir']} minutes")."')");
    }

    $showButton = false;
  }
}
        if($files['zd_filter'] == "on"){
        $isdeltime = $settings['factwebir'];
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
⚠️ پیام بالا را هر چه سریعتر در <b>Saved Message</b>  خود انتقال دهید !

⌛️این پیام زیر <b> $isdeltime دقیقه </b>دیگر حذف خواهد شد .",
'parse_mode'=>"HTML",
    		]);
        }
        	$connect->query("UPDATE files SET dl = '$bash' WHERE code = '$edit' LIMIT 1");	
        	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}else{  
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ این فایل به حداکثر دانلود رسیده است .",
'parse_mode'=>"HTML",
 'reply_markup'=>$keymenu
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
        	}
        	} 
        	}else{
        	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔐 این محتوا حاوی پسورد است !

- لطفا رمز دسترسی را وارد کنید:",
'parse_mode'=>"HTML",
    		]);
    			$connect->query("UPDATE user SET step = 'khiiipassz_$edit' WHERE id = '$from_id' LIMIT 1");	
        	}
        	
        	
        }
        
        }
    }}
    else{
         factweb('answercallbackquery', [
              'callback_query_id' => $update->callback_query->id,
            'text' => "❌ هنوز روی پست های کانال ری اکشن نزده اید", 
            'message_id' => $message_id,
            'show_alert' => false
        ]); 
    }
    
}

  elseif($text == "🆒 پردانلودترین ها" ){ 
$sql = "SELECT * FROM files ORDER BY dl DESC LIMIT 5";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
$mtn = "";
    while($row = $result->fetch_assoc()) {
    $code = $row['code'];
    $dl = $row['dl'];
    $type = doc($row['file_type']);
     $mtn = $mtn."🌀 کد : <code>$code</code>
📥 تعداد دانلود : $dl
🔖 نوع فایل : <b>$type</b>
🔗 لینک دریافت : <a href='https://telegram.me/$bottag?start=dl_$code'> دانلود فایل</a>\n\n";
    }
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🆒 <b> پردانلودترین فایل های آپلود شده:</b> \n\n".$mtn,
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
      [['text'=>"♻️ بروزرسانی ♻️",'callback_data'=>"uptopup"]],
              ]
        ])
    		]);
} else {
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ چیزی آپلود نشده است .",
'parse_mode'=>"HTML",
    		]);
} 
    }
    elseif($data == "uptopup"){
        $sql = "SELECT * FROM files ORDER BY dl DESC LIMIT 5";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
$mtn = "";
    while($row = $result->fetch_assoc()) {
   $code = $row['code'];
    $dl = $row['dl'];
    $type = doc($row['file_type']);
     $mtn = $mtn."🌀 کد : <code>$code</code>
📥 تعداد دانلود : $dl
🔖 نوع فایل : <b>$type</b>
🔗 لینک دریافت : <a href='https://telegram.me/$bottag?start=dl_$code'> دانلود فایل</a>\n\n";
    }
    factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"🆒 <b> پردانلودترین فایل های آپلود شده:</b> \n\n".$mtn,
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
              [['text'=>"♻️ بروزرسانی ♻️",'callback_data'=>"uptopup"]],
              ]
        ])
    		]);
    		factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "با موفقیت بروزرسانی شد .",
        'show_alert' => false
    ]);
} else {
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"❌ چیزی آپلود نشده است .",
'parse_mode'=>"HTML",
    		]);
} 
        }
#==============================جدیدترین ها==================================
  elseif($text == "🆕 جدیدترین ها" ){ 
$sql = "SELECT * FROM files ORDER BY zaman DESC LIMIT 5";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
$mtn = "";
    while($row = $result->fetch_assoc()) {
    $code = $row['code'];
    $dl = $row['dl'];
    $type = doc($row['file_type']);
     $mtn = $mtn."🌀 کد : <code>$code</code>
📥 تعداد دانلود : $dl
🔖 نوع فایل : <b>$type</b>
🔗 لینک دریافت : <a href='https://telegram.me/$bottag?start=dl_$code'> دانلود فایل</a>\n\n";
    }
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🆕 <b>جدیدترین فایل های آپلود شده: </b> :\n\n".$mtn,
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"♻️ بروزرسانی ♻️",'callback_data'=>"uptopu2p"]],
              ]
        ])
    		]);
} else {
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ چیزی آپلود نشده است .",
'parse_mode'=>"HTML",
    		]);
} 
    }
    elseif($data == "uptopu2p"){
$sql = "SELECT * FROM files ORDER BY zaman DESC LIMIT 5";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
$mtn = "";
    while($row = $result->fetch_assoc()) {
    $code = $row['code'];
    $dl = $row['dl'];
    $type = doc($row['file_type']);
     $mtn = $mtn."🌀 کد : <code>$code</code>
📥 تعداد دانلود : $dl
🔖 نوع فایل : <b>$type</b>
🔗 لینک دریافت : <a href='https://telegram.me/$bottag?start=dl_$code'> دانلود فایل</a>\n\n";
    }
    factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"🆕 <b>جدیدترین فایل های آپلود شده: </b> :\n\n".$mtn,
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
         [['text'=>"♻️ بروزرسانی ♻️",'callback_data'=>"uptopu2p"]],
              ]
        ])
    		]);
    		factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "با موفقیت بروزرسانی شد .",
        'show_alert' => false
    ]);
} else {
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"❌ چیزی آپلود نشده است .",
'parse_mode'=>"HTML",
    		]);
} 
        }
#==============================ارسال فایل توسط کاربرا=========================
  elseif($text == "📤 ارسال رسانه" ){ 
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ فایل مورد نظر خود را برای ادمین ارسال کنید

🔻 فایل مورد نظر باید شامل موارد زیر باشد:

✅ عکس ✅ فیلم ✅ صوت ✅ سند
",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]);   
	$connect->query("UPDATE user SET step = 'sendfilebyuser' WHERE id = '$from_id'");      		
  }
  elseif($user['step'] == "sendfilebyuser" and $text != "🏠 برگشت به منو"){ 
 if($message->document || $message->video || $message->photo || $message->audio) {
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ فایل شما با موفقیت دریافت شد و پس از بررسی توسط ادمین در ربات منتشر میشود",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]); 
 factweb('ForwardMessage',[
'chat_id'=>$admins[0],
'from_chat_id'=>$chat_id,
'message_id'=>$message_id,
]);	
	factweb('sendmessage',[    
	    'chat_id'=>$admins[0],
			'text'=>"👆🏻 یک فایل توسط کاربر $chat_id ارسال شد؛
",
			 'parse_mode'=>"MarkDown",
			 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"🚫 بلاک کردن کاربر",'callback_data'=>"blockusernow_$chat_id"]]
              ]
        ])
			 
	]);
	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id'");    
 }
 else{
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ خطا

پیام ارسالی شما باید عکس ، فیلم ، صوت یا سند باشد!",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]);  
 }
}
#==========================BLOCK USER SENDER======================
        elseif(strpos($data,"blockusernow_") !== false ){
         $block = str_replace("blockusernow_",null,$data);
    		 $connect->query("UPDATE user SET step = 'ban' WHERE id = '$block' LIMIT 1");	
    		 factweb('sendmessage',[
	'chat_id'=>$block,
'text'=>"❌ شما از ربات مسدود‌ شدید .",
'parse_mode'=>"HTML",
    		]);
    		 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"<code>$block</code> مسدود شد .⭕",
'parse_mode'=>"HTML",
    		]);
    		 }
#========================SUPPORT TIME===================================
        elseif($text=="👨🏼‍💻 پشتیبانی" ){
        factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"👨🏼‍💻 به بخش پشتیبانی ربات خوش آمدید؛

🔹در صورت سوال یا مشکل در مورد ربات ، آن را با ما در میان بگذارید؛",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]); 
   	$connect->query("UPDATE user SET step = 'sendpmtoadmin' WHERE id = '$from_id'");     		
    		 }
  elseif($user['step'] == "sendpmtoadmin" and $text != "🏠 برگشت به منو"){ 
            factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ پیام شما با موفقیت به ادمین ارسال شد و پس از بررسی از همین طریق خدمت شما اطلاع داده خواهد شد",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]); 
                factweb('sendmessage',[
	'chat_id'=>$admins[0],
'text'=>"🧑‍💻 ادمین عزیز یک پیام از $chat_id دارید :

 
متن پیام :

$text

🔻 چه کار کنم؟",
'parse_mode'=>"HTML",
       'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"📝 پاسخ به کاربر",'callback_data'=>"replytouser_$chat_id"],['text'=>"🚫 بلاک کردن کاربر",'callback_data'=>"blocksuser_$chat_id"]]
              ]
        ])
    		]);	
   	$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id'");     	   		
  }
        elseif(strpos($data,"replytouser_") !== false ){
$ok = str_replace("replytouser_",null,$data);
            factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ پیام خود را به کاربر ارسال کنید",
'parse_mode'=>"HTML",
    'message_id'=>$message_id,
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]);
   	$connect->query("UPDATE user SET step = 'replytouser',step2 ='$ok' WHERE id = '$from_id'");     	   		    		
}
  elseif($user['step'] == "replytouser" and $text != "🏠 برگشت به منو"){ 
      $usertepl=$user['step2'];
                 factweb('sendmessage',[
	'chat_id'=>$usertepl,
'text'=>"
👨🏻‍✈️ پیام ادمین به شما:

$text",
'parse_mode'=>"HTML",
    'message_id'=>$message_id,
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]); 
                     factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"پیام با موفقیت به کاربر ارسال شد",
'parse_mode'=>"HTML",
    'message_id'=>$message_id,
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"🏠 برگشت به منو"]],
        
              ],	'resize_keyboard'=>true
        ])
    		]);		
  	$connect->query("UPDATE user SET step = 'none',step2 ='none' WHERE id = '$from_id'");     	   	   		
  }
#==============================LIKE AND DISLIKE=========================
        elseif(strpos($data,"like_") !== false ){
$ok = str_replace("like_",null,$data);
$filesgg = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));  
$alllikes=$filesgg["likes"];  
$dislikes=$filesgg["dislikes"];  
$alllikess=$alllikes+1;
$connect->query("UPDATE files SET likes = $alllikess WHERE code = '$ok' LIMIT 1"); 
#=
    			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
       'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"👍🏻 $alllikess",'callback_data'=>"none"],['text'=>"👎🏻 $dislikes",'callback_data'=>"none"]]
              ]
        ])
    ]);
     $linkfile="https://telegram.me/$bottag?start=dl_$ok";
    $file_type= $filesgg['file_type'];
    $file_size=$filesgg['file_size'];
      $type2 = doc($file_type);
    			factweb('editMessageReplyMarkup',[
    'chat_id' => $settings['chupl'], 
    'message_id' =>$filesgg['msg_id'],
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

		[['text'=>"👁 مشاهده / دانلود 👁",'url'=>$linkfile]],
			  	[['text'=>"🔸 حجم فایل: $file_size",'callback_data'=>"nocall"],['text'=>"🔹 نوع فایل: $type2",'callback_data'=>"nocall"]],
              [['text'=>"👍🏻 تعداد $alllikess کاربر این $type2 را پسندیده اند",'callback_data'=>"nocall"]]
              ]
        ])
    		]);	
    }
            elseif(strpos($data,"disliuke_") !== false ){
$okk = str_replace("disliuke_",null,$data);
$fil = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$okk' LIMIT 1"));  
$likethis=$fil["likes"];  
$dislike=$fil["dislikes"];  
$alldislikess=$dislike+1;
$connect->query("UPDATE files SET dislikes = $alldislikess WHERE code = '$okk' LIMIT 1");
    	factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
       'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"👍🏻 $likethis",'callback_data'=>"none"],['text'=>"👎🏻 $alldislikess",'callback_data'=>"none"]]
              ]
        ])
    ]);
    }
#=======================================================================
   elseif($text == $ramzvorodadmin && in_array($from_id,$admins)){
    if($user['id'] == null ){
  $connect->query("INSERT INTO user (id , step , step2 , step3 , step4 , step5 , spam,timejoin) VALUES ('$from_id', 'none', 'none', 'none', 'none', 'none', '0','$dateen')");
  }else{
    		$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = 'none' WHERE id = '$from_id'");  
  }
  factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
👤 به پنل مدیریت ربات خوش آمدید

📅 تاریخ : <code>$ToDay $date $time</code>

📆 Date: <code>$ToDayen $dateen</code>

🔻 یکی از گزینه های زیر را انتخاب کنید:

    ",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[
[['text'=>"👥 آمار کامل ربات و کاربران 👥"]],
[['text'=>"📨 | فروارد همگانی"],['text'=>"📨 | پیام همگانی"]],
[['text'=>"📣 | تغییر قفل کانال"],['text'=>"🗂 تنظیمات رسانه"]],
[['text'=>"📤 آپلود تکی/گروهی رسانه"]],
[['text'=>"ℹ️ | اطلاعات رسانه"],['text'=>"🗂 | تمام رسانه ها"]],
[['text'=>"🔈 تنظیمات کانال رسانه"]],
[['text'=>"👁‍🗨 تنظیم سین اجباری"],['text'=>"👌🏻 تنظیم ری اکشن اجباری"]],
[['text'=>"📢 تنظیم تبلیغات"],['text'=>"⚙️ شخصی سازی ربات ⚙️"]],
[['text'=>"📛 | تنظیم تایم حذف"]],
[['text'=>"📛 | مسدود کردن"],['text'=>"❇️ | آزاد کردن"]],
[['text'=>"❌ | ربات خاموش"],['text'=>"✅ | ربات روشن"]],
[['text'=>"🏠 برگشت به منو"]],
 	],
            	'resize_keyboard'=>true
       		])
       		]);
  } 
  elseif($text == "🔙 منوی پنل" && in_array($from_id,$admins)){  
  if($user['id'] == null ){
   $connect->query("INSERT INTO user (id , step , step2 , step3 , step4 , step5 , spam,timejoin) VALUES ('$from_id', 'none', 'none', 'none', 'none', 'none', '0','$dateen')");
  }else{
    		$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = 'none' WHERE id = '$from_id'");  
  } 
  factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
👤 به پنل مدیریت ربات خوش آمدید

📅 تاریخ : <code>$ToDay $date $time</code>

📆 Date: <code>$ToDayen $dateen</code>

🔻 یکی از گزینه های زیر را انتخاب کنید:
  
    ",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[
[['text'=>"👥 آمار کامل ربات و کاربران 👥"]],
[['text'=>"📨 | فروارد همگانی"],['text'=>"📨 | پیام همگانی"]],
[['text'=>"📣 | تغییر قفل کانال"],['text'=>"🗂 تنظیمات رسانه"]],
[['text'=>"📤 آپلود تکی/گروهی رسانه"]],
[['text'=>"ℹ️ | اطلاعات رسانه"],['text'=>"🗂 | تمام رسانه ها"]],
[['text'=>"🔈 تنظیمات کانال رسانه"]],
[['text'=>"👁‍🗨 تنظیم سین اجباری"],['text'=>"👌🏻 تنظیم ری اکشن اجباری"]],
[['text'=>"📢 تنظیم تبلیغات"],['text'=>"⚙️ شخصی سازی ربات ⚙️"]],
[['text'=>"📛 | تنظیم تایم حذف"]],
[['text'=>"📛 | مسدود کردن"],['text'=>"❇️ | آزاد کردن"]],
[['text'=>"❌ | ربات خاموش"],['text'=>"✅ | ربات روشن"]],
[['text'=>"🏠 برگشت به منو"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
  } 
  
    elseif($text == "🗂 تنظیمات رسانه" && in_array($from_id,$admins)){  
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
🔻 یکی از گزینه های زیر را انتخاب کنید:
    ",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[
        [['text'=>"❎ | حذف رسانه"]],
            	[['text'=>"🔒 | تنظیم پسورد"],['text'=>"🚷 | محدودیت دانلود"]],
            	[['text'=>"💫 | تنظیم قفل آپلود"],['text'=>"🔥 | تنظیم ضد فیلتر"]],
            	[['text'=>"📥 | تنظیم دانلود فیک"],['text'=>"👍🏻 | تنظیم لایک فیک"]],
            			[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);    
    }
    #====================لایک فیک==================
    elseif($text=="📥 | تنظیم دانلود فیک" ){  
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 | لطفا کد آپلود را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setdlfake' WHERE id = '$from_id' LIMIT 1");	
    } 
    }
    elseif($user['step'] == "setdlfake" && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ تعداد دانلود فیک خود را وارد کنید",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setdllike_$text' WHERE id = '$from_id' LIMIT 1");	
    			}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    } 
}
}
elseif(strpos($user['step'],"setdllike_") !== false && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
if(!is_numeric($text) || $text < 1 || $text > 100000000){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"❌ مقدار وارد شده معتبر نیست" ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}   
else{
$ok = str_replace("setdllike_",null,$user['step']);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
$file_size=$files['file_size'];
$file_type=$files['file_type'];
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ با موفقیت انجام شد",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE files SET dl = '$text' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
}    
}    
    
    #======================دانلود فیک====================
 elseif($text=="👍🏻 | تنظیم لایک فیک" ){   
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 | لطفا کد آپلود را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setlikefake' WHERE id = '$from_id' LIMIT 1");	
    } 
    }
    elseif($user['step'] == "setlikefake" && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ تعداد لایک فیک خود را وارد کنید",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setfakelike_$text' WHERE id = '$from_id' LIMIT 1");	
    			}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    } 
}
}
elseif(strpos($user['step'],"setfakelike_") !== false && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
if(!is_numeric($text) || $text < 1 || $text > 100000000){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"❌ مقدار وارد شده معتبر نیست" ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}   
else{
$ok = str_replace("setfakelike_",null,$user['step']);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ با موفقیت انجام شد",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
     $linkfile="https://telegram.me/$bottag?start=dl_$ok";
    $file_type= $files['file_type'];
    $file_size=$files['file_size'];
      $type2 = doc($file_type);
    			factweb('editMessageReplyMarkup',[
    'chat_id' => $settings['chupl'], 
    'message_id' =>$files['msg_id'],
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

		[['text'=>"👁 مشاهده / دانلود 👁",'url'=>$linkfile]],
			  	[['text'=>"🔸 حجم فایل: $file_size",'callback_data'=>"nocall"],['text'=>"🔹 نوع فایل: $type2",'callback_data'=>"nocall"]],
              [['text'=>"👍🏻 تعداد $text کاربر این $type2 را پسندیده اند",'callback_data'=>"nocall"]]
              ]
        ])
    		]);	 		
    		$connect->query("UPDATE files SET likes = '$text' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
}    
}    
  
#===========================
    
    elseif($text == "🔈 تنظیمات کانال رسانه" && in_array($from_id,$admins)){  
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
🔻 یکی از گزینه های زیر را انتخاب کنید:
    ",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[

            	[['text'=>"💬 | تنظیم متن چنل"],['text'=>"📣 تنظیم کانال رسانه"]],
            		[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);    
    }    
  elseif(strpos($data,"pnlzdfilter_") !== false ){
    if(in_array($chat_id,$admins)){
$ok = str_replace("pnlzdfilter_",null,$data);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💎 لطفا انتخاب کنید 

️ ℹ️ فایل شماره : <code>$ok</code>
👇🏻 ضد فیلتر برای کد آپلود بالا روشن/خاموش شود",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"❌ خاموش"],['text'=>"✅ روشن"]],
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'setzdfilpn_$ok' WHERE id = '$from_id' LIMIT 1");	
    			}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    }
}
}
// 
elseif(strpos($user['step'],"setzdfilpn_") !== false && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$ok = str_replace("setzdfilpn_",null,$user['step']);
if($text == "❌ خاموش" or $text == "✅ روشن" ){
if($text == "✅ روشن"){
$oonobbin = "on";
$textttt = "روشن";
}
if($text == "❌ خاموش"){
$oonobbin = "off";
$textttt = "خاموش";
} 
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['zd_filter'] != $oonobbin ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💥 ضد فیلتر برای کد آپلود ( $ok ) با موفقیت $textttt شد !",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE files SET zd_filter = '$oonobbin' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    		}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 ضد فیلتر برای کد آپلود ( $ok ) قبلا $textttt بود!",
'parse_mode'=>"HTML",
    		]);
}
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا فقط یکی از گزینه های کیبورد را انتخاب کنید :",
'parse_mode'=>"HTML",
    		]);
}
}
}
elseif(strpos($data,"ghdpnl_") !== false ){ 
    if(in_array($chat_id,$admins)){
$ok = str_replace("ghdpnl_",null,$data);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💎 لطفا انتخاب کنید 

️ ℹ️ فایل شماره : <code>$ok</code>
👇🏻 قفل چنل برای کد آپلود بالا روشن/خاموش شود",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"❌ خاموش"],['text'=>"✅ روشن"]],
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    			$connect->query("UPDATE user SET step = 'setghfpnl_$ok' WHERE id = '$from_id' LIMIT 1");	
   	}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    }
}
}
elseif(strpos($user['step'],"setghfpnl_") !== false && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$ok = str_replace("setghfpnl_",null,$user['step']);
if($text == "❌ خاموش" or $text == "✅ روشن" ){
if($text == "✅ روشن"){
$oonobbin = "on";
$textttt = "روشن";
}
if($text == "❌ خاموش"){
$oonobbin = "off";
$textttt = "خاموش";
}
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['ghfl_ch'] != $oonobbin ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💥 قفل چنل برای کد آپلود ( $ok ) با موفقیت $textttt شد !",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE files SET ghfl_ch = '$oonobbin' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    		}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 ضد فیلتر برای کد آپلود ( $ok ) قبلا $textttt بود!",
'parse_mode'=>"HTML",
    		]);
}
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا فقط یکی از گزینه های کیبورد را انتخاب کنید :",
'parse_mode'=>"HTML",
    		]);
}
} 
}
#=====================شخصی سازی ربات ها============
elseif($text=="⚙️ شخصی سازی ربات ⚙️" || $data=="sakhsisazimenu"){  
    if(in_array($chat_id,$admins)){
          if($topdlbut=="on"){
          $topdlbut="✅ روشن";  
          }
          else{
          $topdlbut="❌ خاموش" ;
          }
              if($newdlbut=="on"){
          $newdlbut="✅ روشن";  
          }
          else{
          $newdlbut="❌ خاموش" ;
          }
             if($supbut=="on"){
          $supbut="✅ روشن";  
          }
          else{
          $supbut="❌ خاموش" ;
          }
              if($sendbut=="on"){
          $sendbut="✅ روشن";  
          }
          else{
          $sendbut="❌ خاموش" ;
          }
           
         
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ برای شخصی سازی ربات یکی از گزینه های زیر را انتخاب کنید:
",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
                [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
            			
 	],
            	'resize_keyboard'=>true
       		])
            ]);	   
    }
}

elseif ($data == "changesendbut" && in_array($chat_id, $admins)) {
      if($topdlbut=="on"){
          $topdlbut="✅ روشن";  
          }
          else{
          $topdlbut="❌ خاموش" ;
          }
              if($newdlbut=="on"){
          $newdlbut="✅ روشن";  
          }
          else{
          $newdlbut="❌ خاموش" ;
          }
             if($supbut=="on"){
          $supbut="✅ روشن";  
          }
          else{
          $supbut="❌ خاموش" ;
          }
  if ($sendbut == "on") {
        $connect->query("UPDATE settings SET sendbut = 'off'");
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ خاموش شد",
            'show_alert' => false
        ]);
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ],
            ])
        ]);
    } else {
        $connect->query("UPDATE settings SET sendbut = 'on'");
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ]
            ])
        ]);
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ فعال شد",
            'show_alert' => false
        ]);
    }   
}
#=========
elseif ($data == "changesupbut" && in_array($chat_id, $admins)) {
       if($topdlbut=="on"){
          $topdlbut="✅ روشن";  
          }
          else{
          $topdlbut="❌ خاموش" ;
          }
              if($newdlbut=="on"){
          $newdlbut="✅ روشن";  
          }
          else{
          $newdlbut="❌ خاموش" ;
          }
              if($sendbut=="on"){
          $sendbut="✅ روشن";  
          }
          else{
          $sendbut="❌ خاموش" ;
          }
  if ($supbut == "on") {
        $connect->query("UPDATE settings SET supportbut = 'off'");
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ خاموش شد",
            'show_alert' => false
        ]);
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ],
            ])
        ]);
    } else {
        $connect->query("UPDATE settings SET supportbut = 'on'");
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ]
            ])
        ]);
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ فعال شد",
            'show_alert' => false
        ]);
    }   
}
#========
elseif ($data == "changetopbut" && in_array($chat_id, $admins)) {
              if($newdlbut=="on"){
          $newdlbut="✅ روشن";  
          }
          else{
          $newdlbut="❌ خاموش" ;
          }
             if($supbut=="on"){
          $supbut="✅ روشن";  
          }
          else{
          $supbut="❌ خاموش" ;
          }
              if($sendbut=="on"){
          $sendbut="✅ روشن";  
          }
          else{
          $sendbut="❌ خاموش" ;
          }
  if ($topdlbut == "on") {
        $connect->query("UPDATE settings SET topdlbut = 'off'");
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ خاموش شد",
            'show_alert' => false
        ]);
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ],
            ])
        ]);
    } else {
        $connect->query("UPDATE settings SET topdlbut = 'on'");
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"$newdlbut",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ]
            ])
        ]);
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ فعال شد",
            'show_alert' => false
        ]);
    }   
}
#========
elseif ($data == "changenewbut" && in_array($chat_id, $admins)) {
      if($topdlbut=="on"){
          $topdlbut="✅ روشن";  
          }
          else{
          $topdlbut="❌ خاموش" ;
          }
             if($supbut=="on"){
          $supbut="✅ روشن";  
          }
          else{
          $supbut="❌ خاموش" ;
          }
              if($sendbut=="on"){
          $sendbut="✅ روشن";  
          }
          else{
          $sendbut="❌ خاموش" ;
          }
  if ($newdlbut == "on") {
        $connect->query("UPDATE settings SET newdlbut = 'off'");
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ خاموش شد",
            'show_alert' => false
        ]);
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ],
            ])
        ]);
    } else {
        $connect->query("UPDATE settings SET newdlbut = 'on'");
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$sendbut",'callback_data'=>"changesendbut"],['text'=>"🔹 ارسال رسانه 🔹",'callback_data'=>"none"]],
            	[['text'=>"$supbut",'callback_data'=>"changesupbut"],['text'=>"🔹 پشتیبانی 🔹",'callback_data'=>"none"]],
            	[['text'=>"$topdlbut",'callback_data'=>"changetopbut"],['text'=>"🔹 پردانلودترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"changenewbut"],['text'=>"🔹 جدیدترین ها 🔹",'callback_data'=>"none"]],
            	[['text'=>"🔹 تغییر متن شروع 🔹",'callback_data'=>"changetextstart"]]
                ]
            ])
        ]);
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ فعال شد",
            'show_alert' => false
        ]);
    }   
}
#============
elseif ($data == "changetextstart" && in_array($chat_id, $admins)) {
       factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ متن شروع را ارسال کنید


✅ راهنمای استفاده از امکانات متن :
🌀 نمونه برجسته کردن متن :
<b> متن شما </b> 
🌀 نمونه کج کردن متن :
<i> متن شما </i>
🌀 نمونه کد کردن متن :
<code> متن شما </code>

",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		
    	$connect->query("UPDATE user SET step = 'settextstart' WHERE id = '$from_id' LIMIT 1");			
}
elseif($user['step']=="settextstart" & in_array($chat_id,$admins)){

    factweb('sendmessage',[
      'chat_id'=>$chat_id,  
      'text'=>"متن با موفقیت تغییر پیدا کرد",
      'parse_mode'=>"HTML",
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [['text'=>"🔙 برگشت به منو شخصی سازی ",'callback_data'=>"sakhsisazimenu"]]
        ],
        'resize_keyboard'=>true
      ])
    ]);

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE settings SET starttext = '$text'");

}
#=====================تنظیم تبلیغات بین پست=============
elseif($text=="📢 تنظیم تبلیغات" || $data=="adsmenu"){  
    if(in_array($chat_id,$admins)){
            if($checkads=="on"){
          $checkads="✅ روشن";  
        }
        else{
           $checkads="❌ خاموش" ;
           
        }
     
 $postali= factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📢 وضعیت تبلیغات بین پست 

متن کنونی : 
$textads

🔻برای تغییر وضعیت و یا تغییر متن یکی از گزینه های زیر را انتخاب کنید:",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	[['text'=>"$checkads",'callback_data'=>"adschange"],['text'=>"📢 وضعیت تبلیغ بین پست :",'callback_data'=>"none"]],
            	[['text'=>"تغییر متن تبلیغ",'callback_data'=>"adschangetext"]],
            			
 	],
            	'resize_keyboard'=>true
       		])
    	    			])->result;	
        
    }
}

elseif ($data == "adschange" && in_array($chat_id, $admins)) {
    if ($checkads == "on") {
        $connect->query("UPDATE adspost SET checkads = 'off'");
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "تبلیغات بین پست خاموش شد",
            'show_alert' => false
        ]);
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "❌ خاموش", 'callback_data' => "adschange"], ['text' => "📢 وضعیت تبلیغ بین پست:", 'callback_data' => "none"]],
                    [['text' => "تغییر متن تبلیغ", 'callback_data' => "adschangetext"]],
                ],
            ])
        ]);
    } else {
        $connect->query("UPDATE adspost SET checkads = 'on'");
        factweb('editMessageReplyMarkup', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "✅ روشن", 'callback_data' => "adschange"], ['text' => "📢 وضعیت تبلیغ بین پست:", 'callback_data' => "none"]],
                    [['text' => "تغییر متن تبلیغ", 'callback_data' => "adschangetext"]]
                ]
            ])
        ]);
        factweb('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "تبلیغات بین پست فعال شد",
            'show_alert' => false
        ]);
    }
}

elseif($data=="adschangetext" & in_array($chat_id,$admins)){
       factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"متن خود را ارسال کنید

✅ راهنمای استفاده از امکانات متن :

🌀 نمونه برجسته کردن متن :
<b> متن شما </b> 
🌀 نمونه کج کردن متن :
<i> متن شما </i>
🌀 نمونه کد کردن متن :
<code> متن شما </code>",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		
    	$connect->query("UPDATE user SET step = 'settextads' WHERE id = '$from_id' LIMIT 1");			
}
elseif($user['step']=="settextads" & in_array($chat_id,$admins)){

    factweb('sendmessage',[
      'chat_id'=>$chat_id,  
      'text'=>"متن با موفقیت تغییر پیدا کرد",
      'parse_mode'=>"HTML",
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [['text'=>"🔙 برگشت به منو تبلیغات ",'callback_data'=>"adsmenu"]]
        ],
        'resize_keyboard'=>true
      ])
    ]);

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE adspost SET textads = '$text'");


}
#=====================تنظیم سین خودکار================
elseif($text=="👁‍🗨 تنظیم سین اجباری" || $data=="seennoww"){  
    if(in_array($chat_id,$admins)){
        if($seencheck=="on"){
          $seenonoff="✅ روشن";  
        }
        else{
           $seenonoff="❌ خاموش" ;
           
        }
          if($seenchannel=="none"){
          $seenchannel="نامشخص";  
        }

        
      $porst=   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"👁 وضعیت سین اجباری کانال:

🔻 برای تغییر وضعیت، دکمه مورد نظر را انتخاب کنید؛

⚠️ توجه : در صورت فعال بودن سین اجباری ، ری اکشن اجباری غیرفعال میشود",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$seenonoff",'callback_data'=>"seenchange"],['text'=>"👁 وضعیت سین اجباری :",'callback_data'=>"none"]],
            		[['text'=>"$seenchannel",'callback_data'=>"seenchannelchange"],['text'=>"📢 کانال سین اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$seenadad پست آخر",'callback_data'=>"seentedadchange"],['text'=>"♾ تعداد سین اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$timefake ثانیه",'callback_data'=>"seettimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
            	'resize_keyboard'=>true
       		])
    	    		])->result;	
        
    }
}
elseif($data=="seenchange" & in_array($chat_id,$admins)){
          if($seenchannel=="none"){
          $seenchannel="نامشخص";  
        }

      if($seencheck=="on"){
          	$connect->query("UPDATE seen SET checkseen = 'off'");	
              factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "سین اجباری خاموش شد",
        'show_alert' => false
    ]);
            			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"seenchange"],['text'=>"👁 وضعیت سین اجباری :",'callback_data'=>"none"]],
            			[['text'=>"$seenchannel",'callback_data'=>"seenchannelchange"],['text'=>"📢 کانال سین اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$seenadad پست آخر",'callback_data'=>"seentedadchange"],['text'=>"♾ تعداد سین اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$timefake ثانیه",'callback_data'=>"seettimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
        ])
    		]);
      }
      else{
	$connect->query("UPDATE seen SET checkseen = 'on'");
	$connect->query("UPDATE reaction SET checkreact = 'off'");	
     factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"seenchange"],['text'=>"👁 وضعیت سین اجباری :",'callback_data'=>"none"]],
            		[['text'=>"$seenchannel",'callback_data'=>"seenchannelchange"],['text'=>"📢 کانال سین اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$seenadad پست آخر",'callback_data'=>"seentedadchange"],['text'=>"♾ تعداد سین اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$timefake ثانیه",'callback_data'=>"seettimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
        ])
    		]);
    		            factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "سین اجباری روشن شد",
        'show_alert' => false
    ]);  
      }
}
elseif($data=="seenchannelchange" & in_array($chat_id,$admins)){
       factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ کانال مورد نظر خود را وارد کنید:

⚠️ کانال باید عمومی باشد.
⚠️ ایدی کانال را بدون @ وارد کنید.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		
    	$connect->query("UPDATE user SET step = 'setchannel' WHERE id = '$from_id' LIMIT 1");			
}
elseif($user['step']=="setchannel" & in_array($chat_id,$admins)){

  if(strpos($text, '@') !==false){
    factweb('sendmessage',[
      'chat_id'=>$chat_id,
      'text'=>"❌ آیدی کانال باید بدون @  ارسال شود",
      'parse_mode'=>"HTML"
    ]);

  } else {

    factweb('sendmessage',[
      'chat_id'=>$chat_id,  
      'text'=>"✔️ کانال با موفقیت ست شد",
      'parse_mode'=>"HTML",
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [['text'=>"🔙 برگشت به منو سین اجباری",'callback_data'=>"seennoww"]]
        ],
        'resize_keyboard'=>true
      ])
    ]);

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE seen SET channelseen = '$text'");

  }

}
elseif($data=="seentedadchange" & in_array($chat_id,$admins)){

factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تعداد بازدیدها را از منوی زیر انتخاب کنید:",  
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'keyboard'=>[

[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]], 
[['text'=>"6"],['text'=>"7"],['text'=>"8"],['text'=>"9"],['text'=>"10"]],
[['text'=>"11"],['text'=>"12"],['text'=>"13"],['text'=>"14"],['text'=>"15"]],
[['text'=>"16"],['text'=>"17"],['text'=>"18"],['text'=>"19"],['text'=>"20"]], 
[['text'=>"21"],['text'=>"22"],['text'=>"23"],['text'=>"24"],['text'=>"25"]],
[['text'=>"26"],['text'=>"27"],['text'=>"28"],['text'=>"29"],['text'=>"30"]],
[['text'=>"31"],['text'=>"32"],['text'=>"33"],['text'=>"34"],['text'=>"35"]],
[['text'=>"36"],['text'=>"37"],['text'=>"38"],['text'=>"39"],['text'=>"40"]],
	[['text'=>"🔙 منوی پنل"]],
],
'resize_keyboard'=>true  
])

]);
  $connect->query("UPDATE user SET step = 'seentedadchangestep' WHERE id = '$from_id' LIMIT 1"); 
}
elseif($user['step']=="seentedadchangestep" & in_array($chat_id,$admins)){

if(!is_numeric($text) || $text < 1 || $text > 100){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"⛔ عدد وارد شده معتبر نیست. لطفا یک عدد بین 1 تا 100 وارد کنید." ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}
factweb('sendmessage',[
'chat_id'=>$chat_id,  
'text'=>"✔️ سین اجباری با موفقیت روی $text ست شد",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
]); 

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE seen SET adadseen = '$text'");   
}
elseif($data=="seettimefakechange" & in_array($chat_id,$admins)){

factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"🕰 تایم فیک برای انتظار کاربران را از منوی زیر وارد کنید:",  
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'keyboard'=>[

[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]], 
[['text'=>"6"],['text'=>"7"],['text'=>"8"],['text'=>"9"],['text'=>"10"]],
[['text'=>"11"],['text'=>"12"],['text'=>"13"],['text'=>"14"],['text'=>"15"]],
[['text'=>"16"],['text'=>"17"],['text'=>"18"],['text'=>"19"],['text'=>"20"]], 
[['text'=>"21"],['text'=>"22"],['text'=>"23"],['text'=>"24"],['text'=>"25"]],
[['text'=>"26"],['text'=>"27"],['text'=>"28"],['text'=>"29"],['text'=>"30"]],
	[['text'=>"🔙 منوی پنل"]],
],
'resize_keyboard'=>true  
])

]);
  $connect->query("UPDATE user SET step = 'seenchangetime' WHERE id = '$from_id' LIMIT 1"); 
}
elseif($user['step']=="seenchangetime" & in_array($chat_id,$admins)){

if(!is_numeric($text) || $text < 1 || $text > 30){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"⛔ عدد وارد شده معتبر نیست. لطفا یک عدد بین 1 تا 100 وارد کنید." ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}
factweb('sendmessage',[
'chat_id'=>$chat_id,  
'text'=>"✔️ تایم فیک با موفقیت روی $text ست شد",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
]); 

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE seen SET timefake = '$text'");   
}
#================================ری اکت اجباری======================
elseif($text=="👌🏻 تنظیم ری اکشن اجباری" || $data=="reactnoww"){ 
    if(in_array($chat_id,$admins)){
        if($reactcheck=="on"){
          $reactonoff="✅ روشن";  
        }
        else{
           $reactonoff="❌ خاموش" ;
           
        }
          if($seenchannel=="none"){
          $seenchannel="نامشخص";  
        }

        
      $porst=   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"👁 وضعیت ری اکشن اجباری کانال:

🔻 برای تغییر وضعیت، دکمه مورد نظر را انتخاب کنید؛

⚠️ توجه : در صورت فعال بودن ری اکشن اجباری ، سین اجباری غیرفعال میشود",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"$reactonoff",'callback_data'=>"reactnchange"],['text'=>"👌🏻 وضعیت ری اکشن اجباری :",'callback_data'=>"none"]],
            		[['text'=>"$reactchannel",'callback_data'=>"reacchannelchange"],['text'=>"📢 کانال ری اکشن اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$reactadad پست آخر",'callback_data'=>"reactedadchange"],['text'=>"♾ تعداد ری اکشن اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$reacttimefake ثانیه",'callback_data'=>"reactimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
            	'resize_keyboard'=>true
       		])
    	    		])->result;	
        
    }
}
elseif($data=="reactnchange" & in_array($chat_id,$admins)){
          if($reactchannel=="none"){
          $reactchannel="نامشخص";  
        }

      if($reactcheck=="on"){
          	$connect->query("UPDATE reaction SET checkreact = 'off'");	
              factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "ری اکشن اجباری خاموش شد",
        'show_alert' => false
    ]);
            			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"❌ خاموش",'callback_data'=>"reactnchange"],['text'=>"👌🏻 وضعیت ری اکشن اجباری :",'callback_data'=>"none"]],
            		[['text'=>"$reactchannel",'callback_data'=>"reacchannelchange"],['text'=>"📢 کانال ری اکشن اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$reactadad پست آخر",'callback_data'=>"reactedadchange"],['text'=>"♾ تعداد ری اکشن اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$reacttimefake ثانیه",'callback_data'=>"reactimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
        ])
    		]);
      }
      else{
	$connect->query("UPDATE reaction SET checkreact = 'on'");	
	$connect->query("UPDATE seen SET checkseen = 'off'");	
        			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
            	'inline_keyboard'=>[
            	    	[['text'=>"🔻 تغییر وضعیت 🔻",'callback_data'=>"none"],['text'=>"🔸 دستورات 🔸",'callback_data'=>"none"]],
            	[['text'=>"✅ روشن",'callback_data'=>"reactnchange"],['text'=>"👌🏻 وضعیت ری اکشن اجباری :",'callback_data'=>"none"]],
            		[['text'=>"$reactchannel",'callback_data'=>"reacchannelchange"],['text'=>"📢 کانال ری اکشن اجباری:",'callback_data'=>"none"]],
            			[['text'=>"$reactadad پست آخر",'callback_data'=>"reactedadchange"],['text'=>"♾ تعداد ری اکشن اجباری:",'callback_data'=>"none"]],
            				[['text'=>"$reacttimefake ثانیه",'callback_data'=>"reactimefakechange"],['text'=>"🕰 تایم فیک : ",'callback_data'=>"none"]],
 	],
        ])
    		]);
    		            factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "سین اجباری روشن شد",
        'show_alert' => false
    ]);  
      }
}
elseif($data=="reacchannelchange" & in_array($chat_id,$admins)){
       factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ کانال مورد نظر خود را وارد کنید:

⚠️ کانال باید عمومی باشد.
⚠️ ایدی کانال را بدون @ وارد کنید.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		
    	$connect->query("UPDATE user SET step = 'setchannelch' WHERE id = '$from_id' LIMIT 1");			
}
elseif($user['step']=="setchannelch" & in_array($chat_id,$admins)){

  if(strpos($text, '@') !==false){
    factweb('sendmessage',[
      'chat_id'=>$chat_id,
      'text'=>"❌ آیدی کانال باید بدون @  ارسال شود",
      'parse_mode'=>"HTML"
    ]);

  } else {

    factweb('sendmessage',[
      'chat_id'=>$chat_id,  
      'text'=>"✔️ کانال با موفقیت ست شد",
      'parse_mode'=>"HTML",
      'reply_markup'=>json_encode([
        'inline_keyboard'=>[
          [['text'=>"🔙 برگشت به منو سین اجباری",'callback_data'=>"reactnoww"]]
        ],
        'resize_keyboard'=>true
      ])
    ]);

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE reaction SET channelreact = '$text'");

  }

}
elseif($data=="reactedadchange" & in_array($chat_id,$admins)){

factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"✅ تعداد بازدیدها را از منوی زیر انتخاب کنید:",  
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'keyboard'=>[

[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]], 
[['text'=>"6"],['text'=>"7"],['text'=>"8"],['text'=>"9"],['text'=>"10"]],
[['text'=>"11"],['text'=>"12"],['text'=>"13"],['text'=>"14"],['text'=>"15"]],
[['text'=>"16"],['text'=>"17"],['text'=>"18"],['text'=>"19"],['text'=>"20"]], 
[['text'=>"21"],['text'=>"22"],['text'=>"23"],['text'=>"24"],['text'=>"25"]],
[['text'=>"26"],['text'=>"27"],['text'=>"28"],['text'=>"29"],['text'=>"30"]],
[['text'=>"31"],['text'=>"32"],['text'=>"33"],['text'=>"34"],['text'=>"35"]],
[['text'=>"36"],['text'=>"37"],['text'=>"38"],['text'=>"39"],['text'=>"40"]],
	[['text'=>"🔙 منوی پنل"]],
],
'resize_keyboard'=>true  
])

]);
  $connect->query("UPDATE user SET step = 'seentedadchangesteptwwo' WHERE id = '$from_id' LIMIT 1"); 
}
elseif($user['step']=="seentedadchangesteptwwo" & in_array($chat_id,$admins)){

if(!is_numeric($text) || $text < 1 || $text > 100){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"⛔ عدد وارد شده معتبر نیست. لطفا یک عدد بین 1 تا 100 وارد کنید." ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}
factweb('sendmessage',[
'chat_id'=>$chat_id,  
'text'=>"✔️ ری اکشن اجباری با موفقیت روی $text ست شد",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
]); 

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE reaction SET reacttedad = '$text'");   
}
elseif($data=="reactimefakechange" & in_array($chat_id,$admins)){

factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"🕰 تایم فیک برای انتظار کاربران را از منوی زیر وارد کنید:",  
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
'keyboard'=>[

[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]], 
[['text'=>"6"],['text'=>"7"],['text'=>"8"],['text'=>"9"],['text'=>"10"]],
[['text'=>"11"],['text'=>"12"],['text'=>"13"],['text'=>"14"],['text'=>"15"]],
[['text'=>"16"],['text'=>"17"],['text'=>"18"],['text'=>"19"],['text'=>"20"]], 
[['text'=>"21"],['text'=>"22"],['text'=>"23"],['text'=>"24"],['text'=>"25"]],
[['text'=>"26"],['text'=>"27"],['text'=>"28"],['text'=>"29"],['text'=>"30"]],
	[['text'=>"🔙 منوی پنل"]],
],
'resize_keyboard'=>true  
])

]);
  $connect->query("UPDATE user SET step = 'seenchangetimetwoo' WHERE id = '$from_id' LIMIT 1"); 
}
elseif($user['step']=="seenchangetimetwoo" & in_array($chat_id,$admins)){

if(!is_numeric($text) || $text < 1 || $text > 30){
  factweb('sendmessage',[
    'chat_id'=>$chat_id,
    'text'=>"⛔ عدد وارد شده معتبر نیست. لطفا یک عدد بین 1 تا 100 وارد کنید." ,
         'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
  ]);
  return;
}
factweb('sendmessage',[
'chat_id'=>$chat_id,  
'text'=>"✔️ تایم فیک با موفقیت روی $text ست شد",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
]); 

    $connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1"); 
    $connect->query("UPDATE reaction SET timefakereact = '$text'");   
}
#=================================================
elseif($text=="🔥 | تنظیم ضد فیلتر" ){  
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 | لطفا کد آپلود را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setzdfilll' WHERE id = '$from_id' LIMIT 1");	
    } 
    }
    elseif($user['step'] == "setzdfilll" && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💎 لطفا انتخاب کنید 

️ ℹ️ فایل شماره : <code>$text</code>
👇🏻 ضد فیلتر برای کد آپلود بالا روشن/خاموش شود",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
            	           	[['text'=>"❌ خاموش"],['text'=>"✅ روشن"]],
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setzdfilpn_$text' WHERE id = '$from_id' LIMIT 1");	
    			}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    } 
}
}
elseif($text=="💫 | تنظیم قفل آپلود" ){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 | لطفا کد آپلود را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setgfup' WHERE id = '$from_id' LIMIT 1");	
    }
    }
    elseif($user['step'] == "setgfup" && $text != '🔙 منوی پنل'){
    if(in_array($chat_id,$admins)){
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
if($files['code'] != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💎 لطفا انتخاب کنید 

️ ℹ️ فایل شماره : <code>$text</code>
👇🏻 قفل چنل برای کد آپلود بالا روشن/خاموش شود",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
            	           	[['text'=>"❌ خاموش"],['text'=>"✅ روشن"]],
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		$connect->query("UPDATE user SET step = 'setghfpnl_$text' WHERE id = '$from_id' LIMIT 1");	
    			}else{
   	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ | این کد آپلود وجود ندارد و یا حذف شده.

🔄 | لطفا دوباره امتحان کنید :",
'parse_mode'=>"HTML",
    		]);
    }
}
}
  elseif($text=="📛 | تنظیم تایم حذف" ){ 
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ لطفا تعداد دقیقه حذف فایل را از کیبورد انتخاب کنید ( در صورتی که بعد آپلود گزینه ضد فیلتر را بزنید ، بعد دقیقه مشخص از پی وی کاربر حذف میشود )

🔹 مقدار پیشفرض : 1 دقیقه
🔸 مقدار فعلی : $factwebir دقیقه

👇 لطفا از کیبورد انتخاب کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
           	[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]],
           	[['text'=>"10"],['text'=>"15"],['text'=>"30"]],
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setdeltime' WHERE id = '$from_id' LIMIT 1");	
    }
    }
    elseif($user['step'] == "setdeltime" && $text != "🔙 منوی پنل" ){
    $array5 = [1,2,3,4,5,10,15,30];
    if(in_array($text,$array5)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ با موفقیت تنظیم شد .

مقدار جدید : $text دقیقه",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE settings SET factwebir = '$text' WHERE botid = '$botid' LIMIT 1");	
    			$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    }else{
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ لطفا فقط از کیبورد انتخاب کنید 👇🏻",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
           	[['text'=>"1"],['text'=>"2"],['text'=>"3"],['text'=>"4"],['text'=>"5"]],
           	[['text'=>"10"],['text'=>"15"],['text'=>"30"]],
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    } 
    }
  elseif($text=="📣 | تغییر قفل کانال" ){
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ به بخش تنظیم چنل های قفل خوش آمدید.

💯 برای حذف چنل، از بخش لیست چنل چنل مورد نظر را حذف کنید .",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"➕ افزودن چنل"]],
							[['text'=>"🔙 منوی پنل"],['text'=>"📚 لیست چنل ها"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
}
} 
elseif($text=="➕ افزودن چنل" ){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"لطفا نوع چنلی که میخواهید اضافه کنید را از کیبورد انتخاب کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            								[['text'=>"عمومی"],['text'=>"خصوصی"]],
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'addch1' WHERE id = '$from_id' LIMIT 1");	
    } 
    }
    elseif($text=="عمومی" && $user['step'] == "addch1" ){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"لطفا یوزرنیم چنل عمومی را بدون @ ارسال کنید ( ربات را قبل ارسال بر ان چنل آدمین کرده باشید )",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'addchpub' WHERE id = '$from_id' LIMIT 1");	
    }
    }
    elseif($user['step'] == "addchpub" && $text != "🔙 منوی پنل" && !$data ){ 
    if(in_array($chat_id,$admins)){
      		 $textt = str_replace("@",null,$text);
      		 			$texttt = "@".$textt;
      		 			    $ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$texttt' LIMIT 1"));
    if($ch['link'] == null ){
    		 $admini = getChatstats("@$textt",API_KEY);
			if($admini == true ){
			$linkk = "https://t.me/$textt";
			$connect->query("INSERT INTO channels (idoruser , link) VALUES ('$texttt', '$linkk')");
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"چنل @$textt با موفقیت افزوده شد .",
'parse_mode'=>"HTML",
       'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"➕ افزودن چنل"]],
							[['text'=>"🔙 منوی پنل"],['text'=>"📚 لیست چنل ها"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		$connect->query("UPDATE user SET step = 'addch1' WHERE id = '$from_id' LIMIT 1");	
			}else{
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"خطا ! ربات بر چنل @$textt آدمین نیست !

ابتدا ربات را ادمین و سپس ارسال کنید تا افزوده شود.",
'parse_mode'=>"HTML",
    		]);
			}
					}else{
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"خطا ! قبلا چنلی با این ایدی ثبت شده !

لطفا دوباره ارسال فرمایید :",
'parse_mode'=>"HTML",
    		]);
			}
			}
			}
    elseif($text=="خصوصی" && $user['step'] == "addch1" ){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"لطفا آیدی عددی چنل خصوصی را ارسال کنید .
نمونه ایدی عددی چنل : 
-1009876262727
ربات را قبل ارسال حتما ادمین کرده باشید.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'addcpr' WHERE id = '$from_id' LIMIT 1");	
    }
    }
    elseif($user['step'] == "addcpr" && $text != "🔙 منوی پنل" && !$data ){
    if(in_array($chat_id,$admins)){
    $ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$text' LIMIT 1"));
    if($ch['link'] == null ){
    		 $admini = getChatstats($text,API_KEY);
			if(strpos($text,"-100") !== false && $admini == true ){
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"لطفا لینک خصوصی دعوت را ارسال کنید :",
'parse_mode'=>"HTML",
       'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		]) 
    		]);
    		$connect->query("UPDATE user SET step2 = '$text' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'addchpr1' WHERE id = '$from_id' LIMIT 1");	
			}else{
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"خطا ! ربات بر چنل $text آدمین نیست و یا ایدی ارسالی حاوی -100 نیست.

ابتدا ربات را ادمین و سپس ارسال کنید تا افزوده شود.",
'parse_mode'=>"HTML",
    		]);
			}
			}else{
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"خطا ! قبلا چنلی با این ایدی ثبت شده !

لطفا دوباره ارسال فرمایید :",
'parse_mode'=>"HTML",
    		]);
			}
			}
			}
			elseif($user['step'] == "addchpr1" && $text != "🔙 منوی پنل" && !$data ){
			if(in_array($chat_id,$admins)){
			if(strpos($text,"://t.me/") !== false ){
			$idus = $user['step2'];
			$connect->query("INSERT INTO channels (idoruser , link) VALUES ('$idus', '$text')");
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"چنل با موفقیت افزوده شد .",
'parse_mode'=>"HTML",
       'reply_markup'=>json_encode([
            	'keyboard'=>[
            	[['text'=>"➕ افزودن چنل"]],
							[['text'=>"🔙 منوی پنل"],['text'=>"📚 لیست چنل ها"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'addch1' WHERE id = '$from_id' LIMIT 1");	
			}else{
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"خطا! لینک ارسالی اشتباه است !

لطفا دوباره ارسال کنید:",
'parse_mode'=>"HTML",
    		]);
			}
			}
			}
   elseif($text=="📚 لیست چنل ها" ){  
    if(in_array($chat_id,$admins)){
    $chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
if($fil != 0){
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
for ($i=0; $i <= $fil; $i++){

$by = $i + 1;
$okk = $ar[$i];
$ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$okk' LIMIT 1"));
$link = $ch['link'];
if($link != null ){
$d4[] = [['text'=>"چنل شماره $by",'url'=>$link],['text'=>"❌ حذف",'callback_data'=>"delc_$okk"]];
}
}
factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"👇🏻 لیست تمام چنل های قفل",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
           'inline_keyboard'=>$d4
              ])
    		]); 
    		}else{
    		factweb('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"❌ هیچ چنل قفلی تنظیم نشده.",
'parse_mode'=>"HTML",
    		]); 
    		}
    } 
    }
    elseif(strpos($data,"delc_") !== false ){
    if(in_array($chat_id,$admins)){
    $ok = str_replace("delc_",null,$data);
    $chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
if($fil == 1){
$connect->query("DELETE FROM channels WHERE idoruser = '$ok'");	
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"👇🏻 لیست تمام چنل های قفل

❌ تمام چنل ها حذف شده است.",
'parse_mode'=>"HTML",
    		]); 
    factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "✅ چنل حذف شد .",
        'show_alert' => false
    ]);
}else{
$connect->query("DELETE FROM channels WHERE idoruser = '$ok'");	 
  $chs = mysqli_query($connect,"select idoruser from channels");
$fil = mysqli_num_rows($chs);
while($row = mysqli_fetch_assoc($chs)){
     $ar[] = $row["idoruser"];
}
for ($i=0; $i <= $fil; $i++){

$by = $i + 1;
$okk = $ar[$i];
$ch = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM channels WHERE idoruser = '$okk' LIMIT 1"));
$link = $ch['link'];
if($link != null ){
$d4[] = [['text'=>"چنل شماره $by",'url'=>$link],['text'=>"❌ حذف",'callback_data'=>"delc_$okk"]];
}
} 
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"👇🏻 لیست تمام چنل های قفل

❌ چنل حذف شد .",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
           'inline_keyboard'=>$d4
              ])
    		]); 
    factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "✅ چنل حذف شد .",
        'show_alert' => false
    ]);
    }
    }
   }
  elseif($text=="✅ | ربات روشن" ){
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✓   عملیات انجام شد .",
'parse_mode'=>"HTML",
    		]);
    		$connect->query("UPDATE settings SET bot_mode = 'on' WHERE botid = '$botid' LIMIT 1");	
}
} 
elseif($text=="❌ | ربات خاموش" ){
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✓   عملیات انجام شد .",
'parse_mode'=>"HTML",
    		]);
    		$connect->query("UPDATE settings SET bot_mode = 'off' WHERE botid = '$botid' LIMIT 1");	
} 
} 
  elseif($text=="🗂 | تمام رسانه ها" ){
    if(in_array($chat_id,$admins)){
    $chid = mysqli_query($connect,"select code from files");
$fil2 = mysqli_num_rows($chid);
if($fil2 != 0 ){
while($row = mysqli_fetch_assoc($chid)){
     $use[] = $row["code"];
}
for ($i=0; $i <= 9; $i++){

$shtr = $use[$i];
$treta = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$shtr' LIMIT 1"));
if($treta['code'] != null ){
$file_size = $treta['file_size'];
$zaman = $treta['zaman'];
$d4[] = [['text'=>"🔢 کد : $shtr با اندازه $file_size",'callback_data'=>"in_$shtr"]];
}
}

if($fil2 > 10.1){
$d4[] = [['text'=>"➡️ صفحه بعدی",'callback_data'=>'saf_2']];
}
if($fil2 > 10.1){
$cp = ceil($fil2 / 10);
}else{
$cp = 1;
}
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔢 تعداد آپلود شده ها : $fil2
📋 صفحه : 1 از $cp

✅ از دکمه های زیر شماره آپلود را انتخاب کنید :",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
             'inline_keyboard'=>$d4
              ])
    		]);
    		$connect->query("UPDATE user SET step = 'saf_2' WHERE id = '$from_id' LIMIT 1");	
    		}else{
    		factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ هیچ رسانه ای آپلود نشده است .",
'parse_mode'=>"HTML",
    		]);
    		}
}
} 
elseif(strpos($data,"saf_") !== false ){
 if(in_array($chat_id,$admins)){
$ok = str_replace("saf_",null,$data);
$a = $ok + 1;
$b = $ok - 1;
      $chid = mysqli_query($connect,"select code from files");
$fil2 = mysqli_num_rows($chid);
while($row = mysqli_fetch_assoc($chid)){
     $use[] = $row["code"];
}
    		$szrb = $b*10;
    		$szrb2 = $szrb+9;
for($i = $szrb; $i <= $szrb2;$i++){
$shtr = $use[$i];
$treta = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$shtr' LIMIT 1"));
if($treta['code'] != null ){
$file_size = $treta['file_size'];
$zaman = $treta['zaman'];
$d4[] = [['text'=>"🔢 کد : $shtr با اندازه $file_size",'callback_data'=>"in_$shtr"]];
}
}

$bomm = $ok * 10 + 0.1;
if($ok != 1){
$kobs = "⬅️ صفحه قبلی";
}
if($fil2 > $bomm ){
$d4[] = [['text'=>"$kobs",'callback_data'=>"saf_$b"],['text'=>"➡️ صفحه بعدی",'callback_data'=>"saf_$a"]];
}else{
$d4[] = [['text'=>"$kobs",'callback_data'=>"saf_$b"]];
}
if($fil2 > 10.1){
$cp = ceil($fil2 / 10);
}else{
$cp = 1;
}
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"🔢 تعداد آپلود شده ها : $fil2
📋 صفحه : $ok از $cp

✅ از دکمه های زیر شماره آپلود را انتخاب کنید :",
'parse_mode'=>"HTML",
  'reply_markup'=>json_encode([
             'inline_keyboard'=>$d4
              ])
    		]);
    		$connect->query("UPDATE user SET step = 'saf_$a' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif(strpos($data,"in_") !== false ){
$ok = str_replace("in_",null,$data);
$s = str_replace("saf_",null,$user['step']);
$kio = $s - 1;
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
   $file_size = $files['file_size'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   if($files['msg_id'] != 'none'){
   $yorn = '✅ ارسال شده است !';
   $khikhi = '✅ ارسال شده در چنل!';
   $khidata = 'none';
   }else{
   $khikhi = 'ارسال به چنل';
   $yorn = '❌ ارسال نشده است !';
   $khidata = "send2_$ok";
   }
   if($files['pass'] == 'none'){
   $ispass = '❌ بدون پسورد';
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$ok";
   }else{
   $ispass = $files['pass'];
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$ok";
   }
   if($files['mahdodl'] == 'none'){
   $ismahd = '❌ بدون محدودیت دانلود';
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$ok";
   }else{
   $ismahd = $files['mahdodl'];
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$ok";
   }
      if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅';
   }else{
   $hesofff2 = '❌';
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌'; 
   }
   $file_type = doc($files['file_type']);
factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"ℹ️ اطلاعات کامل این رسانه یافت شد :

▪️ کد رسانه : <code>$ok</code>

🔹 نوع : $file_type
🔸 اندازه : $file_size
🔹 زمان آپلود : $zaman
🔸 تعداد دانلود : $dl 

🔹 توضیحات : $tozihat

❓ ارسال به چنل : $yorn
🔓 پسورد : <code>$ispass</code>
🖇 محدودیت دانلود : $ismahd
📌 ضد فیلتر : $hesofff
🔐 قفل چنل : $hesofff2
🔗 لینک دریافت : https://telegram.me/$bottag?start=dl_$ok

🔸 توسط ادمین <a href='tg://user?id=$id'>$id</a> آپلود شده است .",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
           'inline_keyboard'=>[
            [['text'=>"$khikhi",'callback_data'=>"$khidata"],['text'=>"حذف",'callback_data'=>"delu_$ok"]],
             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
                          [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"pnlzdfilter_$ok"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghdpnl_$ok"]],
               [['text'=>"🔙 برگشت به صفحات",'callback_data'=>"saf_$kio"]],
                                               ]
              ])
    		]);
    		  		$connect->query("UPDATE user SET step = 'saf_$kio' WHERE id = '$from_id' LIMIT 1");	
}
elseif(strpos($data,"delu_") !== false ){
 if(in_array($chat_id,$admins)){
 $ok = str_replace("delu_",null,$data);
 $kio = str_replace("saf_",null,$user['step']);
  $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
  factweb('deletemessage', [
'chat_id' => $settings['chupl'], 
'message_id' =>$files['msg_id'],
]);
 $connect->query("DELETE FROM files WHERE code = '$ok'");	
     $chid = mysqli_query($connect,"select code from files");
$fil2 = mysqli_num_rows($chid);
if($fil2 != 0 ){
if($user['step'] != "infoupl"){
$motghier = "🔙 برگشت به صفحات";
$connect->query("UPDATE user SET step = 'saf_$kio' WHERE id = '$from_id' LIMIT 1");	
}else{
$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
}
  	factweb('editMessagetext',[
   'chat_id'=>$chat_id,
   'message_id'=>$message_id,
'text'=>"📌 کد آپلود : <code>$ok</code>

✅ با موفقیت از ربات حذف گردید .",
 'parse_mode'=>"HTML",
 'reply_markup'=>json_encode([
           'inline_keyboard'=>[
               [['text'=>"$motghier",'callback_data'=>"saf_$kio"]],
                                               ]
              ])
    		]); 
}
}
elseif($text == "🚷 | محدودیت دانلود"){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💯 لطفا شماره آپلود را ارسال کنید:",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'getcodeuu' WHERE id = '$from_id' LIMIT 1");	
}
}
elseif($user['step'] == "getcodeuu" && $text != "🔙 منوی پنل"){
    if(in_array($chat_id,$admins)){
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
    if($files['code'] != null && is_numeric($text) == true ){
    if($files['mahdodl'] != 'none'){
    $khi = '❌ برداشتن محدودیت';
    }else{
    $khi = null;
    }
      factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ لطفا حداکثر تعداد دانلود فایل شماره $text را بصورت عدد لاتین (123) وارد فرمایید:",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"],['text'=>"$khi"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'newpmah_$text' WHERE id = '$from_id' LIMIT 1");	
    }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این کد آپلود یافت نشد و یا بصورت لاتین(123) ارسال نکردید .

💯 لطفا دوباره امتحان کنید .",
'parse_mode'=>"HTML",
    		]);
   }
    }
}
elseif(strpos($data,"mahdl_") !== false ){
    if(in_array($chat_id,$admins)){
$ok = str_replace("mahdl_",null,$data);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
   if($files['mahdodl'] != 'none'){
    $khi = '❌ برداشتن محدودیت';
        }else{
    $khi = null;
    }
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ لطفا حداکثر تعداد دانلود فایل شماره $ok را بصورت عدد لاتین (123) وارد فرمایید:",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"],['text'=>"$khi"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'newpmah_$ok' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif(strpos($user['step'],"newpmah_") !== false && $text != '🔙 منوی پنل' && $text != '❌ برداشتن محدودیت'){
    if(in_array($chat_id,$admins)){
$ok = str_replace("newpmah_",null,$user['step']);
if(is_numeric($text) == true){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔘 محدودیت دانلود تنظیم شد .

ℹ️ فایل شماره : <code>$ok</code>
🚷 محدودیت دانلود : <code>$text نفر</code>",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE files SET mahdodl = '$text' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا فقط یک عدد لاتین(123) ارسال کنید.",
'parse_mode'=>"HTML",
    		]);
}
}
}
elseif(strpos($user['step'],"newpmah_") !== false && $text == "❌ برداشتن محدودیت"){
if(in_array($chat_id,$admins)){
$ok = str_replace("newpmah_",null,$user['step']);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['code'] != null ){
$connect->query("UPDATE files SET mahdodl = 'none' WHERE code = '$ok' LIMIT 1");	
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ محدودیت دانلود برداشته شد !

ℹ️ فایل شماره : <code>$ok</code>",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
}
}
elseif(strpos($user['step'],"newpass_") !== false && $text == "❌ برداشتن پسورد"){
if(in_array($chat_id,$admins)){
$ok = str_replace("newpass_",null,$user['step']);
$files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
if($files['code'] != null ){
$connect->query("UPDATE files SET pass = 'none' WHERE code = '$ok' LIMIT 1");	
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ پسورد برداشته شد !

ℹ️ فایل شماره : <code>$ok</code>",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
}
} 
elseif($text == "🔒 | تنظیم پسورد"){
    if(in_array($chat_id,$admins)){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"💯 لطفا شماره آپلود را ارسال کنید:",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'getcodeu' WHERE id = '$from_id' LIMIT 1");	
}
}  
elseif($user['step'] == "getcodeu" && $text != "🔙 منوی پنل"){
    if(in_array($chat_id,$admins)){
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
    if($files['code'] != null && is_numeric($text) == true ){
       if($files['pass'] != 'none'){
    $khi = '❌ برداشتن پسورد';
        }else{
    $khi = null;
    }
      factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ لطفا پسورد جدید را وارد کنید:

ℹ️ فایل شماره : <code>$text</code>",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"],['text'=>"$khi"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'newpass_$text' WHERE id = '$from_id' LIMIT 1");	
    }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این کد آپلود یافت نشد و یا بصورت لاتین(123) ارسال نکردید .

💯 لطفا دوباره امتحان کنید .",
'parse_mode'=>"HTML",
    		]);
   }
    } 
}
elseif(strpos($data,"Setpas_") !== false ){
    if(in_array($chat_id,$admins)){
$ok = str_replace("Setpas_",null,$data);
 $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
 if($files['pass'] != 'none'){
    $khi = '❌ برداشتن پسورد';
        }else{
    $khi = null;
    }
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ لطفا پسورد جدید را وارد کنید:

ℹ️ فایل شماره : <code>$ok</code>",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"],['text'=>"$khi"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    			$connect->query("UPDATE user SET step = 'newpass_$ok' WHERE id = '$from_id' LIMIT 1");	
}
}
elseif(strpos($user['step'],"newpass_") !== false && $text != '🔙 منوی پنل' && $text != '❌ برداشتن پسورد'){
    if(in_array($chat_id,$admins)){
$ok = str_replace("newpass_",null,$user['step']);
if($text != null ){
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔐 پسورد تنظیم گردید.

ℹ️ فایل شماره : <code>$ok</code>
🔑 پسورد جدید : <code>$text</code>",
'parse_mode'=>"HTML",
'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		]) 
    		]);
    		$connect->query("UPDATE files SET pass = '$text' WHERE code = '$ok' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا فقط یک متن ارسال کنید:",
'parse_mode'=>"HTML",
    		]);
}
}
} 
  elseif($text=="❎ | حذف رسانه" ){
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا کد رسانه را برای حذف وارد کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'delres' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif($user['step'] =="delres" && $text != "🔙 منوی پنل" && !$data ){
    if(in_array($chat_id,$admins)){
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
    if($files['code'] != null && is_numeric($text) == true ){
    factweb('deletemessage', [
'chat_id' => $settings['chupl'], 
'message_id' =>$files['msg_id'],
]);
    $connect->query("DELETE FROM files WHERE code = '$text'");	
     factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ با موفقیت حذف گردید .",
'parse_mode'=>"HTML",
    		]);
    			$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
       }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این کد آپلود یافت نشد و یا بصورت لاتین(123) ارسال نکردید .

💯 لطفا دوباره امتحان کنید .",
'parse_mode'=>"HTML",
    		]);
   }
    }
}
   elseif($text=="💬 | تنظیم متن چنل" ){ 
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا متنی که زیر پیام های ارسال به چنل، زمینه گردد را ارسال کنید.

حداکثر 1000 کاراکتر !

برای مثال :
➖➖➖➖➖➖➖
↪️ J O I N : @uploader",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setmtnkhi' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif($user['step'] =="setmtnkhi" && $text != "🔙 منوی پنل" && !$data ){
if(in_array($chat_id,$admins)){
if(mb_strlen($text) < 1001 ){
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ متن با موفقیت تنظیم شد .",
'parse_mode'=>"HTML",
 'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE settings SET mtn_s_ch = '$text' WHERE botid = '$botid' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
			} else { 
 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📛 حداکثر 1000 کاراکتر !

🖌 لطفا دوباره ارسال فرمایید :",
'parse_mode'=>"HTML",
    		]);
}
}
}
    elseif($text=="📤 آپلود تکی/گروهی رسانه" ){
   if(in_array($chat_id,$admins)){
     factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 لطفا فایل خود را برای آپلود ارسال فرمایید:

شما می توانید پرونده(سند) ، ویدیو ، عکس ، ویس ، استیکر ، موزیک را ارسال کنید تا در ربات آپلود شود .",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'upload' WHERE id = '$from_id' LIMIT 1");	
}
} 

elseif($text != "/start" && $user['step'] =="upload" && $text != "🔙 منوی پنل" && !$data ){
   if(in_array($chat_id,$admins)){
   if(isset($message->video)) {
    $file_id = $message->video->file_id;
    $file_size = $message->video->file_size;
            $size = convert($file_size);
            $type = 'video';
            }
      if(isset($message->document)) {
    $file_id = $message->document->file_id;
    $file_size = $message->document->file_size;
         $size = convert($file_size);
         $type = 'document';
    }
    if(isset($message->photo)) {
    $photo = $message->photo;
    $file_id = $photo[count($photo)-1]->file_id;
    $file_size = $photo[count($photo)-1]->file_size;
         $size = convert($file_size);
         $type = 'photo';
    } 
    if(isset($message->voice)) {
    $file_id = $message->voice->file_id;
    $file_size = $message->voice->file_size;
         $size = convert($file_size);
         $type = 'voice';
    }
    if(isset($message->audio)) {
    $file_id = $message->audio->file_id;
    $file_size = $message->audio->file_size;
         $size = convert($file_size);
         $type = 'audio';
    }
    if(isset($message->sticker)) {
    $file_id = $message->sticker->file_id;
    $file_size = $message->sticker->file_size;
         $size = convert($file_size);
         $type = 'sticker';
    }
    if($file_id != null ){ 
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE file_id = '$file_id' LIMIT 1"));
    if($files['code'] == null ){
     $type_farsi = doc($type);
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"➕ بسیار خب ! اکنون توضیحات را ارسال کنید :

🔹 نوع فایل شما : $type_farsi

توضیحات حداکثر 500 کاراکتر میتواند باشد.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step4 = '$type' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step3 = '$size' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step2 = '$file_id' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'tozihat' WHERE id = '$from_id' LIMIT 1");	
    		}else{
    	$code =	$files['code'];
    	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این فایل قبلا با کد $code در ربات آپلود شده است !

💯 جهت دریافت اطلاعات کامل این فایل برگشت را زده و به بخش اطلاعات آپلود بروید 

❕ لطفا فایل دیگری برای آپلود ارسال در غیراین صورت از برگشت به پنل استفاده کنید :",
'parse_mode'=>"HTML",
    		]);
    		}
    }
   }
   }
   elseif($text != "/start" && $user['step'] =="tozihat" && $text != "🔙 منوی پنل" && !$data ){
   if(in_array($chat_id,$admins)){
   if(mb_strlen($text) < 501 ){
   $type = $user['step4'];
   $size = $user['step3'];
   $file_id = $user['step2'];
            $code = rand(1000,99999);
            $type_farsi = doc($type);
               $zaman = "$ToDay $time $date";
               $connect->query("INSERT INTO files (code , msg_id , ghfl_ch , zd_filter , file_id , file_size , file_type , id , dl , tozihat , zaman , mahdodl , pass) VALUES ('$code', 'none', 'on', 'off', '$file_id', '$size', '$type', '$from_id', '1', '$text', '$zaman', 'none', 'none')");
               factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"درحال آپلود فایل...",
'parse_mode'=>"HTML",
    		]);
  factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"$type_farsi شما با موفقیت آپلود شد .✅

▪️ کد رسانه : <code>$code</code>

🔸 اندازه : $size
🔹 زمان آپلود : $zaman

🔹 توضیحات : $text

و توسط شما $from_id در ربات آپلود شد  .

🔗 لینک دریافت : https://telegram.me/$bottag?start=dl_$code

💢 هر زمان خواستید از بخش اطلاعات آپلود میتوانید از آخرین وضعیت این رسانه با خبر شوید.",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"ارسال به چنل",'callback_data'=>"send_$code"]],
             [['text'=>"تنظیم محدودیت",'callback_data'=>"mahdl_$code"],['text'=>"تنظیم پسورد",'callback_data'=>"Setpas_$code"]],
                 [['text'=>"ضدفیلتر : ❌",'callback_data'=>"antifil_$code"],['text'=>"قفل چنل : ✅",'callback_data'=>"ghflch_$code"]],
              ]
        ])
    		]);
               factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ رسانه با موفقیت دریافت شد

⚠️ آیا فایل دیگری برای ارسال دارید؟",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"✅ بله فایل دیگری دارم"]],
 [['text'=>"❌ خیر فایل دیگری ندارم"]],
 
],
	'resize_keyboard'=>true
        ])
    		]);
               	$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = '$code' WHERE id = '$from_id'");  
   }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ خطا ! توضیحات طولانی است

لطفا متن توضیحات را دوباره و کوتاه ارسال کنید ( حداکثر 1000 کاراکتر )",
'parse_mode'=>"HTML",
    		]);
   }
   }
   }
#========================================================
    elseif($text=="❌ خیر فایل دیگری ندارم" ){
      if(in_array($chat_id,$admins)){
  factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"
✔️ به پنل مدیریت بازگشتید.

📅 تاریخ : <code>$ToDay $date $time</code>

ℹ️ یکی از گزینه هارا انتخاب کنید :
    
    
    ",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[
[['text'=>"👥 آمار کامل ربات و کاربران 👥"]],
[['text'=>"📨 | فروارد همگانی"],['text'=>"📨 | پیام همگانی"]],
[['text'=>"📣 | تغییر قفل کانال"],['text'=>"🗂 تنظیمات رسانه"]],
[['text'=>"📤 آپلود تکی/گروهی رسانه"]],
[['text'=>"ℹ️ | اطلاعات رسانه"],['text'=>"🗂 | تمام رسانه ها"]],
[['text'=>"🔈 تنظیمات کانال رسانه"]],
[['text'=>"👁‍🗨 تنظیم سین اجباری"],['text'=>"👌🏻 تنظیم ری اکشن اجباری"]],
[['text'=>"📢 تنظیم تبلیغات"],['text'=>"⚙️ شخصی سازی ربات ⚙️"]],
[['text'=>"📛 | تنظیم تایم حذف"]],
[['text'=>"📛 | مسدود کردن"],['text'=>"❇️ | آزاد کردن"]],
[['text'=>"❌ | ربات خاموش"],['text'=>"✅ | ربات روشن"]],
[['text'=>"🏠 برگشت به منو"]],
 	],
            	'resize_keyboard'=>true
       		])
       		]); 
    	$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = 'none' WHERE id = '$from_id'");     		
      }
    }
#===========================دریافت فایل دوم===================
    elseif($text=="✅ بله فایل دیگری دارم" ){
   if(in_array($chat_id,$admins)){
       $getcode=$user['step5'];
     factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔹 لطفا فایل خود را برای آپلود ارسال فرمایید:

فایل ها مربوط به کد $getcode می باشند

شما می توانید پرونده(سند) ، ویدیو ، عکس ، ویس ، استیکر ، موزیک را ارسال کنید تا در ربات آپلود شود .",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'upload1' WHERE id = '$from_id' LIMIT 1");	
}
} 

elseif($text != "/start" && $user['step'] =="upload1" && $text != "🔙 منوی پنل" && !$data ){
   if(in_array($chat_id,$admins)){
   if(isset($message->video)) {
    $file_id = $message->video->file_id;
    $file_size = $message->video->file_size;
            $size = convert($file_size);
            $type = 'video';
            }
      if(isset($message->document)) {
    $file_id = $message->document->file_id;
    $file_size = $message->document->file_size;
         $size = convert($file_size);
         $type = 'document';
    }
    if(isset($message->photo)) {
    $photo = $message->photo;
    $file_id = $photo[count($photo)-1]->file_id;
    $file_size = $photo[count($photo)-1]->file_size;
         $size = convert($file_size);
         $type = 'photo';
    } 
    if(isset($message->voice)) {
    $file_id = $message->voice->file_id;
    $file_size = $message->voice->file_size;
         $size = convert($file_size);
         $type = 'voice';
    }
    if(isset($message->audio)) {
    $file_id = $message->audio->file_id;
    $file_size = $message->audio->file_size;
         $size = convert($file_size);
         $type = 'audio';
    }
    if(isset($message->sticker)) {
    $file_id = $message->sticker->file_id;
    $file_size = $message->sticker->file_size;
         $size = convert($file_size);
         $type = 'sticker';
    }
    if($file_id != null ){ 
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE file_id = '$file_id' LIMIT 1"));
    if($files['code'] == null ){
     $type_farsi = doc($type);
      $getcode=$user['step5'];
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"➕ بسیار خب ! اکنون توضیحات را ارسال کنید :

🔹 نوع فایل شما : $type_farsi

توضیحات حداکثر 500 کاراکتر میتواند باشد.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step4 = '$type' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step3 = '$size' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step2 = '$file_id' WHERE id = '$from_id' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'tozihat2' WHERE id = '$from_id' LIMIT 1");	
    		}else{
    	$code =	$files['code'];
    	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این فایل قبلا با کد $code در ربات آپلود شده است !

💯 جهت دریافت اطلاعات کامل این فایل برگشت را زده و به بخش اطلاعات آپلود بروید 

❕ لطفا فایل دیگری برای آپلود ارسال در غیراین صورت از برگشت به پنل استفاده کنید :",
'parse_mode'=>"HTML",
    		]);
    		}
    }
   }
   }
   elseif($text != "/start" && $user['step'] =="tozihat2" && $text != "🔙 منوی پنل" && !$data ){
   if(in_array($chat_id,$admins)){
   if(mb_strlen($text) < 501 ){
   $type = $user['step4'];
   $size = $user['step3'];
   $file_id = $user['step2'];
   $getcode=$user['step5'];
            $type_farsi = doc($type);
               $zaman = "$ToDay $time $date";
                $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$getcode' LIMIT 1"));
                if($files['file_id2'] == null ){
               $connect->query("UPDATE files SET file_id2 = '$file_id', tozihat2 = '$text', file_size2 = '$size', file_type2 = '$type' WHERE code = '$getcode'");  
                }
                else{
                switch (true) {
                case $files['file_id3'] == null:    
                    $connect->query("UPDATE files SET file_id3 = '$file_id', tozihat3 = '$text', file_size3 = '$size', file_type3 = '$type' WHERE code = '$getcode'");   
                break;
                 case $files['file_id4'] == null:    
                   $connect->query("UPDATE files SET file_id4 = '$file_id', tozihat4 = '$text', file_size4 = '$size', file_type4 = '$type' WHERE code = '$getcode'");    
                break;
                 case $files['file_id5'] == null:    
                    $connect->query("UPDATE files SET file_id5 = '$file_id', tozihat5 = '$text', file_size5 = '$size', file_type5 = '$type' WHERE code = '$getcode'");   
                break;
                
                }
                }
               factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"درحال آپلود فایل...",
'parse_mode'=>"HTML",
    		]);
               factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"$type_farsi شما با موفقیت آپلود شد .✅

▪️ کد رسانه : <code>$getcode</code>

🔸 اندازه : $size
🔹 زمان آپلود : $zaman

🔹 توضیحات : $text

و توسط شما $from_id در ربات آپلود شد  .

🔗 لینک دریافت : https://telegram.me/$bottag?start=dl_$getcode

💢 هر زمان خواستید از بخش اطلاعات آپلود میتوانید از آخرین وضعیت این رسانه با خبر شوید.",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"ارسال به چنل",'callback_data'=>"send_$getcode"]],
             [['text'=>"تنظیم محدودیت",'callback_data'=>"mahdl_$getcode"],['text'=>"تنظیم پسورد",'callback_data'=>"Setpas_$getcode"]],
                 [['text'=>"ضدفیلتر : ❌",'callback_data'=>"antifil_$getcode"],['text'=>"قفل چنل : ✅",'callback_data'=>"ghflch_$getcode"]],
              ]
        ])
    		]);
    		 $getcode1=$user['step5'];
$files1 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$getcode1' LIMIT 1"));
    		if($files1['file_id5'] == null){
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ رسانه با موفقیت دریافت شد

⚠️ آیا فایل دیگری برای ارسال دارید؟",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'keyboard'=>[
            [['text'=>"✅ بله فایل دیگری دارم"]],
 [['text'=>"❌ خیر فایل دیگری ندارم"]],
],
	'resize_keyboard'=>true
        ])
    		]);   		
               	$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = '$getcode' WHERE id = '$from_id'");      
    		}
    		else{
    factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✔️ رسانه با موفقیت دریافت شد",
'parse_mode'=>"HTML",
    'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);  
    		 	$connect->query("UPDATE user SET step = 'none', step2 = 'none', step3 = 'none', step4 = 'none', step5 = 'none' WHERE id = '$from_id'");  
    		}
  		
              
   }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ خطا ! توضیحات طولانی است

لطفا متن توضیحات را دوباره و کوتاه ارسال کنید ( حداکثر 1000 کاراکتر )",
'parse_mode'=>"HTML",
    		]);
   }
   }
   }
#========================================================
  elseif(strpos($data,"send_") !== false ){
   $ok = str_replace("send_",null,$data);
   if(in_array($chat_id,$admins)){
   if($settings['mtn_s_ch'] != 'none' ){
    if($settings['chupl'] != 'none' ){
    $mtn = $settings['mtn_s_ch'];
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
     $tozihat = $files['tozihat'];
     $file_size = $files['file_size'];
     $file_type = doc($files['file_type']);
     if($files['msg_id'] == 'none'){
        if($files['pass'] == 'none'){
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$ok";
   }else{
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$ok";
   }
   if($files['mahdodl'] == 'none'){
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$ok";
   }else{
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$ok";
   }
      if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅';
   }else{
   $hesofff2 = '❌';
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌';
   }
    $linkfile="https://telegram.me/$bottag?start=dl_$ok";
     $dlnows=$files['likes'];
        $post = factweb('sendmessage',[
	'chat_id'=>$settings['chupl'],
'text'=>"$tozihat


<a href='https://telegram.me/$bottag?start=dl_$ok'> دانلود / Download</a>

$mtn",
'parse_mode'=>"HTML",
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

				[['text'=>"👁 مشاهده / دانلود 👁",'url'=>$linkfile]],
			  	[['text'=>"🔸 حجم فایل: $file_size",'callback_data'=>"nocall"],['text'=>"🔹 نوع فایل: $file_type",'callback_data'=>"nocall"]],
               [['text'=>"👍🏻 تعداد $dlnows کاربر این $file_type را پسندیده اند",'callback_data'=>"nocall"]]
			           
              ]
        ])
    		])->result;	
    		$msg_id = $post->message_id;
    			$connect->query("UPDATE files SET msg_id = '$msg_id' WHERE code = '$ok' LIMIT 1");	
    			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

			[['text'=>"✅ به چنل ارسال شد .",'callback_data'=>"none"]],
			             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
			             [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"antifil_$ok"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghflch_$ok"]],
              ]
        ])
    		]);
    		  }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "قبلا ارسال شده است !",
        'show_alert' => true
    ]);
    }
   }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "❌ چنل رسانه تنظیم نشده ! ابتدا چنل را از بخش تنظیم رسانه تنظیم سپس دوباره روی این گزینه بزنید .",
        'show_alert' => true
    ]);
   }
   }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "❌ متن زمینه پیام تنظیم نشده ابتدا از بخش تنظیم متن ارسال به چنل متن زمینه را تنظیم سپس روی این گزینه بزنید.",
        'show_alert' => true
    ]);
   }
   }
   }
    elseif(strpos($data,"send2_") !== false ){
   $ok = str_replace("send2_",null,$data);
   if(in_array($chat_id,$admins)){
   if($settings['mtn_s_ch'] != 'none' ){
    if($settings['chupl'] != 'none' ){
    $mtn = $settings['mtn_s_ch'];
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
     $tozihat = $files['tozihat'];
     $file_size = $files['file_size'];
     $file_type = doc($files['file_type']);
     if($files['msg_id'] == 'none'){
        if($files['pass'] == 'none'){
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$ok";
   }else{
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$ok";
   }
   if($files['mahdodl'] == 'none'){
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$ok";
   }else{
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$ok";
   }
      if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅';
   }else{
   $hesofff2 = '❌'; 
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌';
   }
   $linkfile="https://telegram.me/$bottag?start=dl_$ok";
   $dlnows=$files['likes'];
    $post = factweb('sendmessage',[
	'chat_id'=>$settings['chupl'],
'text'=>"$tozihat


<a href='https://telegram.me/$bottag?start=dl_$ok'> دانلود / Download</a>

$mtn",
'parse_mode'=>"HTML",
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

				[['text'=>"👁 مشاهده / دانلود 👁",'url'=>$linkfile]],
			  	[['text'=>"🔸 حجم فایل: $file_size",'callback_data'=>"nocall"],['text'=>"🔹 نوع فایل: $file_type",'callback_data'=>"nocall"]],
                [['text'=>"👍🏻 تعداد $dlnows کاربر این $file_type را پسندیده اند",'callback_data'=>"nocall"]]
			           
              ]
        ])
    		])->result;	
    		$msg_id = $post->message_id;
    			$connect->query("UPDATE files SET msg_id = '$msg_id' WHERE code = '$ok' LIMIT 1");	
    			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

			[['text'=>"✅ به چنل ارسال شد .",'callback_data'=>"none"],['text'=>"حذف",'callback_data'=>"delu_$ok"]],
			             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
			             [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"pnlzdfilter_$ok"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghdpnl_$ok"]],
              ]
        ])
    		]);
    		  }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "قبلا ارسال شده است !",
        'show_alert' => true
    ]);
    }
   }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "❌ چنل رسانه تنظیم نشده ! ابتدا چنل را از بخش تنظیم رسانه تنظیم سپس دوباره روی این گزینه بزنید .",
        'show_alert' => true
    ]);
   }
   }else{
   	factweb('answercallbackquery', [
        'callback_query_id' => $update->callback_query->id,
'text' => "❌ متن زمینه پیام تنظیم نشده ابتدا از بخش تنظیم متن ارسال به چنل متن زمینه را تنظیم سپس روی این گزینه بزنید.",
        'show_alert' => true
    ]);
   }
   }
   } 
   elseif(strpos($data,"antifil_") !== false ){
   $ok = str_replace("antifil_",null,$data);
   if(in_array($chat_id,$admins)){
    $files2 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
    if($files2['zd_filter'] == 'on'){
   $nmddd1 = 'off';
   }else{
   $nmddd1 = 'on';
   }
        $connect->query("UPDATE files SET zd_filter = '$nmddd1' WHERE code = '$ok' LIMIT 1");	
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
        if($files['pass'] == 'none'){
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$ok";
   }else{
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$ok";
   } 
   if($files['mahdodl'] == 'none'){
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$ok";
   }else{
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$ok";
   }
      if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅';
   }else{
   $hesofff2 = '❌';
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌';
   }
      if($files['msg_id'] == 'none'){
   $mtnsch = 'ارسال به چنل';
   $stepmsc = "send_$ok";
   }else{
   $mtnsch = '✅ به چنل ارسال شد .';
   $stepmsc = 'none';
   }
    			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

			[['text'=>"$mtnsch",'callback_data'=>"$stepmsc"]],
			             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
			             [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"antifil_$ok"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghflch_$ok"]],
              ]
        ])
    		]);
   }
   }
   elseif(strpos($data,"ghflch_") !== false ){
   $ok = str_replace("ghflch_",null,$data);
   if(in_array($chat_id,$admins)){
    $files2 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
    if($files2['ghfl_ch'] == 'on'){
   $nmddd1 = 'off';
   }else{
   $nmddd1 = 'on';
   }
        $connect->query("UPDATE files SET ghfl_ch = '$nmddd1' WHERE code = '$ok' LIMIT 1");	
    $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$ok' LIMIT 1"));
        if($files['pass'] == 'none'){
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$ok";
   }else{
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$ok";
   }
   if($files['mahdodl'] == 'none'){
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$ok";
   }else{
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$ok";
   }
      if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅'; 
   }else{
   $hesofff2 = '❌';
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌'; 
   }
      if($files['msg_id'] == 'none'){
   $mtnsch = 'ارسال به چنل';
  $stepmsc = "send_$ok";
   }else{
   $mtnsch = '✅ به چنل ارسال شد .';
   $stepmsc = 'none';
   }
    			factweb('editMessageReplyMarkup',[
    'chat_id'=>$chat_id,
    'message_id'=>$message_id,
  	'reply_markup'=>json_encode([
   'inline_keyboard'=>[

			[['text'=>"$mtnsch",'callback_data'=>"$stepmsc"]],
			             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
			             [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"antifil_$ok"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghflch_$ok"]],
              ]
        ])
    		]);
   }
   }  
  elseif($text=="ℹ️ | اطلاعات رسانه" ){
   if(in_array($chat_id,$admins)){
     factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❕ لطفا کد عددی رسانه آپلود شده را وارد کنید.",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'infoupl' WHERE id = '$from_id' LIMIT 1");	
}
}   
elseif($user['step'] =="infoupl" && $text != "🔙 منوی پنل" && !$data ){
   if(in_array($chat_id,$admins)){
   $files = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM files WHERE code = '$text' LIMIT 1"));
   if(is_numeric($text) == true && $files['code'] != null ){
   $file_size = $files['file_size'];
   $zaman = $files['zaman'];
   $tozihat = $files['tozihat'];
   $dl = $files['dl'];
   $id = $files['id'];
   if($files['msg_id'] != 'none'){
   $yorn = '✅ ارسال شده است !';
   $khikhi = '✅ ارسال شده در چنل!';
   $khidata = 'none';
   }else{
   $khikhi = 'ارسال به چنل';
   $yorn = '❌ ارسال نشده است !';
   $khidata = "send2_$text";
   }
      if($files['pass'] == 'none'){
   $ispass = '❌ بدون پسورد';
   $namepass = 'تنظیم پسورد';
   $datapass = "Setpas_$text";
   }else{
   $ispass = $files['pass'];
   $namepass = '🔐 تغییر پسورد';
   $datapass = "Setpas_$text";
   }
   if($files['mahdodl'] == 'none'){
   $ismahd = '❌ بدون محدودیت دانلود';
   $namemahd = 'تنظیم محدودیت';
   $datamahd = "mahdl_$text";
   }else{
   $ismahd = $files['mahdodl'];
   $namemahd = '🚷 تغییر محدودیت';
   $datamahd = "mahdl_$text";
   }
   if($files['ghfl_ch'] == 'on'){
   $hesofff2 = '✅';
   }else{
   $hesofff2 = '❌';
   }
   if($files['zd_filter'] == 'on'){
   $hesofff = '✅';
   }else{
   $hesofff = '❌';
   } 
   $file_type = doc($files['file_type']);
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"ℹ️ اطلاعات کامل این رسانه یافت شد :

▪️ کد رسانه : <code>$text</code>

🔹 نوع : $file_type
🔸 اندازه : $file_size
🔹 زمان آپلود : $zaman
🔸 تعداد دانلود : $dl 

🔹 توضیحات : $tozihat

❓ ارسال به چنل : $yorn
🔓 پسورد : <code>$ispass</code>
🖇 محدودیت دانلود : $ismahd
📌 ضد فیلتر : $hesofff
🔐 قفل چنل : $hesofff2
🔗 لینک دریافت : https://telegram.me/$bottag?start=dl_$text

🔸 توسط ادمین <a href='tg://user?id=$id'>$id</a> آپلود شده است .",
'parse_mode'=>"HTML",
'reply_markup'=> json_encode([
            'inline_keyboard'=>[
            [['text'=>"$khikhi",'callback_data'=>"$khidata"],['text'=>"حذف",'callback_data'=>"delu_$text"]],
             [['text'=>"$namemahd",'callback_data'=>"$datamahd"],['text'=>"$namepass",'callback_data'=>"$datapass"]],
                          [['text'=>"ضدفیلتر : $hesofff",'callback_data'=>"pnlzdfilter_$text"],['text'=>"قفل چنل : $hesofff2",'callback_data'=>"ghdpnl_$text"]],
              ]
        ])
    		]);
   }else{
   factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ این کد آپلود یافت نشد و یا بصورت لاتین(123) ارسال نکردید .

💯 لطفا دوباره امتحان کنید .",
'parse_mode'=>"HTML",
    		]);
   }
  }
  } 
  elseif($text=="📣 تنظیم کانال رسانه" ){ 
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا آیدی عددی چنل ارسال رسانه را ارسال کنید:

⚠️ ربات حتما باید بر چنل ارسالی ادمین و قابلیت ارسال پیام نیز داشته باشد !",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'setchsmd' WHERE id = '$from_id' LIMIT 1");	
}
} 

elseif($user['step'] =="setchsmd" && $text != "🔙 منوی پنل" && !$data ){
if(in_array($chat_id,$admins)){
    		 $admini = getChatstats($text,$API_KC);
			if($admini == true ){
			factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✅ چنل آپلود، با موفقیت تنظیم شد .",
'parse_mode'=>"HTML",
 'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE settings SET chupl = '$text' WHERE botid = '$botid' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
			} else {  
 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📛 خطا !

❗️ احتمالا آیدی درست ارسال نشده و یا ربات بر چنل ارسالی ادمین نیست !

❓ نمونه ارسال :
-1003367727282

💯 پس از رفع مشکل ، دوباره ارسال کنید  :",
'parse_mode'=>"HTML",
    		]);
}
} 
}
elseif($text=="❇️ | آزاد کردن" ){
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"⭕ آیدی عددی شخص را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]);
    		$connect->query("UPDATE user SET step = 'unban_user' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif($user['step'] =="unban_user" && $text != "🔙 منوی پنل" && !$data ){ 
if(in_array($chat_id,$admins)){
$usere = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM user WHERE id = '$text' LIMIT 1")); 
    		 if($usere['id'] != null ){
    		 $connect->query("UPDATE user SET step = 'none' WHERE id = '$text' LIMIT 1");	
    		 factweb('sendmessage',[
	'chat_id'=>$text,
'text'=>"✅ شما دیگر مسدود‌ نیستید !",
'parse_mode'=>"HTML",
    		]);
    		 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"<code>$text</code> از لیست مسدود آزاد شد.✅",
'parse_mode'=>"HTML",
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    		 } else {
 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✘ این آیدی عددی در ربات موجود نیست .",
'parse_mode'=>"HTML",
    		]);
    		} 
    		}
    		}
elseif($text=="📛 | مسدود کردن" ){  
    if(in_array($chat_id,$admins)){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"⭕ آیدی عددی شخص را ارسال کنید :",
'parse_mode'=>"HTML",
     'reply_markup'=>json_encode([
            	'keyboard'=>[
							[['text'=>"🔙 منوی پنل"]],
 	],
            	'resize_keyboard'=>true
       		])
    		]); 
    		$connect->query("UPDATE user SET step = 'ban_user' WHERE id = '$from_id' LIMIT 1");	
}
} 
elseif($user['step'] =="ban_user" && $text != "🔙 منوی پنل" && !$data ){
if(in_array($chat_id,$admins)){
$usere = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM user WHERE id = '$text' LIMIT 1"));
    		 if($usere['id'] != null ){
    		 $connect->query("UPDATE user SET step = 'ban' WHERE id = '$text' LIMIT 1");	
    		 factweb('sendmessage',[
	'chat_id'=>$text,
'text'=>"❌ شما از ربات مسدود‌ شدید .",
'parse_mode'=>"HTML",
    		]);
    		 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"<code>$text</code> مسدود شد .⭕",
'parse_mode'=>"HTML",
    		]);
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    		 } else {
 factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"✘ این آیدی عددی در ربات موجود نیست .",
'parse_mode'=>"HTML",
    		]);
    		}
    		} 
    		}
    		elseif($text=="📨 | فروارد همگانی" && in_array($chat_id,$admins)){
    		if($is_all == "no" ){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📩 لطفا پیام را در اینجا فروارد کنید :",
'parse_mode'=>"HTML",
   'reply_markup'=>json_encode([
           'keyboard'=>[
   	 	[['text'=>"🔙 منوی پنل"]],
	],
		"resize_keyboard"=>true,
	 ])
    		]);
    		$connect->query("UPDATE user SET step = 'forall' WHERE id = '$from_id' LIMIT 1");	
    		}else{
    		 $tddd = $settings['tedad'];
    		 $users = mysqli_query($connect,"select id from user");  
$fil = mysqli_num_rows($users);
$tfrigh = $fil - $tddd; 
$min = Takhmin($tfrigh);
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ خطا برای انجام عملیات همگانی

 ادمین زیر اقدام به همگانی کرده و هنوز همگانی به اتمام نرسیده ، لطفا تا پایان همگانی قبلی صبر کنید .",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
             [['text'=>"👤 $is_all",'callback_data'=>"none"]],
             [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
             [['text'=>"🔸 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]);
}
}  
    		 elseif($user["step"] =="forall" && $text != "🔙 منوی پنل" && !$data ){
    		 if(in_array($chat_id,$admins)){
$connect->query("UPDATE settings SET forall = 'true' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET tedad = '0' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET chat_id = '$chat_id' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET msg_id = '$message_id' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET is_all = '$chat_id' WHERE botid = '$botid' LIMIT 1");	
$users = mysqli_query($connect,"select id from user");
$fil = mysqli_num_rows($users);
$min = Takhmin($fil);
$tddd = $settings['tedad'];
$id = factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📣 <i>پیام به صف فروارد قرار گرفت !</i>

✅ <b>بعد از اتمام فروارد، به شما اطلاع داده میشود.</b>

👥 تعداد اعضای ربات: <code>$fil</code> نفر

🔹 تعداد افراد ارسال شده در دکمه شیشه ای زیر، قابل مشاهده است ( خودکار ادیت میشود )",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		   		])->result;
    		$msgid22 = $id->message_id;
    		$connect->query("UPDATE settings SET factweb = '$msgid22' WHERE botid = '$botid' LIMIT 1");	
    			$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
}
} 
    		elseif($text=="📨 | پیام همگانی" && in_array($chat_id,$admins) ){
    		if($is_all == "no" ){
	factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"🔺 نکات مهم از ارسال پیام همگانی :

🔹 شما فقط میتوانید متن ارسال کنید.
🔸 متن نباید بیشتر از 25,000 کاراکتر باشد. ( پیام های طولانی ، ارسال نخواهد شد )
❗️ برای ارسال عکس ، فیلم و... از بخش فروارد همگانی استفاده کنید .

✅ راهنمای استفاده از امکانات متن :

🌀 نمونه برجسته کردن متن :
<b> متن شما </b> 
🌀 نمونه کج کردن متن :
<i> متن شما </i>
🌀 نمونه کد کردن متن :
<code> متن شما </code>
- - - - - - - - - - - - - -
📩 لطفا پیام متنی را در اینجا ارسال کنید :",
    'reply_markup'=>json_encode([
           'keyboard'=>[
   	 	[['text'=>"🔙 منوی پنل"]],
	],
		"resize_keyboard"=>true,
	 ]) 
    		]); 
    		$connect->query("UPDATE user SET step = 'sendall' WHERE id = '$from_id' LIMIT 1");	
    		}else{
    		 $tddd = $settings['tedad'];
    		 $users = mysqli_query($connect,"select id from user");
$fil = mysqli_num_rows($users);
$tfrigh = $fil - $tddd;
$min = Takhmin($tfrigh);
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❌ خطا برای انجام عملیات همگانی

 ادمین زیر اقدام به همگانی کرده و هنوز همگانی به اتمام نرسیده ، لطفا تا پایان همگانی قبلی صبر کنید .",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
             [['text'=>"👤 $is_all",'callback_data'=>"none"]],
             [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
             [['text'=>"🔸 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		]);
} 
}
    		 elseif($user["step"] =="sendall" && $text != "🔙 منوی پنل" && !$data ){
    		 if(in_array($chat_id,$admins)){
    		 if($text != null ){
$connect->query("UPDATE settings SET sendall = 'true' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET tedad = '0' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET text = '$text' WHERE botid = '$botid' LIMIT 1");	
$connect->query("UPDATE settings SET is_all = '$chat_id' WHERE botid = '$botid' LIMIT 1");	
$users = mysqli_query($connect,"select id from user");
$fil = mysqli_num_rows($users);
$min = Takhmin($fil);
$tddd = $settings['tedad']; ;
$id = factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"📣 <i>پیام به صف ارسال قرار گرفت !</i>

✅ <b>بعد از اتمام ارسال، به شما اطلاع داده میشود.</b>

👥 تعداد اعضای ربات: <code>$fil</code> نفر

🔹 تعداد افراد ارسال شده در دکمه شیشه ای زیر، قابل مشاهده است ( خودکار ادیت میشود )",
'parse_mode'=>"HTML",
 'reply_markup'=> json_encode([
            'inline_keyboard'=>[
                  [['text'=>"🔹 تعداد افراد ارسال شده : $tddd",'callback_data'=>"none"]],
                  [['text'=>"🚀 زمان تخمینی ارسال : $min دقیقه (باقیمانده)",'callback_data'=>"none"]],
              ]
        ])
    		])->result;
    		$msgid22 = $id->message_id;
    		$connect->query("UPDATE settings SET factweb = '$msgid22' WHERE botid = '$botid' LIMIT 1");	
    		$connect->query("UPDATE user SET step = 'none' WHERE id = '$from_id' LIMIT 1");	
    		}else{
factweb('sendmessage',[
	'chat_id'=>$chat_id,
'text'=>"❗️ لطفا فقط یک متن ارسال کنید:",
'parse_mode'=>"HTML",
    		]);
}
}
}  
 elseif($text=="👥 آمار کامل ربات و کاربران 👥"){
    if(in_array($chat_id,$admins)){
$users = mysqli_query($connect,"select id from user");
$fil = mysqli_num_rows($users);
$load = sys_getloadavg();
	 $mem = memory_get_usage();
	 $ver = phpversion();  
	 $settings = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM settings WHERE botid = '$botid' LIMIT 1"));
	 $bot_mode = $settings['bot_mode'];
	 if($bot_mode == 'on'){
	 $a4 = "✅ روشن";
	 }else{
	 $a4 = "❌ خاموش";
	 }
$database6 = mysqli_query($connect,"select code from files");
$all_up = mysqli_num_rows($database6);
$s_spm = $settings['s_spm'];

$dateen = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));
$month_ago = date('Y-m-d', strtotime('-1 month'));

$users_today = mysqli_query($connect, "SELECT id FROM user WHERE timejoin >= '$dateen'");
$users_this_week = mysqli_query($connect, "SELECT id FROM user WHERE timejoin >= '$week_ago'");
$users_this_month = mysqli_query($connect, "SELECT id FROM user WHERE timejoin >= '$month_ago'");

$count_today = mysqli_num_rows($users_today);
$count_this_week = mysqli_num_rows($users_this_week);
$count_this_month = mysqli_num_rows($users_this_month);
        
        // ویدیو  
$sql = "SELECT COUNT(*) AS video FROM files WHERE file_type='video'";
$res = mysqli_query($connect, $sql);
$video_count = mysqli_fetch_assoc($res)['video'];

// صوت
$sql = "SELECT COUNT(*) AS audio FROM files WHERE file_type IN ('audio','voice')";
$res = mysqli_query($connect, $sql);  
$audio_count = mysqli_fetch_assoc($res)['audio'];

// عکس
$sql = "SELECT COUNT(*) AS photo FROM files WHERE file_type='photo'";
$res = mysqli_query($connect, $sql);
$photo_count = mysqli_fetch_assoc($res)['photo'];  

// سند
$sql = "SELECT COUNT(*) AS document FROM files WHERE file_type='document'";  
$res = mysqli_query($connect, $sql);
$document_count = mysqli_fetch_assoc($res)['document'];

$sql = "SELECT SUM(likes) AS total_likes FROM files";
$res = mysqli_query($connect, $sql);
$total_likes = mysqli_fetch_assoc($res)['total_likes'];

// تعداد کل دیسلایک ها  
$sql = "SELECT SUM(dislikes) AS total_dislikes FROM files";
$res = mysqli_query($connect, $sql);
$total_dislikes = mysqli_fetch_assoc($res)['total_dislikes'];

$sql = "SELECT SUM(dl) AS total_dl FROM files";
$res = mysqli_query($connect, $sql);
$total_dl = mysqli_fetch_assoc($res)['total_dl'];


$sql = "SELECT COUNT(*) AS banned_users 
        FROM user 
        WHERE step = 'ban'";
$res = mysqli_query($connect, $sql);
$banned_users_count = mysqli_fetch_assoc($res)['banned_users'];

if ($checkads =="on"){
 $checkads= "✅ روشن";   
}
else{
 $checkads= "❌ خاموش";   
}
if ($reactcheck =="on"){
 $reactcheck= "✅ روشن";   
}
else{
 $reactcheck= "❌ خاموش";   
}
if ($seencheck =="on"){
 $seencheck= "✅ روشن";   
}
else{
 $seencheck= "❌ خاموش";   
}
	factweb('sendmessage',[
	'chat_id'=>$chat_id, 
'text'=>"
👥 به بخش آمار پیشرفته ربات و کاربران خوش آمدید؛

👤 <b> آمار کاربران : </b>
🔹 تعداد کل کاربران ربات : <code> $fil </code>نفر
🔹 تعداد کاربران امروز : <code> $count_today </code>نفر
🔹 تعداد کاربران این هفته : <code> $count_this_week </code>نفر
🔹 تعداد کاربران این ماه : <code> $count_this_month </code>نفر

👤 <b> آمار رسانه ها : </b>
🔸 کل رسانه های آپلود شده : <code> $all_up </code>رسانه
🔸 رسانه های ویدیویی آپلود شده :<code> $video_count </code>ویدیو
🔸 رسانه های صوتی آپلود شده:<code> $audio_count </code>صوت
🔸 رسانه های تصویری آپلود شده:<code> $photo_count </code>عکس
🔸 رسانه های داکیومنت آپلود شده:<code> $document_count </code>سند

⭐️ آمار لایک ، دیسلایک و تعداد دانلودها:

📥 تعداد کل دانلودها: <code> $total_dl </code> دانلود
👍🏻 تعداد کل لایک های رسانه ها: <code> $total_likes </code>لایک
👎🏻 تعداد کل دیسلایک های رسانه ها:  <code> $total_dislikes </code>دیسلایک

🚀 <b> آمار دیگر : </b>
🔘 تعداد کاربران مسدود شده:  <code> $banned_users_count </code>کاربر
🔘 وضعیت سین اجباری کانال : <code> $seencheck </code>
🔘 وضعیت ری اکشن اجباری کانال : <code> $reactcheck </code>
🔘 وضعیت تبلیغات پست کانال: <code> $checkads </code>

◽️وضعیت سرور :
▫️ میانگین انتقال داده :  <code>$load[0]</code>
▫️ استفاده از رم :  <code>$mem</code>
▫️ نسخه PHP هاست/ سرور : <code>$ver</code>

◾️ وضعیت ربات : <code>$a4</code>

",
'parse_mode'=>"HTML",
  'reply_markup'=> json_encode([
            'inline_keyboard'=>[
    [['text'=>"$date",'callback_data'=>"none"],['text'=>"$ToDay",'callback_data'=>"none"],['text'=>"$time",'callback_data'=>"none"]],
    [['text'=>"$dateen",'callback_data'=>"none"],['text'=>"$ToDayen",'callback_data'=>"none"],['text'=>"$timeen",'callback_data'=>"none"]],
              ]
        ]) 
    		]);
} 
} 
}
} 
}

$connect->close();