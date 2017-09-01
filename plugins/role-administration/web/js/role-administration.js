function toggle(id) {

	if($('#' + id).is(":visible")) {
		$('#' + id).hide();
		$('#' + id + '_a_label').addClass('expand').removeClass('collapse');
	} else {
		$('#' + id).show();
		$('#' + id + '_a_label').addClass('collapse').removeClass('expand');
	}
}