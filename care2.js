
$(function(){
	$('#rx > area').hover(function(e){ // Hover event

	var titleText = $(this).attr('title');

	$(this)
		.data('tiptext', titleText)
		.removeAttr('title');

	$('<p class="tooltip"></p>')
		.html(titleText)
		.appendTo('body')
		.css('top', (e.pageY + 4) + 'px')
		.css('left', (e.pageX - 92) + 'px')
		.fadeIn('slow');

	}, function(){ // Hover off event

	$(this).attr('title', $(this).data('tiptext'));
	$('.tooltip').remove();

	}).mousemove(function(e){ // Mouse move event

    $('.tooltip')
		.css('top', (e.pageY + 4) + 'px')
		.css('left', (e.pageX - 92) + 'px');
	});
});