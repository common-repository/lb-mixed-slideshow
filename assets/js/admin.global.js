jQuery(document).ready(function($){
	var chks = $(".chk-row");
	$.each(chks, function(){
		$(this)
		.click(function(){
			var chkAll = true;
			for(var i=0, n = chks.length; i < n; i++){
				if(!chks[i].checked){chkAll = false;break;}
			};
			$(".chk-all").attr("checked", chkAll);
		});
	});
	$.each($(".chk-all"), function(){
		$(this)
		.click(function(){
			chks.attr("checked", this.checked);
		});
	});
	$(".remove").click(function(){
		var href = $(this).attr("href");
		var id = [];
		$.each(chks, function(){
			if(this.checked) id.push(this.value);
		});
		if(id.length){
			window.location = href+"&id="+id.join(",");
		}else{
			alert("Please select a least 1 item");
		}
		return false;
	});
});

jQuery(document).ready(function($){
		
	$('.colorSelector').ColorPicker({
		color: '#'+$('#config_border_color').val(),
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#config_border_color').val(hex);
			$('.colorSelector span').css({backgroundColor: '#'+hex});
		}
	}).find("span").css({backgroundColor: '#'+$('#config_border_color').val()});		
});
function moverow(id, ordering, type){
	location.href = location.href+'&noheader=true&act='+type+'&id='+id+'&ordering='+ordering;
}