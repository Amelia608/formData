<?php
/**
 * 转义SQL语句中的字符
 *
 * @param  [type] $str [description]
 *
 * @return [type] [description]
 */
function str_input($str) {
	$str=trim($str);
	$str=mysql_real_escape_string($str);
	return $str;
}

/**
 * 获取IP
 *
 * @return [type] [description]
 */
function getip(){
	if (getenv('HTTP_CLIENT_IP') and strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')) {
		$onlineip=getenv('HTTP_CLIENT_IP');
	}elseif(getenv('HTTP_X_FORWARDED_FOR') and strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')) {
		$onlineip=getenv('HTTP_X_FORWARDED_FOR');
	}elseif(getenv('REMOTE_ADDR') and strcasecmp(getenv('REMOTE_ADDR'),'unknown')) {
		$onlineip=getenv('REMOTE_ADDR');
	}elseif(isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] and strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')) {
		$onlineip=$_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}

/**
 * 单引号双引号转义+消除HTML标签+ 消除">"防止破坏input框
 * 
 * @param  [type] $value [description]
 *
 * @return [type] [description]
 */
function addslashes_deep($value){
    if (empty($value))    {
        return $value;
    }else {
		$value = is_array($value) ? array_map('addslashes_deep', $value) : mystrip_tags($value);
		return $value;
    }
}

/**
 * 单引号双引号转义
 *
 * @param  [type] $value [description]
 *
 * @return [type] [description]
 */
function addslashes_deep2($value){
    if (empty($value))    {
        return $value;
    }else {
		$value=is_array($value) ? array_map('addslashes', $value) : addslashes($value);
		return $value;
    }
}

/**
 * 替换敏感字符
 *
 * @param  [type] $string [description]
 *
 * @return [type] [description]
 */
function mystrip_tags($string){
	$find    = array('&', '"', '<', '>', "'");
	$replace = array('&amp;', '&quot;', '&lt;', '&gt;', '’');
	$string = str_replace($find, $replace, $string);
	$string = strip_tags($string);
	return $string;
}

/**
 * 创建目录
 *
 * @param  [type] $path [description]
 *
 * @return [type] [description]
 */
function make_dir($path){ 
	if(!file_exists($path)){
		make_dir(dirname($path));
		@mkdir($path,0777);
		@chmod($path,0777);
	}
}

/**	
 * 生成随机数
 *
 * @param  [type] $type [description]
 * @param  [type] $length [description]
 *
 * @return [type] [description]
 */
function str_random($type,$length) {
	if($type=="numstr") $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
	else if($type=="num") $pattern = "123456789";
	else if($type=="str") $pattern = "abcdefghijklmnopqrstuvwxyz";
	else if($type=="exchange_code") $pattern = "123456789ABCDEFGHJKLMNPQRSTWXY";

	for($i=0;$i<$length;$i++){
		$max=strlen($pattern)-1;
		$str_random .= $pattern{mt_rand(0,$max)};
	}
	return $str_random;
}

/**
 * 获取文件扩展名
 *
 * @param  [type] $file [description]
 *
 * @return [type] [description]
 */
function get_extension($file){	
	return pathinfo($file, PATHINFO_EXTENSION);
}

/**
 * 压缩图片尺寸
 *
 * @param  [type] $in_file [description]
 * @param  [type] $out_file [description]
 * @param  [type] $resizeType [description]
 * @param  [type] $Number [description]
 *
 * @return [type] [description]
 */
function resize_pic($in_file, $out_file, $resizeType,$Number){
	$image = null;
	$extension = strtolower(preg_replace('/^.*\./', '', $in_file));
	switch($extension){
		case 'jpg':
		case 'jpeg':
			$image = imagecreatefromjpeg($in_file);
			break;
		case 'png':
			$image = imagecreatefrompng($in_file);
			break;
		case 'gif':
			$image = imagecreatefromgif($in_file);
			break;
	}

	if(!$image || !is_resource($image)) return false;

	$width  = imagesx($image);
	$height = imagesy($image);
	$ratio  = $height/$width;
	
	if($resizeType=="width"){
		$new_width	= intval($Number);
		$new_height = $new_width*$ratio;
	}else{
		$new_height = intval($Number);
		$new_width	= $new_height/$ratio;
	}

	$new_image = imagecreatetruecolor($new_width, $new_height);
	if($extension=="png"){
		//保存透明度
		imagesavealpha($image,true);
		imagealphablending($new_image,false);
		imagesavealpha($new_image,true);
	}

	imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	if($extension=="png"){
		//保存透明度
		$ret = imagepng($new_image, $out_file);
	}else{
		$ret = imagejpeg($new_image, $out_file);
	}
	imagedestroy($new_image);
	imagedestroy($image);

	return $ret;
}

