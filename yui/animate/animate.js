YUI.add('moodle-mod_abook-animate', function(Y) {
  M.mod_abook = M.mod_abook || {};
  M.mod_abook.animate = {
    init: function(slidetype) {
    	var lastrequest = '#';

    	window.onpopstate = function(e){
    	    if(e.state){
    	    	document.title = e.state.pageTitle;
    	    	$('html').html(e.state.html);
    	    } else {
    	    	window.location.href = e.currentTarget.location.href;
    	    }
    	};
    	
    	adjustnavbar();
    	
    	function adjustnavbar() {
			$("#prevbutton, #nextbutton").click(function() {
				var url = $(this).attr("href");
				lastrequest = url;
				$.get(url, {'json': 1}, showslide).done(sethistory).fail(fail);
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

    		document.title = data.pagetitle;

    		if (data.slidetype != slidetype) {
    			slidetype = data.slidetype;
    			$("#slidepanel").html(data.html);
    			adjustnavbar();
    			return;
    		}
    		
    		$("#slidenavbar").html(data.navigation);
    		adjustnavbar();
    		
    		$("#titlepanel").html(data.title);
    
    		$("#wallpaper").css("background-image", 'url("'+data.wallpaper+'")');
    		$("#wallpaper").css("height", data.frameheight);

	    	$("#content").css("background-image", 'url("'+data.boardpix+'")');
	    	$("#content1").css("background-image", 'url("'+data.boardpix1+'")');
	    	$("#content2").css("background-image", 'url("'+data.boardpix2+'")');
	    	$("#content3").css("background-image", 'url("'+data.boardpix3+'")');
	    	
	    	$("#content").css("height", data.boardheight);
	    	$("#content1").css("height", data.boardheight1);
	    	$("#content2").css("height", data.boardheight2);
	    	$("#content3").css("height", data.boardheight3);
    		
    		$("#content").html(data.content);
    		$("#content1").html(data.content1);
	    	$("#content2").html(data.content2);
	    	$("#content3").html(data.content3);
    
	    	$("#content").addClass(data.contentanimation).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    		$("#content").removeClass(data.contentanimation);
	    	});
	    	$("#content1").addClass(data.contentanimation1).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    		$("#content1").removeClass(data.contentanimation1);
	    	});
	    	$("#content2").addClass(data.contentanimation2).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    		$("#content2").removeClass(data.contentanimation2);
	    	});
	    	$("#content3").addClass(data.contentanimation3).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
	    		$("#content3").removeClass(data.contentanimation3);
	    	});
	    	
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
    	
    	function sethistory() {
    		var html = $('html').html();
    		var url = this.url.replace('?json=1', '').replace('&json=1', '');
    		$(".abook_toc_none>ul>li").removeClass('abook_toc_selected');
    		$(".abook_toc_none>ul>li>a[href$='"+url+"']").parent().addClass('abook_toc_selected')
    		// This line shall be the last statement in the function
    		window.history.pushState({"html": html, "pageTitle": document.title}, "", url);
    	}
    }
  };
}, '@VERSION@', {
  requires: ['node']
});