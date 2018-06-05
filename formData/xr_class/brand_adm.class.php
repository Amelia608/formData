<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-10
 * 
 */

class brand_adm {
    function __construct() {
        $this->tbl_user       = "brands";
        $this->tbl_user_login = "adm_login_log";
    }

    /**
     * 获取错误次数
     *
     * @param  [type] $username [description]
     *
     * @return [type] [description]
     */
    function get_error_times($username){
        global $db;
        $sql_adm = "select errortimes from {$this->tbl_user} where username='{$username}' limit 1 ";
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
            $sql_adm = "select id,title,username,password,locktime,status from {$this->tbl_user} where username='{$username}' limit 1 ";
            $arr_adm = $db->getone($sql_adm);
            if ($arr_adm['title']){
                if(!$arr_adm['status']){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "账号已停用";
                    $needLog = "n";
                }else if($arr_adm['locktime'] > time()){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "账号已被锁定";
                    $needLog = "n";
                // }else if(($arr_adm['alw_ip'] != $login_ip) && $config['ip_restrictions']){
                //     $arr_result['code'] = "error";
                //     $arr_result['info'] = "此IP不允许登录";
                //     $needLog = "y";
                }else if($arr_adm['password'] != md5($password)){
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "密码错误";
                    $needLog = "y";
                }else{
                    $_SESSION['usertype']     = "品牌商家";
                    $_SESSION['brand_userid'] = $arr_adm['id'];
                    $_SESSION['brand_title']  = $arr_adm['title'];
                    $_SESSION['brand_name']   = $arr_adm['username'];
                    
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
            $datareturn = $db->update($setsql,"{$this->tbl_user}","username='{$username}'");
        }else{
            if ($config['login_trial']) {
                //添加一条记录
                $sql_times = "update {$this->tbl_user} set errortimes = errortimes+1 where username='{$username}'";
                $result = $db->query($sql_times);
                $e_times = self::get_error_times($username);

                if ($e_times>4) {
                    unset($setsql);
                    $setsql['locktime'] = time()+24*60*60;
                    $datareturn = $db->update($setsql,"{$this->tbl_user}","username='{$username}'");
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
            $sql = "select id from {$this->tbl_user} where id='{$id}' and password='".md5($oldPwd)."'";
            $arr = $db->getone($sql);
            if ($arr['id']) {
                $new_pwd = md5($newpwd1);
                unset($setsql);
                $setsql['password']      = $new_pwd;
                $setsql['changepwdtime'] = time();
                $upreturn = $db->update($setsql,"{$this->tbl_user}"," id='{$id}'");
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
     * 获取信息
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_info($id){
        global $db,$config;
        $sql = "select title,address,label,brands,telephone,cover,logo,banner,content,template,email,hotlinetime,qr_wx,qr_sina from {$this->tbl_user} where id='{$id}'";
        $arr = $db->getone($sql);
        $arr['cover_path']   = $config['dir_base_path'].$config['brand']."/small_".$arr['cover'];
        $arr['log_path']     = $config['dir_base_path'].$config['brand_logo']."/small_".$arr['logo'];
        $arr['banner_path']  = $config['dir_base_path'].$config['brand_banner']."/small_".$arr['banner'];
        $arr['qr_wx_path']   = $config['dir_base_path'].$config['brand']."/small_".$arr['qr_wx'];
        $arr['qr_sina_path'] = $config['dir_base_path'].$config['brand']."/small_".$arr['qr_sina'];
        return $arr;
    }

    /**
     * 完善基本信息
     *
     * @param [type] $userid [description]
     * @param [type] $title [description]
     * @param [type] $address [description]
     * @param [type] $brands [description]
     * @param [type] $projects [description]
     * @param [type] $telephone [description]
     * @param [type] $image_title [description]
     * @param [type] $logo_title [description]
     * @param [type] $banner_title [description]
     * @param [type] $content [description]
     */
    function add_baseinfo($userid,$title,$email,$address,$label,$brands,$telephone,$telephonetime,$image_title,$logo_title,$banner_title,$sinaqr,$wxqr,$content,$template){
        if (!$userid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写品牌名称";
        }elseif (!$address) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写地址";
        }elseif (!$brands) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择品牌范围";
        }elseif (!$label) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写涉及品牌";
        }elseif (!$telephone) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写客服电话";
        }elseif (!$telephone) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写客服工作时间";
        }elseif (!$image_title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传封面图片";
        }elseif (!$logo_title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传品牌Logo";
        }elseif (!$banner_title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传小站Banner";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入品牌介绍";
        }elseif (!$template) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择使用的模板";
        }else{
            global $db;
            $sql = "select folder,template from {$this->tbl_user} where id='{$userid}'";
            $arr = $db->getone($sql);

            if ($arr['template'] != $template) {
                $dir = iconv("UTF-8", "GBK",preg_replace('# #','',"../../brands/".$arr['folder']));
                if (!file_exists($dir)){
                    mkdir ($dir,0777,true);
                }
                //复制文件
                $sc_filepath = "../../brands/";
                $to_filepath = $dir;
                copy($sc_filepath."template".$template.".zip", $dir."/template.zip");
                $zip = new ZipArchive;
                $res = $zip->open($dir."/template.zip");
                if ($res===TRUE) {
                    $zip->extractTo($dir); 
                    $zip->close();
                    $myfile = fopen($to_filepath."/brand_id.php", "w") or die("Unable to open file!");
                    $txt = '<?php $brand_id='.$userid.' ?>';
                    fwrite($myfile, $txt);
                    fclose($myfile);
                }
                unlink($dir."/template.zip");
            }else{
                $res = TRUE;
            }
            if ($res===TRUE) {
                unset($setsql);
                $setsql['title']       = $title;
                $setsql['template']    = $template;
                $setsql['address']     = $address;
                $setsql['label']       = $label;
                $setsql['brands']      = $brands;
                $setsql['telephone']   = $telephone;
                $setsql['cover']       = $image_title;
                $setsql['logo']        = $logo_title;
                $setsql['banner']      = $banner_title;
                $setsql['content']     = $content;
                $setsql['hotlinetime'] = $telephonetime;
                $setsql['qr_wx']       = $wxqr;
                $setsql['qr_sina']     = $sinaqr;
                $setsql['email']       = $email;
                $newid = $db->update($setsql,"{$this->tbl_user}"," id='{$userid}'");
                if (!$newid) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败";
                }else{
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "修改完成";
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "模板创建失败".$res;
            }
        }
        return $arr_result;
    }
}