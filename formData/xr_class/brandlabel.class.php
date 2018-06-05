<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-08
 * 
 */

class brandlabel {
    
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
    	global $db;
    	$sql = "select title,status from brand_labels where id='{$id}'";
    	$arr = $db->getone($sql);
    	return $arr;
    }

    /**
     * 获取所有启用的集合
     *
     * @return [type] [description]
     */
    function get_all(){
        global $db;
        $sql = "select id,title from brand_labels where status=1";
        $arr = $db->getall($sql);
        return $arr;
    }

    /**
     * 获取翻页
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
        $per_page = $config['per_page_label'];
        if (!$per_page) {
           $per_page = 15;
        }
        $cur_pos=($page-1)*$per_page;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按标签名称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按标签名称排序";
        }

        $total     = $db->getResultRows("select id from brand_labels ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        $sql_str = "select id,title,status from brand_labels ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']     = $arr_str[$i]['id'];
            $info[$i]['title']  = $arr_str[$i]['title'];
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
     * 新增
     *
     * @param [type] $title [description]
     * @param [type] $status [description]
     */
    function add($title,$status){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名称";
        }elseif ($strlen>10) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签名称不能超过5个字";
        }else{
            global $db;
            /* 校验是否重复 */
            $arr_check = $db->getone("select title from brand_labels where title = '{$title}'");
            if ($arr_check['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "存在同名标签";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $newid = $db->insert($setsql,"brand_labels");
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
     * 修改
     *
     * @param  [type] $id [description]
     * @param  [type] $title [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify($id,$title,$status){
        $strlen = (strlen($title)+mb_strlen($title,"UTF8"))/2;
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写标签名称";
        }elseif ($strlen>10) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "标签名称不能超过5个字";
        }else{
            global $db;

            /* 校验是否重复 */
            $sql_check = "select title from brand_labels where id != '{$id}' and title='{$title}'";
            $arr_check = $db->getone($sql_check);
            if ($arr_check['title']) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "存在同名标签";
            }else{
                unset($setsql);
                $setsql['title']  = $title;
                $setsql['status'] = $status;
                $datareturn = $db->update($setsql,"brand_labels","id='{$id}'");
                if ($datareturn=="0") {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "无内容修改";
                }elseif ($datareturn=="1") {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "修改完成";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败";
                }
            }
        }
        return $arr_result;
    }

    /**
     * 更新状态
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
            $return = $db->update($setsql,"brand_labels","id='{$id}'");
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
}