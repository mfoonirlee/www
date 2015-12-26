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
        if (data.stime.length == 10) {
            data.stime *= 1000;
        }
        if (data.etime.length == 10) {
            data.etime *= 1000;
        }
        data.timestr = Util.parseTimeStr(data.stime, 'yÄêmÔÂ') + '-' + Util.parseTimeStr(data.etime, 'yÄêmÔÂ');
        var htmlFun = _.template($('#j_tpl').html());
        $('#j_container').html(htmlFun({data: data}));
    }

}());