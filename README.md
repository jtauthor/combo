

    把目录中的几个文件放到你的php环境中，你的combo就搭建完成了，非常简单吧？^_^


    使用如下：

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
