var div = document.createElement("div");

                div.className = "mode popup_in";

                div.innerHTML =  "<p class='pic'><a href='[field:arcurl/]'><img src=[field:litpic/] style='height:"+"auto"+";'/></a></p><font><span class='clearfix news-title'><a href='[field:arcurl/]'>[field:title/]</a><font class='fr'>[field:pubdate function="MyDate('Y-m-d',@me)"/]</font></span></font><font><a href='[field:arcurl/]' class='news-content'>[field:description function='cn_substr(@me,200)'/]</a></font>";

                div.style.top = 0;

                div.style.left = 0;

                document.getElementById("wrapp").appendChild(div);