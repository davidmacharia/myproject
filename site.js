//this codes remove the side bar;
function valid(){
var a=document.getElementById("side").style;
a.display="none";
var b=document.getElementById("top");
b.style.left="1%";b.style.top="";
b.style.right="1%";
var c=document.getElementById("content").style;
c.left="1%";
c.right="1%";
var d=document.getElementById("img").style;
d.left="40%";
d.right="1%";
var e=document.getElementById("separator").style;
e.right="1%";
e.left="1%";
var f=document.getElementById("ecommerce").style;
f.right="69%";
f.left="2%";
var g=document.getElementById("system").style;
g.right="35%";
g.left="32%";
var h=document.getElementById("web").style;
h.right="4%";
h.left="66%";
var i=document.getElementById("website").style;
i.right="1%";
i.left="1%";
var j=document.getElementById("span").style;
j.right="4%";
j.left="8%";
var z=document.getElementById("button");
z.value="<";
}
//this codes are responsible for returning back side bar;
function validate(){
var a=document.getElementById("side").style;
a.display="";
var b=document.getElementById("top").style;
b.left="";
b.right="1%";
var c=document.getElementById("content").style;
c.left="";
c.right="35%";
var d=document.getElementById("img");
d.style.left="";
d.style.right="1%";
var e=document.getElementById("separator").style;
e.right="1%";
e.left="";
var f=document.getElementById("ecommerce").style;
f.right="";
f.left="";
var g=document.getElementById("system").style;
g.right="";
g.left="";
var h=document.getElementById("web").style;
h.right="1%";
h.left="";
var i=document.getElementById("website").style;
i.right="0";
i.left="";
var j=document.getElementById("span").style;
j.right="4%";
j.left="";
var z=document.getElementById("button");
z.value="X";
}


//This determines the day on the contact page;
function contact(){
var t=document.getElementById("top");
var d=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var y=new Date();
var x=y.getDay();
var z=y.getHours();
t.style.color="gold";
switch(x){
case 0:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[0];
break;
case 1:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[1];
break;
case 2:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[2];
break;
case 3:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[3];
break;
case 4:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[4];
break;
case 5:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[5];
break;
case 6:
t.innerHTML="DOvtech wishes you a <b>Happy </b>"+ ""+d[6];
}

}

