<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-02-02
 * @Link    : http://www.xiruiad.com
 *
 * 用来防止直接访问当前页面
 * 当前页面只可以通过本站URL调用
 * 
 * @perv_servername  : 上一级页面的域名
 * @this_servername  : 当前页面域名
 * 
 */

$perv_servername = parse_url($_SERVER['HTTP_REFERER']);
$this_servername = $_SERVER['SERVER_NAME'];

if ($perv_servername['host'] != $this_servername) {
	die("error");
}