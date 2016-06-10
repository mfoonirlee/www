$(function(){
    /*
    * @description: 体验活动详情逻辑
    * @auther: mf...
    */
    var aid = Util.getQueryParam('aid') || '133';

    $.ajax({
        type: 'GET',
        url: Util.replaceUrlParam(Config.getUrl('expdetial'), aid),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            renderHTML(data);
        },
        error: function () {

        }
    });

    function renderHTML(data) {
        // if (data.stime.length == 10) {
        //     data.stime *= 1000;
        // }
        // if (data.etime.length == 10) {
        //     data.etime *= 1000;
        // }
        // data.timestr = Util.parseTimeStr(data.stime, 'yÄêmÔÂ') + '-' + Util.parseTimeStr(data.etime, 'yÄêmÔÂ');
        data.timestr = data.stime + '-' + data.etime;
        var htmlFun = _.template($('#j_tpl').html());
        $('#j_container').html(htmlFun({data: data}));
        
        $('#signup').on('click', function(e){
            var $target = $(e.target || e.srcElement);
            var aid = $target.data('id');
            var img = $target.data('img');
            var title = encodeURIComponent( $target.data('title') );
            
            location.href = "../activities/signup.html?tid=32&aid="+aid+"&img="+img+"&title="+title;
        });
        
        $('#back').on('click',function(e){
            window.history.back();
        });
    }

}());