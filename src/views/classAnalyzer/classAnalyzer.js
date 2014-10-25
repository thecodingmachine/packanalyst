$(function() {
	$('a.otherpackageslink').click(function() {
		$(this).parent('.otherpackagescontainer').find('.otherpackages').show();
		$(this).hide();
	});
});