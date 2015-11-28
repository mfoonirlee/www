var Config = {
    url: {
        index: ''
    },
    log: function (msg) {
        try{
            console && console.log && console.log(msg);
        }catch(e){}
    },
    isDebug: true
}