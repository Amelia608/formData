<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-12-08 13:47:49
 * 
 */

class members {
    
    function __construct(){
    }

    /**
     * 获取一条
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function getone($id){
    	global $db;
    	$sql = "select phone,realname,nickname,gender,age,province,province_cn,favorite_vehicles,status,reg_time,last_logintime,last_loginIP from members where id='{$id}'";
    	$arr = $db->getone($sql);
    	if ($arr['gender']=="1") {
    		$arr['gender_cn'] = "男";
    	}else{
    		$arr['gender_cn'] = "女";
    	}
    	if ($arr['status']=="1") {
    		$arr['status_cn'] = "启用中";
    	}else{
    		$arr['status_cn'] = "停用中";
    	}
    	return $arr;
    }

    /**
     * 翻页
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_all_page($keywords,$sort,$page){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and (realname like '%".$keywords."%' or nickname like '%".$keywords."%' or phone like '%".$keywords."%')";

        if ($sort=="by_username") {
            $sql_sort = " order by realname desc,id desc ";
            $sort_cn  = "按姓名排序";
        }else if ($sort=="by_nickname") {
            $sql_sort = " order by nickname desc,id desc ";
            $sort_cn  = "按昵称排序";
        }else if ($sort=="by_phone") {
            $sql_sort = " order by phone desc,id desc ";
            $sort_cn  = "按用户名排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by flag desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by id desc ";
            $sort_cn  = "按注册时间排序";
        }

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from members ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,phone,nickname,realname,status from members ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
			$info[$i]['id']       = $arr_str[$i]['id'];
			$info[$i]['phone']    = $arr_str[$i]['phone'];
            $info[$i]['realname'] = $arr_str[$i]['realname'];
            $info[$i]['nickname'] = $arr_str[$i]['nickname'];
			$info[$i]['status']   = $arr_str[$i]['status'];
            if($arr_str[$i]['status']==1) $info[$i]['status_cn']  = "启用中";
            else $info[$i]['status_cn']    = "停用中";
        }

        //构建json
        $json_data['total']     = $total;
        $json_data['sort_cn']   = $sort_cn;
        $json_data['page']      = $page;
        $json_data['last_page'] = $page_info['total_page'];
        $json_data['page_info'] = $page_info['page_info'];
        $json_data['list']      = $info;

        return $json_data;
    }

    /**
     * 城市列表
     *
     * @param  [type] $cateid [description]
     *
     * @return [type] [description]
     */
    function china_block($cateid){
        global $db;
        $sql = "select id,title from china_block where parent='{$cateid}'";
        $arr = $db->getall($sql);
        return $arr;
    }

