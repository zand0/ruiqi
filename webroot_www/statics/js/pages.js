/**
 * 分页插件
 */

    /*
    *   定义分页构造函数
    *   currentPage {number} 当前页码
    *   totalPage {number}  总页码
    *   wrapId {string} 分页元素id
    *   showPageCount {number} 显示当前页前后几页
    *   callback {function}回调
    * */
    
    function getPage(page) {
        document.location.href = document.location.pathname+'?page='+page;
    }
    
    function postPage(page) {
        document.form.page.value = page;
        document.form.submit();
    }

    function PageNav(params){
        params = params || {};
        this.currentPage = params.currentPage || 1;
        this.totalPage = params.totalPage || 1;
        this.wrapId = params.wrapId || '';
        this.showPageCount = params.showPageCount || 2;
        this.callback = params.callback || getPage;
        this.init();
    }

    //初始化
    PageNav.prototype.init = function(){
        var _this = this;
        var strHtml = '';
        var currentPage = this.currentPage;
        var totalPage = this.totalPage;
        var wrapId = this.wrapId;
        var showPageCount = 2; //显示当前页临近的几页

        if(arguments.length==1){
            currentPage = parseInt(arguments[0]);
        }
        //当前页码小于1，置为1
        if(currentPage < 1){
            currentPage = 1;
        }
        if(currentPage > totalPage){
            currentPage = totalPage;
        }
        //总页码小于1，置为1
        if(totalPage < 1){
            totalPage = 1;
        }
        //加载信息条
        strHtml += createInfos(currentPage,totalPage);
        //加载首页按钮
        strHtml += createFirstPage();
        //创建上一页按钮
        strHtml += createPrevBtn(currentPage);
        //创建页码
        strHtml += createPageNum(currentPage,totalPage,showPageCount);
        //加载下一页按钮
        strHtml += createNextBtn(currentPage);
        //创建最后一页按钮
        strHtml += createLastPage(totalPage);
        //创建Go按钮
        strHtml += createGoBtn(currentPage);

        $(wrapId).html(strHtml);
        //当前页如果是第一页，禁用上一页按钮
        if(currentPage == 1){
            $('.prevPage',$(wrapId)).addClass('disable');
            $('.first-page',$(wrapId)).addClass('disable');
        }else{
            $('.prevPage',$(wrapId)).removeClass('disable');
            $('.first-page',$(wrapId)).removeClass('disable');
        }
        if(currentPage == totalPage){
            $('.nextPage',$(wrapId)).addClass('disable');
            $('.last-page',$(wrapId)).addClass('disable');
        }else{
            $('.nextPage',$(wrapId)).removeClass('disable');
            $('.last-page',$(wrapId)).removeClass('disable');
        }

        //跳转到第一页
        $('.first-page',$(wrapId)).off('click').on('click',function(){
            if($(this).hasClass('disable')) return;
            var pageNumber = parseInt($(this).data('p'));
            _this.init(pageNumber);
            _this.callback && _this.callback(pageNumber);
        });

        //跳转到最后一页
        $('.last-page',$(wrapId)).off('click').on('click',function(){
            if($(this).hasClass('disable')) return;
            var pageNumber = parseInt($(this).data('p'));
            _this.init(pageNumber);
            _this.callback && _this.callback(pageNumber);
        });

        //上一页事件
        $('.prevPage',$(wrapId)).off('click').on('click',function(){
            if($(this).hasClass('disable')) return;
            var pageNumber = parseInt($(this).data('p')) -1;
            if(pageNumber >= 1){
                _this.init(pageNumber);
                _this.callback && _this.callback(pageNumber);
            }
        });
        //下一页事件
        $('.nextPage',$(wrapId)).off('click').on('click',function(){
            if($(this).hasClass('disable')) return;
            var pageNumber = parseInt($(this).data('p')) +1;
            if(pageNumber <= totalPage){
                _this.init(pageNumber);
                _this.callback && _this.callback(pageNumber);
            }
        });
        //页码切换
        $('a.page-num',$(wrapId)).off('click').on('click',function(){
            var pageNumber = parseInt($(this).data('p'));
            _this.init(pageNumber);
            _this.callback && _this.callback(pageNumber);
        });

        //页码跳转
        $('.goPageBtn',$(wrapId)).off('click').on('click',function(){
            var pageNumber = Number($(this).prev('span').find('input').val());
            if(!isNaN(pageNumber)){
                if(pageNumber<1){
                    _this.init(1);
                    _this.callback && _this.callback(pageNumber);
                }else if(pageNumber>totalPage){
                    _this.init(totalPage);
                    _this.callback && _this.callback(pageNumber);
                }else{
                    _this.init(pageNumber);
                    _this.callback && _this.callback(pageNumber);
                }
            }else{
                alert('请输入数字');
            }

        });
    };

    //创建信息条（<span>共200个职位分10页显示</span>）
    function createInfos(curPage,totalPage){
        var str = '<span>第'+curPage+'页/共'+totalPage+'页</span>';
        return str;
    }
    //创建首页按钮
    function createFirstPage(pager){
        var str = '<a href="javascript:;" data-p="1" class="first-page page-icon" title="跳转到第一页"></a>';
        return str;
    }
    //创建上一页按钮
    function createPrevBtn(curPage){
        var str = '<a href="javascript:;" data-p="'+curPage+'" class="prevPage">上一页</a>';
        return str;
    }
    //创建页码
    function createPageNum(curPage,totalPage,showPageCount){
        var str = '';
        //当显示页数小于总页数
        if( totalPage < showPageCount){
            showPageCount = totalPage;
        }

        var leftStart = 1,
            rightEnd = totalPage;

        if(curPage - showPageCount >= 1){   //左边可以显示完全
            leftStart = curPage - showPageCount;
            if(curPage + showPageCount <= totalPage){ //右边可以显示完全
                rightEnd = curPage + showPageCount;
            }else{  //右边不能显示完全
                rightEnd = totalPage;
            }
        }else{  //左边显示不完全
            leftStart = 1;
            if(curPage + showPageCount <= totalPage){ //右边可以显示完全
                rightEnd = curPage + showPageCount;
                var dis = 2*showPageCount - (rightEnd-leftStart); //距离显示个数缺少数
                if(dis>0 && rightEnd+dis<=totalPage){
                    rightEnd = rightEnd+dis;
                }else{
                    rightEnd = totalPage;
                }
            }else{  //右边不能显示完全
                rightEnd = totalPage;
            }
        }
        for(var i=leftStart;i<=rightEnd;i++){
            if( i==curPage ){
                str+='<a href="javascript:;" data-p="'+i+'" class="page-num on">'+i+'</a>';
            }else{
                str+='<a href="javascript:;" data-p="'+i+'" class="page-num">'+i+'</a>';
            }
        }

        return str;
    }
    //创建下一页按钮
    function createNextBtn(curPage){
        var str = '<a href="javascript:;" data-p="'+curPage+'" class="nextPage">下一页</a>';
        return str;
    }
    //创建尾页按钮
    function createLastPage(totalPage){
        var str = '<a href="javascript:;" data-p="'+totalPage+'" class="last-page page-icon" title="跳转到最后一页"></a>';
        return str;
    }
    //创建Go按钮
    function createGoBtn(curPage){
        var str = '<span class="goPageNum">跳转到<input type="text" class="goPageInput" value="'+curPage+'" class="text-small"/>页</span>\
                    <a href="javascript:;" class="goPageBtn">确定</a>';
        return str;
    }


    window.PageNav =PageNav;
