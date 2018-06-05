<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-02-05
 * @Link    : http://www.xiruiad.com
 */

class media {

    function __construct(){

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
            $return = $db->update($setsql,"corporative_media","id='{$id}'");
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
    function add($title,$image){
    	if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入媒体名称";
    	}elseif(!$image){
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传媒体Logo";
    	}else{
    		global $db;
    		$sql = "select title from corporative_media where title='{$title}' limit 1";
    		$arr = $db->getone($sql);
    		if ($arr['title']) {
	            $arr_result['code'] = "error";
	            $arr_result['info'] = "媒体已经存在";
    		}else{
                unset($setsql);
                $setsql['title'] = $title;
                $setsql['image'] = $image;
                $newid = $db->insert($setsql,"corporative_media");
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
    function delete($id){
        global $db;
        if ($id) {
            $sqls = array("delete from corporative_media where id ='{$id}'");
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
    function get_limit($limit=0){
    	global $db;
    	if ($limit) {
    		$sql_limit = " limit ".$limit;
    	}
    	$sql = "select id,title,image from corporative_media where status=1 ".$sql_limit;
    	$arr = $db->getall($sql);
    	$arr['logo_path'] = $config['dir_base_path'].$config['media']."/".$arr['image'];
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

        $total     = $db->getResultRows("select id from corporative_media ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,image,status from corporative_media ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['title']     = $arr_str[$i]['title'];
            $info[$i]['image']     = $arr_str[$i]['image'];
            $info[$i]['imagepath'] = $config['dir_base_path'].$config['media']."/".$arr_str[$i]['image'];
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