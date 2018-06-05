<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-08
 * 
 */

class brand_news {
    
    function __construct(){
        $this->tbl_list    = "brand_news";
        $this->tbl_comment = "brand_news_comment";
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
    	$sql = "select title,photo,content,status,pageview,reviewtimes,add_time from $this->tbl_list where id='{$id}'".$sql_where;
    	$arr = $db->getone($sql);
        if ($arr['photo']) {
            $arr['photo_path'] = $config['dir_base_path'].$config['brand_news']."/small_".$arr['photo'];
        }
        if ($brand_id==0) {
            $arr['status']?$arr['status_cn']="启用中":$arr['status_cn']="停用中";
        }
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

        $sql_str = "select id,title,pageview,reviewtimes,status from ".$this->tbl_list.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']     = $arr_str[$i]['id'];
            $info[$i]['title']  = $arr_str[$i]['title'];
            $info[$i]['vtimes'] = $arr_str[$i]['pageview'];
            $info[$i]['rtimes'] = $arr_str[$i]['reviewtimes'];
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
     * 前台新闻列表
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     * @param  int $brand_id [description]
     *
     * @return [type] [description]
     */
    function get_all_page_f($keywords,$sort,$page,$per_page,$brand_id){
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

        $total     = $db->getResultRows("select id from ".$this->tbl_list.$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,brand_id,title,photo,pageview,reviewtimes,status,content,add_time from ".$this->tbl_list.$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']      = $arr_str[$i]['id'];
            $info[$i]['title']   = $arr_str[$i]['title'];
            $info[$i]['vtimes']  = $arr_str[$i]['pageview'];
            $info[$i]['photo']   = $config['dir_base_path'].$config['brand_news']."/".$arr_str[$i]['photo'];
            $info[$i]['rtimes']  = $arr_str[$i]['reviewtimes'];
            $info[$i]['content'] = $arr_str[$i]['content'];
            $info[$i]['time']    = $arr_str[$i]['add_time'];

            $sql = "select title from brands where id='{$arr_str[$i]['brand_id']}'";
            $arr = $db->getone($sql);
            $info[$i]['author'] = $arr['title'];

            $info[$i]['status']  = $arr_str[$i]['status'];
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
            $setsql['photo']    = $photo;
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
            $setsql['photo']       = $photo;
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
     * @param [type] $newsid [description]
     * @param [type] $userid [description]
     * @param [type] $comment [description]
     */
    function add_comment($newsid,$userid,$username,$comment){
        if (!$newsid) {
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
            $check_news = $db->getone("select status from $this->tbl_list where id='{$newsid}'");
            if ($check_news['status']) {
                unset($setsql);
                $setsql['newsid']   = $newsid;
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

    function brand_news_list($num,$sort,$sql_where=''){
        global $db,$config;
        $sql = "select a.id,a.title as newstitle,b.title as brandtitle,a.content,a.photo,a.pageview,a.reviewtimes,a.likes,a.add_time,b.folder from brand_news as a left join brands as b on a.brand_id=b.id $sql_where order by $sort limit $num";
        $arr = $db->getall($sql);
        for ($i=0; $i < count($arr); $i++) { 
             $result[$i]['id']          = $arr[$i]['id'];
             $result[$i]['title']       = $arr[$i]['newstitle'];
             $result[$i]['btitle']      = $arr[$i]['brandtitle'];
             $result[$i]['cnt']         = $arr[$i]['content'];
             $result[$i]['photo']       = $config['dir_base_path'].$config['brand_news'] ."/".$arr[$i]['photo'];
             $result[$i]['pageview']    = $arr[$i]['pageview'];
             $result[$i]['reviewtimes'] = $arr[$i]['reviewtimes'];
             $result[$i]['likes']       = $arr[$i]['likes'];
             $result[$i]['path']        = $arr[$i]['folder'];
             $result[$i]['add_time']    = $arr[$i]['add_time'];
        }
        return $result;
    }

    /**
     * 获取指定条数的内容
     *
     * @param  int $topnum [description]
     * @param  string $brand_id [description]
     * @param  [type] $idsnotin [description]
     *
     * @return [type] [description]
     */
    function get_brand_news_top($topnum=5,$brand_id="",$idsnotin){
        global $db,$config;

        $sql_where = " where status = 1";
        if ($idsnotin) {
            $sql_where = " and in not in ('$idsnotin')";
        }

        $sql = "select id,title,photo,content from brand_news ".$sql_where." order by add_time desc limit ".$topnum;
        $arr = $db->getall($sql);
        if ($arr['photo']) {
            $arr['photo_path'] = $config['dir_base_path'].$config['brand']."/".$arr['photo'];
        }
        return $arr;
    }
}