/**
 * base64编码转图片
 *
 * @param  [type] $org_str [description]
 * @param  [type] $dir [description]
 * @param  [type] $location [description]
 *
 * @return [type] [description]
 */
function resolve_base64_img($org_str, $dir,$location){
	global $dir_site_path_upload,$dir_img_path;
	if($location=="landlord"){
		$base_path = "../..".$dir_img_path;
	}else{
		$base_path = $dir_site_path_upload.$dir_img_path;
	}

	$tmp_obj_path=$base_path.$dir;
	make_dir($tmp_obj_path);
	$tmp_obj_path.="/".date("Y");
	make_dir($tmp_obj_path);
	$tmp_obj_path.="/".date("n");
	make_dir($tmp_obj_path);
	$tmp_obj_path.="/".date("j");
	make_dir($tmp_obj_path);
	$obj_path=$tmp_obj_path."/";
	$sm_path="/".date("Y")."/".date("n")."/".date("j")."/";

	$pattern='/<img src="data:image\/(.*?)">/';
	preg_match_all($pattern, $org_str, $matchs);

	$n_resolve_img=count($matchs[1]);
	if ($n_resolve_img){
		for ($i=0;$i<$n_resolve_img;$i++) {
			$tmp1=explode("\"",$matchs[1][$i]);
			if (count($tmp1)>1) {
				$img_base64_str=$tmp1[0];
			}else{
				$img_base64_str=$matchs[1][$i];
			}

			$tmp3=explode(",",$img_base64_str);
			$img_info=$tmp3[0];
			$img_str=$tmp3[1];

			if (strstr(strtolower($img_info),"jpg") or strstr(strtolower($img_info),"jpeg")) $file_ext="jpg";
			else if (strstr(strtolower($img_info),"png")) $file_ext="png";
			else if (strstr(strtolower($img_info),"gif")) $file_ext="gif";
			else if (strstr(strtolower($img_info),"bmp")) $file_ext="bmp";
			else $file_ext="jpg";

			$img           = base64_decode($img_str);
			$rand_name     = str_random("numstr",12);
			$img_file_name = $rand_name.".".$file_ext;
			$obj_file      = $obj_path.$img_file_name;
			$result        = file_put_contents($obj_file, $img);
			
			if ($result) {
				$n_create_ok++;
				$obj_str[] = $matchs[0][$i];
				$new_str[] = '<img src="'.$obj_file.'">';
			}
		}
		$resolve_str = str_replace($obj_str,$new_str,$org_str);
	}

	$return_info['n_resolve']=$n_resolve_img;
	$return_info['n_create']=$n_create_ok;
	$return_info['resolve_str']=$resolve_str;

	return $return_info;
	unset($dir_data);
}

/**
 * 加密
 *
 * @param  [type] $txt [description]
 * @param  [type] $key [description]
 *
 * @return [type] [description]
 */
function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt);$i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

/**
 * 解密
 *
 * @param  [type] $txt [description]
 * @param  [type] $key [description]
 *
 * @return [type] [description]
 */
function passport_decrypt($txt, $key) {
	$txt = passport_key(base64_decode($txt), $key);
	$tmp = '';
	for($i = 0;$i < strlen($txt);$i++) {
		$md5 = $txt[$i];
		$tmp .= $txt[++$i] ^ $md5;
	}
	return $tmp;
}

/**
 * 密钥
 *
 * @param  [type] $txt [description]
 * @param  [type] $encrypt_key [description]
 *
 * @return [type] [description]
 */
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt);$i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}

/**
 * 检查是否是整数
 *
 * @param  [type] $varnum [description]
 *
 * @return [type] [description]
 */
function check_int($varnum){
	$string_var = "0123456789";
	$len_string = strlen($varnum);
	if(substr($varnum,0,1)=="0"){
		return false;
		die();
	}else{
		for($i=0;$i<$len_string;$i++){
			$checkint = strpos($string_var,substr($varnum,$i,1));
			if($checkint===false){
			return false;
			die();
		}
	}
		return true;
	}
}

/**
 * 校验是否是手机
 *
 * @param  [type] $mobile [description]
 *
 * @return bool [description]
 */
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    if(strlen($mobile)!=11){
        return false;
    }
    return preg_match("/^1[34578]{1}\d{9}$/",$mobile) ? true : false;
}

/**
 * 加时间
 *
 * @param [type] $part [description]
 * @param [type] $number [description]
 * @param [type] $date [description]
 */
