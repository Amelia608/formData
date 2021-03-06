<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date    : 2018-01-18
 * @Link    : http://www.xiruiad.com
 */

class tribe_store {

    function __construct(){
        $this->tbl_base               = "tribe_store";
        $this->tbl_brand              = "tribe_store_brands";
        $this->tbl_label              = "tribe_store_labels";
        $this->tbl_case               = "tribe_store_cases";
        $this->tbl_case_comment       = "tribe_store_case_comment";
        $this->tbl_activities         = "tribe_store_activities";
        $this->tbl_activities_comment = "tribe_store_activities_comment";
        $this->tbl_login_log          = "tribe_store_login_log";
        $this->tbl_storeview          = "tribe_store_view";
    }

    /**
     * 获取一条用户信息
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one($id){
        global $db,$config;
        $sql = "select title,username,baseinfo,synopsis,status,block,brands,labels,logo,cover from {$this->tbl_base} where id='{$id}'";
        $arr_tmp = $db->getone($sql);

        $sql_block = "select title from china_block where id='{$arr_tmp['block']}'";
        $arr_block = $db->getone($sql_block);

        $arr_tmp['block_cn']   = $arr_block['title'];
        $arr_tmp['logo_path']  = $config['dir_base_path'].$config['tribe_store']."/small_".$arr_tmp['logo'];
        $arr_tmp['cover_path'] = $config['dir_base_path'].$config['tribe_store']."/small_".$arr_tmp['cover'];

        return $arr_tmp;
    }

    /**
     * 获取地区
     *
     * @param  [type] $parent [description]
     *
     * @return [type] [description]
     */
    function get_china_block($parent){
        global $db;
        $sql = "select id,title,all_path from china_block where parent='{$parent}'";
        $arr = $db->getall($sql);
        return $arr;
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

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total = $db->getResultRows("select id from ".$this->tbl_base.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and (username like '%".$keywords."%' or title like '%".$keywords."%')";

        if ($sort=="by_username") {
            $sql_sort = " order by username desc,id desc ";
            $sort_cn  = "按登录名排序";
        }else if ($sort=="by_nickname") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按门店名称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by username desc,id desc ";
            $sort_cn  = "按登录名排序";
        }

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        $sql_str = "select id,username,title,status from ".$this->tbl_base.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['username'] = $arr_str[$i]['username'];
            $info[$i]['title']    = $arr_str[$i]['title'];
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
     * 新增
     *
     * @param [type] $username [description]
     * @param [type] $nickname [description]
     * @param [type] $password1 [description]
     * @param [type] $password2 [description]
     * @param [type] $userright [description]
     * @param [type] $flag [description]
     */
    function add($title,$block,$brand,$label,$username,$password1,$password2,$baseinfo,$synopsis,$status,$logo,$cover){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入门店名称";
        }elseif (!$block) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择所在地区";
        }elseif (!$logo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传logo";
        }elseif (!$cover) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传门店封面";
        // }elseif (!$brand) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择品牌范围";
        // }elseif (!$label) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择改装范围";
        }elseif (!$username) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录名";
        }elseif (!ctype_alnum($username)) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "登录名仅可为数字和字母组合";
        }elseif (!$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录密码";
        }elseif (!$password2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入确认密码";
        }elseif ($password2!=$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次密码输入不一致";
        }elseif (!$baseinfo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写门店信息";
        }elseif (!$baseinfo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写简介";
        }else{
            global $db;
            $temp1 = $db->getone("select username from {$this->tbl_base} where username='{$username}'");
            $temp2 = $db->getone("select title from {$this->tbl_base} where title='{$title}'");
            if ($temp1['username']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已存在";
            }elseif($temp2['title']){
                $arr_result['code'] = "error";
                $arr_result['info'] = "门店名称已经存在";
            }else{
                unset($setsql);
                $setsql['title']         = $title;
                $setsql['cover']         = $cover;
                $setsql['logo']          = $logo;
                $setsql['block']         = $block;
                $setsql['brands']        = $brand;
                $setsql['labels']        = $label;
                $setsql['username']      = $username;
                $setsql['password']      = md5($password1);
                $setsql['changepwdtime'] = time();
                $setsql['baseinfo']      = $baseinfo;
                $setsql['synopsis']      = $synopsis;
                $setsql['status']        = $status;
                $setsql['addtime']       = time();
                $setsql['adduserid']     = $_SESSION['adm_userid'];
                $newid = $db->insert($setsql,$this->tbl_base);
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
    function modify($id,$title,$block,$brand,$label,$username,$password1,$password2,$synopsis,$baseinfo,$status,$logo,$cover){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$block) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择所在地区";
        }elseif (!$logo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传logo";
        }elseif (!$cover) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传门店封面";
        // }elseif (!$brand) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择品牌范围";
        // }elseif (!$label) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择改装范围";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入门店名称";
        }elseif (!$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入登录密码";
        }elseif (!$password2) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入确认密码";
        }elseif ($password2!=$password1) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "两次密码输入不一致";
        }elseif (!$synopsis) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写门店简介";
        }elseif (!$baseinfo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写门店信息";
        }else{
            global $db;
            $temp1 = $db->getone("select username from {$this->tbl_base} where username='{$username}' and id!='{$id}'");
            $temp2 = $db->getone("select title from {$this->tbl_base} where title='{$title}' and id!='{$id}'");
            if ($temp1['username']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "用户名已存在";
            }elseif($temp2['title']){
                $arr_result['code'] = "error";
                $arr_result['info'] = "门店名称已经存在";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['cover']  = $cover;
                $setsql['logo']   = $logo;
                $setsql['block']  = $block;
                $setsql['brands'] = $brand;
                $setsql['labels'] = $label;
                if ($password1 != "********") {
                    $setsql['password']      = md5($password1);
                    $setsql['changepwdtime'] = time();
                }
                $setsql['baseinfo'] = $baseinfo;
                $setsql['synopsis'] = $synopsis;
                $setsql['status']   = $status;
                $setsql['modifytime'] = time();
                $setsql['modifyuserid'] = $_SESSION['adm_userid'];
                $newid = $db->update($setsql,$this->tbl_base," id='{$id}'");
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
     * 用户更新信息
     *
     * @param [type] $id [description]
     * @param [type] $title [description]
     * @param [type] $block [description]
     * @param [type] $brand [description]
     * @param [type] $label [description]
     * @param [type] $synopsis [description]
     * @param [type] $baseinfo [description]
     * @param [type] $logo [description]
     * @param [type] $cover [description]
     */
    function add_baseinfo($id,$title,$block,$brand,$label,$synopsis,$baseinfo,$logo,$cover){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$block) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择所在地区";
        }elseif (!$logo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传logo";
        }elseif (!$cover) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传门店封面";
        // }elseif (!$brand) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择品牌范围";
        // }elseif (!$label) {
        //     $arr_result['code'] = "error";
        //     $arr_result['info'] = "请选择改装范围";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入门店名称";
        }elseif (!$synopsis) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写门店简介";
        }elseif (!$baseinfo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写门店信息";
        }else{
            global $db;
            unset($setsql);
            $setsql['title']        = $title;
            $setsql['cover']        = $cover;
            $setsql['logo']         = $logo;
            $setsql['block']        = $block;
            $setsql['brands']       = $brand;
            $setsql['labels']       = $label;
            $setsql['baseinfo']     = $baseinfo;
            $setsql['synopsis']     = $synopsis;
            $setsql['modifytime']   = time();
            $setsql['modifyuserid'] = $_SESSION['adm_userid'];
            $newid = $db->update($setsql,$this->tbl_base," id='{$id}'");
            if (!$newid) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败";
            }else{
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
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
            $before = $db->getone("select status from {$this->tbl_base} where id='{$id}'");
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_base," id='{$id}'");
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
        $return = $db->delete($this->tbl_base," id = '{$id}'");
        if ($return) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "删除完成";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
        return $arr_result;
    }

    /**
     * 获取一条
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one_case($id,$showcateinfo=1,$store_id=0){
        global $db,$config;
        if ($store_id) {
            $sql_where = " and cate_id='{$store_id}'";
        }
        $sql = "select cate_id,title,image,description,content,pageview,reviewtimes,addtime,status from {$this->tbl_case} where id='{$id}'".$sql_where;
        $arr = $db->getone($sql);

        if ($showcateinfo) {
            $arr['cateinfo'] = self::get_one($arr['cate_id']);
        }

        if ($arr['status']=="1") {
            $arr['status_cn'] = "启用中";
        }else{
            $arr['status_cn'] = "停用中";
        }

        if ($arr['image']) {
            $arr['cover_path'] = $config['dir_base_path'].$config['tribe_store']."/small_".$arr['image'];
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
    function get_case_page($keywords,$page,$store_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($store_id) {
             $sql_where = " where cate_id='{$store_id}'";
        }else $sql_where = " where 1=1";

        if ($keywords) $sql_where .= " and (title like '%".$keywords."%' or username like '%".$keywords."%')";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按姓名排序";
        }else if ($sort=="by_nickname") {
            $sql_sort = " order by username desc,id desc ";
            $sort_cn  = "按昵称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
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

        $total = $db->getResultRows("select id from ".$this->tbl_case.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;
        
        $sql_str = "select id,cate_id,title,pageview,reviewtimes,status from ".$this->tbl_case.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['cateinfo'] = self::get_one($arr_str[$i]['cate_id']);
            $info[$i]['title']    = $arr_str[$i]['title'];
            $info[$i]['pv']       = $arr_str[$i]['pageview'];
            $info[$i]['rt']       = $arr_str[$i]['reviewtimes'];
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
     * 修改案例状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function change_case_status($id,$status,$store_id=0){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }else{
            global $db;
            if ($store_id) {
                $sql_where = "id='{$id}' and cate_id='{$store_id}'";
            }else $sql_where = "id='{$id}'";

            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_case,$sql_where);
        }
        return $status;
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
            $return = $db->update($setsql,$this->tbl_case,"id='{$id}'");
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
     * 删除评论
     *
     * 将评论内容更改为：评论已删除
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function delete_comment($id,$store_id=0){
        global $db;
        // $sql = "update {$this->tbl_case_comment} set comment='评论已删除' where id='{$id}'";
        if ($store_id) {
            $sql_where = " id='{$id}' and cate_id='{$store_id}'";
        }else $sql_where = " id='{$id}'";
        
        $result = $db->delete($this->tbl_case_comment,$sql_where);
        if ($result) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "评论已删除";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
    }

    /**
     * 评论列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_comment_page($keywords,$sort,$page,$cate_id,$store_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where = " where 1=1 ";

        if ($cate_id) {
            $sql_where .= " and caseid=".$cate_id;
        }

        if ($store_id) {
            $sql_where .= " and cate_id = '".$store_id."' ";
        }

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_news") {
            $sql_sort = " order by newsid desc,id desc ";
            $sort_cn  = "按标题排序";
        }else if ($sort=="by_review_time") {
            $sql_sort = " order by review desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by add_time desc,id desc ";
            $sort_cn  = "按标题排序";
        }

        //获得分页
        $per_page = $config['num_per_page'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from ".$this->tbl_case_comment.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,comment,add_time,review,review_time from ".$this->tbl_case_comment.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['comment_s'] = mb_substr( $arr_str[$i]['comment'],0,20,'utf-8')."...";
            $info[$i]['comment']   = $arr_str[$i]['comment'];
            $info[$i]['add_time']  = date("Y-d-m H:i:s",$arr_str[$i]['add_time']);
            $info[$i]['review']    = $arr_str[$i]['review'];
            if ($info[$i]['review']) {
                $info[$i]['status_cn']   = "已回复";
                $info[$i]['status_cl']   = "success";
                $info[$i]['review_time'] = date("Y-d-m H:i:s",$arr_str[$i]['review_time']);
            }else{
                $info[$i]['status_cn']   = "未回复";
                $info[$i]['status_cl']   = "warning";
                $info[$i]['review_time'] = " -- ";
            }
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
     * 管理员回复
     *
     * @param [type] $commentid [description]
     * @param [type] $userid [description]
     * @param [type] $content [description]
     */
    function add_review($commentid,$usertype,$userid,$content,$store_id=0){
        if (!$commentid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$userid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请登录后回复";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写回复内容";
        }else{
            global $db;

            if ($store_id) {
                $sql_where = " id='{$commentid}' and cate_id='{$store_id}'";
            }else $sql_where = " id='{$commentid}'";
            unset($setsql);
            $setsql['r_utype']     = $usertype;
            $setsql['r_uid']       = $userid;
            $setsql['review']      = $content;
            $setsql['review_time'] = time();
            $newid = $db->update($setsql,$this->tbl_case_comment,$sql_where);
            if ($newid) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "回复完成";
                $arr_result['time'] = date("Y-m-d H:i:s",time());
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 获取聚会活动列表
     *
     * @param  [type] $keywords [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_activities_page($keywords,$sort,$page,$store_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($store_id) {
            $sql_where=" where cate_id='{$store_id}'";
        }else $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%'";

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total = $db->getResultRows("select id from ".$this->tbl_activities.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;
        
        $sql_str = "select id,title,cate_id,pageview,reviewtimes,status from ".$this->tbl_activities.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']    = $arr_str[$i]['id'];
            $info[$i]['title'] = $arr_str[$i]['title'];
            if ($arr_str[$i]['cate_id']) {
                $cate_info = self::get_one($arr_str[$i]['cate_id']);
            }
            if ($cate_info['title']) {
               $info[$i]['cate_title']    = $cate_info['title'];
            }
            $info[$i]['pv']    = $arr_str[$i]['pageview'];
            $info[$i]['rt']    = $arr_str[$i]['reviewtimes'];
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
     * 获取一条数据
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one_activity($id,$showcateinfo=1,$store_id=0){
        global $db,$config;
        if ($store_id) {
            $sql_where = " and cate_id='{$store_id}'";
        }
        $sql = "select cate_id,title,image,description,content,status,pageview,reviewtimes from {$this->tbl_activities} where id='{$id}'".$sql_where;
        $arr = $db->getone($sql);
        $arr['photo_path']  = $config['dir_base_path'].$config['tribe_store']."/small_".$arr['image'];
        if ($arr['cate_id'] && $showcateinfo) {
            $arr_cate = self::get_one($arr['cate_id']);
            $arr['cate_title'] = $arr_cate['title'];
        }
        if ($arr['status']=="1") {
            $arr['status_cn'] = "正常访问";
        }else $arr['status_cn'] = "草稿";
        return $arr;
    }
    /**
     * 评论列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_comment_page_activity($keywords,$sort,$page,$store_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($store_id) {
            $sql_where=" where brand_id='{$store_id}'";
        }else $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_news") {
            $sql_sort = " order by newsid desc,id desc ";
            $sort_cn  = "按标题排序";
        }else if ($sort=="by_review_time") {
            $sql_sort = " order by review desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by add_time desc,id desc ";
            $sort_cn  = "按标题排序";
        }

        //获得分页
        $per_page = $config['num_per_page'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from ".$this->tbl_activities_comment.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,comment,add_time,review,review_time from ".$this->tbl_activities_comment.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['comment_s'] = mb_substr( $arr_str[$i]['comment'],0,20,'utf-8')."...";
            $info[$i]['comment']   = $arr_str[$i]['comment'];
            $info[$i]['add_time']  = date("Y-d-m H:i:s",$arr_str[$i]['add_time']);
            $info[$i]['review']    = $arr_str[$i]['review'];
            if ($info[$i]['review']) {
                $info[$i]['status_cn']   = "已回复";
                $info[$i]['status_cl']   = "success";
                $info[$i]['review_time'] = date("Y-d-m H:i:s",$arr_str[$i]['review_time']);
            }else{
                $info[$i]['status_cn']   = "未回复";
                $info[$i]['status_cl']   = "warning";
                $info[$i]['review_time'] = " -- ";
            }
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
     * 删除评论
     *
     * 将评论内容更改为：评论已删除
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function delete_comment_activity($id,$store_id=0){
        global $db;
        // $sql = "update {$this->tbl_activities_comment} set comment='评论已删除' where id='{$id}'";
        if ($store_id) {
            $sql_where = " id='{$id}' and cate_id='{$store_id}'";
        }else $sql_where = " id='{$id}'";

        $result = $db->delete($this->tbl_activities_comment,$sql_where);
        if ($result) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "评论已删除";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
    }

    /**
     * 管理员回复
     *
     * @param [type] $commentid [description]
     * @param [type] $userid [description]
     * @param [type] $content [description]
     */
    function add_review_activity($commentid,$usertype,$userid,$content,$store_id=0){
        if (!$commentid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$userid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请登录后回复";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写回复内容";
        }else{
            global $db;
            if ($store_id) {
                $sql_where = " id='{$commentid}' and brand_id='{$store_id}'";
            }else $sql_where=" id='{$commentid}'";

            unset($setsql);
            $setsql['r_utype']     = $usertype;
            $setsql['r_uid']       = $userid;
            $setsql['review']      = $content;
            $setsql['review_time'] = time();
            $newid = $db->update($setsql,$this->tbl_activities_comment,$sql_where);
            if ($newid) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "回复完成";
                $arr_result['time'] = date("Y-m-d H:i:s",time());
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 修改浏览次数
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
            $return = $db->update($setsql,$this->tbl_activities,"id='{$id}'");
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
     * 添加品牌属性
     *
     * @param [type] $title [description]
     * @param [type] $status [description]
     */
    function add_brands($title,$status){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名";
        }else{
            global $db;
            $sql_check_title = "select * from {$this->tbl_brand} where title='{$title}' limit 1";
            $arr_check_title = $db->getone($sql_check_title);
            if ($arr_check_title['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "标签名已经存在";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $newid = $db->insert($setsql,$this->tbl_brand);
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
     * 修改品牌属性
     *
     * @param [type] $title [description]
     * @param [type] $status [description]
     */
    function modify_brands($id,$title,$status){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名";
        }else{
            global $db;
            $sql_check_title = "select * from {$this->tbl_brand} where title='{$title}' and id!={$id} limit 1";
            $arr_check_title = $db->getone($sql_check_title);
            if ($arr_check_title['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "标签名已经存在";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $newid = $db->update($setsql,$this->tbl_brand," id='{$id}'");
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
     * 品牌属性列表
     *
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function get_brands_list($status){
        global $db;
        if ($status!="") {
            $sql_where = " where status='{$status}'";
        }else $sql_where ="";
        $sql = "select * from ".$this->tbl_brand.$sql_where;
        $arr = $db->getall($sql);
        for ($i=0;$i<(count($arr));$i++){
            $info[$i]['id']     = $arr[$i]['id'];
            $info[$i]['title']  = $arr[$i]['title'];
            $info[$i]['status'] = $arr[$i]['status'];
            if($arr[$i]['status']==1) $info[$i]['status_cn']  = "启用中";
            else $info[$i]['status_cn']    = "停用中";
        }
        //构建json
        $json_data['list'] = $info;

        return $json_data;
    }

     /**
     * 更新品牌属性状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function change_brands_status($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_brand,"id='{$id}'");
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据修改错误，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 添加标签
     *
     * @param [type] $title [description]
     * @param [type] $status [description]
     */
    function add_label($title,$status){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名";
        }else{
            global $db;
            $sql_check_title = "select * from {$this->tbl_label} where title='{$title}' limit 1";
            $arr_check_title = $db->getone($sql_check_title);
            if ($arr_check_title['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "标签名已经存在";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $newid = $db->insert($setsql,$this->tbl_label);
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
     * 修改标签
     *
     * @param [type] $title [description]
     * @param [type] $status [description]
     */
    function modify_label($id,$title,$status){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名";
        }else{
            global $db;
            $sql_check_title = "select * from {$this->tbl_label} where title='{$title}' and id!={$id} limit 1";
            $arr_check_title = $db->getone($sql_check_title);
            if ($arr_check_title['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "标签名已经存在";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $newid = $db->update($setsql,$this->tbl_label," id='{$id}'");
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

    function get_labels_list($status){
        global $db;
        if ($status!="") {
            $sql_where = " where status='{$status}'";
        }else $sql_where ="";
        $sql = "select * from ".$this->tbl_label.$sql_where;
        $arr = $db->getall($sql);
        for ($i=0;$i<(count($arr));$i++){
            $info[$i]['id']     = $arr[$i]['id'];
            $info[$i]['title']  = $arr[$i]['title'];
            $info[$i]['status'] = $arr[$i]['status'];
            if($arr[$i]['status']==1) $info[$i]['status_cn']  = "启用中";
            else $info[$i]['status_cn']    = "停用中";
        }
        //构建json
        $json_data['list'] = $info;

        return $json_data;
    }

     /**
     * 更新状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function change_labels_status($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_label,"id='{$id}'");
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据修改错误，请联系管理员";
            }
        }
        return $arr_result;
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
        $sql_adm = "select errortimes from {$this->tbl_base} where username='{$username}' limit 1 ";
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
            $sql_adm = "select id,title,username,password,locktime,status from {$this->tbl_base} where username='{$username}' limit 1 ";
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
                    $_SESSION['usertype']     = "门店";
                    $_SESSION['store_userid'] = $arr_adm['id'];
                    $_SESSION['store_title']  = $arr_adm['title'];
                    $_SESSION['store_name']   = $arr_adm['username'];

                    $arr_result['code'] = "success";
                    $arr_result['info'] = "登录成功";
                    $needLog = "y";
                }
            }else{
                $sql_getNums = "select id from $this->tbl_login_log where login_ip='{$login_ip}' and login_time<'".(time()+5*60)."'";
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
        $newid = $db->insert($setsql,"$this->tbl_login_log");

        if ($result=="登录成功") {
            unset($setsql);
            $setsql['lgn_time']   = time();
            $setsql['errortimes'] = 0;
            $setsql['locktime']   = 0;
            $setsql['lgn_ip']     = $login_ip;
            $datareturn = $db->update($setsql,"{$this->tbl_base}","username='{$username}'");
        }else{
            if ($config['login_trial']) {
                //添加一条记录
                $sql_times = "update {$this->tbl_base} set errortimes = errortimes+1 where username='{$username}'";
                $result = $db->query($sql_times);
                $e_times = self::get_error_times($username);

                if ($e_times>4) {
                    unset($setsql);
                    $setsql['locktime'] = time()+24*60*60;
                    $datareturn = $db->update($setsql,"{$this->tbl_base}","username='{$username}'");
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
            $sql = "select id from {$this->tbl_base} where id='{$id}' and password='".md5($oldPwd)."'";
            $arr = $db->getone($sql);
            if ($arr['id']) {
                $new_pwd = md5($newpwd1);
                unset($setsql);
                $setsql['password']      = $new_pwd;
                $setsql['changepwdtime'] = time();
                $upreturn = $db->update($setsql,"{$this->tbl_base}"," id='{$id}'");
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
     * 添加一个案例
     *
     * @param [type] $title [description]
     * @param [type] $label [description]
     * @param [type] $newstype [description]
     * @param [type] $keywords [description]
     * @param [type] $description [description]
     * @param [type] $photo [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     */
    function add_case($title,$photo,$content,$status,$club_id=0){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标题";
        }elseif ($strlen>100) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标题不能超过100个字";
        }elseif (!$photo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传照片";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请编写内容";
        }else{
            global $db;
            unset($setsql);
            $setsql['title']   = $title;
            $setsql['cate_id'] = $club_id;
            $setsql['image']   = $photo;
            $setsql['content'] = $content;
            $setsql['status']  = $status;
            $newid = $db->insert($setsql,$this->tbl_case);
            if ($newid) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 修改
     *
     * @param  [type] $id [description]
     * @param  [type] $title [description]
     * @param  [type] $label [description]
     * @param  [type] $photo [description]
     * @param  [type] $content [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify_case($id,$title,$photo,$content,$status,$club_id=0){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标题";
        }elseif ($strlen>100) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标题不能超过100个字";
        }elseif (!$photo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传照片";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请编写内容";
        }else{
            global $db;

            if ($club_id) {
                $sql_where = "id='{$id}' and cate_id='{$club_id}'";
            }else $sql_where = "id='{$id}'";

            unset($setsql);
            $setsql['title']       = $title;
            $setsql['image']       = $photo;
            $setsql['content']     = $content;
            $setsql['status']      = $status;
            $datareturn = $db->update($setsql,$this->tbl_case,$sql_where);
            if ($datareturn=="0") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "无内容修改";
            }elseif ($datareturn=="1") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 删除记录
     *
     * @param  [type] $ids [description]
     *
     * @return [type] [description]
     */
    function delete_case($ids,$club_id=0){
        if ($ids) {
            global $db;
            if ($club_id) {
                $sql_where = " and cate_id='{$club_id}'";
            }else $sql_where = "";

            $sqls = array("delete from $this->tbl_case where id in ($ids) {$sql_where}","delete from $this->tbl_case_comment where caseid in ($ids) {$sql_where}");
            $resutl = $db->transaction($sqls);
            if ($resutl=="success") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "删除成功";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据丢失";
        }
        return $arr_result;
    }

    /**
     * 添加
     *
     * @param [type] $title [description]
     * @param [type] $label [description]
     * @param [type] $newstype [description]
     * @param [type] $keywords [description]
     * @param [type] $description [description]
     * @param [type] $photo [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     */
    function add_activity($title,$photo,$content,$status,$club_id=0){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标题";
        }elseif ($strlen>100) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标题不能超过100个字";
        }elseif (!$photo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传照片";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请编写内容";
        }else{
            global $db;
            unset($setsql);
            $setsql['title']    = $title;
            $setsql['cate_id']  = $club_id;
            $setsql['image']    = $photo;
            $setsql['content']  = $content;
            $setsql['status']   = $status;
            $newid = $db->insert($setsql,$this->tbl_activities);
            if ($newid) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 修改
     *
     * @param  [type] $id [description]
     * @param  [type] $title [description]
     * @param  [type] $label [description]
     * @param  [type] $photo [description]
     * @param  [type] $content [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify_activity($id,$title,$photo,$content,$status,$club_id=0){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标题";
        }elseif ($strlen>100) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标题不能超过100个字";
        }elseif (!$photo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传照片";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请编写内容";
        }else{
            global $db;

            if ($club_id) {
                $sql_where = "id='{$id}' and cate_id='{$club_id}'";
            }else $sql_where = "id='{$id}'";

            unset($setsql);
            $setsql['title']       = $title;
            $setsql['image']       = $photo;
            $setsql['content']     = $content;
            $setsql['status']      = $status;
            $datareturn = $db->update($setsql,$this->tbl_activities,$sql_where);
            if ($datareturn=="0") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "无内容修改";
            }elseif ($datareturn=="1") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
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
    function change_status_activity($id,$status,$club_id=0){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_activities," id='{$id}'");
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
    function delete_activity($ids,$club_id=0){
        if ($ids) {
            global $db;
            if ($club_id) {
                $sql_where1 = " and cate_id='{$club_id}'";
                $sql_where2 = " and brand_id='{$club_id}'";
            }else $sql_where = "";

            $sqls = array("delete from $this->tbl_activities where id in ($ids) {$sql_where1}","delete from $this->tbl_activities_comment where caseid in ($ids) {$sql_where2}");
            $resutl = $db->transaction($sqls);
            if ($resutl=="success") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "删除成功";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据丢失";
        }
        return $arr_result;
    }

//==================================== 门店实景(2018-2-14 小戴要求更改案例为门店实景，只显示照片，无正文) ================================
        /**
     * 修改状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function storeview_change_status($id,$status,$storeid=0){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }else{
            global $db;
            if ($storeid===0) {
                $sql_where = "id='{$id}'";
            }else{
                $sql_where = "id='{$id}' and storeid='{$storeid}'";
            }
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_storeview,$sql_where);
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据修改错误，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 添加一条记录
     *
     * @param [type] $title [description]
     * @param [type] $image [description]
     */
    function storeview_add($title,$image,$storeid=0){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入媒体名称";
        }elseif(!$image){
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传媒体Logo";
        }else{
            global $db;
            if ($storeid) {
                $sql_where = " and storeid='{$storeid}'";
            }else $sql_where="";
            $sql = "select title from {$this->tbl_storeview} where title='{$title}'{$sql_where} limit 1";
            $arr = $db->getone($sql);
            if ($arr['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "媒体已经存在";
            }else{
                unset($setsql);
                $setsql['title']   = $title;
                $setsql['image']   = $image;
                $setsql['storeid'] = $storeid;
                $newid = $db->insert($setsql,$this->tbl_storeview);
                if ($newid) {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "添加完成";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据添加错误，请联系管理员";
                }
            }
        }
        return $arr_result;
    }

    /**
     * 删除一条记录
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function storeview_delete($id,$storeid=0){
        global $db;
        if ($id) {
            if ($storeid) {
                $sql_where = " and storeid='{$storeid}'";
            }else $sql_where="";
            $sqls = array("delete from {$this->tbl_storeview} where id ='{$id}'{$sql_where}");
            $resutl = $db->transaction($sqls);
            if ($resutl=="success") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "删除成功";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据丢失";
        }
        return $arr_result;
    }

    /**
     * 获取指定条数
     *
     * @param  [type] $limit [description]
     *
     * @return [type] [description]
     */
    function storeview_get_limit($limit=0){
        global $db;
        if ($limit) {
            $sql_limit = " limit ".$limit;
        }
        $sql = "select id,title,image from {$this->tbl_storeview} where status=1 ".$sql_limit;
        $arr = $db->getall($sql);
        $arr['logo_path'] = $config['dir_base_path'].$config['tribe_store']."/".$arr['image'];
        return $arr;
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
    function storeview_get_all_page($keywords,$sort,$page,$storeid,$page_per_num=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($storeid) {
            $sql_where = " where storeid='{$storeid}'";
        }else $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按标题排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按标题排序";
        }

        //获得分页
        if (!$page_per_num) {
            $per_page = $config['num_per_page'];
        }else $per_page = $page_per_num;

        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from {$this->tbl_storeview} ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,image,status,storeid from {$this->tbl_storeview} ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['title']     = $arr_str[$i]['title'];
            $info[$i]['image']     = $arr_str[$i]['image'];
            $info[$i]['imagepath'] = $config['dir_base_path'].$config['tribe_store']."/".$arr_str[$i]['image'];
            if ($storeid===0) {
                $sql_storeinfo = "select title from {$this->tbl_base} where id='{$arr_str[$i]['storeid']}'";
                $arr_storeinfo = $db->getone($sql_storeinfo);
                $info[$i]['storetitle']     = $arr_storeinfo['title'];
            }
            $info[$i]['status']    = $arr_str[$i]['status'];
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

}