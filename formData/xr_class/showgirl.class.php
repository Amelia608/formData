<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-01-26
 * @Link    : http://www.xiruiad.com
 */

class showgirl {

    function __construct(){

    }

    /**
     * 获取字符串长度
     *
     * @param  [type] $str [description]
     *
     * @return [type] [description]
     */
    function get_length($str){
    	if ($str) {
	    	$strlen = (strlen($str)+mb_strlen($str,"UTF8"))/2;
    	}else $strlen = 0;
    	return $strlen;
    }

    /**
     * 获取一条模特信息
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_one($id){
    	global $db,$config;
    	$sql = "select * from showgirl where id='{$id}'";
    	$arr = $db->getone($sql);
    	$arr['photo_path_small'] = $config['dir_base_path'].$config['showgirl']."/small_".$arr['cover'];
    	$arr['photo_path_big'] = $config['dir_base_path'].$config['showgirl']."/".$arr['cover'];
    	return $arr;
    }

    /**
     * 添加一条信息
     *
     * @param [type] $name [description]
     * @param [type] $block [description]
     * @param [type] $sex [description]
     * @param [type] $title [description]
     * @param [type] $constellation [description]
     * @param [type] $specialty [description]
     * @param [type] $height [description]
     * @param [type] $image_title [description]
     * @param [type] $measurements [description]
     * @param [type] $content [description]
     * @param [type] $status [description]
     */
    function add($name,$block,$sex,$title,$constellation,$specialty,$height,$image_title,$measurements,$content,$status){
    	if (!$name) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入姓名";
    	}elseif (self::get_length($name)-10>0) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "姓名不能超过10个字符长途";
    	}elseif ($sex==="") {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择性别";
    	}elseif (!$constellation) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写星座";
    	}elseif (!$specialty) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写专业特长";
    	}elseif (!$height) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写身高";
    	}elseif (!$image_title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传焦点图片";
    	}elseif (!$measurements) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写三围";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写参赛经历";
    	}else{
    		global $db;
    		unset($setsql);
			$setsql['realname']      = $name;
			$setsql['cover']         = $image_title;
			$setsql['block']         = $block;
			$setsql['sex']           = $sex;
			$setsql['constellation'] = $constellation;
			$setsql['specialty']     = $specialty;
			$setsql['height']        = $height;
			$setsql['measurements']  = $measurements;
			$setsql['title']         = $title;
			$setsql['experience']    = $content;
			$setsql['status']        = $status;

            $newid = $db->insert($setsql,"showgirl");
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
     * 修改一条信息
     *
     * @param  [type] $id [description]
     * @param  [type] $name [description]
     * @param  [type] $block [description]
     * @param  [type] $sex [description]
     * @param  [type] $title [description]
     * @param  [type] $constellation [description]
     * @param  [type] $specialty [description]
     * @param  [type] $height [description]
     * @param  [type] $image_title [description]
     * @param  [type] $measurements [description]
     * @param  [type] $content [description]
     * @param  [type] $status [description]
     *
     * @return [type] [description]
     */
    function modify($id,$name,$block,$sex,$title,$constellation,$specialty,$height,$image_title,$measurements,$content,$status){
    	if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
    	}elseif (!$name) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入姓名";
    	}elseif (self::get_length($name)-10>0) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "姓名不能超过10个字符长途";
    	}elseif ($sex==="") {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请选择性别";
    	}elseif (!$constellation) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写星座";
    	}elseif (!$specialty) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写专业特长";
    	}elseif (!$height) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写身高";
    	}elseif (!$image_title) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传焦点图片";
    	}elseif (!$measurements) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写三围";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请填写参赛经历";
    	}else{
    		global $db;
    		unset($setsql);
			$setsql['realname']      = $name;
			$setsql['cover']         = $image_title;
			$setsql['block']         = $block;
			$setsql['sex']           = $sex;
			$setsql['constellation'] = $constellation;
			$setsql['specialty']     = $specialty;
			$setsql['height']        = $height;
			$setsql['measurements']  = $measurements;
			$setsql['title']         = $title;
			$setsql['experience']    = $content;
			$setsql['status']        = $status;

            $datareturn = $db->update($setsql,"showgirl","id='{$id}'");
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

        $total     = $db->getResultRows("select id from showgirl ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_str = "select id,realname,sex,block,cover,status from showgirl ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']   = $arr_str[$i]['id'];
            $info[$i]['name'] = $arr_str[$i]['realname'];
            $info[$i]['sex']  = $arr_str[$i]['sex'];
            if ($arr_str[$i]['sex']=="1") {
            	$info[$i]['sex_cn'] = "男";
            }else{
                $info[$i]['sex_cn'] = "女";
            }
            
            $info[$i]['block']  = $arr_str[$i]['block'];
            $info[$i]['photo']   = $config['dir_base_path'].$config['showgirl']."/".$arr_str[$i]['cover'];
            
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
            $return = $db->update($setsql,"showgirl","id='{$id}'");
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
            $sqls = array("delete from showgirl where id in ($ids)","delete from showgirl_images where sg_id in ($ids)");
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
     * [add_photo description]
     *
     * @param [type] $sg_id [description]
     * @param [type] $title [description]
     * @param [type] $image [description]
     */
    function add_photo($sg_id,$title,$image){
    	if (!$sg_id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
    	}elseif (!$image) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请上传照片";
    	}else{
    		global $db;
    		unset($setsql);
			$setsql['sg_id']  = $sg_id;
			$setsql['title']  = $title;
			$setsql['images'] = $image;
            $newid = $db->insert($setsql,"showgirl_images");
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
     * 删除照片
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function delete_photo($id){
    	if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
    	}else{
            global $db;
	        $sql = "delete from showgirl_images where id='{$id}'";
	        $result = $db->query($sql);
	        if ($result) {
	            $arr_result['code'] = "success";
	            $arr_result['info'] = "照片已删除";
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
    function change_status_photo($id,$status){
        if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "信息丢失";
        }else{
            global $db;
            unset($setsql);
            $setsql['status'] = $status;
            $return = $db->update($setsql,"showgirl_images","id='{$id}'");
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
     * 获取翻页列表
     *
     * @param  [type] $type [description]
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_photo_all($sg_id,$page_per_num=0){
        global $db,$config,$page_fn;

        $sql_where=" where sg_id='{$sg_id}'";

        if ($keywords) $sql_where .= " and title like '%".$keywords."%' ";

        $sql_sort = " order by add_time desc,id desc ";
        $sort_cn  = "按添加时间排序";

        $sql_str = "select id,title,images,add_time,status from showgirl_images ".$sql_where.$sql_sort;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']        = $arr_str[$i]['id'];
            $info[$i]['title']     = $arr_str[$i]['title'];
            $info[$i]['images']    = $arr_str[$i]['images'];
            $info[$i]['add_time']  = $arr_str[$i]['add_time'];
            $info[$i]['status']  = $arr_str[$i]['status'];
            if($arr_str[$i]['status']==1) $info[$i]['status_cn']  = "启用中";
            else $info[$i]['status_cn']    = "停用中";
            $info[$i]['photopath'] = $config['dir_base_path'].$config['showgirl']."/".$arr_str[$i]['images'];
        }

        //构建json
        $json_data['code'] = "success";
        $json_data['list'] = $info;

        return $json_data;
    }

}