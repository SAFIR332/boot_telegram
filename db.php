<?php
/*
dev: https://jahanbots
channel telegram : @jahanbots
*/
error_reporting(0);
include "config.php";
define('API_KEY',$API_KC); 
//-----------------------------------------------------------------------------------------------

$connect->query("CREATE TABLE `adspost` (
  `checkads` varchar(250) DEFAULT NULL,
  `textads` varchar(1200) DEFAULT NULL
)");

$connect->query("INSERT INTO `adspost` (`checkads`, `textads`) VALUES
('off', 'NO')");

$connect->query("CREATE TABLE `channels` (
  `idoruser` varchar(30) NOT NULL,
  `link` varchar(200) NOT NULL)");

$connect->query("CREATE TABLE `dbremove` (
  `id` bigint(64) NOT NULL,
  `message_id` int(250) NOT NULL,
  `time` int(250) NOT NULL
)");

$connect->query("CREATE TABLE `files` (
  `code` varchar(3070) PRIMARY KEY,
  `msg_id` varchar(5000) NOT NULL,
  `ghfl_ch` varchar(5000) NOT NULL,
  `zd_filter` varchar(5000) NOT NULL,
  `file_id` varchar(500) NOT NULL,
  `file_id2` varchar(500) NOT NULL,
  `file_id3` varchar(500) NOT NULL,
  `file_id4` varchar(500) NOT NULL,
  `file_id5` varchar(500) NOT NULL,
  `file_size` varchar(200) NOT NULL,
  `file_size2` varchar(200) NOT NULL,
  `file_size3` varchar(200) NOT NULL,
  `file_size4` varchar(200) NOT NULL,
  `file_size5` varchar(200) NOT NULL,
  `file_type` varchar(200) NOT NULL,
  `file_type2` varchar(200) NOT NULL,
  `file_type3` varchar(200) NOT NULL,
  `file_type4` varchar(200) NOT NULL,
  `file_type5` varchar(200) NOT NULL,
  `id` varchar(500) NOT NULL,
  `dl` bigint(250) NOT NULL,
  `pass` varchar(500) NOT NULL,
  `mahdodl` varchar(100) NOT NULL,
  `tozihat` text NOT NULL,
  `tozihat2` text NOT NULL,
  `tozihat3` text NOT NULL,
  `tozihat4` text NOT NULL,
  `tozihat5` text NOT NULL,
  `zaman` varchar(500) NOT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0
)");

$connect->query("CREATE TABLE `reaction` (
  `checkreact` varchar(100) DEFAULT NULL,
  `channelreact` varchar(250) DEFAULT NULL,
  `reacttedad` int(11) DEFAULT NULL,
  `timefakereact` int(11) DEFAULT NULL
)");

$connect->query("INSERT INTO `reaction` (`checkreact`, `channelreact`, `reacttedad`, `timefakereact`) VALUES
('off', 'no', 1, 10)");

$connect->query("CREATE TABLE `seen` (
  `checkseen` varchar(10) DEFAULT 'off',
  `channelseen` varchar(20) DEFAULT 'none',
  `adadseen` int(11) DEFAULT 0,
  `timefake` int(11) DEFAULT 10
)");

$connect->query("INSERT INTO `seen` (`checkseen`, `channelseen`, `adadseen`, `timefake`) VALUES
('off', 'no', 10, 5)");

$connect->query("CREATE TABLE `settings` (
  `botid` varchar(30) PRIMARY KEY,
  `bot_mode` varchar(20) NOT NULL,
  `mtn_s_ch` text NOT NULL,
  `chupl` varchar(500) NOT NULL,
  `forall` varchar(20) NOT NULL,
  `sendall` varchar(20) NOT NULL,
  `tedad` varchar(20) NOT NULL,
  `text` text NOT NULL,
  `chat_id` varchar(20) NOT NULL,
  `is_all` varchar(20) NOT NULL,
  `jahanbots` varchar(20) NOT NULL,
  `msg_id` varchar(20) NOT NULL,
  `topdlbut` varchar(25) DEFAULT NULL,
  `newdlbut` varchar(25) DEFAULT NULL,
  `supportbut` varchar(25) DEFAULT NULL,
  `sendbut` varchar(25) DEFAULT NULL,
  `starttext` text DEFAULT NULL
)");

$connect->query("CREATE TABLE `user` (
  `id` bigint(64) PRIMARY KEY,
  `step` varchar(500) NOT NULL,
  `step2` varchar(500) NOT NULL,
  `step3` varchar(500) NOT NULL,
  `step4` varchar(500) NOT NULL,
  `step5` varchar(500) NOT NULL,
  `spam` varchar(20) NOT NULL,
  `timejoin` varchar(50) DEFAULT NULL
)");

//-----------------------------------------------------------------------------------------------
if($settings['botid'] == null ){
    	$connect->query("INSERT INTO settings (`botid`, `bot_mode`, `mtn_s_ch`, `chupl`, `forall`, `sendall`, `tedad`, `text`, `chat_id`, `is_all`, `jahanbots`, `msg_id`, `topdlbut`, `newdlbut`, `supportbut`, `sendbut`, `starttext`) VALUES
('$botid', 'on', 'none', 'none', 'false', 'false', '0', 'none', 'none', 'no', '1', 'none', 'on', 'on', 'on', 'on', 'NONE')");
} 
echo "<b>جداول ربات با موفقیت ایجاد شد</b>";
