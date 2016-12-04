/**
 * Created by yangweichao on 2015/7/28.
 * 图片上传,仅支持图片上传
 */

define(['jquery',
    'webuploader',], function($,WebUploader) {
    var funcs = {};

    //上传图片
    funcs.upload = function($parent,uploadSelector,serverUrl,labelText){
        var _this = this;
        var targetNode = $parent.find(uploadSelector);
        labelText = labelText || '点击选择图片';
        var percentages=[];
        // 缩略图大小
        var thumbnailWidth = 236;
        var thumbnailHeight = 236;

        var uploader = WebUploader.create({
            pick: {
                id: targetNode,
                label:labelText,
                multiple: false
            },
            auto: true,                //自动调用上传
            method :'POST',
            formData:{

            },
            accept:{                    //指定接受哪些类型的文件
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            },
            swf:'js/vendors/webUploader/Uploader.swf',  //swf地址
            server:serverUrl,         //后台处理接口地址
            //runtimeOrder: 'flash', //上传方式
            thumb:{
                width: thumbnailWidth,
                height: thumbnailHeight,
                // 图片质量，只有type为`image/jpeg`的时候才有效。
                quality: 70,
                // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                allowMagnify: false,
                // 是否允许裁剪。
                crop: false,
                // 为空的话则保留原有图片格式。
                // 否则强制转换成指定的类型。
                type: ''
            },
            sendAsBinary: false,          //是否以二进制传输
            fileSingleSizeLimit: 10 * 1024 * 1024   //单个文件大小限制（单位字节 4M）
        });

        // 判断浏览器是否支持图片的base64
        isSupportBase64 = ( function() {
            var data = new Image();
            var support = true;
            data.onload = data.onerror = function() {
                if( this.width != 1 || this.height != 1 ) {
                    support = false;
                }
            };
            data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
            return support;
        } )();

        //文件加入队列后 回调方法
        uploader.onFileQueued = function( file ) {
            if ( file.getStatus() === 'invalid' ) {

            } else {
                percentages[ file.id ] = [ file.size, 0 ];
            }

            //生成缩略图
            uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    return;
                }
                var img = new Image();
                img.src=src;
                img.onload = function(){
                    var r1 = 169/169;
                    var r2 = this.width/this.height;
                    if(r2>=r1){
                        var _s_width = 169*r2;
                        var _s_height = 169;
                        img.style.marginLeft  = (169-_s_width)/2 +'px';
                    }else{
                        var _s_width = 169;
                        var _s_height = 169/r2;
                        img.style.marginTop  = (169-_s_height)/2 +'px';
                    }
                    img.width = _s_width;
                    img.height = _s_height;
                    $parent.find('.imgReview>img').remove();
                    $parent.find('.imgReview').prepend(img);
                    $parent.find('.imgReview').show();
                };
            }, thumbnailWidth, thumbnailHeight );
        };
        //上传过程中触发，携带上传进度。
        uploader.onUploadProgress = function( file, percentage ) {
            percentages[ file.id ][ 1 ] = percentage;
            //updateTotalProgress(file);
        };
        //当文件上传成功时触发
        uploader.onUploadSuccess = function( file, response  ) {
            if(response.code == 200){
                uploader.destroy();
                funcs.upload($parent,'.uploadVBtn',serverUrl,'重新上传');
                $parent.find('input[type=hidden]').val(response.data);

                //成功提示
                var _dg = dialog({
                    content: '上传成功！',
                    padding: '10px'
                }).showModal();
                setTimeout(function(){
                    _dg.close().remove();
                },1000);
            }else{
                //成功提示
                var _dg = dialog({
                    content: '上传失败！',
                    padding: '10px'
                }).showModal();
                setTimeout(function(){
                    _dg.close().remove();
                },1000);
            }
        };
    };

    return funcs;

});