(function() {
    /*
    * @description: 新首页逻辑
    * @auther: mf...
    */
    var currentIndex = 0;
    var currentPage = 0;
    var totalPage = -1;
    var totalData = [];
    $('#j_videocontainer').hide();


    $.ajax({
        type: 'GET',
        url: Config.getUrl('index'),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            ajaxCallBack(data);
        },
        error: function () {

        }
    });

    function ajaxCallBack(data){
        //控制容器展示
        var height = $('#j_videocontainer').show().height();
        $('#j_videocontainer').css('height', 0).animate({height: height}, 500);
        if(!data){
            return;
        }
        totalPage = Math.ceil(data.length / 4);
        totalData = data;
        turnPage();
    }
    //点击了某个item
    function onClickItem(e){
        var $target = $(e.target || e.srcElement);
        if($target[0].tagName != 'li'){
            $target = $target.parents('li');
        }
        $target.addClass('current').siblings().removeClass('current');
        currentIndex = $target.index();
        $('#j_video').html($target.data('vurl'));
    }
    //点击翻页
    function turnNext(){
        if(currentPage >= totalPage){
            return;
        }
        currentPage++;
        turnPage();

    }
    //点击上翻页
    function turnPrev(){
        if(currentPage <= 0){
            return;
        }
        currentPage--;
        turnPage();
    }
    function turnPage(){
        var rdata;
        if(totalData.length > 4){
            rdata = totalData.slice(currentPage * 4, (currentPage + 1) * 4);
        }else{
            rdata = totalData;
        }

        //提取模板进行渲染
        var template = $('#j_list_tpl').html(),
            html = _.template(template);
        $('#j_list').html(html({data: rdata.list, current: currentIndex}));
        //标志视频的默认值
        $('#j_video').html(rdata.list[0].vurl);
        currentIndex = currentPage * 4;
        //绑定事件
        $('#j_list').on('click', function(e) {
            onClickItem(e);
        });
    }

    //在调试模式下，将内置函数外露
    if(Config.isDebug){
        window.ajaxCallBack = ajaxCallBack;
        window.onClickItem = onClickItem;
    }

})();