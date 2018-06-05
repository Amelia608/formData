<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-01-21
 * @Link    : http://www.xiruiad.com
 */

class exhibition {

    function __construct(){

    }

    /**
     * 获取一条展会
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function getone_exhibition($id){
        global $db,$config;
        $sql = "select * from exhibition where id='{$id}'";
        $arr = $db->getone($sql);
        $arr['photo_path'] = $config['dir_base_path'].$config['exhibition']."/small_".$arr['cover'];
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
    function get_exhibition_page($keywords,$sort,$page){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        if ($sort=="by_title") {
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按展会名称排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by title desc,id desc ";
            $sort_cn  = "按展会名称排序";
        }

        //获得分页
        $per_page = $config['num_per_page'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total     = $db->getResultRows("select id from exhibition ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,title,status,addtime from exhibition ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']     = $arr_str[$i]['id'];
            $info[$i]['title']  = $arr_str[$i]['title'];
            $info[$i]['addtime'] = date("Y-m-d H:i:s",$arr_str[$i]['addtime']);
            $info[$i]['status'] = $arr_str[$i]['status'];
            if($arr_str[$i]['status']==1) $info[$i]['status_cn']  = "当届";
            else $info[$i]['status_cn']    = "历史";
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
     * @param [type] $cover [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     */
    function add_exhibition($title,$cover,$content,$status,$userid){
    	if (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写展会名称";
    	}elseif (!$cover) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传焦点图片";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写展会介绍";
    	}else{
    		global $db;
    		unset($setsql);
			$setsql['title']     = $title;
			$setsql['cover']     = $cover;
			$setsql['content']   = $content;
			$setsql['status']    = $status;
			$setsql['addtime']   = time();
			$setsql['adduserid'] = $userid;
            $newid = $db->insert($setsql,"exhibition");
            if ($newid) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
                if ($status=="1") {
                    $sql = "update exhibition set status=0 where id!='{$newid}'";
                    $res = $db->query($sql);
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
    	}
    	return $arr_result;
    }

