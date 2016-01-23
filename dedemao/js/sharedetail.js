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
            $('.b').click(function(){
                var text = $('textarea').val();
                $.ajax({
                    type: 'POST',
                    url: Util.replaceUrlParam(Config.getUrl('comment'), aid),
                    contentType: 'application/json;charset=utf-8',
                    dataType: 'json',
                    data: {info: text},
                    success: function (data) {
                        Config.log(data);
                        alert(data.msg);
                        setTimeout(function(){
                            location.reload();
                        }, 3000);
                    },
                    error: function () {
                        alert('submit failed, please try again.');
                    }
                });
            });
        },
        error: function () {
            alert('submit failed, please try again.');
        }
    });

    function renderHTML(data) {
        var htmlFun = _.template($('#j_tpl').html());
        $('#j_container').html(htmlFun({data: data}));
    }

}());