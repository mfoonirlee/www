$(function () {
    var Util = {
        getQueryParam: function (key) {
            var param = {},
                search = window.location.search;
                search = search && search.replace('?', '').split('&') || [];
            for (var i = 0, length = search.length; i < length; i++) {
                var q = search[i].split('=');
                param[q[0]] = q[1];
            }
            return key ? (param[key] || '') : param;
        },
        replaceUrlParam: function(str, val){
            if ( str ) {
                for (var i=1;i<arguments.length;i++)
                {
                    str = str.replace(/{\w+}/, arguments[i]);
                }
            }
            
            //return str && str.replace(/{\w+}/, val);
            return str;
        },
        parseTimeStr: function(timeStr, form){
            var d = new Date(timeStr),
                dateObj = {
                            year: d.getFullYear(),
                            month: (d.getMonth() + 1),
                            date: d.getDate(),
                            hour: d.getHours(),
                            min: d.getMinutes()
                        };
            form = form.replace(/y/, dateObj.year);
            form = form.replace(/m/, dateObj.month);
            form = form.replace(/d/, dateObj.date);
            form = form.replace(/H/, dateObj.hour);
            form = form.replace(/i/, dateObj.min);
            return form;
        }
    };

    window.Util = Util;
}());