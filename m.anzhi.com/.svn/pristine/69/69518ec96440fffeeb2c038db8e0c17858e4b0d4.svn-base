// JavaScript Document

$(function(){  
  
var imglist =document.getElementsByTagName("img");  
//安卓4.0+等高版本不支持window.screen.width，安卓2.3.3系统支持  
/* 
var _width = window.screen.width; 
var _height = window.screen.height - 20; 
 
var _width = document.body.clientWidth; 
var _height = document.body.clientHeight - 20; 
*/  
var _width,   
    _height;  
doDraw();  
  
window.onresize = function(){  
    doDraw();  
}  
  
function doDraw(){  
    _width = window.innerWidth;  
    _height = window.innerHeight - 20;  
    for( var i = 0, len = imglist.length; i < len; i++){  
        DrawImage(imglist[i],_width,_height);  
    }  
}  
  
function DrawImage(ImgD,_width,_height){   
    var image=new Image();   
    image.src=ImgD.src;   
    image.onload = function(){  
        if(image.width>30 && image.height>30){   
       
            if(image.width/image.height>= _width/_height){   
                if(image.width>_width){  
                    ImgD.width=_width;   
                    ImgD.height=(image.height*_width)/image.width;   
                }else{   
                    ImgD.width=image.width;   
                    ImgD.height=image.height;   
                }   
            }else{   
                if(image.height>_height){  
                    ImgD.height=_height;   
                    ImgD.width=(image.width*_height)/image.height;   
                }else{   
                    ImgD.width=image.width;   
                    ImgD.height=image.height;   
                }   
            }  
        }     
    }  
  
}  
     
})  