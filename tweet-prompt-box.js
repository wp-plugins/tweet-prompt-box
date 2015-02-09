jQuery(function($) {
	var document_height = $(document).outerHeight();
	var window_scroll;
	var tweet_prompt_box = $('#tweet-prompt-box');

	$(window).scroll(function() {
		window_scroll = $(window).scrollTop();

		if ( window_scroll > document_height / 2 ) {
			tweet_prompt_box.not( '.closed' ).fadeIn();
		}
	});

	$('.tweet-prompt-box-close').click(function() {
		tweet_prompt_box.animate({'bottom': -50, 'opacity': 0}, 300);
		setTimeout(function() {
			tweet_prompt_box.hide().addClass('closed');
		}, 300);
	});
});

function tweet_prompt_box_open_win( url ) {
	window.open(url,'tweetwindow','width=550,height=450,location=yes,directories=no,channelmode=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no');
	return false;
}