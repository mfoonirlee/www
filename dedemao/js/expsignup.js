$(function(){
    /*
    * @description: 体验活动列表逻辑
    * @auther: mf...
    */
    var hotTplFun = _.template($('#j_act_tpl').html()),
        CONST_WIDTH = 371 + 24 + 2,
        deltax = 0,
        //显示元素的总长度
        totalWidth = 0,
        CONST_CONTAINER_WIDTH = 1200 - 12;

    /*$.ajax({
        type: 'GET',
        url: Util.replaceUrlParam(Config.getUrl('explist2'),atype),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            // data = data.concat(data).concat(data).concat(data).concat(data);
            ajaxCallBack(data);
        },
        error: function () {

        }
    });*/
    var act = { };
    act.aid = Util.getQueryParam('aid');
    act.img = Util.getQueryParam('img');
    act.title = decodeURIComponent( Util.getQueryParam('title') );
    
    $('#j_act_container').html(hotTplFun({data: act})).find(".detail").on('click', function(e){
                    var $target = $(e.target || e.srcElement);
        if($target[0].tagName.toLowerCase() != 'li'){
            $target = $target.parents('li');
        }
        onClickItem($target.data('id'));
    });
    
    var getnum = function(){
      return parseInt( $('#num').text() ) || 1;
    }
    var setnum = function(num){
        $('#num').text(num);
    }
    
    $('#addnum').on('click', function(e){
        setnum( getnum() + 1 );
    });
    
    $('#subnum').on('click', function(e){
        setnum( (getnum() - 1) || 1 );
    });
    
    $('#back').on('click',function(e){
        window.history.back();
    });
    
    $('#signup').on('click', function(e){
        var activedate = $('#activedate').val();
        var number = getnum();
        var mobile = $('#mobile').val();
        var name = $('#name').val();
        var aid = $('#aid').val();
        
        if( '' == activedate ){
            alert("请填写活动日期");
            $('#activedate').focus();
            return;
        }
        else if( '' == mobile ){
            alert("请填写手机号码");
            $('#mobile').focus();
            return;
        }
        else if( '' == name ){
            alert("请填写姓名");
            $('#name').focus();
            return;
        }
        
        $.ajax({
            type: 'GET',
            url: Util.replaceUrlParam(Config.getUrl('expsignup'),aid,activedate,number,mobile,name),
            contentType: 'application/json;charset=utf-8',
            dataType: 'json',
            success: function (data) {
                Config.log(data);
                // data = data.concat(data).concat(data).concat(data).concat(data);
                //ajaxCallBack(data);
                if ( 0 === data.IsSuccess ){
                    alert(data.msg);
                    if ( null != data.url ) {
                        window.location.href = data.url;
                    }
                }else{
                    alert(data.msg);
                }
            },
            error: function () {

            }
        });
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

        $('#j_act_container').html(hotTplFun({data: newActivityList})).find(".detail").on('click', function(e){
                        var $target = $(e.target || e.srcElement);
            if($target[0].tagName.toLowerCase() != 'li'){
                $target = $target.parents('li');
            }
            onClickItem($target.data('id'));
        });
        $('#j_act_container').find(".signup").on('click', function(e){
                        var $target = $(e.target || e.srcElement);
            if($target[0].tagName.toLowerCase() != 'li'){
                $target = $target.parents('li');
            }
            var aid = $target.data('id');
            var img = $target.data('img');
            var title = $target.data('title');
            //var activedate;
            //var number;
            //var mobile;
            //var name;
            location.href = "/plus/list.php?tid=32&aid="+aid+"&img="+img+"&title="+title;
        });

        /*$('#j_act_container').html(actTplFun({data: data})).children().on('click', function(e){
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
        });*/
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