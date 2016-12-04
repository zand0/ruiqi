define(['umeditor','vendors/umeditor/umeditor.config','vendors/umeditor/zh_cn'],function (UM){
	var ret={};
	//实例化编辑器
	ret.editorFn=function (id){
		var um = UM.getEditor(id);
	    um.addListener('blur',function(){
	        $('#focush2').html('编辑器失去焦点了');
	    });
	    um.addListener('focus',function(){
	        $('#focush2').html('');
	    });
		//对编辑器的操作最好在编辑器ready之后再做
		// um.ready(function(){
		// 	   um.setContent('hello');
		//     //获取html内容，返回: <p>hello</p>
		//     var html = um.getContent();
		//     //获取纯文本内容，返回: hello
		//     var txt = um.getContentTxt();
		// });
	};
    return ret;
});
