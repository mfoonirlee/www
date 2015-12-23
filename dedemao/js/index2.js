(function() {
    /*
    * @description: 新首页逻辑
    * @auther: mf...
    */
    var deltaY = 0,
        CONST_HEIGHT = 91 + 6,
        CONST_CONATINER_HEIGHT = 436,
        totalHeight = 0,
        OFFSETY = 0;

    $('#j_videocontainer').hide();


    $.ajax({
        type: 'GET',
        url: Config.getUrl('index'),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            // data.list = data.list.concat(data.list).concat(data.list).concat(data.list).concat(data.list).concat(data.list).concat(data.list);
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
        var template = $('#j_list_tpl').html(),
            html = _.template(template);

        $('#j_list').html(html({data: data.list, current: 0})).children().on('click', function(e){
            onClickItem(e);
        })
        $('#j_up').on('click', function(){
            turnPrev();
        });
        $('#j_down').on('click', function(){
            turnNext();
        });
        //设置默认的video
        $('#j_video').html(data.list[0].vurl);

        totalHeight = data.list.length * CONST_HEIGHT;
    }
    //点击了某个item
    function onClickItem(e){
        var $target = $(e.target || e.srcElement);
        if($target[0].tagName.toLowerCase() != 'li'){
            $target = $target.parents('li');
        }
        $target.addClass('current').siblings().removeClass('current');
        $('#j_video').html($target.data('vurl'));

    }
    //点击翻页
    function turnNext(){
        if(Math.abs(deltaY) < totalHeight - CONST_CONATINER_HEIGHT){
            deltaY -= CONST_CONATINER_HEIGHT;
        }
        turnPage();

    }
    //点击上翻页
    function turnPrev(){
        if(deltaY < 0){
            deltaY += CONST_CONATINER_HEIGHT;
        }
        turnPage();
    }

    function turnPage(){
        $('#j_list').animate({top: deltaY + OFFSETY});
    }

    //在调试模式下，将内置函数外露
    if(Config.isDebug){
        window.ajaxCallBack = ajaxCallBack;
        window.onClickItem = onClickItem;
    }

})();