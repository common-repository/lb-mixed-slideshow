// JavaScript Document
/*
 * jQuery css clip animation support -- Jim Palmer
 * version 0.1.2
 * idea spawned from jquery.color.js by John Resig
 * Released under the MIT license.
 */
(function(jQuery){
	jQuery.fx.step.clip = function(fx){
		var clip=0, clipTest=0;
		try{
			
			if (fx.state == 0) { 
				/*	handle three valid possibilities 
				*	val val val val 
				*	val, val, val, val 
				*	val,val,val,val 
				*/ 
				var cRE = /rect\(([0-9]{1,})(px|em)[,\s]+([0-9]{1,})(px|em)[,\s]+([0-9]{1,})(px|em)[,\s]+([0-9]{1,})(px|em)\)/; 
				//	no longer replace commas - they may not exist and the regex compensates for them anyway 
				//grab the curent clip region of the element 
				$elem = jQuery(fx.elem); 
				//var clip = cRE.test(fx.elem.style.clip) ? fx.elem.style.clip : 'rect(0px ' + $elem.width() + 'px ' + $elem.height() + 'px 0px)'; 
				clipTest = getClip(fx.elem),
    			clip = cRE.test(clipTest) ? clipTest : 'rect(0px ' + $elem.width() + 'px ' + $elem.height() + 'px 0px)';
	
				fx.start = cRE.exec(clip.replace(/,/g, " ")); 
				//	handle the fx.end error 
				//try { 
				if(typeof fx.end == 'string')
				fx.end = cRE.exec(fx.end.replace(/,/g, '')); 
				//} catch (e) { 
				//return false; 
				//} 
			} 
				var sarr = new Array(), earr = new Array(), spos = fx.start.length, epos = fx.end.length, 
				emOffset = fx.start[ss + 1] == 'em' ? (parseInt($(fx.elem).css('fontSize')) * 1.333 * parseInt(fx.start[ss])) : 1; 
				for (var ss = 1; ss < spos; ss += 2) { sarr.push(parseInt(emOffset * fx.start[ss])); } 
				for (var es = 1; es < epos; es += 2) { earr.push(parseInt(emOffset * fx.end[es])); } 

				fx.elem.style.clip = 'rect(' + 
				parseInt((fx.pos * (earr[0] - sarr[0])) + sarr[0]) + 'px ' + 
				parseInt((fx.pos * (earr[1] - sarr[1])) + sarr[1]) + 'px ' + 
				parseInt((fx.pos * (earr[2] - sarr[2])) + sarr[2]) + 'px ' + 
				parseInt((fx.pos * (earr[3] - sarr[3])) + sarr[3]) + 'px)'; 
		}catch(ex){
				
		}
		//jQuery("#yyy").html(clip+"|"+clipTest+"|"+fx.start+"|"+fx.end);//spos+","+epos+","+fx.pos +","+ earr[0] +","+ sarr[0] +","+ sarr[0] );
		/*jQuery("#xxxxxx").html(Math.random()+',rect(' + 
			parseInt( ( fx.pos * ( earr[0] - sarr[0] ) ) + sarr[0] ) + 'px, ' + 
			parseInt( ( fx.pos * ( earr[1] - sarr[1] ) ) + sarr[1] ) + 'px, ' +
			parseInt( ( fx.pos * ( earr[2] - sarr[2] ) ) + sarr[2] ) + 'px, ' + 
			parseInt( ( fx.pos * ( earr[3] - sarr[3] ) ) + sarr[3] ) + 'px)');*/
	}
})(jQuery);

function getClip(elem) {
    var $elem = jQuery(elem),
        domEl = $elem.get(0),
        ret = $elem.css('clip');

    if (!ret && domEl.currentStyle)
    { 
        // IE refuses to return 'clip'
        // but has no problem with 'clipTop' and the likes
        // This unpleasantness reconstructs what a "good" browser would return
        ret = 'rect('
            + domEl.currentStyle.clipTop
            + ', '
            + domEl.currentStyle.clipRight
            + ', '
            + domEl.currentStyle.clipBottom
            + ', '
            + domEl.currentStyle.clipLeft
            + ')'
    }
    return ret;
}