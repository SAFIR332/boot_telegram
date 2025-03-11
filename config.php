<?php
/*
dev: https://jahanbots
channel telegram : @jahanbots
*/
error_reporting(0);
//-----------------------------------------------------------------------------------------------
$API_KC = "6863762722:AAGUVwpg8AaD6DiNPmkQR5Q3nskwzG_T338"; // توکن خود را اینجا قرار دهید
$admins = array(370477581,370477581,6217176337);//ایدی عددی ادمین ها را اینجا وارد کنید
$Channel = "@Premium2store"; // ایدی کانال خود را با @ اینجا قرار بدهیدنید
$ramzvorodadmin="/panel";//رمز دلخواه خود را برای ورود به پنل مدیریت وارد کنید ، برای ورود به پنل مدیریت از این کد استفاده کنید
//-----------------------------------------------------------------------------------------------
$dbname = "saeedmiz_bots"; //نام دیتابیس را اینجا وارد کنید
$username = "saeedmiz_bots"; // یوزرنیم دیتابیس را اینجا وارد کنید
$password = 'saeedmiz_bots'; // پسورد دیتابیس را اینجا وارد کنید
//-----------------------------------------------------------------------------------------------
$connect = mysqli_connect("localhost", $username, $password, $dbname);
mysqli_query($connect,"SET SESSION collation_connection = 'utf8_persian_ci'");
//-----------------------------------------------------------------------------------------------
$API_TEL = json_decode(file_get_contents('https://api.telegram.org/bot'.$API_KC.'/getme'));
$botid = $API_TEL->result->id;
$bottag = $API_TEL->result->username;
//-----------------------------------------------------------------------------------------------
