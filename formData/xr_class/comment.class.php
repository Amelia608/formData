<?php
/**
 * @Authors : Hardy (hardy@xiruiad.com)
 * @Date	: 2018-01-30
 * @Link    : http://www.xiruiad.com
 */

class comment {
    function __construct($clumn){
        switch ($clumn) {
            case '新闻':
                $this->table_info       = "news";
                $this->table_comment    = "news_comment";
                $this->table_praises    = "news_comment_praises";
                $this->table_collection = "news_comment_collection";
                break;
            case '品牌新闻':
                $this->table_info       = "brand_news";
                $this->table_comment    = "brand_news_comment";
                $this->table_praises    = "brand_news_comment_praises";
                $this->table_collection = "brand_news_comment_collection";
                break;

            case '品牌案例':
                $this->table_info       = "brand_cases";
                $this->table_comment    = "brand_cases_comment";
                $this->table_praises    = "brand_cases_comment_praises";
                $this->table_collection = "brand_cases_comment_collection";
                break;
            case '品牌活动':
                $this->table_info       = "brand_activities";
                $this->table_comment    = "brand_activities_comment";
                $this->table_praises    = "brand_activities_comment_praises";
                $this->table_collection = "brand_activities_comment_collection";
                break;
            case '俱乐部活动':
                $this->table_info       = "tribe_club_activities";
                $this->table_comment    = "tribe_club_activities_comment";
                $this->table_praises    = "tribe_club_activities_comment_praises";
                $this->table_collection = "tribe_club_activities_comment_collection";
                break;
            case '俱乐部案例':
                $this->table_info       = "tribe_club_cases";
                $this->table_comment    = "tribe_club_case_comment";
                $this->table_praises    = "tribe_club_case_comment_praises";
                $this->table_collection = "tribe_club_case_comment_collection";
                break;
             case '达人活动':
                $this->table_info       = "tribe_expert_activities";
                $this->table_comment    = "tribe_expert_activities_comment";
                $this->table_praises    = "tribe_expert_activities_comment_praises";
                $this->table_collection = "tribe_expert_activities_comment_collection";
                break;
             case '达人案例':
                $this->table_info       = "tribe_expert_cases";
                $this->table_comment    = "tribe_expert_case_comment";
                $this->table_praises    = "tribe_expert_case_comment_praises";
                $this->table_collection = "tribe_expert_case_comment_collection";
                break;
             case '门店活动':
                $this->table_info       = "tribe_store_activities";
                $this->table_comment    = "tribe_store_activities_comment";
                $this->table_praises    = "tribe_store_activities_comment_praises";
                $this->table_collection = "tribe_store_activities_comment_collection";
                break;
             case '门店案例':
                $this->table_info       = "tribe_store_cases";
                $this->table_comment    = "tribe_store_case_comment";
                $this->table_praises    = "tribe_store_case_comment_praises";
                $this->table_collection = "tribe_store_case_comment_collection";
        }
    }

