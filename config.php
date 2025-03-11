<?php
/*
dev: https://jahanbots
channel telegram : @jahanbots
*/
error_reporting(0);
//-----------------------------------------------------------------------------------------------
$API_KC = "7515490715:AAE2OPCKl1mJuOxrirtXptd0D3bnMbWM8Lo"; // توکن خود را اینجا قرار دهید
$admins = array(5628628903);//ایدی عددی ادمین ها را اینجا وارد کنید
$Channel = "@MixCastChannel"; // ایدی کانال خود را با @ اینجا قرار بدهیدنید
$ramzvorodadmin="safir";//رمز دلخواه خود را برای ورود به پنل مدیریت وارد کنید ، برای ورود به پنل مدیریت از این کد استفاده کنید
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
