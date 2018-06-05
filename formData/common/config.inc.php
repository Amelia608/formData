<?PHP
session_start();

//************************禁用错误提示***********************************
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//************************定义时区***********************************
date_default_timezone_set("PRC");
define("root_web",$_SERVER['DOCUMENT_ROOT']);


// require_once(root_xrcommon.'function.php');
require_once(dirname(__FILE__).'/function.php');
require_once(dirname(__FILE__).'/../xr_class/mysql.class.php');

//************************定义系统常量********************************

$tss_company   = "XiruiAD";
$tss_url       = "http://www.xiruiad.com";
$company_title = "CAS改装展";
$web_url       = "http://".$_SERVER["HTTP_HOST"];

//************************管理后台页面配置************************************
$adm_title    = $company_title . " 管理后台";
$adm_homepage = "/Management/user/";

//************************品牌商家管理后台************************************
$adm_brand_title    = $company_title . " 品牌商家管理后台";
$adm_brand_hp		= "/brand_adm/user/";

//************************俱乐部管理后台************************************
$adm_club_title     = $company_title . " 俱乐部管理后台";
$adm_club_hp		= "/club_adm/user/";

//************************俱乐部管理后台************************************
$adm_store_title    = $company_title . " 门店管理后台";
$adm_store_hp		= "/store_adm/user/";

//************************俱乐部管理后台************************************
$adm_expert_title   = $company_title . " 达人管理后台";
$adm_expert_hp		= "/expert_adm/user/";

//************************SEO配置************************************
$web_title       = "CAS改装展";
$seo_keywords    = "SEO关键字";
$seo_description = "SEO描述";

//************************配置开关************************************
$config_is_shutdown = 0;	//系统维护

//************************12星座************************************
$arr_constellation = array("白羊座","金牛座","双子座","巨蟹座","狮子座","处女座","天秤座","天蝎座","射手座","摩羯座","水瓶座","双鱼座");
$arr_car_series = array("德系车","美系车","日系车","机车","新能源车","越野车","房车");

//************************数据传输安全性*******************************

//单引号双引号转义+消除HTML标签时请使用+ 取消">"以防止恶意破坏input结构
if (!empty($_GET))$_GET2	= addslashes_deep($_GET);
if (!empty($_POST))$_POST2	= addslashes_deep($_POST);
$_COOKIE2	= addslashes_deep($_COOKIE);
$_REQUEST2	= addslashes_deep($_REQUEST);

//单引号双引号转义时请使用
if (!empty($_GET))$_GET3	= addslashes_deep2($_GET);
if (!empty($_POST))$_POST3	= addslashes_deep2($_POST);
$_COOKIE3	= addslashes_deep2($_COOKIE);
$_REQUEST3	= addslashes_deep2($_REQUEST);

//**********************定义数组配置**************************************************
//数据库配置
$config['db_server']         = "localhost";
$config['db_username']       = "qiquanwa_louis";
$config['db_password']       = "920618wang";
$config['db_name']           = "qiquanwa_test";
$config['dbcharset']         = "utf8";
$config['connect']           = 1;

//常用变量配置
$config['online_ip']      = getip();
$config['time_gmt']       = time();
$config['time_ymd']       = date("Y-m-d H:i:s");
$config['this_day']       = date("Y-m-d");
$config['web_host']       = $web_url;
$config['url']            = $web_url;
?>