    /**
     * 添加评论
     *
     * @param [type] $cate_id 		[所属ID]
     * @param [type] $target_id		[目标ID，可空]
     * @param [type] $addusertype	[添加人类型，默认为普通会员]
     * @param [type] $adduserid		[评论人id]
     * @param [type] $content		[评论内容]
     */
    function add_comment($cate_id,$target_id,$addusertype,$adduserid,$content){
        $strlen = (strlen($title)+mb_strlen($content,"UTF8"))/2;
        $conlen = (strlen($content)+mb_strlen($content,"UTF8"))/2;
    	if (!$cate_id) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "评论目标文章错误";
    	}elseif (!$adduserid) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请登录后评论";
    	}elseif (!$addusertype) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "基本评论人属性错误";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入评论内容";
    	}elseif (!$content) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "请输入评论内容";
    	}elseif ($conlen>350) {
            $arr_result['code'] = "error";
            $arr_result['info'] = "评论内容过长";
    	}else{
    		global $db;
    		if ($target_id) {
    			$sql = "select content from {$this->table_comment} where id='{$target_id}'";
    			$arr = $db->getone($sql);
    			$target_content = $arr['content'];
    		}
            unset($setsql);
			$setsql['cate_id']        = $cate_id;
			$setsql['target_id']      = $target_id;
			$setsql['target_content'] = $target_content;
			$setsql['addusertype']    = $addusertype;
			$setsql['adduserid']      = $adduserid;
			$setsql['content']        = $content;
			$setsql['addtime_gmt']    = time();
            $return = $db->insert($setsql,$this->table_comment);

            $sql_count = "update {$this->table_info} set reviewtimes=(select count(*) from {$this->table_comment} where cate_id='{$cate_id}') where id='{$cate_id}'";
            $result = $db->query($sql_count);

            if ($return) {
                $arr_result['code'] = "success";
                $arr_result['info'] = "评论成功";
            }else{
                $arr_result['code'] = "error";
                $arr_result['info'] = "数据操作错误，请联系管理员";
            }
    	}
    	return $arr_result;
    }

    /**
     * 获取用户信息
     *
     * @return [type] [description]
     */
    function getuserinfo($type,$userid){
        global $db;
 		switch ($type) {
			case '会员':
				$sql_userinfo = "select nickname as username,face as userface from members where id='{$userid}'";
                break;
			case '品牌商家':
				$sql_userinfo = "select title as username,logo as userface from brands where id='{$userid}'";
                break;
			case '俱乐部':
				$sql_userinfo = "select title as username,'/images/icon_list/fault-pic.png' as userface from tribe_club where id='{$userid}'";
                break;
			case '达人':
				$sql_userinfo = "select title as username,logo as userface from tribe_expert where id='{$userid}'";
                break;
			case '门店':
				$sql_userinfo = "select title as username,logo as userface from tribe_store where id='{$userid}'";
                break;
		}
		if ($sql_userinfo) {
           	$arr_userinfo = $db->getone($sql_userinfo);
		}
		if (!$arr_userinfo['username']) {
            $arr_userinfo['username'] = "游客";
            $arr_userinfo['userface'] = "/images/icon_list/fault-pic.png";
		}
		return $arr_userinfo;
    }

    /**
     * 获取翻页的评论
     *
     * @param  [type] $cate_id	[所属ID]
     *
     * @return [type] [description]
     */
    function get_comment($cate_id=0,$page,$num_per_page=5,$keywords="",$usertype,$userid){
        global $db,$config,$page_fn;

        if ($page=="" or $page<=0) $page=1;

        $sql_where = " where isdelete!=1";

        if ($keywords) {
            $sql_where .= " and (target_content like '%".$keywords."%' or content like '%".$keywords."%')";
        }

        if ($cate_id) {
            $sql_where .= " and cate_id='{$cate_id}'";
        }

        $total = $db->getResultRows("select id from {$this->table_comment}".$sql_where );

        //翻页信息
        $page_info = $page_fn->get_page_info($total,$num_per_page,$page);

        //如果传输的页码大于总页码
        if ($page-$page_info['total_page']>0) {
           $page = $page_info['total_page'];
        }

        //获得数据
        $cur_pos=($page-1)*$num_per_page;

        $sql_str = "select id,cate_id,target_content,target_id,addusertype,adduserid,content,praises,addtime_gmt,isdelete from {$this->table_comment} ".$sql_where." order by addtime_gmt desc limit ".$cur_pos.",".$num_per_page;
        $arr_str = $db->getall($sql_str);

        for ($i=0;$i<(count($arr_str));$i++){
            $info[$i]['id']             = $arr_str[$i]['id'];
            $info[$i]['target_content'] = $arr_str[$i]['target_content'];

            $sql_target = "select addusertype,adduserid,addtime_gmt from {$this->table_comment} where id='{$arr_str[$i]['id']}'";
            $arr_target = $db->getone($sql_target);
            $target_userinfo = self::getuserinfo($arr_target['addusertype'],$arr_target['adduserid']);
            $info[$i]['target_username'] = $target_userinfo['username'];
            $info[$i]['target_addtime']  = date("Y-m-d H:i:s",$arr_target['addtime_gmt']);

			$userinfo = self::getuserinfo($arr_str[$i]['addusertype'],$arr_str[$i]['adduserid']);
            $info[$i]['username'] = $userinfo['username'];
            $info[$i]['userface'] = $userinfo['userface'];

            $info[$i]['c_id'] = $arr_str[$i]['cate_id'];

            $sql_info = "select title from {$this->table_info} where id='{$arr_str[$i]['cate_id']}'";
            $arr_info = $db->getone($sql_info);
            $info[$i]['c_title'] = $arr_info['title'];

            $info[$i]['praises'] = $arr_str[$i]['praises'];
            $sql_ped = "select usertype from {$this->table_praises} where comment_id='{$arr_str[$i]['id']}' and usertype='{$usertype}' and userid='{$userid}' limit 1";
            $arr_ped = $db->getone($sql_ped);
            $arr_ped['usertype']?$info[$i]['praisesed'] = "yes":$info[$i]['praisesed'] = "no";

            $info[$i]['content']  = $arr_str[$i]['content'];
			if ($arr_str[$i]['isdelete']==1) {
                $info[$i]['status_cl'] = "danger";
				$info[$i]['status_cn'] = "已删除";
			}else{
                $info[$i]['status_cl'] = "success";
                $info[$i]['status_cn'] = "正常";
            };

			$info[$i]['addtime']        = date("Y-m-d H:i:s",$arr_str[$i]['addtime_gmt']);
        }

        //构建json
        $json_data['total']     = $total;
        $json_data['page']      = $page;
        $json_data['last_page'] = $page_info['total_page'];
        $json_data['page_info'] = $page_info['page_info'];
        $json_data['list']      = $info;

        return $json_data;
    }

    /**
     * 删除评论
     *
     * @return [type] [description]
     */
    function delete_comment($id){
        global $db;
        $sql = "update news_comment set isdelete='1' where id='{$id}'";
        // $sql = "delete from news_comment where id='{$id}'";
        $result = $db->query($sql);
        if ($result) {
            $arr_result['code'] = "success";
            $arr_result['info'] = "评论已删除";
        }else{
            $arr_result['code'] = "error";
            $arr_result['info'] = "数据操作失败，请联系管理员";
        }
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
                $sql_check = "select usertype from {$this->table_praises}  where comment_id='{$comment_id}' and usertype='{$usertyper}' and userid='{$userid}' limit 1";
                $arr_check = $db->getone($sql_check);
                $arr_check['usertype']?$praised=true:$praised=false;
                $sql_p = "insert into {$this->table_praises}(comment_id, usertype, userid) values ('{$comment_id}','$usertyper','$userid')";
            }else{
                $sql_p = "delete from {$this->table_praises} where comment_id='{$comment_id}' and usertype='{$usertyper}' and userid='{$userid}'";
            }
            if ($praised) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "您已赞过";
            }else{
                $sql_count = "update {$this->table_comment} set praises=(select count(*) from {$this->table_praises} where comment_id='{$comment_id}') where id='{$comment_id}'";
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


    /**
     * 收藏
     *
     * 点一次是收藏，再点取消收藏
     *
     * @return [type] [description]
     */
    function collection($cate_id,$usertyper,$userid,$type){
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
                $sql_check = "select usertype from {$this->table_collection}  where cate_id='{$cate_id}' and usertype='{$usertyper}' and userid='{$userid}' limit 1";
                $arr_check = $db->getone($sql_check);
                $arr_check['usertype']?$praised=true:$praised=false;
                $sql_c = "insert into {$this->table_collection}(cate_id, usertype, userid) values ('{$cate_id}','$usertyper','$userid')";
            }else{
                $sql_c = "delete from {$this->table_collection} where cate_id='{$cate_id}' and usertype='{$usertyper}' and userid='{$userid}'";
            }
            if ($praised) {
                $arr_result['code'] = "error";
                $arr_result['info'] = "您已收藏";
            }else{
                $sql_count = "update {$this->table_info} set collectiontimes=(select count(*) from {$this->table_collection} where cate_id='{$cate_id}') and id='{$cate_id}'";
                $sqls = array($sql_c,$sql_count);
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