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
<div class="search-wrapper">
    <h1 class="logo">
        <span class="medium">Pack</span><span class="light">analyst</span>
        <small class="light">Explore PHP classes from Packagist</small>
    </h1>

    <?php // <h3 class="text-center">Search in PHP open-source code</h3> ?>
    <form role="form" id="searchForm" action="search">
        <div class="row">
            <div class="col-xs-12 col-md-10">
                <input type="text" name="q" class="form-control typeahead inputlg search-field light" placeholder="Search any PHP class / interface / trait / function or package">
            </div>
            <div class="col-xs-12 col-md-2">
                <button type="submit" class="btn btn-default inputlg btn-block button-search">
                    <i class="glyphicon glyphicon-search"></i>
                    Search
                </button>
            </div>
        </div>
    </form>
</div>


<h3 class="text-center"><?php // What is this? ?></h3>

<div class="row">
	<div class="col-xs-12 col-md-12">
		<div class="panel panel-default">
		  <!-- Default panel contents -->
		  <div class="panel-heading">A PHP class analyzer</div>
		  <div class="panel-body">
		    <p>Packanalyst is a service that let's you browse in <strong>any</strong> PHP class / interface / trait
		    defined in <a href="http://packagist.org/">Packagist</a>. Not used to Packagist? You should! This is the
		    de-facto central repository for storing any PHP open-source project. 
		    </p>
		  </div>
		</div>
		<div class="panel panel-default">
		  <!-- Default panel contents -->
		  <div class="panel-heading">Feedback needed!</div>
		  <div class="panel-body">
		    <p>Packanalyst is a service in beta. Do not hesitate to send feedback! 
		    <a href="https://twitter.com/david_negrier">@david_negrier</a>. 
		    </p>
		  </div>
		</div>
		<div class="panel panel-default">
		  <!-- Default panel contents -->
		  <div class="panel-heading">Find any class implementing your interface</div>
		  <div class="panel-body">
		    <p>Packanalyst can be useful for the average developer, but we believe it can be tremendously 
		    useful for any package developer. Indeed, using Packanalyst, you can find any package containing
		    classes that implement/extend your classes/interfaces. 
		    </p>
		    <p>Therefore, this is an absolutely unique tool to know who is using and implementing
		    your interfaces / abstract classes / traits.</p>
		  </div>
		</div>
    </div>
</div>

<div class="row footer text-center">
    <div class="col-md-12">
        All Rights Reserved to Packanalyst © - Developed by <a href="">David Négrier</a> & designed by <a href="">Hugo Averty</a>
    </div>
</div>