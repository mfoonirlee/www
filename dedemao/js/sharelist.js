$(function() {
    /*
    * @description: ?芒路?路??铆??卤铆??录颅
    * @auther: mf...
    */

    var plistTplFun = _.template($('#j_itemlist_tpl').html()),
        rlistTplFun = _.template($('#j_itemlist2_tpl').html()),
        tlistTplFun = _.template($('#j_tab_tpl').html()),
        pageObj = {};

    $.ajax({
        type: 'GET',
        url: Config.getUrl('sharelist'),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            $('#j_main').removeClass('hide');
            // data = data.concat(data).concat(data).concat(data).concat(data);
            data.rlist = _.sortBy(data.plist, function(n){return n.rank;});
            data.list = _.groupBy(data.plist, function(n){return n.type});
            data.tlist = _.map(data.tlist, function(n){
                n.num = data.list[n.type] && data.list[n.type].length;
                return n;
            });

            data.tlist.unshift({name: '全部', type: null, num: data.plist.length});
            //?ㄤ??剧ず?list
            data.cplist = data.plist;

            pageObj.type = data.tlist && data.tlist[0].type;
            pageObj.data = data;

            ajaxCallBack(data);
        },
        error: function () {

        }
    });

    function filterList(){
        if(!pageObj.type){
            pageObj.data.cplist = pageObj.data.plist;
        }else{
            pageObj.data.cplist = pageObj.data.list[pageObj.type];
        }

        $('#j_plist_container').html(plistTplFun({data: pageObj.data}));
    }

    function forwardDetail(id){
        location.href = Config.pageurl['sharedetail'] + '?aid=' + id;
    }

    function ajaxCallBack(data){
        $('#j_tlist_container').html(tlistTplFun({data: data})).children().on('click', function(e){
            var $target = $(e.currentTarget);
            if(!$target.hasClass('current')){
                $target.addClass('current').siblings().removeClass('current');
                pageObj.type = $target.data('type');
                filterList();
            }
        });
        //for detail page
        $('#j_plist_container').delegate('.j_detail', 'click', function(e){
            var $target = $(e.currentTarget).parents('li'),
                id = $target.data('id');

            forwardDetail(id);
        });
        //for book act
        $('#j_plist_container').delegate('.j_book', 'click', function(e){
            var $target = $(e.currentTarget).parents('li'),
                id = $target.data('id');

            $.ajax({
                type: 'GET',
                url: Util.replaceUrlParam(Config.getUrl('enrollActivity'), id),
                contentType: 'application/json;charset=utf-8',
                dataType: 'json',
                success: function (data) {
                    alert(data.msg);
                },
                error: function () {

                }
            });
            //todo for booking
            console.log(321);
        });

        $('#j_plist_container').html(plistTplFun({data: data}))
        $('#j_rlist_container').html(rlistTplFun({data: data})).find('span').on('click', function(e){
            var $target = $(e.currentTarget).parents('li'),
                id = $target.data('id');

            forwardDetail(id);
        });
    }

});