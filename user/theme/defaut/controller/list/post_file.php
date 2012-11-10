<?php

$id = str_split($me['id_user'], 1);
$id = implode('/', $id);

$upload_dir = KROOT.'/media/ui/img/_user/'.$id.'/';
@mkdir($upload_dir, 0777, true);

$allowed_ext = array('jpg','jpeg','png','gif');
 
if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
    exit_status('Error! Wrong HTTP method!', "none");
}
 
if(array_key_exists('pic',$_FILES) && $_FILES['pic']['error'] == 0 ){
 
    $pic = $_FILES['pic'];
 
    if(!in_array(get_extension($pic['name']),$allowed_ext)){
        exit_status('Only '.implode(',',$allowed_ext).' files are allowed!', "none");
    }   
 
    //Deplace le fichier dans le dossier _user/i/d/time().ext
 
 	$newFile = $upload_dir.time().'.'.get_extension($pic['name']);
    if(move_uploaded_file($pic['tmp_name'], $newFile )){
        exit_status('File was uploaded successfuly!', $newFile);
    }
 
}
 
exit_status('Something went wrong with your upload!');
 
function exit_status($str, $filename){
    echo json_encode(array(	'status'=>$str,
							'file'	=>$filename));
    exit;
}
 
function get_extension($file_name){
    $ext = explode('.', $file_name);
    $ext = array_pop($ext);
    return strtolower($ext);
}
?>