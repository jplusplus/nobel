$(document).ready(function() {
	console.log("HEJ");
	/* inject CSS */
	var css = document.createElement("style");
	document.getElementsByTagName("head")[0].appendChild(css);
	var cssCode = "¤CSS";
	if (css.styleSheet) {
	    // IE
	    css.styleSheet.cssText += cssCode;
	} else {
	    // Other browsers
	    css.innerHTML += cssCode;
	}
});