    /**
     * 判断是否是手机号
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
     * 添加一个会员
     *
     * @param [type] $uname [description]
     * @param [type] $nname [description]
     * @param [type] $rname [description]
     * @param [type] $province [description]
     * @param [type] $gender [description]
     * @param [type] $age [description]
     * @param [type] $fav [description]
     * @param [type] $pwd1 [description]
     * @param [type] $pwd2 [description]
     * @param [type] $status [description]
     */
    function add($uname,$nname,$rname,$province,$gender,$age,$fav,$pwd1,$pwd2,$status){
        if (!$uname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写用户名";
        }else if (!self::isMobile($uname)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户名必须为手机号码";
        }else if (!$nname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写昵称";
        }else if (!$rname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写姓名";
        }else if (!$province) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择省份";
        }else if ($gender==="") {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择性别";
        }else if (!$age) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "年龄需为整数";
        }else if (!$fav) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择喜爱的车系";
        }else if (!$pwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写登录密码";
        }else if (!$pwd2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写确认密码";
        }else if ($pwd2!=$pwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次填写的密码不一致";
        }else{
            global $db;
            $sql_check = "select phone from members where phone='{$uname}'";
            $arr_check = $db->getone($sql_check);
            if ($arr_check['phone']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已经存在";
            }else{
                //获取省份
                $arr_province = $db->getone("select title from china_block where id='{$province}'");
                if ($arr_province['title']) {
                    unset($insertSql);
                    $insertSql['phone']             = $uname;
                    $insertSql['password']          = md5($pwd1);
                    $insertSql['realname']          = $rname;
                    $insertSql['nickname']          = $nname;
                    $insertSql['gender']            = $gender;
                    $insertSql['age']               = $age;
                    $insertSql['province']          = $province;
                    $insertSql['province_cn']       = $arr_province['title'];
                    $insertSql['favorite_vehicles'] = $fav;
                    $insertSql['status']            = $status;
                    $insertSql['reg_time']          = time();
                    $return = $db->insert($insertSql,"members");
                    if ($return) {
                        $arr_result['code'] = "success";
                        $arr_result['info'] = "添加完成";
                    }else{
                        $arr_result['code'] = "error";
                        $arr_result['info'] = "数据操作错误，请联系管理员";
                    }
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "省份数据错误";
                }

            }
        }
        return $arr_result;
    }

    /**
     * 修改一条记录
     *
     * @param  [type] $id [description]
     * @param  [type] $uname [description]
     * @param  [type] $nname [description]
     * @param  [type] $rname [description]
     * @param  [type] $province [description]
     * @param  [type] $gender [description]
     * @param  [type] $age [description]
     * @param  [type] $fav [description]
     * @param  [type] $pwd1 [description]
     * @param  [type] $pwd2 [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify($id,$uname,$nname,$rname,$province,$gender,$age,$fav,$pwd1,$pwd2,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户信息丢失";
        }else if (!$uname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写用户名";
        }else if (!self::isMobile($uname)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户名必须为手机号码";
        }else if (!$nname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写昵称";
        }else if (!$rname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写姓名";
        }else if (!$province) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择省份";
        }else if ($gender==="") {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择性别";
        }else if (!$age) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写年龄";
        }else if (!$fav) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择喜爱的车系";
        }else if (!$pwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写登录密码";
        }else if (!$pwd2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写确认密码";
        }else if ($pwd2!=$pwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次填写的密码不一致";
        }else{
            global $db;
            $sql_check = "select phone from members where phone='{$uname}' and id!='{$id}'";
            $arr_check = $db->getone($sql_check);
            if ($arr_check['phone']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已经存在";
            }else{
                //获取省份
                $arr_province = $db->getone("select title from china_block where id='{$province}'");
                if ($arr_province['title']) {
                    unset($insertSql);
                    if ($pwd1 != "********") {
                        $insertSql['password']      = md5($pwd1);
                    }
                    $insertSql['realname']          = $rname;
                    $insertSql['nickname']          = $nname;
                    $insertSql['gender']            = $gender;
                    $insertSql['age']               = $age;
                    $insertSql['province']          = $province;
                    $insertSql['province_cn']       = $arr_province['title'];
                    $insertSql['favorite_vehicles'] = $fav;
                    $insertSql['status']            = $status;
                    $insertSql['reg_time']          = time();
                    $return = $db->update($insertSql,"members"," id='{$id}'");
                    if ($return=="1") {
                        $arr_result['code'] = "success";
                        $arr_result['info'] = "修改完成";
                    }elseif($return=="0"){
                        $arr_result['code'] = "success";
                        $arr_result['info'] = "内容没有变化，无需修改";
                    }else{
                        $arr_result['code'] = "error";
                        $arr_result['info'] = "数据操作错误，请联系管理员";
                    }
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "省份数据错误";
                }
            }
        }
        return $arr_result;
    }

    /**
     * 修改状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function change_status($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,"members","id='{$id}'");
        }
        return $status;
    }
    /**
     * 根据手机和密码获取用户信息
     * @param  [type] $phone    [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    function get_one($phone,$password){
        global $db;
        $sql_str = "select * from members where phone='".$phone."' and password='".$password."' limit 1";
        $arr_str = $db->getone($sql_str);
        $new_arr['id']         = $arr_str['id'];
        $new_arr['phone']      = $arr_str['phone'];
        $new_arr['password']   = $arr_str['password'];
        $new_arr['sex']        = $arr_str['gender'];
        if($arr_str['gender']==="0"){
            $new_arr['gender_cn'] = "女";
        }elseif($arr_str['gender']==="1"){
            $new_arr['gender_cn'] = "男";
        }else{
            $new_arr['gender_cn'] = "";
        }
        $new_arr['realname']   = $arr_str['realname'];
        $new_arr['nickname']   = $arr_str['nickname'];
        $new_arr['userface']   = $arr_str['face'];
        $new_arr['status']     = $arr_str['status'];
        $new_arr['wx_openid']  = $arr_str['wx_unionid'];
        $new_arr['sn_openid']  = $arr_str['sina_uid'];
        $new_arr['qq_openid']  = $arr_str['QQ_OpenID'];

        return $new_arr;
    }

    /**
     * 注册
     * [user_reg description]
     * @param  [type] $phone     [description]
     * @param  [type] $v_code    [description]
     * @param  [type] $password1 [description]
     * @param  [type] $password2 [description]
     * @return [type]            [description]
     */
    function user_reg($phone,$v_code,$password1,$password2){
        if(!$phone){
            $result_code = "error";
            $result_msg  = "请输入手机号";
        }elseif(!isMobile($phone)){
            $result_code = "error";
            $result_msg  = "手机格式非法";
        }elseif(!$v_code){
            $result_code = "error";
            $result_msg  = "请输入验证码";
        }elseif(!$password1){
            $result_code = "error";
            $result_msg  = "请输入密码";
        }elseif(!$password1){
            $result_code = "error";
            $result_msg  = "请输入密码";
        }elseif(strlen($password1)<6){
            $result_code = "error";
            $result_msg  = "密码请设置6位以上";
        }elseif(strlen($password1)>16){
            $result_code = "error";
            $result_msg  = "密码请设置16位以下";
        }elseif($password1!=$password2){
            $result_code = "error";
            $result_msg  = "两次输入密码不一致";
        }else{
            global $db;
            $sql_getinfo = "select * from members where phone='{$phone}' limit 1";
            $arr_getinfo = $db->getone($sql_getinfo);
            $s_phone = $arr_getinfo['phone'];
            if($s_phone){
                $result_code = "error";
                $result_msg  = "手机号已经注册";
            }else{
                $sql_vcode = "select id,v_code,time from sms_chekcode where type='signin' and phone='{$phone}' and status=0 order by time desc limit 1";
                $arr_vcode = $db->getone($sql_vcode);
                $s_id   = $arr_vcode['id'];
                $s_code = $arr_vcode['v_code'];
                $s_time = $arr_vcode['time'];
                if($s_code){
                    $time_cha = $s_time-time();
                    if($time_cha<0){
                        $result_code = "error";
                        $result_msg  = "验证码已失效";
                    }elseif($s_code != $v_code){
                        $result_code = "error";
                        $result_msg  = "验证码无效，请重新输入。";
                    }else{
                        unset($setsql);
                        $setsql['phone']    = $phone;
                        $setsql['password'] = md5($password1);
                        $setsql['status']   = 1;
                        $setsql['reg_time'] = time();
                        $newid = $db->insert($setsql,"members");

                        //获取下用户信息
                        if($newid){
                            $result_code = "success";
                            $result_msg  = "成功注册";
                            $result_uid  = $newid;

                            unset($set_vcode);
                            $set_vcode['status'] = 1;
                            $datareturn=$db->update($set_vcode,"sms_chekcode"," id={$s_id}");
                        }else{
                            $result_code = "error";
                            $result_msg  = "数据操作失败";
                        }
                    }
                }else{
                    $result_code = "error";
                    $result_msg  = "验证码无效，请重新输入。";
                }
            }
        }
        $return_info['result'] = $result_code;
        $return_info['info']   = $result_msg;
        $return_info['uid']   = $result_uid;

        return $return_info;
    }

    /**
     * 登录
     * @param  [type] $phone    [description]
     * @param  [type] $password [description]
     * @param  [type] $source   [description]
     * @return [type]           [description]
     */
    function login($phone,$password){
        if(!$phone){
            $result_code = "error";
            $result_msg  = "请输入手机号";
        }elseif(!isMobile($phone)){
            $result_code = "error";
            $result_msg  = "手机格式非法";
        }elseif(!$password){
            $result_code = "error";
            $result_msg  = "请输入密码";
        }else{
            global $db,$config;
            $sql_str="select * from members where phone='".$phone."' and password='".$password."' limit 1";
            $arr_getinfo = self::get_one($phone,$password);
            $user_id = $arr_getinfo['id'];
            $arr_userinfo['id']         = $user_id;
            $arr_userinfo['phone']      = $arr_getinfo['phone'];
            $arr_userinfo['nickname']   = $arr_getinfo['nickname'];
            $arr_userinfo['sex']        = $arr_getinfo['gender'];
            $arr_userinfo['sex_cn']     = $arr_getinfo['gender_cn'];
            $arr_userinfo['userface']   = $arr_getinfo['face'];
            $arr_userinfo['realname']   = $arr_getinfo['realname'];
            
            $arr_userinfo['wx_unionid'] = $arr_getinfo['wx_openid'];
            $arr_userinfo['sina_uid']   = $arr_getinfo['sn_openid'];
            $arr_userinfo['QQ_OpenID']  = $arr_getinfo['qq_openid'];
           
            if(substr($arr_getinfo['userface'],0,4) == "http"){
                $userface = $arr_getinfo['userface'];
            }else{
                $userface =$config['dir_base_path'].$config['members']."/".$arr_getinfo['userface'];
            }
            $arr_userinfo['userface'] = $userface;

            $this_token               = md5($arr_getinfo['id']."--".time());
            $arr_userinfo['token']    = $this_token;

            if($user_id){
                $result_code = "success";
                $result_msg  = "登录成功";
                $result_user = $arr_userinfo;
            }else{
                $result_code = "error";
                $result_msg  = "用户名密码输入有误";
            }
        }
        $return_info['code'] = $result_code;
        $return_info['info'] = $result_msg;
        if($result_user){
            $return_info['userinfo'] = $result_user;
        }

        return $return_info;
    }

    //绑定第三方登录的
    function bind_openid($userid,$type,$openid){
        global $db,$config;
        //微信绑定
        if($type=="wx"){
            $columnname = "wx_unionid";
        }elseif($type=="sina"){
            $columnname = "sina_uid";
        }elseif($type=="qq"){
            $columnname = "QQ_OpenID";
        }else{
            $columnname = "";
        }
        if($columnname){
            unset($update_userinfo);
            $update_userinfo[$columnname] = $openid;
            $u_user_id = $db->update($update_userinfo, "members" ," id=".$userid);
            $result_code = "success";
            $result_msg  = "绑定成功";
        }else{
            $result_code = "error";
            $result_msg  = "类型错误";
        }
        $return_info['result'] = $result_code;
        $return_info['msg']    = $result_msg;
        return $return_info;
    }

}