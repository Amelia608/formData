<?php
class fn_page_info {
    function __construct() {
    }
    
    /**
     * 获取分页信息
     *
     * @param  [type] $total    [总数]
     * @param  [type] $per_page [每页条数]
     * @param  [type] $page     [当前页码]
     *
     * @return [type] [以『,』分隔的字符串]
     */
    function get_page_info($total,$per_page,$page){ 
        $n_left_max  = 3;
        $n_right_max = 3;

        if ($total%$per_page==0) $total_page=$total/$per_page;
        else $total_page=ceil($total/$per_page);
        
        if ($total_page>($n_left_max+$n_right_max+1)) $flag_more=1;
        else $flag_more=0;

        if ($flag_more==1){
            if ($page<($n_left_max)) {
                for ($i=1;$i<=$n_left_max+$n_right_max-2;$i++){
                    $tmp1[]=$i;
                }
                $tmp1[]="…";
                $tmp1[]=$total_page;
            }else{
                $tmp1[]="1";
                $tmp1[]="…";
                if ($total_page-$page<=$n_right_max){
                    for ($i=$total_page-$n_left_max-$n_right_max+2;$i<=$total_page;$i++){
                        $tmp1[]=$i;
                    }
                }else{
                    $tmp_pos=round($n_left_max+$n_right_max-4)/2;
                    for ($i=$page-$tmp_pos;$i<=$page+$tmp_pos;$i++){
                        $tmp1[]=$i;
                    }
                    $tmp1[]="…";
                    $tmp1[]=$total_page;
                }
            }
        }else{
            for ($i=1;$i<=$total_page;$i++){
                $tmp1[]=$i;
            }
        }
        
        if (!$total_page) {
           $total_page = 1;
        }

        $page_info["now_page"]   = $page;
        $page_info["total_page"] = $total_page;
        
        if ($tmp1) {
            $page_info["page_info"]  = join(",",$tmp1);
        }else $page_info["page_info"]  = "";

        return $page_info;
    }
}
?>