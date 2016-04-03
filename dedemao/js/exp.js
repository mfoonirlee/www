$(function(){
    /*
    * @description: 体验活动列表逻辑
    * @auther: mf...
    */

    var hotTplFun = _.template($('#j_hotact_tpl').html()),
        actTplFun = _.template($('#j_act_tpl').html()),
        CONST_WIDTH = 371 + 24 + 2,
        deltax = 0,
        //显示元素的总长度
        totalWidth = 0,
        CONST_CONTAINER_WIDTH = 1200 - 12;

    $.ajax({
        type: 'GET',
        url: Config.getUrl('explist'),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            // data = data.concat(data).concat(data).concat(data).concat(data);
            ajaxCallBack(data);
        },
        error: function () {

        }
    });

    function ajaxCallBack(data){
        //控制容器展示
        if(!data){
            return;
        }

        totalWidth = CONST_WIDTH * data.length;

        var newActivityList = _.groupBy(data, function(n){return n.isnew != '0'})['true'];
        if(newActivityList){
            newActivityList = newActivityList.slice(0, 6);
        }else{
            newActivityList = [];
        }

        /*$('#j_hot_container').html(hotTplFun({data: newActivityList})).children().on('click', function(e){
                        var $target = $(e.target || e.srcElement);
            if($target[0].tagName.toLowerCase() != 'li'){
                $target = $target.parents('li');
            }
            onClickItem($target.data('id'));
        });*/

        $('#j_act_container').html(actTplFun({data: data})).children().on('click', function(e){
            var $target = $(e.target || e.srcElement);
            if($target[0].tagName.toLowerCase() != 'dd'){
                $target = $target.parents('dd');
            }
            onClickItem($target.data('id'));
        });

        $('#j_left').on('click', function(){
            if(deltax < 0){
                deltax += CONST_CONTAINER_WIDTH;
            }
            // else{
            //     deltax = 0;
            // }
            turnPage(deltax);
        });

        $('#j_right').on('click', function(){
            if(Math.abs(deltax) < totalWidth - CONST_CONTAINER_WIDTH){
                deltax -= CONST_CONTAINER_WIDTH;
            }
            // else{
            //     deltax = 0;
            // }
            turnPage(deltax);
        });
    }

    //点击了某个item
    function onClickItem (id) {
        location.href = Config.getPageURL('expdetial') + '?aid=' + id;
    }

    function turnPage () {
        $('#j_act_container').animate({left: deltax});
    }

    //在调试模式下，将内置函数外露
    if(Config.isDebug){
        window.ajaxCallBack = ajaxCallBack;
        window.onClickItem = onClickItem;
    }

}());