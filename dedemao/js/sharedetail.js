$(function(){
    /*
    * @description: 体验活动详情逻辑
    * @auther: mf...
    */
    var aid = Util.getQueryParam('aid') || '133';

    $.ajax({
        type: 'GET',
        url: Util.replaceUrlParam(Config.getUrl('sharedetail'), aid),
        contentType: 'application/json;charset=utf-8',
        dataType: 'json',
        success: function (data) {
            Config.log(data);
            data.aabs = data.aabs.replace(/<\/*\w+>/g, '');
            renderHTML(data);
        },
        error: function () {

        }
    });

    function renderHTML(data) {
        var htmlFun = _.template($('#j_tpl').html());
        $('#j_container').html(htmlFun({data: data}));
    }

}());