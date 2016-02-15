<?php

/*    

    http://xxx.com/combo?   // combo地址
    minify                  // 是否压缩 [true||false] 默认true
    type                    // 文件类型 [js||css] 默认js
    sep                     // 分隔符separator 可以指定 默认","  
    files                   // 要合并与压缩的文件列表, 没有后缀名，用分隔符分开。如：selector,dom-event,widget
    host                    // 文件存放的主机 默认为发出请求的主机地址
    dir                     // 文件存放绝对目录 如：/js/mod/20121206/

    eg:
    http://xxx.com/combo?files=a,b,c
    http://xxx.com/combo?type=css&files=a,b,c
    http://xxx.com/combo?host=xxx.com&dir=/js/&files=a,b,c
*/


    //获得文档输出MIME类型
    function get_outContent_type( $type='' ){
        if($type=='js' || $type=='css'){ 
            $arr = array(
                'js'    => 'application/x-javascript',
                'css'   => 'text/css'
            );
            return $arr[$type];
        }else{
            return 'text/plain'; 
        }
    }

    //获得合并文件后的内容
    function get_files_content_string($obj){
        $minify             = $obj['minify'];  
        $host               = $obj['host']; 
        $dir                = $obj['dir'];
        $type               = $obj['type'];
        $files              = $obj['files'];
        $sep                = $obj['sep'];//separator

        $path               = $obj['host'].$obj['dir'];
        $files_array        = explode($sep, $files);
        $file_count         = count($files_array);
        $files_content      = array();

        //压缩js
        $minify_js          = function($str){ 
            include_once 'jsmin.php';
            return JSMin::minify($str); 
        };
        //压缩css
        $minify_css         = function($str){
            include_once 'cssmin.php';
            return cssmin::minify($str); 
        };

        //访问远程，获得数据
        $func_httpGet       = function($url, $timeout=30) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_URL, $url);

            $res = curl_exec($ch);
            curl_close($ch);
            return $res;
        };

        //判断文件是否存在
        $func_file_exist    = function($fileUrl){
            if(preg_match('/^http:\/\//', $fileUrl)){
                if(ini_get('allow_url_fopen')){
                    if(@fopen($fileUrl,'r')) return true;
                }else{
                    $parseurl=parse_url($fileUrl);
                    $host=$parseurl['host'];
                    $path=$parseurl['path'];
                    $fp=fsockopen($host,80, $errno, $errstr, 10);
                    if(!$fp)return false;
                    fputs($fp,"GET {$path} HTTP/1.1 \r\nhost:{$host}\r\n\r\n");
                    if(preg_match('/HTTP\/1.1 200/',fgets($fp,1024))) return true;
                }
                return false;
            }
            return file_exists($fileUrl);
        };

        if(!$files || !$file_count){
            return '/* 文件列表参数files不存在 */';
        }

        for ($i=0; $i<$file_count; $i++){
            $file_name=$files_array[$i];

            if($func_file_exist($path.$file_name.'.'.$type) && preg_match('/js|css/', $type)){
                //join('',file($path.$file_name.'.'.$type));
                $file_content = $func_httpGet($path.$file_name.'.'.$type); 
            
                if($minify == true){
                    if($type == 'js'){
                        $files_content[] = preg_replace('/\r|\n/', "", trim($minify_js($file_content)));
                    }elseif($type == 'css'){
                        $files_content[] = trim($minify_css($file_content));
                    }
                }else{
                    $files_content[]="\n";
                    $files_content[] = '/***** Follow is the content of "'.$path.$file_name.'.'.$type.'" *****/' . "\n\n"; 
                    $files_content[] = trim($file_content)."\n\n\n";
                }
            }else{
                if($file_name){
                    $files_content[] = "\n".'/***** The file of "'.$path.$file_name.'.'.$type.'" is not exist. *****/'. "\n"; 
                }
            }
        }

        if(preg_match('/js|css/', $type)){
            $res = join('', $files_content);
            return $res;
        }else{
            return '文件类型错误';
        }
    }

    //获得URL参数, 没有取到则返回默认值
    function getUrlParam($key, $defaultValue=''){
        return isset($_GET[$key]) ? $_GET[$key]  : $defaultValue;
    }
 
    $obj['minify']  = isset($_GET['minify']) && $_GET['minify'] =='false' ? false : true; 
    $obj['host']    = 'http://'.getUrlParam('host', $_SERVER['HTTP_HOST']); 
    $obj['dir']     = getUrlParam('dir'  , '/');
    $obj['type']    = getUrlParam('type' , 'js');
    $obj['sep']     = getUrlParam('sep'  , ',');
    $obj['files']   = getUrlParam('files', '');

    header("Expires: " . date("D, j M Y H:i:s", strtotime("now + 10 years")) ." GMT");
    header('Content-Type: '.get_outContent_type( $obj['type'] ).'; charset=utf-8;');
    header("Access-Control-Allow-Origin:*");

    echo get_files_content_string( $obj );

?>
