<?PHP
//注意上传文件不要超过php.ini中设定的文件大小
require_once(dirname(__FILE__).'/config.inc.php');

// if (!$_SESSION['usertype']) {
//    $result['errno'] = '无法保存文件';
// }else{
    $result = array();

    $file   = $_FILES['imageFile'];

    $file['name'] = strtolower($file['name']);
    $category     = $_POST2['category'];

    $size_limit_1 = 10000; //单位 Kb

    $upload_max_size = $size_limit_1*1100;
    $dir_path        = "../imgCardAndBank".DIRECTORY_SEPARATOR;

    if(!preg_match('/^image\//' , $file['type']) or !preg_match('/\.(jpe?g|gif|png)$/' , $file['name']) or getimagesize($file['tmp_name']) === FALSE) {
        $result['errno'] = '非法文件类型';
    }else if($file['size'] > $upload_max_size) {
        $result['errno'] = '超过上传文件上限，最大允许上传：'.$size_limit_1." Kb";
    }else if($file['error'] != 0 || !is_uploaded_file($file['tmp_name'])) {
        $result['errno'] = '未知错误';
    }else {
    //*********  开始上传   **************************
        //目标路径：<基本路径/自定义目录/年/月/日>
       
        $obj_path = $dir_path;
        $show_path = $dir_path;

        //获得随机文件名
        $rand_name      = date("YmdHms").str_random("numstr",4);;    //获得12位随机数
        $file_extension = get_extension($file['name']);  //获得文件后缀名

        if($file_extension) $file_name = $rand_name.".".$file_extension;
        else $file_name = $rand_name.".jpg";

        $save_path  = $obj_path.$file_name;

        $flag_upload=move_uploaded_file($file['tmp_name'] , $save_path);

        if(!$flag_upload) {
            $result['errno'] = '无法保存文件';
        }
        else {
            $result =array(
                'errno' => "success",
                'data'  => array("/data/up/imgCardAndBank/".$file_name),
                'filename' => $file_name
            );      
        }
    }
// }

$result = json_encode($result, JSON_UNESCAPED_UNICODE);
echo $result;
?>