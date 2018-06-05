<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-08
 * 
 */

class brand_cases {
    
    function __construct(){
        $this->tbl_brand   = "brands";
        $this->tbl_list    = "brand_cases";
        $this->tbl_comment = "brand_cases_comment";
    }

    /**
     * 商家信息
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function brand_info($id){
        global $db;
        $sql = "select title,folder from $this->tbl_brand where id='$id'";
        $arr = $db->getone($sql);
        return $arr;
    }

    /**
     * 获取单条记录记录
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one($id,$brand_id=0){
    	global $db,$config;
        if ($brand_id) {
            $sql_where = " and brand_id='{$brand_id}'";
        }
        $sql = "select brand_id,title,image,description,content,status,pageview,reviewtimes from $this->tbl_list where id='{$id}'".$sql_where;
        $arr = $db->getone($sql);

        $brand_info = self::brand_info($arr['brand_id']);
        $arr['brand_title'] = $brand_info['title'];

        $arr['photo_path'] = $config['dir_base_path'].$config['brand']."/small_".$arr['image'];
        if ($arr['status']=="1") {
            $arr['status_cn'] = "正常访问";
        }else $arr['status_cn'] = "草稿";

    	return $arr;
    }

    /**
     * 获取单条记录记录
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one_f($id){
        global $db,$config;
        $sql = "select id, brand_id,title,content,pageview,reviewtimes,addtime from $this->tbl_list where id='{$id}' and status=1";
        $arr = $db->getone($sql);
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
    function get_all_page($keywords,$sort,$page,$brand_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($brand_id) {
            $sql_where=" where brand_id='{$brand_id}'";
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
        $per_page = $config['num_per_page'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from ".$this->tbl_list.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,brand_id,pageview,reviewtimes,status from ".$this->tbl_list.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']     = $arr_str[$i]['id'];
            $info[$i]['title']  = $arr_str[$i]['title'];

            $brand_info = self::brand_info($arr_str[$i]['brand_id']);
            $info[$i]['brand_title']  = $brand_info['title'];

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
    function add($title,$photo,$content,$status,$brand_id=0){
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
            $setsql['brand_id'] = $brand_id;
            $setsql['image']    = $photo;
            $setsql['content']  = $content;
            $setsql['status']   = $status;
            $newid = $db->insert($setsql,$this->tbl_list);
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
    function modify($id,$title,$photo,$content,$status,$brand_id=0){
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

            if ($brand_id) {
                $sql_where = "id='{$id}' and brand_id='{$brand_id}'";
            }else $sql_where = "id='{$id}'";

            unset($setsql);
            $setsql['title']       = $title;
            $setsql['image']       = $photo;
            $setsql['content']     = $content;
            $setsql['status']      = $status;
            $datareturn = $db->update($setsql,$this->tbl_list,$sql_where);
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
    function change_status($id,$status,$brand_id=0){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            if ($brand_id) {
                $sql_where = "id='{$id}' and brand_id='{$brand_id}'";
            }else $sql_where = "id='{$id}'";
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,$this->tbl_list,$sql_where);
            if ($return) {
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
    function delete($ids,$brand_id=0){
        if ($ids) {
            global $db;
            if ($brand_id) {
                $sql_where = " and brand_id='{$brand_id}'";
            }else $sql_where = "";

            $sqls = array("delete from $this->tbl_list where id in ($ids) {$sql_where}","delete from $this->tbl_comment where cate_id in ($ids)");
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
     * 评论列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_comment_page($keywords,$sort,$page,$brand_id=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        if ($brand_id) {
            $sql_where=" where brand_id='{$brand_id}'";
        }else $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_news") {
            $sql_sort = " order by caseid desc,id desc ";
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

        $total     = $db->getResultRows("select id from $this->tbl_comment ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,comment,add_time,review,review_time from $this->tbl_comment ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
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
     * 添加一条评论
     *
     * @param [type] $caseid [description]
     * @param [type] $userid [description]
     * @param [type] $comment [description]
     */
    function add_comment($caseid,$userid,$username,$comment){
        if (!$caseid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "新闻信息丢失";
        }elseif (!$userid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请登录后评论";
        }elseif (!$comment) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写评论";
        }else{
            global $db;
            $check_news = $db->getone("select status from $this->tbl_list where id='{$caseid}'");
            if ($check_news['status']) {
                unset($setsql);
                $setsql['caseid']   = $caseid;
                $setsql['userid']   = $userid;
                $setsql['username'] = $username;
                $setsql['comment']  = $comment;
                $setsql['add_time'] = time();
                $newid = $db->insert($setsql,"$this->tbl_comment");
                if ($newid) {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "添加完成";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败，请联系管理员";
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "新闻资讯信息错误";
            }
        }
        return $arr_result;
    }

    /**
     * 管理员回复
     *
     * @param [type] $commentid [description]
     * @param [type] $userid [description]
     * @param [type] $content [description]
     */
    function add_review($commentid,$usertype,$userid,$content,$brand_id=0){
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
            if ($brand_id) {
                $sql_where = " id='{$commentid}' and brand_id='{$brand_id}'";
            }else $sql_where =" id='{$commentid}'";
            unset($setsql);
            $setsql['r_utype']     = $usertype;
            $setsql['r_uid']       = $userid;
            $setsql['review']      = $content;
            $setsql['review_time'] = time();
            $newid = $db->update($setsql,"$this->tbl_comment",$sql_where);
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
     * 删除评论
     *
     * 将评论内容更改为：评论已删除
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function delete_comment($id,$brand_id=0){
        global $db;
        if ($brand_id) {
            $sql_where = " and brand_id='{$brand_id}'";
        }
        $sql = "delete from $this->tbl_comment where id='{$id}'".$sql_where;
        $result = $db->query($sql);
        if ($newid) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "评论已删除";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
    }
}