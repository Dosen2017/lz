/*
 * 灞忓箷鍏煎
 */ 

var phoneWidth = parseInt(window.screen.width);
var phoneScale = phoneWidth/640;
var ua = navigator.userAgent;
if (/Android (\d+\.\d+)/.test(ua)){
	var version = parseFloat(RegExp.$1);
	// andriod 2.3
	if(version>2.3){
		document.write('<meta name="viewport" content="width=640, minimum-scale = '+phoneScale+', maximum-scale = '+phoneScale+', target-densitydpi=device-dpi, minimal-ui">');
		// andriod 2.3浠ヤ笂
	}else{
		document.write('<meta name="viewport" content="width=640, target-densitydpi=device-dpi, minimal-ui">');
	}
// 鍏朵粬绯荤粺
} else {
	document.write('<meta name="viewport" content="width=640, user-scalable=no">');
}  