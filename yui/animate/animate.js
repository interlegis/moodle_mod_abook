YUI.add('moodle-mod_abook-animate', function(Y) {
  M.mod_abook = M.mod_abook || {};
  M.mod_abook.animate = {
    init: function(slidetype) {
    	var lastrequest = '#';
    	adjustnavbar();
    	function adjustnavbar() {
//    		$("#prevbutton, #nextbutton").each(function() {
//    			var url = $(this).attr("href");
//    			if (url.indexOf('?') > -1) {
//    				url = url + '&';
//    			} else {
//    				url = url + '?';
//    			}
//    			$(this).attr("href", url+'json=1')
//    		});
    		
			$("#prevbutton, #nextbutton").click(function() {
				var url = $(this).attr("href");
				lastrequest = url;
				$.get(url, {'json': 1}, showslide).fail(fail);
				return false;
			});	    	
    	}
    	
    	function fail() {
    		if (lastrequest != '#') {
    			// Try redirect instead ajax
    			window.location.href = lastrequest;
    		} else {
    			$("#wallpaper").html("<strong>ERROR: An error occurred while trying to get data. Contact site administrator.</strong>");
    			$("#prevbutton, #nextbutton").off('click');
    		}
    		return false;
    	}
    	
    	function showslide(data, status) {
    		if (status != "success") {
    			fail();
    			return;
    		}
    		
    		if ($.type(data) == "string") {
    			data = $.parseJSON(data);
    		}
    		
    		if (data.slidetype != slidetype) {
    			$("#slidepanel").html(data.html);
    			adjustnavbar();
    			return;
    		}
    
    		$("#slidenavbar").html(data.navigation);
    		adjustnavbar();
    
    		$("#wallpaper").css("background-image", 'url("'+data.wallpaper+'")');
    		
    		if (data.frameheight > 0) {
    			$("#slidepanel").css("height", data.frameheight+"px");
    		} else {
    			$("#slidepanel").css("height", "");
    		}
    		
    		$("#titlepanel").html(data.title);
    		$("#content").html(data.content);
    		$("#content1").html(data.content1);
	    	$("#content2").html(data.content2);
	    	$("#content3").html(data.content3);
    
	    	$("#content, #content1, #content2, #content3").addClass(data.contentanimation).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    		$("#content, #content1,  #content2, #content3").removeClass(data.contentanimation);
	    	});
    	
	    	$("#content, #content1, #content2, #content3").css("background-image", 'url("'+data.boardpix+'")');
    	
	    	if (data.boardheight > 0) {
	    		$("#content, #content1, #content2, #content3").css("height", data.boardheight+'px');
	    	} else {
	    		$("#content, #content1, #content2, #content3").css("height", "");
	    	}
	    	
	    	$("#teacherpanel").attr('class', 'abteacher ' + data.teacherpos);
	    	    	
	    	$("#teacherpix").attr('src', data.teacherpix).attr('class', '').addClass(data.teacheranimation)
	    		.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    			$("#teacherpix").removeClass(data.teacheranimation);
	    	});

	    	$("#floorpanel").attr('class', 'abfloor ' + data.footerpos);

	    	$("#floorpix").attr('src', data.footerpix).attr('class', '').addClass(data.footeranimation)
	    		.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    			$("#floorpix").removeClass(data.footeranimation);
	    	});			
    	}
    	
    	
    }
  };
}, '@VERSION@', {
  requires: ['node']
});