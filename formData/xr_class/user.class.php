<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-10
 * 
 */

class adm {
    function __construct() {
    }

    function get_error_times($username){
        global $db;
        $sql_adm = "select errortimes from adm_users where username='{$username}' limit 1 ";
        $arr_adm = $db->getone($sql_adm);
        return $arr_adm['errortimes'];
    }

    /**
     * 登录
     *
     * 登录安全：
     * 1，有用户名：记录连续输入错误次数，如果连续5次，锁定24小时。
     * 2，无用户名：检测登录IP，1小时内，不能超过5次。
     * 
     * @return [type] [description]
     */
    function login($username,$password,$login_ip){
        if (!$username) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入用户名";
        }elseif (!$password) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入密码";
        }else{
            global $db,$config;
            $sql_adm = "select id,nickname,username,password,locktime,alw_ip,flag,user_right from adm_users where username='{$username}' limit 1 ";
            $arr_adm = $db->getone($sql_adm);
            if ($arr_adm['nickname']){
                if(!$arr_adm['flag']){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "账号已停用";
                    $needLog = "n";
                }else if($arr_adm['locktime'] > time()){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "账号已被锁定";
                    $needLog = "n";
                }else if(($arr_adm['alw_ip'] != $login_ip) && $config['ip_restrictions']){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "此IP不允许登录";
                    $needLog = "y";
                }else if($arr_adm['password'] != md5($password)){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "密码错误";
                    $needLog = "y";
                }else{
                    $_SESSION['adm_userid']   = $arr_adm['id'];
                    $_SESSION['adm_nickname'] = $arr_adm['nickname'];
                    $_SESSION['adm_name']     = $arr_adm['username'];
                    $_SESSION['adm_right']    = $arr_adm['user_right'];
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "登录成功";
                    $needLog = "y";
                }
            }else{
                $sql_getNums = "select id from adm_login_log where login_ip='{$login_ip}' and login_time<'".(time()+5*60)."'";
                $total = $db->getResultRows($sql_getNums);

                if ($total>4 && $config['login_trial']) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "尝试过于频繁，请稍候再试";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "用户名或密码输入有误";
                    $needLog = "y";
                }
            }
            if ($needLog == "y") {
                $lg = self::log($username,$arr_result['info'],$login_ip);
            }
        }
        return $arr_result;
    }

    /**
     * 登录日志
     *
     * 30分钟内连续输入错误5次，锁定账号
     * 5次以内输入成功，清除输入错误次数
     *
     * @return [type] [description]
     */
    function log($username,$result,$login_ip){
        global $db,$config;
        unset($setsql);
        $setsql['username']   = $username;
        $setsql['result']     = $result;
        $setsql['login_time'] = time();
        $setsql['login_ip']   = $login_ip;
        $newid = $db->insert($setsql,"adm_login_log");

        if ($result=="登录成功") {
            unset($setsql);
            $setsql['lgn_time']   = time();
            $setsql['errortimes'] = 0;
            $setsql['locktime']   = 0;
            $setsql['lgn_ip']     = $login_ip;
            $datareturn = $db->update($setsql,"adm_users","username='{$username}'");
        }else{
            if ($config['login_trial']) {
                //添加一条记录
                $sql_times = "update adm_users set errortimes = errortimes+1 where username='{$username}'";
                $result = $db->query($sql_times);
                $e_times = self::get_error_times($username);

                if ($e_times>4) {
                    unset($setsql);
                    $setsql['locktime'] = time()+24*60*60;
                    $datareturn = $db->update($setsql,"adm_users","username='{$username}'");
                }
            }
        }
    }

    /**
     * 修改密码
     *
     * @param  [type] $id [description]
     * @param  [type] $oldPwd [description]
     * @param  [type] $newpwd1 [description]
     * @param  [type] $newpwd2 [description]
     *
     * @return [type] [description]
     */
    function change_pwd($id,$oldPwd,$newpwd1,$newpwd2){
        if (!$oldPwd) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入原始密码";
        }elseif (!$newpwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入新密码";
        }elseif ($newpwd1==$oldPwd) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "新密码和原始密码相同，请修改";
        }elseif (!$newpwd2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入确认密码";
        }elseif ($newpwd2!=$newpwd1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次密码输入不一致";
        }else{
            global $db;
            $sql = "select id from adm_users where id='{$id}' and password='".md5($oldPwd)."'";
            $arr = $db->getone($sql);
            if ($arr['id']) {
                $new_pwd = md5($newpwd1);
                unset($setsql);
                $setsql['password']      = $new_pwd;
                $setsql['changepwdtime'] = time();
                $setsql['mdy_user']      = $_SESSION['adm_userid'];
                $upreturn = $db->update($setsql,"adm_users"," id='{$id}'");
                if (!$upreturn) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败";
                }else{
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "密码已修改，请使用新密码登录";
                    session_unset();
                    session_destroy();
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "原始密码输入有误";
            }
        }
        return $arr_result;
    }

    /**
     * 获取权限列表
     *
     * @return [type] [description]
     */
    function get_rightlist(){
        global $db;
        $arr_temp = $db->getall("select id,title from adm_right order by id asc");
        return $arr_temp;
    }

    /**
     * 获取一条管理员信息
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one($id){
        global $db;
        $sql = "select username,nickname,flag,user_right from adm_users where id='{$id}'";
        $arr_tmp = $db->getone($sql);

        return $arr_tmp;
    }

    /**
     * 获取翻页列表
     *
     * @param  [type] $type [description]
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_all_page($keywords,$sort,$page){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where user_right!=-1";

        if ($keywords) $sql_where .= " and (username like '%".$keywords."%' or nickname like '%".$keywords."%')";

        if ($sort=="by_username") {
            $sql_sort = " order by username desc,id desc ";
            $sort_cn  = "按登录名排序";
        }else if ($sort=="by_nickname") {
            $sql_sort = " order by nickname desc,id desc ";
            $sort_cn  = "按显示名排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by flag desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by username desc,id desc ";
            $sort_cn  = "按登录名排序";
        }

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from adm_users ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,username,nickname,flag from adm_users ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['username'] = $arr_str[$i]['username'];
            $info[$i]['nickname'] = $arr_str[$i]['nickname'];
            $info[$i]['status']   = $arr_str[$i]['flag'];
            if($arr_str[$i]['flag']==1) $info[$i]['status_cn']  = "启用中";
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
     * 新增
     *
     * @param [type] $username [description]
     * @param [type] $nickname [description]
     * @param [type] $password1 [description]
     * @param [type] $password2 [description]
     * @param [type] $userright [description]
     * @param [type] $flag [description]
     */
    function add($username,$nickname,$password1,$password2,$userright,$flag){
        if (!$username) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录名";
        }elseif (!ctype_alnum($username)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "登录名仅可为数字和字母组合";
        }elseif (!$nickname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入显示名";
        }elseif (!$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录密码";
        }elseif (!$password2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入确认密码";
        }elseif ($password2!=$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次密码输入不一致";
        }elseif (!$userright) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择权限";
        }else{
            global $db;
            $temp = $db->getone("select username from adm_users where username='{$username}'");
            if ($temp['username']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已存在";
            }else{
                unset($setsql);
                $setsql['username']      = $username;
                $setsql['nickname']      = $nickname;
                $setsql['password']      = md5($password1);
                $setsql['flag']          = 1;
                $setsql['user_right']    = $userright;
                $setsql['add_time']      = time();
                $setsql['changepwdtime'] = time();
                $setsql['add_userid']    = $_SESSION['adm_userid'];
                $newid = $db->insert($setsql,"adm_users");
                if (!$newid) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败";
                }else{
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "添加完成";
                }
            }
        }
        return $arr_result;
    }

    /**
     * 修改
     *
     * @param  [type] $username [description]
     * @param  [type] $nickname [description]
     * @param  [type] $password1 [description]
     * @param  [type] $password2 [description]
     * @param  [type] $userright [description]
     * @param  [type] $flag [description]
     *
     * @return [type] [description]
     */
    function modify($id,$nickname,$password1,$password2,$userright,$flag){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户信息丢失";
        }elseif (!$nickname) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入显示名";
        }elseif (!$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录密码";
        }elseif (!$password2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入确认密码";
        }elseif ($password2!=$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次密码输入不一致";
        }elseif (!$userright) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择权限";
        }else{
            global $db;
            $temp = $db->getone("select * from adm_users where username='{$username}' and id!='{$id}'");
            if ($temp['username']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已存在";
            }else{
                $before = $db->getone("select * from adm_users where id='{$id}'");
                unset($setsql);
                $setsql['nickname']      = $nickname;
                if ($password1 != "********") {
                    $setsql['password']      = md5($password1);
                    $setsql['changepwdtime'] = time();
                }
                $setsql['flag']          = $flag;
                $setsql['user_right']    = $userright;
                $setsql['mdy_time']      = time();
                $setsql['mdy_user']      = $_SESSION['adm_userid'];
                $newid = $db->update($setsql,"adm_users"," id='{$id}'");
                if (!$newid) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败";
                }else{
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "修改完成";
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
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            $before = $db->getone("select flag from adm_users where id='{$id}'");
            unset($setsql);
            $setsql['flag'] = $status;
            $return = $db->update($setsql,"adm_users"," id='{$id}'");
        }
        return $status;
    }
    
    /**
     * 删除记录
     *
     * @param  [type] $ids [description]
     *
     * @return [type] [description]
     */
    function delete($id){
        global $db;
        $before = $db->getone("select * from adm_users where id='{$id}'");
        $return = $db->delete("adm_users"," id = '{$id}'");
        if ($return) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "删除完成";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
        return $arr_result;
    }
}