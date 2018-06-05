<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-12-08 13:47:49
 * 
 */

class brands {
    
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
    	global $db,$config;
    	$sql = "select title,label,username,logo,status,folder,banner,content,cover from brands where id='{$id}'";
    	$arr = $db->getone($sql);
        $arr['logo_path']   = $config['dir_base_path'].$config['brand_logo']."/small_".$arr['logo'];
        $arr['banner_path'] = $config['dir_base_path'].$config['brand_banner']."/small_".$arr['banner'];
        $arr['cover_path']  = $config['dir_base_path'].$config['brand']."/small_".$arr['cover'];
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

        if ($keywords) $sql_where .= " and (title like '%".$keywords."%' or username like '%".$keywords."%')";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按品牌名称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by id desc ";
            $sort_cn  = "按添加时间排序";
        }

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total = $db->getResultRows("select id from brands ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;
        
        $sql_str = "select id,title,label,status from brands ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']     = $arr_str[$i]['id'];
            $info[$i]['title']  = $arr_str[$i]['title'];
            $info[$i]['label']  = $arr_str[$i]['label'];
            $info[$i]['status'] = $arr_str[$i]['status'];
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
     * 翻页
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_all_page_front($keywords="",$page,$label=""){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where status=1";

        if ($keywords) $sql_where .= " and (title like '%".$keywords."%' or brands like '%".$keywords."%')";

        if($label) $sql_where .= " and label like '%".$label."%'";

        $sql_sort = " order by showorder desc,title desc ";

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total = $db->getResultRows("select id from brands ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;
        
        $sql_str = "select id,title,folder,label,address,brands,telephone,cover,logo from brands ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);
        $image_path = $config['dir_base_path'].$config['brand']."/";
        $logo_path = $config['dir_base_path'].$config['brand_logo']."/";
        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['title']     = $arr_str[$i]['title'];
            $info[$i]['label']     = $arr_str[$i]['label'];
            $info[$i]['folder']    = $arr_str[$i]['folder'];
            $info[$i]['address']   = $arr_str[$i]['address'];
            $info[$i]['brands']    = $arr_str[$i]['brands'];
            $info[$i]['telephone'] = $arr_str[$i]['telephone'];

            $arr_str[$i]['cover']?$info[$i]['cover']=$image_path.$arr_str[$i]['cover']:$image_path.$config['default_brandcover'];
            $arr_str[$i]['logo']?$info[$i]['logo']=$logo_path.$arr_str[$i]['logo']:$logo_path.$config['default_brandlog'];
        }

        //构建json
        $json_data['page']      = $page;
        $json_data['last_page'] = $page_info['total_page'];
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
     * 复制模板
     *
     * @param  [type] $rootFrom [description]
     * @param  [type] $rootTo [description]
     *
     * @return [type] [description]
     */
    function cp_files($rootFrom,$rootTo){  
        $handle=opendir($rootFrom);  
        while(false  !== ($file = readdir($handle))){  
            $fileFrom=$rootFrom.DIRECTORY_SEPARATOR.$file;  
            $fileTo=$rootTo.DIRECTORY_SEPARATOR.$file;  
            if($file=='.' || $file=='..'){continue;}  
            if(is_dir($fileFrom)){  
                mkdir($fileTo,0777);  
                cp_files($fileFrom,$fileTo);  
            }else{  
                @copy($fileFrom,$fileTo);  
            }
        }
        return true;
    } 
    /**
     * 添加一条记录
     *
     * @param [type] $title [description]
     * @param [type] $label [description]
     * @param [type] $username [description]
     * @param [type] $pwd1 [description]
     * @param [type] $pwd2 [description]
     * @param [type] $image [description]
     * @param [type] $status [description]
     * @param [type] $userid [description]
     */
    function add($title,$label,$username,$folder,$pwd1,$pwd2,$image,$status,$userid){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写品牌名称";
        }else if (!$username) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写用户名";
        }elseif (!ctype_alnum($username)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户名仅可为数字和字母组合";
        }else if (!$folder) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写访问目录";
        }elseif (!ctype_alnum($folder)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "访问目录仅可为数字和字母组合";
        }else if (!$label) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择品牌属性";
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
            //校验品牌，用户名，访问目录，三项均唯一
            $sql_check_title = "select title from brands where title='{$title}'";
            $arr_check_title = $db->getone($sql_check_title);
            $sql_check_username = "select title from brands where username='{$username}'";
            $arr_check_username = $db->getone($sql_check_username);
            $sql_check_folder = "select folder from brands where folder='{$folder}'";
            $arr_check_folder = $db->getone($sql_check_folder);
            if ($arr_check_title['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "品牌商家已经存在";
            }else if ($arr_check_username['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已存在";
            }else if ($arr_check_folder['folder']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "访问目录已存在";
            }else{
                //根目录创建个文件夹
                $dir = iconv("UTF-8", "GBK",preg_replace('# #','',"../../brands/".$folder));
                if (!file_exists($dir)){
                    mkdir ($dir,0777,true);
                }

                //复制文件
                $sc_filepath = "../../brands/";
                $to_filepath = $dir;
                copy("../../brands/template1.zip", $to_filepath."/template1.zip");
                $zip = new ZipArchive;
                $res = $zip->open($to_filepath."/template1.zip");
                unlink($to_filepath."/template1.zip");

                if ($res === TRUE) { 
                    $zip->extractTo($to_filepath); 
                    $zip->close();
                    unset($insertSql);
                    $insertSql['title']      = $title;
                    $insertSql['label']      = $label;
                    $insertSql['username']   = $username;
                    $insertSql['folder']     = $folder;
                    $insertSql['password']   = md5($pwd1);
                    $insertSql['logo']       = $image;
                    $insertSql['status']     = $status;
                    $insertSql['add_time']   = time();
                    $insertSql['add_userid'] = $userid;
                    $newsid = $db->insert($insertSql,"brands");
                    if ($newsid) {
                        //创建一个包含品牌商家ID的文件
                        $myfile = fopen($to_filepath."/brand_id.php", "w") or die("Unable to open file!");
                        $txt = '<?php $brand_id='.$newsid.' ?>';
                        fwrite($myfile, $txt);
                        fclose($myfile);

                        $arr_result['code'] = "success";
                        $arr_result['info'] = "添加完成";
                    }else{
                        $arr_result['code'] = "error";
                        $arr_result['info'] = "数据操作错误，请联系管理员";
                    }
                } else { 
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "创建目录失败";
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
    function modify($id,$title,$label,$pwd1,$pwd2,$image,$status,$userid){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户信息丢失";
        }else if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写品牌名称";
        }else if (!$label) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择品牌属性";
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
            $sql_check = "select title from brands where title = '{$title}' and id!='{$id}'";
            $arr_check = $db->getone($sql_check);
            if ($arr_check['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "品牌名称已经存在";
            }else{
                unset($insertSql);
                $insertSql['title']      = $title;
                $insertSql['label']      = $label;
                if ($pwd1 != "********") {
                    $insertSql['password']      = md5($pwd1);
                }
                // $insertSql['logo']       = $image;
                $insertSql['status']     = $status;
                $insertSql['mdy_time']   = time();
                $insertSql['mdy_userid'] = $userid;

                $return = $db->update($insertSql,"brands"," id='{$id}'");
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
            }
        }
        return $arr_result;
    }

    /**
     * 修改商家基本信息
     *
     * @param  [type] $id [description]
     * @param  [type] $image [description]
     * @param  [type] $content [description]
     * @param  [type] $userid [description]
     *
     * @return [type] [description]
     */
    function modify_brand($id,$banner,$cover,$logo,$content,$userid){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "用户信息丢失";
        }else if (!$banner) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传Banner";
        }else if (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写简介";
        }else{
            global $db;
            unset($insertSql);
            $insertSql['content'] = $content;
            $insertSql['banner']  = $banner;
            $insertSql['cover']   = $cover;
            $insertSql['logo']    = $logo;
            
            $insertSql['mdy_time']   = time();
            $insertSql['mdy_userid'] = $userid;

            $return = $db->update($insertSql,"brands"," id='{$id}'");
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
            $return = $db->update($setsql,"brands","id='{$id}'");
        }
        return $status;
    }

    /**
     * 添加一张图片
     *
     * @param [type] $title [description]
     * @param [type] $image [description]
     * @param [type] $status [description]
     */
    function add_image($title,$image,$status,$brand_id){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写图片标题";
        }elseif (!$image) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传图片";
        }else{
            global $db;
            unset($insertSql);
            $insertSql['brand_id']      = $brand_id;
            $insertSql['image']         = $image;
            $insertSql['title'] = $title;
            $return = $db->insert($insertSql,"brand_images");
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "上传完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败";
            }
        }
        return $arr_result;
    }

    /**
     * 添加一张图片
     *
     * @param [type] $title [description]
     * @param [type] $image [description]
     * @param [type] $status [description]
     */
    function modify_image($id,$title,$image,$status,$brand_id=0){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写图片标题";
        }elseif (!$image) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传图片";
        }else{
            global $db;
            if ($brand_id) {
                $sql_where = " id='{$id}' and brand_id='{$brand_id}'";
            }else $sql_where = " id='{$id}'";

            unset($setsql);
            $setsql['brand_id'] = $brand_id;
            $setsql['image']    = $image;
            $setsql['title']    = $title;
            $return = $db->update($setsql,"brand_images",$sql_where);
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "上传完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败";
            }
        }
        return $arr_result;
    }

    /**
     * 删除一张图
     *
     * @param  [type] $brand_id [description]
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function delete_image($id,$brand_id=0){
        global $db;
        if ($brand_id) {
            $sql_where =  " id='{$id}' and brand_id='{$brand_id}'";
        }else $sql_where =  " id='{$id}' ";
        $re = $db->delete("brand_images",$sql_where);
        if ($re) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "删除完成";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "删除错误，请联系管理员";
        }
        return $arr_result;
    }

    /**
     * 精美图片
     *
     * @param  [type] $brand_id [description]
     *
     * @return [type] [description]
     */
    function get_images($brand_id=0){
        global $db;
        if ($brand_id) {
            $sql_where = " where brand_id='{$brand_id}'";
        }
        $sql = "select * from brand_images ".$sql_where;
        $arr = $db->getall($sql);
        if ($arr['status']==1) {
            $arr['status_cn']=="启用中";
        }$arr['status_cn']=="停用中";
        $result['list'] = $arr;
        return $result;
    }

    /**
     * 获取一张图片
     *
     * @param  [type] $id [description]
     * @param  int $brand_id [description]
     *
     * @return [type] [description]
     */
    function get_one_image($id,$brand_id=0){
        global $db,$config;
        if ($brand_id) {
            $sql_where = " and brand_id='{$brand_id}'";
        }
        $sql = "select * from brand_images where id='{$id}' ".$sql_where;

        $arr = $db->getone($sql);
        $arr['photo_path'] = $config['dir_base_path'].$config['brand_images']."/small_".$arr['image'];
        return $arr;
    }

    /**
     * 修改浏览次数
     *
     * @return [type] [description]
     */
    function modify_pv($id,$val){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据丢失";
        }elseif (!$val) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入浏览次数";
        }else{
            global $db;
            unset($setsql);
            $setsql['pageview'] = $val;
            $return = $db->update($setsql,"brand_cases","id='{$id}'");
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
        }
        return $arr_result;
    }

    /* 修改浏览次数
     *
     * @return [type] [description]
     */
    function modify_activity_pv($id,$val){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据丢失";
        }elseif (!$val) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入浏览次数";
        }else{
            global $db;
            unset($setsql);
            $setsql['pageview'] = $val;
            $return = $db->update($setsql,"brand_activities","id='{$id}'");
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
        }
        return $arr_result;
    }

    /**
     * 获取展商信息（小站内容）
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_brandsinfo($id){
        global $db,$config;
        $sql = "select title, label,address, brands, telephone, cover, logo, banner, content, hotlinetime, qr_wx, qr_sina, email from brands where id='{$id}'";
        $arr = $db->getone($sql);
        if ($arr['cover']) {
            $arr['cover_path'] = $config['dir_base_path'].$config['brand']."/".$arr['cover'];
        }
        if ($arr['logo']) {
            $arr['logo_path'] = $config['dir_base_path'].$config['brand_logo']."/".$arr['logo'];
        }
        if ($arr['banner']) {
            $arr['banner_path'] = $config['dir_base_path'].$config['brand_banner']."/".$arr['banner'];
        }
        if ($arr['qr_wx']) {
            $arr['qr_wx_path'] = $config['dir_base_path'].$config['brand']."/".$arr['qr_wx'];
        }
        if ($arr['qr_sina']) {
            $arr['qr_sina_path'] = $config['dir_base_path'].$config['brand']."/".$arr['qr_sina'];
        }
        return $arr;
    }
}