    /**
     * 修改展会信息
     *
     * @param  [type] $id [description]
     * @param  [type] $title [description]
     * @param  [type] $cover [description]
     * @param  [type] $content [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
   	function modify_exhibition($id,$title,$cover,$content,$status,$userid){
    	if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
    	}elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写展会名称";
    	}elseif (!$cover) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传焦点图片";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写展会介绍";
        }else{
            global $db;
    		unset($setsql);
			$setsql['title']        = $title;
			$setsql['cover']        = $cover;
			$setsql['content']      = $content;
			$setsql['status']       = $status;
			$setsql['modifytime']   = time();
			$setsql['modifyuserid'] = $userid;
            $datareturn = $db->update($setsql,"exhibition","id='{$id}'");
            if ($datareturn=="0") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "无内容修改";
            }elseif ($datareturn=="1") {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
                if ($status=="1") {
                    $sql = "update exhibition set status=0 where id!='{$id}'";
                    $res = $db->query($sql);
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败，请联系管理员";
            }
        }
        return $arr_result;
   	}

   	/**
   	 * 修改状态（展会）
   	 *
   	 * @param  [type] $id [description]
   	 * @param  [type] $status [description]
   	 *
   	 * @return [type] [description]
   	 */
   	function change_status_exhibition($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            if ($status=="0") {
                //检查下是否有设定为应届展会，没有的话提示
                $sql_check = "select title from exhibition where status=1 and id!='{$id}' limit 1";
                $res_check = $db->getone($sql_check);
                if (!$res_check['title']) {
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "必须设定一个当届展会";
                    return $arr_result;
                    exit;
                }
            }
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,"exhibition","id='{$id}'");
            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "修改完成";
                if ($status=="1") {
                    $sql = "update exhibition set status=0 where id!='{$id}'";
                    $res = $db->query($sql);
                }
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
    function delete_exhibition($ids){
        global $db;
        if ($ids) {
            $sqls = array("delete from exhibition where id in ($ids)","delete from exhibition_guide where cateid in ($ids)");
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
     * 获取未添加的展会指南
     *
     * @return [type] [description]
     */
    function get_all_guide_type($cateid,$mtitle){
        if ($cateid) {
            global $db;
            if ($mtitle) {
                $sql_where = " and title!='{$mtitle}'";
            }
            $sql = "SELECT title from exhibition_guide_category where exhibition_guide_category.title not IN(select title from exhibition_guide where cateid='{$cateid}'{$sql_where} )";
            $arr = $db->getall($sql);

            $arr_result['code'] = "success";
            $arr_result['info'] = "获取成功";
            $arr_result['list'] = $arr;
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "展会信息丢失";
        }
        return $arr_result;
    }

    /**
     * 获取指南 无需翻页
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_guide_all($cateid){
        global $db;
        if ($cateid) {
            $sql_str = "select id,title,status from exhibition_guide where cateid='{$cateid}' order by id asc";
            $arr_str = $db->getall($sql_str);

            for ($i=0;$i<(count($arr_str));$i++){
                $info[$i]['id']     = $arr_str[$i]['id'];
                $info[$i]['title']  = $arr_str[$i]['title'];
                $info[$i]['status'] = $arr_str[$i]['status'];
                if($arr_str[$i]['status']==1) $info[$i]['status_cn']  = "启用中";
                else $info[$i]['status_cn']    = "停用中";
            }
            $arr_result['code'] = "success";
            $arr_result['info'] = "获取成功";
            $arr_result['list'] = $info;
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "展会信息丢失";
        }
        return $arr_result;
    }

    /**
     * 添加一条指南信息
     *
     * @param [type] $title [description]
     * @param [type] $cover [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     * @param [type] $userid [description]
     */
    function add_guide($cateid,$title,$content){
        if (!$cateid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "展会信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择类型";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写内容";
        }else{
            global $db;
            //检查属性是否已经存在
            $sql_check = "select title from exhibition_guide where title = '{$title}' limit 1";
            $arr_check = $db->getone($sql_check);
            if (!$arr_check['title']) {
                unset($setsql);
                $setsql['cateid']  = $cateid;
                $setsql['title']   = $title;
                $setsql['content'] = $content;
                $newid = $db->insert($setsql,"exhibition_guide");
                if ($newid) {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "添加完成";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败，请联系管理员";
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "指南已存在，如有必要，请删除后再行添加。";
            }
        }
        return $arr_result;
    }

    /**
     * 修改一条指南信息
     *
     * @param [type] $title [description]
     * @param [type] $cover [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     * @param [type] $userid [description]
     */
    function modify_guide($id,$title,$content){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }elseif (!$title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择类型";
        }elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写内容";
        }else{
            global $db;
            //检查属性是否已经存在
            $sql_check = "select title from exhibition_guide where title = '{$title}' and id!='{$id}' limit 1";
            $arr_check = $db->getone($sql_check);
            if (!$arr_check['title']) {
                unset($setsql);
                $setsql['title']   = $title;
                $setsql['content'] = $content;
                $return = $db->update($setsql,"exhibition_guide","id='{$id}'");
                if ($return) {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "修改完成";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败，请联系管理员";
                }
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "指南已存在，如有必要，请删除后再行添加。";
            }
        }
        return $arr_result;
    }

    /**
     * 修改指南状态
     *
     * @param  [type] $id [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function change_status_guide($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,"exhibition_guide","id='{$id}'");
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
     * 删除指南
     *
     * @param  [type] $ids [description]
     *
     * @return [type] [description]
     */
    function delete_guide($ids){
        global $db;
        if ($ids) {
            $sqls = array("delete from exhibition_guide where id in ($ids)");
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

    function get_one_guide($id){
        global $db;
        $sql = "select * from exhibition_guide where id='{$id}'";
        $arr = $db->getone($sql);
        return $arr;
    }
    /**
     * 获取所有的历史记录
     * [get_all_exhibition description]
     * @param  string $sql_where [description]
     * @return [type]            [description]
     */
    function get_exhibition_list($sql_where=''){
        global $db,$config;
        $sql="select  id,title,cover,content from exhibition $sql_where";
        $arr=$db->getall($sql);
        for ($i=0; $i < count($arr) ; $i++) { 
            $result[$i]['id']        = $arr[$i]['id'];
            $result[$i]['title']     = $arr[$i]['title'];
            $result[$i]['cover']     = $config['dir_base_path'].$config['exhibition']."/small_".$arr[$i]['cover'];
            $result[$i]['cover_big'] = $config['dir_base_path'].$config['exhibition']."/".$arr[$i]['cover'];
            $result[$i]['content']   = $arr[$i]['content'];
        }
        return $result;
    }

    function exhibition_guide($sort){
        global $db,$config;
        $sql_exhibition="select id from exhibition where status=1 limit 1";
        $guide = $db->getone($sql_exhibition);
        $current_exhibition_id = $guide['id'];
        $sql = "select id, title,content from exhibition_guide where cateid ='{$current_exhibition_id}' $sort";
        $arr = $db->getall($sql);
        return $arr;
    }
}