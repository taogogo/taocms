function $tao(obj){
   return (typeof obj == "object")?obj:document.getElementById(obj);;
}
function SetCookie(name,value){
	var minute = 1000; //保存天数
	var exp = new Date();
	exp.setTime(exp.getTime() + minute*60*1000*60*24);
	document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name){//取cookies函数
	var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
	if(arr != null) return unescape(arr[2]); return null;
}
function delCookie(name)//删除cookie
{
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval=getCookie(name);
	if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}
function SelectItemByValue(objSelect,objItemText)
{    
    //判断是否存在
    for(var i=0;i<objSelect.options.length;i++)
    {//alert(objItemText.);
        if(objSelect.options[i].value == objItemText)
        {
			
            objSelect.options[i].selected = true;
            break;
        }
    } 
}
function SelectRadio(ids,value) {
	//alert(value);
		swit=(value==1)?true:false;
		ids.checked= swit;

}
var swit=true;
function VerifyRadio() {
	for (i=0;i<document.getElementsByName("id[]").length;i++) {
		document.getElementsByName("id[]")[i].checked= swit;
	}
	swit=swit==true?false:true;

}