function DateAdd($part, $number, $date){
	$date_array = getdate(strtotime($date));
	$hor = $date_array["hours"];
	$min = $date_array["minutes"];
	$sec = $date_array["seconds"];
	$mon = $date_array["mon"];
	$day = $date_array["mday"];
	$yar = $date_array["year"];
	switch($part){
		case "y": $yar += $number; break;
		case "q": $mon += ($number * 3); break;
		case "m": $mon += $number; break;
		case "w": $day += ($number * 7); break;
		case "d": $day += $number; break;
		case "h": $hor += $number; break;
		case "n": $min += $number; break;
		case "s": $sec += $number; break;
	}
	return date("Y-m-d H:i:s", mktime($hor, $min, $sec, $mon, $day, $yar));
}

/**
 * 时间差
 *
 * @param [type] $part [description]
 * @param [type] $begin [description]
 * @param [type] $end [description]
 */
function DateDiff($part, $begin, $end){
	$diff = strtotime($end) - strtotime($begin);
	switch($part){
		case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
		case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
		case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
		case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
		case "h": $retval = bcdiv($diff, (60 * 60)); break;
		case "n": $retval = bcdiv($diff, 60); break;
		case "s": $retval = $diff; break;
	}
	return $retval;
}

/**
 * 获取周
 *
 * @param [type] $date1 [description]
 */
function GetWeek($date1){
	$datearr = explode("-",$date1);     //将传来的时间使用“-”分割成数组
	$year = $datearr[0];       //获取年份
	$month = sprintf('%02d',$datearr[1]);  //获取月份
	$day = sprintf('%02d',$datearr[2]);      //获取日期
	$hour = $minute = $second = 0;   //默认时分秒均为0
	$dayofweek = mktime($hour,$minute,$second,$month,$day,$year);    //将时间转换成时间戳
	$shuchu = date("w",$dayofweek);      //获取星期值
	$weekarray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
	echo $weekarray[$shuchu];
}

/**
 * 校验日期
 *
 * @param  [type] $date [description]
 *
 * @return [type] [description]
 */
function valid_date($date){
    //匹配日期格式
    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)){
        //检测是否为日期,checkdate为月日年
        if(checkdate($parts[2],$parts[3],$parts[1]))
            return true;
        else return false;
    }
    else return false;
}

/**
 * 过滤危险字符
 *
 * @param [type] $str [description]
 */
function SaftLeaveWord($str) {  
	if ($str=="") {  
		return "";  
	}  
	
	$str = strip_tags ( $str );  
	$str = htmlspecialchars ( $str );  
	$str = nl2br ( $str );  
	$str = str_replace ( "?", "", $str );  
	$str = str_replace ( "*", "", $str );  
	$str = str_replace ( "!", "", $str );  
	$str = str_replace ( "~", "", $str );  
	$str = str_replace ( "$", "", $str );  
	$str = str_replace ( "%", "", $str );  
	$str = str_replace ( "'", "", $str );  
	$str = str_replace ( "^", "", $str );  
	$str = str_replace ( "select", "", $str );  
	$str = str_replace ( "join", "", $str );  
	$str = str_replace ( "union", "", $str );  
	$str = str_replace ( "where", "", $str );  
	$str = str_replace ( "insert", "", $str );  
	$str = str_replace ( "delete", "", $str );  
	$str = str_replace ( "update", "", $str );  
	$str = str_replace ( "like", "", $str );  
	$str = str_replace ( "drop", "", $str );  
	$str = str_replace ( "create", "", $str );  
	$str = str_replace ( "modify", "", $str );  
	$str = str_replace ( "rename", "", $str );  
	$str = str_replace ( "alter", "", $str );  
	$str = str_replace ( "cast", "", $str );  
	
	$farr = array ("//s+/", //过滤多余的空白  
	//过滤 <script 防止引入恶意内容或恶意代码,如果不需要插入flash等,还可以加入<object的过滤
	"/<(//?)(img|script|i?frame|style|html|body|title|link|meta|/?|/%)([^>]*?)>/isU", 
	//过滤javascript的on事件 
	"/(<[^>]*)on[a-zA-Z]+/s*=([^>]*>)/isU") 
	;  
	//如果要直接清除不安全的标签，这里可以留空
	$tarr = array (" ", "", "" );
	
	return $str;  
}

/**
 * XML转数组
 *
 * @param  [type] $xml [description]
 *
 * @return [type] [description]
 */
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}

/**
 * 获取指定日期段内每一天的日期
 * @param  Date  $startdate 开始日期
 * @param  Date  $enddate   结束日期
 * @return Array
 */
function getDateFromRange($startdate, $enddate){
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);
    $days = ($etimestamp-$stimestamp)/86400+1;
    $date = array();
    for($i=0; $i<$days; $i++){
        $date[] = date('Y-m-d', $stimestamp+(86400*$i));
    }
    return $date;
}

/**
 * 是否是手机浏览器
 *
 * @return bool [description]
 */
