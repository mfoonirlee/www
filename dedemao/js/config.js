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
        index: '/plus/list.php?tid=25'
    },
    log: function (msg) {
        try{
            console && console.log && console.log(msg);
        }catch(e){}
    },
    isDebug: true
}