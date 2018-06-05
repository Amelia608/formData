<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-01-19
 * @Link    : http://www.xiruiad.com
 */

class tribe_qa {

    function __construct(){

    }

    /**
     * 添加一个问题
     *
     * @param [type] $userid [description]
     * @param [type] $question [description]
     */
    function add_question($userid,$usertype,$question){
    	if (!$userid) {
            $arr_result['code'] = "unlogin";
            $arr_result['info'] = "请登录后提问";
    	}elseif (!$question) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入问题";
    	}else{
	    	global $db;
	    	unset($setsql);
            $setsql['userid']   = $userid;
            $setsql['usertype'] = $usertype;
            $setsql['question'] = $question;
			$newid = $db->insert($setsql,"tribe_qa_questions");
            if (!$newid) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败";
            }else{
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
            }
    	}
    	return $arr_result;
    }

    /**
     * 获取列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_question_page($keywords,$page,$showfrm = ""){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        //获得数据
        $cur_pos=($page-1)*$per_page;

        if ($showfrm) {
            $sql_where = " where status!=0";
        }else $sql_where = " where 1=1";

        if ($keywords) $sql_where .= " and question like '%".$keywords."%'";

        if ($sort=="by_addtime") {
            $sql_sort = " order by addtime desc,id desc ";
            $sort_cn  = "按提问时间排序";
        }else if ($sort=="by_viewtimes") {
            $sql_sort = " order by viewtimes desc,id desc ";
            $sort_cn  = "按浏览次数排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by status desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by addtime desc,id desc ";
            $sort_cn  = "按提问时间排序";
        }

        //获得分页
        $per_page = $config['per_page_banner'];
        if (!$per_page) {
           $per_page = 15;
        }

        $total = $db->getResultRows("select id from tribe_qa_questions ".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        $sql_str = "select id,question,userid,usertype,viewtimes,answertimes,status,addtime from tribe_qa_questions ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']          = $arr_str[$i]['id'];
            $info[$i]['question']    = $arr_str[$i]['question'];

            $authorid = $arr_str[$i]['userid'];

            switch ($arr_str[$i]['usertype']) {
                case '会员':
                    $sql_user = "select realname as author from members where id='{$authorid}'";
                    break;
                
                case '俱乐部':
                    $sql_user = "select title as author from tribe_club where id='{$authorid}'";
                    break;
                
                case '品牌商家':
                    $sql_user = "select title as author from brands where id='{$authorid}'";
                    break;
                
                case '达人':
                    $sql_user = "select title as author from tribe_expert where id='{$authorid}'";
                    break;
                
                case '门店':
                    $sql_user = "select title as author from tribe_store where id='{$authorid}'";
                    break;
                
                default:
                    # code...
                    break;
            }
            $arr_user = $db->getone($sql_user);
            $arr_user['author']?$author=$arr_user['author']:$author="游客";

            $info[$i]['author']      = $author;
            $info[$i]['status']      = $arr_str[$i]['status'];
            
            $info[$i]['addtime']     = $arr_str[$i]['addtime'];
            $info[$i]['viewtimes']   = $arr_str[$i]['viewtimes'];
            $info[$i]['answertimes'] = $arr_str[$i]['answertimes'];
            if($arr_str[$i]['status']==1){
                $info[$i]['status_cl'] = "";
                $info[$i]['status_cn'] = "启用中";
            }elseif($arr_str[$i]['status']==2){
                $info[$i]['status_cl'] = "success";
                $info[$i]['status_cn'] = "已解决";
            }else{
                $info[$i]['status_cl'] = "warning";
                $info[$i]['status_cn'] = "停用中";
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
     * 获取前几条数据
     *
     * @param  [type] $num [description]
     * @param  [type] $sort [description]
     *
     * @return [type] [description]
     */
    function get_question_top($num,$sort){
    	global $db;
    	if ($sort) {
    		$sql_sort = " order by ".$sort;
    	}
    	if (!$num) {
    		$sql_limit = " limit ".$num;
    	}
    	$sql = "select id,userid,question,addtime,viewtimes,answertimes from tribe_qa_questions where status!=0 ".$sql_sort.$sql_limit;
    	$arr = $db->getall($sql);
    	return $arr;
    }

    /**
     * 获取一条数据
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_question_one($id){
    	if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "id丢失";
    	}else{
            global $db;
	    	$sql = "select * from tribe_qa_questions where id=".$id;
	    	$arr = $db->getone($sql);

            $authorid = $arr['userid'];
            switch ($arr['usertype']) {
                case '会员':
                    $sql_user = "select realname as author from members where id='{$authorid}'";
                    break;
                
                case '俱乐部':
                    $sql_user = "select title as author from tribe_club where id='{$authorid}'";
                    break;
                
                case '品牌商家':
                    $sql_user = "select title as author from brands where id='{$authorid}'";
                    break;
                
                case '达人':
                    $sql_user = "select title as author from tribe_expert where id='{$authorid}'";
                    break;
                
                case '门店':
                    $sql_user = "select title as author from tribe_store where id='{$authorid}'";
                    break;
                
                default:
                    # code...
                    break;
            }
            $arr_user = $db->getone($sql_user);
            $arr_user['author']?$author=$arr_user['author']:$author="游客";
            $arr['author'] = $author;
	    	if ($arr['id']) {
	            $arr_result['code'] = "success";
	            $arr_result['info'] = $arr;
	    	}else{
	            $arr_result['code'] = "empty";
	            $arr_result['info'] = "未获取到内容";
	    	}
    	}
    	return $arr_result;
    }

    /**
     * 删除问题及回答
     *
     * 使用事务删除
     *
     * @return [type] [description]
     */
    function delete_question($id){
        global $db;
        $sqls = array("delete from tribe_qa_questions where id='{$id}'","delete from tribe_qa_answers where q_id='{$id}'");
        $resutl = $db->transaction($sqls);
        if ($resutl=="success") {
            $arr_result['code'] = "success";
            $arr_result['info'] = "删除成功";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
        return $arr_result;
    }

    /**
     * 添加一个回答
     *
     * @param [type] $userid [description]
     * @param [type] $question [description]
     */
    function add_answer($userid,$q_id,$answer,$usertype="CAS"){
    	if (!$userid) {
            $arr_result['code'] = "unlogin";
            $arr_result['info'] = "请登录后回答";
    	}elseif (!$answer) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入回答内容";
    	}else{
	    	global $db;
	    	unset($setsql);
            $setsql['q_id']     = $q_id;
            $setsql['userid']   = $userid;
            $setsql['usertype'] = $usertype;
            $setsql['answer']   = $answer;
			$newid = $db->insert($setsql,"tribe_qa_answers");
            if (!$newid) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作失败";
            }else{
                $sql_count = "update tribe_qa_questions set answertimes=(select count(*) from tribe_qa_answers where q_id='{$q_id}') where id='{$q_id}'";
                $result = $db->query($sql_count);
                $arr_result['code'] = "success";
                $arr_result['info'] = "添加完成";
            }
    	}
    	return $arr_result;
    }

    /**
     * 获取列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_answer_page($keywords,$page,$q_id,$showmore="yes",$per_page=15){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        //获得数据
        $cur_pos=($page-1)*$per_page;

        $sql_where=" where 1=1";

        if ($keywords) $sql_where .= " and answer like '%".$keywords."%'";

        if ($sort=="by_addtime") {
            $sql_sort = " order by addtime desc,id desc ";
            $sort_cn  = "按提问时间排序";
        }else if ($sort=="by_viewtimes") {
            $sql_sort = " order by likes desc,id desc ";
            $sort_cn  = "按浏览次数排序";
        }else if ($sort=="by_status") {
            $sql_sort = " order by unlikes desc,id desc ";
            $sort_cn  = "按状态排序";
        }else{
            $sql_sort = " order by addtime desc,id desc ";
            $sort_cn  = "按提问时间排序";
        }

        $total = $db->getResultRows("select * from tribe_qa_answers ".$sql_where );
        //获得分页

        if (!$per_page) {
           $per_page = 15;
        }

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }
        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        $sql_str = "select id,answer,userid,usertype,addtime,likes,unlikes from tribe_qa_answers ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['answer_i'] = mb_substr(strip_tags($arr_str[$i]['answer']),0,10);
            $info[$i]['answer']   = $arr_str[$i]['answer'];

            $authorid = $arr_str[$i]['userid'];
            switch ($arr_str[$i]['usertype']) {
                case '会员':
                    $sql_user = "select realname as author from members where id='{$authorid}'";
                    break;
                
                case '俱乐部':
                    $sql_user = "select title as author from tribe_club where id='{$authorid}'";
                    break;
                
                case '品牌商家':
                    $sql_user = "select title as author from brands where id='{$authorid}'";
                    break;
                
                case '达人':
                    $sql_user = "select title as author from tribe_expert where id='{$authorid}'";
                    break;
                
                case '门店':
                    $sql_user = "select title as author from tribe_store where id='{$authorid}'";
                    break;
                
                default:
                    # code...
                    break;
            }
            $arr_user = $db->getone($sql_user);
            $arr_user['author']?$author=$arr_user['author']:$author="游客";

            $info[$i]['author']  = $author; 
            $info[$i]['likes']   = $arr_str[$i]['likes'];
            $info[$i]['unlikes'] = $arr_str[$i]['unlikes'];
            $info[$i]['addtime'] = $arr_str[$i]['addtime'];
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
     * 获取列表（翻页）
     *
     * @param  [type] $keywords [description]
     * @param  [type] $sort [description]
     * @param  [type] $page [description]
     *
     * @return [type] [description]
     */
    function get_answer_page_f($keywords,$page,$q_id,$showmore="yes",$per_page=15,$userid,$usertype){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        //获得数据
        $cur_pos=($page-1)*$per_page;
        if ($q_id) {
            $sql_where=" where q_id='{$q_id}' ";
        }else $sql_where=" where 1=1 ";
        

        if ($keywords) $sql_where .= " and answer like '%".$keywords."%'";

        $sql_sort = " order by addtime desc,id desc ";

        $total = $db->getResultRows("select * from tribe_qa_answers ".$sql_where );
        //获得分页

        if (!$per_page) {
           $per_page = 15;
        }

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);

        //如果传输的页码大于总页码
        if ($page - $page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }
        //翻页信息
        $page_info = $page_fn->get_page_info($total,$per_page,$page);
        
        $sql_str = "select id,answer,userid,usertype,addtime,likes,unlikes from tribe_qa_answers ".$sql_where.$sql_sort." limit ".$cur_pos.",".$per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']       = $arr_str[$i]['id'];
            $info[$i]['answer_i'] = mb_substr(strip_tags($arr_str[$i]['answer']),0,10);
            $info[$i]['answer']   = $arr_str[$i]['answer'];

            $authorid = $arr_str[$i]['userid'];
            switch ($arr_str[$i]['usertype']) {
                case '会员':
                    $sql_user = "select realname as author from members where id='{$authorid}'";
                    break;
                
                case '俱乐部':
                    $sql_user = "select title as author from tribe_club where id='{$authorid}'";
                    break;
                
                case '品牌商家':
                    $sql_user = "select title as author from brands where id='{$authorid}'";
                    break;
                
                case '达人':
                    $sql_user = "select title as author from tribe_expert where id='{$authorid}'";
                    break;
                
                case '门店':
                    $sql_user = "select title as author from tribe_store where id='{$authorid}'";
                    break;
                
                default:
                    # code...
                    break;
            }

            $arr_user = $db->getone($sql_user);
            $arr_user['author']?$author=$arr_user['author']:$author="游客";

            $info[$i]['author']  = $author; 

            $sql_ped = "select usertype from tribe_qa_answers_praises where comment_id='{$arr_str[$i]['id']}' and usertype='{$usertype}' and userid='{$userid}' limit 1";
            $arr_ped = $db->getone($sql_ped);
            $arr_ped['usertype']?$info[$i]['praisesed'] = "yes":$info[$i]['praisesed'] = "no";

            $info[$i]['likes']   = $arr_str[$i]['likes'];
            $info[$i]['unlikes'] = $arr_str[$i]['unlikes'];
            $info[$i]['addtime'] = $arr_str[$i]['addtime'];
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
     * 获取前几条数据
     *
     * @param  [type] $num [description]
     * @param  [type] $sort [description]
     *
     * @return [type] [description]
     */
    function get_answer_top($num,$sort,$q_id){
    	global $db;
    	if ($sort) {
    		$sql_sort = " order by ".$sort;
    	}
    	if (!$num) {
    		$sql_limit = " limit ".$num;
    	}
    	$sql = "select * from tribe_qa_answers where status!=0 ".$sql_sort.$sql_limit;
    	$arr = $db->getall($sql);
    	return $arr;
    }

    /**
     * 获取一条数据
     *
     * @param  [type] $id [description]
     *
     * @return [type] [description]
     */
    function get_answer_one($id){
    	if (!$id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "id丢失";
    	}else{
	    	$sql = "select * from tribe_qa_answers where id=".$id;
	    	$arr = $db->getone($sql);
	    	if ($arr['id']) {
                $sql_user = "select realname from members where id='{$arr['userid']}'";
                $arr_user = $db->getone($sql_user);
                $arr['username']    = $arr_user['realname'];
	            $arr_result['code'] = "success";
	            $arr_result['info'] = $arr;
	    	}else{
	            $arr_result['code'] = "empty";
	            $arr_result['info'] = "未获取到内容";
	    	}
    	}
    	return $arr_result;
    }

     /**
     * 删除回答
     * @return [type] [description]
     */
    function delete_answer($ids){
        global $db;
        $return = $db->delete("tribe_qa_answers"," id in ($ids)");
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
     * 点赞
     *
     * 点一次是赞，再点取消点赞
     *
     * @return [type] [description]
     */
    function praises($comment_id,$usertyper,$userid,$type){
        if (!$comment_id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "评论id丢失";
        }elseif (!$usertyper ||!$userid ){
            $arr_result['code'] = "error";
            $arr_result['info'] = "会员信息丢失";
        }else{
            global $db;
            $praised = false;
            if ($type==1) {
                $sql_check = "select usertype from tribe_qa_answers_praises where comment_id='{$comment_id}' and usertype='{$usertyper}' and userid='{$userid}' limit 1";
                $arr_check = $db->getone($sql_check);
                $arr_check['usertype']?$praised=true:$praised=false;
                $sql_p = "insert into tribe_qa_answers_praises(comment_id, usertype, userid) values ('{$comment_id}','$usertyper','$userid')";
            }else{
                $sql_p = "delete from tribe_qa_answers_praises where comment_id='{$comment_id}' and usertype='{$usertyper}' and userid='{$userid}'";
            }
            if ($praised) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "您已赞过";
            }else{
                $sql_count = "update tribe_qa_answers set likes=(select count(*) from tribe_qa_answers_praises where comment_id='{$comment_id}') where id='{$comment_id}'";
                $sqls = array($sql_p,$sql_count);
                $resutl = $db->transaction($sqls);
                if ($resutl=="success") {
                    $arr_result['code'] = "success";
                    $arr_result['info'] = "操作成功";
                }else{
                    $arr_result['code'] = "error";
                    $arr_result['info'] = "数据操作失败，请联系管理员";
                }
            }
        }
        return $arr_result;
    }
}