function isMobile_browsers(){    
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';    
    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';      
    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');  
    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');    
                
    $found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||    
              CheckSubstrs($mobile_token_list,$useragent);    
                
    if ($found_mobile){    
        return true;    
    }else{    
        return false;    
    }    
}

/**
 * 使用百度API根据IP地址获取详细地址
 *
 * @param  [type] $ip [description]
 *
 * @return [type] [description]
 */
function get_address($ip){
	// {  
	//     address: "CN|北京|北京|None|CHINANET|1|None",    #地址  
	//     content:						#详细内容  
	//     {  
	//         address: "北京市",		#简要地址  
	//         address_detail:			#详细地址信息  
	//         {  
	//             city: "北京市",		#城市  
	//             city_code: 131,		#百度城市代码  
	//             district: "",		#区县  
	//             province: "北京市",  #省份  
	//             street: "",			#街道  
	//             street_number: ""	#门址  
	//         },  
	//         point:					#当前城市中心点，注意当前坐标返回类型
	//         {  
	//             x: "116.39564504",  
	//             y: "39.92998578"  
	//         }  
	//     },  
	//     status: 0					#返回状态码  
	// }

	$url = "http://api.map.baidu.com/location/ip?ak=omi69HPHpl5luMtrjFzXn9df&ip=$ip&coor=bd09ll";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	if(curl_errno($ch)){
		echo 'CURL ERROR Code: '.curl_errno($ch).', reason: '.curl_error($ch);
	}
	curl_close($ch);
	return $output;
}

/**
 * 校验是否是图片
 *
 * @param  [type] $path [description]
 *
 * @return bool [description]
 */
function is_image($path){
	$data = file_get_contents($path);
	$im   = imagecreatefromstring($data);
	if($im != false){
	    echo '<p>图片正常...</p>';
	}else{
	    echo '<p>图片已损坏...</p>';
	}
}

/**
 * 获取字符长度
 *
 * php中UTF-8中文字符占3位，GB2312占2位，为了统一，计算出来
 * ((中文*3 + 英文*1)+(中文*1 + 英文*1)) / 2 = 中文*2 + 英文*1
 * 
 * @return [type] [description]
 */
function get_strlen($str){
	return (strlen($str)+mb_strlen($str,"UTF8"))/2; 
}

function unicode_decode($name){
  $json = '{"str":"'.$name.'"}';
  $arr = json_decode($json,true);
  if(empty($arr)) return '';
  return $arr['str'];
}

function json_encode_api($arr){
  $json = json_encode(unsetnullArr($arr),JSON_UNESCAPED_UNICODE);
  return $json;
}

//APP`说出现NULL会报错，统一替换成 " "
function unsetnullArr($arr){
    $narr = array();
    while(list($key, $val) = each($arr)){
        if($val==="need_return_arr_for_json"){
            $narr[$key] = array();
        }elseif(is_array($val)){
            $val = unsetnullArr($val);
            count($val)==0 || $narr[$key] = $val;
        }else{
            $val === null?$narr[$key] = '':$narr[$key] = $val;
        }
    }
    return $narr;
}

function get_page_info($total,$per_page,$page){ 
    $n_left_max  = 3;
    $n_right_max = 3;

    if ($total%$per_page==0) $total_page=$total/$per_page;
    else $total_page=ceil($total/$per_page);
    
    if ($total_page>($n_left_max+$n_right_max+1)) $flag_more=1;
    else $flag_more=0;

    if ($flag_more==1){
        if ($page<($n_left_max)) {
            for ($i=1;$i<=$n_left_max+$n_right_max-2;$i++){
                $tmp1[]=$i;
            }
            $tmp1[]="…";
            $tmp1[]=$total_page;
        }else{
            $tmp1[]="1";
            $tmp1[]="…";
            if ($total_page-$page<=$n_right_max){
                for ($i=$total_page-$n_left_max-$n_right_max+2;$i<=$total_page;$i++){
                    $tmp1[]=$i;
                }
            }else{
                $tmp_pos=round($n_left_max+$n_right_max-4)/2;
                for ($i=$page-$tmp_pos;$i<=$page+$tmp_pos;$i++){
                    $tmp1[]=$i;
                }
                $tmp1[]="…";
                $tmp1[]=$total_page;
            }
        }
    }else{
        for ($i=1;$i<=$total_page;$i++){
            $tmp1[]=$i;
        }
    }
    
    if (!$total_page) {
       $total_page = 1;
    }

    $page_info["now_page"]   = $page;
    $page_info["total_page"] = $total_page;
    
    if ($tmp1) {
        $page_info["page_info"]  = join(",",$tmp1);
    }else $page_info["page_info"]  = "";

    return $page_info;
}

?>