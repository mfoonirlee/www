var Config = {
    getUrl: function(reqName){
        if(this.isDebug){
            return this.scheme['dev'] + this.url[reqName];
        }else{
            return this.scheme['prd'] + this.url[reqName];
        }
    },
    scheme: {
        'dev': 'http://' + location.host,
        'prd': ''
    },
    url: {
        //首页幻灯片服务
        index: '/plus/list.php?tid=25',
        //体验活动列表
        explist: '/plus/index.php?tid=26',
        //体验活动详情
        expdetial: '/plus/view_data.php?aid={aid}',
        explist2: '/plus/index.php?tid=26&atype={atype}',
        expsignup: '/plus/signup.php?aid={aid}&activedate={activedate}&number={number}&mobile={mobile}&name={name}',
        //免费分享列表
        sharelist: '/plus/index.php?tid=27',
        //免费分享详情
        sharedetail: '/plus/view_data.php?aid={aid}',
        //活动报名url
        enrollActivity: '/plus/signup.php?aid={aid}',
        //评论
        comment: '/plus/comment.php?aid={aid}'
    },
    getPageURL: function(pageName){
        if(this.isDebug){
            return this.scheme['dev'] + this.pageurl[pageName];
        }else{
            return this.scheme['prd'] + this.pageurl[pageName];
        }
    },
    //页面相对的地址
    pageurl:{
        //体验活动详情
        expdetial: '/plus/view.php',
        //免费分享详情
        sharedetail: '/plus/view.php'
    },
    log: function (msg) {
        try{
            console && console.log && console.log(msg);
        }catch(e){}
    },
    isDebug: true
}