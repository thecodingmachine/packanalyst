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

<h1>Packanalyst (beta): explore PHP classes from Packagist</h1>



<div class="jumbotron">
<h3 class="text-center">Search in PHP open-source code</h3>
<form role="form" id="searchForm" action="search">
<div class="row form-group-lg">
	<div class="col-xs-12 col-md-8 col-md-offset-1">
		<input type="text" name="q" class="form-control typeahead inputlg " placeholder="Search any PHP class / interface / trait / function or package">
	</div>
	<div class="col-xs-12 col-md-2">
    	<button type="submit" class="btn btn-default inputlg btn-block btn-lg"><i class="glyphicon glyphicon-search"></i> Search</button>
    </div>
</div>
    
</form>
<p>&nbsp;</p>
<p>Packanalyst is a service that let's you browse in <strong>any</strong> PHP class / interface / trait
defined in <a href="http://packagist.org/">Packagist</a>.</p>
</div>

<div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading"><h3 class="text-center">Find any class implementing your interface</h3></div>
  <div class="panel-body">
    <p>Packanalyst can be useful for the average developer, but we believe it can be tremendously 
    useful for any package developer. Indeed, using Packanalyst, you can find any package containing
    classes that implement/extend or simply use your classes/interfaces. 
    </p>
    <p>Therefore, this is an absolutely unique tool to <strong>know who is using and implementing
    your interfaces / abstract classes / traits</strong>.</p>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading"><h3 class="text-center">How does it work?</h3></div>
  <div class="panel-body">
    <p>Packanalyst regularly scans the Packagist repository for new or updated PHP packages. Each package is
    analyzed and all classes interfaces and traits are extracted and stored in our database for later search. 
    </p>
    <dl>

    	<dt>Do I need to do something special to register my package on Packanalyst?</dt>
    	<dd>No, you just need to register your package on Packanalyst and it will automatically be scanned
    	by Packanalyst.</dd>
    
	    <dt>How long does it take for my package to be scanned?</dt>
	    <dd>Depending on the number of packages changed, it can take anything between an hour and a few days for 
	    your package to be analyzed after you register it or you make changes to it.</dd>
	    
	    <dt>What versions of my package are scanned and stored?</dt>
	    <dd>For performance reason, Packanalyst does not scan all versions of your package. It will scan
	    the master branch of your project and all latest tagged major versions.</dd>
	    
    </dl>
  </div>
</div>

<div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading"><h3 class="text-center">Feedback needed!</h3></div>
  <div class="panel-body">
    <p>Packanalyst is a service in beta. Do not hesitate to send feedback! 
    <a href="https://twitter.com/david_negrier">@david_negrier</a>. 
    </p>
  </div>
</div>

<div class="panel panel-default">
  <!-- Default panel contents -->
  <div class="panel-heading"><h3 class="text-center">Who is behind this?</h3></div>
  <div class="panel-body">
    <p>Packanalyst is a service developed by David Négrier who happens to be the 
    lead developer of the <a href="http://mouf-php.com" target="_blank">Mouf framework</a>.
    Mouf is a PHP framework based on dependency injection. The core idea of Mouf is to help bind classes and components
    developed by many developers together. For this vision to come true, we need a set of core interfaces
    (this is the work of the PHP-FIG group), and a tool to find classes implementing those common interfaces
    (hence the development of Packanalyst).
    </p>
    <p>David is CTO of <a href="http://www.thecodingmachine.com" target="_blank">TheCodingMachine</a>, a French
    IT company, who is kindly sponsoring Packanalyst's development and hosting.</p>
  </div>
</div>

