<?php
/**
 * 
 * @authors Hardy (hardy@xiruiad.com)
 * @date    2017-11-08
 * 
 */

class banner {
    
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
    	$sql = "select * from banner where id='{$id}'";
    	$arr = $db->getone($sql);
        if ($arr['logo']) {
            $arr['logo_path'] = $config['dir_base_path'].$config['banner']."/small_".$arr['logo'];
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
    function get_all_page($keywords,$sort,$page){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按名称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按名称排序";
        }

        //获得分页
        $per_page = $config['num_per_page'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from banner ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,status from banner ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
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
     * 添加一条记录
     *
     * @param [type] $title [description]
     * @param [type] $logo [description]
     * @param [type] $link [description]
     * @param [type] $target [description]
     * @param [type] $status [description]
     */
    function add($title,$logo,$link,$target,$status){
        if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写名称";
        }elseif (!$logo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传logo";
        }elseif (!$link) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写链接地址";
        }else{
            global $db;
            unset($setsql);
			$setsql['title']  = $title;
			$setsql['logo']   = $logo;
			$setsql['link']   = $link;
			$setsql['target'] = $target;
			$setsql['status'] = $status;
            $return = $db->insert($setsql,"banner");
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作错误，请联系管理员";
            }
        }
        return $arr_result;
    }

    /**
     * 修改一条记录
     *
     * @param  [type] $id [description]
     * @param  [type] $title [description]
     * @param  [type] $logo [description]
     * @param  [type] $link [description]
     * @param  [type] $target [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify($id,$title,$logo,$link,$target,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据ID丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写名称";
        }elseif (!$logo) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传logo";
        }elseif (!$link) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写链接地址";
        }else{
            global $db;
            unset($setsql);
			$setsql['title']  = $title;
			$setsql['logo']   = $logo;
			$setsql['link']   = $link;
			$setsql['target'] = $target;
			$setsql['status'] = $status;
            $return = $db->update($setsql,"banner"," id='{$id}'");

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
            $return = $db->update($setsql,"banner","id='{$id}'");
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
    function delete($ids){
        global $db;
        $return = $db->delete("banner"," id in ($ids)");
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
     * [get_all description]
     * 获取前端页面所有banner
     * @return [type] [description]
     */
    function get_all(){
        global $db,$config;
        $sql = "select title,logo,link,target from banner where status=1";
        $arr = $db->getall($sql);
        for($i=0;$i<count($arr);$i++){
            $result[$i]['title'] = $arr[$i]['title'];
            $result[$i]['logo']  = $config['dir_base_path'].$config['banner']."/".$arr[$i]['logo'];
            $result[$i]['link']  = $arr[$i]['link'];
            $result[$i]['target']=$arr[$i]['target']==0?'_blank':'_self';
        }
        return  $result;
    }
}