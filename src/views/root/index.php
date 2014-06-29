<?php /* @var $this Mouf\Packanalyst\Controllers\RootController */ ?>
<script type="text/javascript">
$(function() {

	var classesAutocomplete = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: window.rootUrl+'suggest?q=%QUERY',
		limit: 10
	});

	classesAutocomplete.initialize();
	
	$('.typeahead').typeahead({
		minLength: 3,
		highlight: true
	},
	{
		name: 'classesDataset',
		displayKey: 'value',
		source: classesAutocomplete.ttAdapter()
	});

	$('.typeahead').on('typeahead:selected', function() {
		$('#searchForm').submit();
	});

});
</script>

<h1>Packanalyst: explore PHP classes from Packagist</h1>

<h3 class="text-center">Search in PHP open-source code</h3>

<form role="form" id="searchForm" action="class">
<div class="row">
	<div class="col-xs-12 col-md-10">
		<input type="text" name="q" class="form-control typeahead inputlg " placeholder="Search any PHP class / interface / trait / function">
	</div>
	<div class="col-xs-12 col-md-2">
    	<button type="submit" class="btn btn-default inputlg btn-block"><i class="glyphicon glyphicon-search"></i> Search</button>
    </div>
</div>
    
</form>
