window.addEventListener('load',()=>{
    images=new Array;
    images[0]="url('ardhi.jpg')";
    images[1]="url('title.jpg')";
    images[2]="url('kimemia.jpg')";
    images[3]="url('logo.jpg')";
    images[4]="url('land.jpg')";

    var g=document.getElementById("details").style;
    
    var b=document.getElementById("bottom").style;

    var x=0;
     var img=document.querySelector("img").style;
     img.position="absolute";
    setInterval(()=>{
    img.left="40%";
    },2000);
    setInterval(()=>{
       if(x<4){
        x+="";

       
        g.backgroundRepeat="no-repeat";
        g.backgroundSize="cover";
        g.backgroundPosition="top";
        
        g.backgroundImage=images[x++];
        }
        else if(x==4){
            x=0;
        }
    },3000);
}
);
