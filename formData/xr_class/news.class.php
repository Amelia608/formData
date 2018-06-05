<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-08
 * 
 */

class news {
    
    function __construct(){
        
    }

    /**
     * 获取单条记录记录
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one($id){
    	global $db,$config;
    	$sql = "select title,keywords,description,photo,content,status,label,newstype,author,pageview,add_time,location from news where id='{$id}'";
    	$arr = $db->getone($sql);

        $arr_label = $db->getone("select title from news_labels where id='{$arr['label']}'");
        $arr['label_cn'] = $arr_label['title'];
        if ($arr['photo']) {
            $arr['photo_path'] = $config['dir_base_path'].$config['news_path']."/small_".$arr['photo'];
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
    function get_all_page($keywords,$sort,$page,$page_per_num=0){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按标题排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else if ($sort=="by_addtime") {
            $sql_sort = " order by add_time desc,id desc ";
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

        $total     = $db->getResultRows("select id from news ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,photo,pageview,reviewtimes,status ,add_time ,content ,author,location from news ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['title']    = $arr_str[$i]['title'];
            $info[$i]['location'] = $arr_str[$i]['location'];
            $info[$i]['vtimes']   = $arr_str[$i]['pageview'];
            $info[$i]['rtimes']   = $arr_str[$i]['reviewtimes'];
            $info[$i]['status']   = $arr_str[$i]['status'];
            $info[$i]['time']     = $arr_str[$i]['add_time'];
            $info[$i]['content']  = $arr_str[$i]['content'];
            $info[$i]['author']   = $arr_str[$i]['author'];
            $info[$i]['photo']   = $config['dir_base_path'].$config['news_path']."/".$arr_str[$i]['photo'];
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
    function add($title,$label,$newstype,$keywords,$description,$photo,$content,$status,$author,$location){
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
            $setsql['location'] = $location;
            if ($author) {
                $setsql['author']       = $author;
            }
            $setsql['label']       = $label;
            $setsql['newstype']    = $newstype;
            $setsql['keywords']    = $keywords;
            $setsql['description'] = $description;
            $setsql['photo']       = $photo;
            $setsql['content']     = $content;
            $setsql['status']      = $status;
            $newid = $db->insert($setsql,"news");
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
    function modify($id,$title,$label,$newstype,$keywords,$description,$photo,$content,$status,$author,$location){
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

            unset($setsql);
            $setsql['title']    = $title;
            $setsql['location'] = $location;
            if ($author) {
                $setsql['author'] = $author;
            }
            $setsql['label']       = $label;
            $setsql['newstype']    = $newstype;
            $setsql['keywords']    = $keywords;
            $setsql['description'] = $description;
            $setsql['photo']       = $photo;
            $setsql['content']     = $content;
            $setsql['status']      = $status;
            $datareturn = $db->update($setsql,"news","id='{$id}'");
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
    function change_status($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,"news","id='{$id}'");
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
    function delete($ids){
        global $db;
        if ($ids) {
            $sqls = array("delete from news where id in ($ids)","delete from news_comment where newsid in ($ids)");
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
     * 获取指定条数的新闻
     * @param  [type] $num       [description]
     * @param  [type] $sql_where [description]
     * @return [type]            [description]
     */
    function get_top($num,$sort,$sql_where=""){
        global $db,$config;
        $sql = "select id,title,newstype,photo,pageview,add_time from news  " .$sql_where. "  order by  "   .$sort.   "   limit  " .$num;
        $arr = $db->getall($sql);
        for ($i=0; $i < count($arr); $i++) { 
            $result[$i]['id']       = $arr[$i]['id'];
            $result[$i]['title']    = $arr[$i]['title'];
            $result[$i]['newstype'] = $arr[$i]['newstype'];
            $result[$i]['photo']    = $config['dir_base_path'].$config['news_path']."/".$arr[$i]['photo'];
            $result[$i]['pageview'] = $arr[$i]['pageview'];
            $result[$i]['add_time'] = $arr[$i]['add_time'];
        }
        return $result;
    }

    /**
     * 获取最新新闻动态列表
     * [get_latest description]
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    function get_latest($num){
        global $db,$config;
        $sql="(select id,title,newstype from news where newstype=2 order by add_time desc limit $num) UNION (select id,title,newstype  from news where newstype=1 order by add_time desc  limit $num)  UNION (select id,title,newstype  from news where newstype=0 order by add_time desc limit $num)";
        $arr=$db->getall($sql);
        return $arr;
    }

}