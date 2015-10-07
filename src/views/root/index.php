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


<a name="search"></a>
<div class="" id="search">
    <div class="container">
        <div class="panel panel-default">
            <div class="row form-group-lg">
                <div class="col-md-12">
                    <h1 class="logo">
                        <a href="<?= ROOT_URL ?>" title="Packanalyst">
                            <img src="<?= ROOT_URL ?>src/views/css/images/logo.png" alt="Logo Packanalyst"/>
                            <span class="logo">Pack<span class="blue">analyst</span></span>
                            <small>Explore PHP classes from Packagist</small>
                        </a>
                    </h1>
                    <form role="form" id="searchForm" action="search">
                    <div class="row form-group-lg">
                        <div class="col-xs-12 col-md-10">
                            <input type="text" name="q" class="form-control typeahead inputlg search-field" placeholder="Search any PHP class / interface / trait / function or package">
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <button type="submit" class="btn btn-default inputlg btn-block btn-lg button-search"><i class="glyphicon glyphicon-search"></i> Search</button>
                        </div>
                    </div>

                    </form>
                    <p>&nbsp;</p>
                    <p class="sub-title">Packanalyst is a service that lets you browse in <strong>any</strong> PHP class / interface / trait
                    defined in <a href="http://packagist.org/">Packagist</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row form-group-lg">
        <div class="col-md-12">
            <div class="panel panel-default">
              <!-- Default panel contents -->
              <div class="panel-heading">
                  <img class="ico-panel" src="<?= ROOT_URL ?>src/views/css/images/ico-search.png" />
                  <h3 class="text-center">Find any class implementing your interface</h3>
              </div>
              <div class="panel-body">
                <div class="">
                <p>Packanalyst can be useful for the average developer, but we believe it can be tremendously
                useful for any package developer. Indeed, using Packanalyst, you can find any package containing
                classes that implement/extend or simply use your classes/interfaces.
                </p>
                <p>Therefore, this is an absolutely unique tool to <strong>know who is using and implementing
                your interfaces / abstract classes / traits</strong>. For instance, have a look at all the classes
                that implement the PSR3 <a href="class?q=Psr\Log\LoggerInterface">LoggerInterface</a>.</p>
                </div>
              </div>
            </div>

            <a name="feedback"></a>
            <div class="panel panel-default" id="feedback">
              <!-- Default panel contents -->
              <div class="panel-heading">
                  <img class="ico-panel" src="<?= ROOT_URL ?>src/views/css/images/ico-talk.png" />
                  <h3 class="text-center">Feedback needed!</h3>
              </div>
              <div class="panel-body">
                <div class="">
                <p>Packanalyst is a service in beta. Do not hesitate to <a href="https://github.com/thecodingmachine/packanalyst/issues">send us feedback</a>, or <a href="https://github.com/thecodingmachine/packanalyst/">pull requests</a>.
                Packanalyst is released in <a href="http://www.gnu.org/licenses/agpl-3.0.html">AGPL</a>.</p>

                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <a class="btn btn-block btn-social btn-github btn-lg" href="https://github.com/thecodingmachine/packanalyst/issues" alt="Github" target="_blank">
                            Report an issue on Github.
                        </a>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <a class="btn btn-block btn-social btn-twitter btn-lg" href="https://twitter.com/david_negrier" alt="Twitter" target="_blank">
                            Follow me on Twitter
                        </a>
                    </div>
                </div>
              </div>
              </div>
            </div>

            <a name="about"></a>
            <div class="panel panel-default" id="about">
              <div class="panel-heading">
                  <img class="ico-panel" src="<?= ROOT_URL ?>src/views/css/images/ico-work.png" />
                  <h3 class="text-center">How does it work?</h3>
              </div>
              <div class="panel-body">
                <div class="">
                <p>Packanalyst regularly scans the Packagist repository for new or updated PHP packages. Each package is
                analyzed and all classes interfaces and traits are extracted and stored in our database for later search.
                </p>
                <dl>

                    <dt>Do I need to do something special to register my package on Packanalyst?</dt>
                    <dd>No, you just need to register your package on Packagist and it will automatically be scanned
                    by Packanalyst.</dd>

                    <dt>How long does it take for my package to be scanned?</dt>
                    <dd>Depending on the number of packages changed, it can take anything between an hour and a few days for
                    your package to be analyzed after you register it or you make changes to it.</dd>

                    <dt>What versions of my package are scanned and stored?</dt>
                    <dd>For performance reason, Packanalyst does not scan all versions of your package. It will scan
                    the master branch of your project and all latest tagged major versions.</dd>

                    <dt>What are those stars displayed next to some classes or interfaces?</dt>
                    <dd>In order to highlight the main classes used, we put in place a simple rating system.
                    The goal is simply to highlight the classes that are most used. The rating system does not
                    reflect the quality of the class, it simply reflects its usage. It is based on the number
                    of downloads on Packagist:
                    <ul>
                    <li>No stars: &lt;100 downloads</li>
                    <li><span class="glyphicon glyphicon-star"></span>: Between 100 and 1000 downloads</li>
                    <li><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span>: Between 1000 and 10000 downloads</li>
                    <li><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span>: Between 10000 and 100000 downloads</li>
                    <li><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span>: Between 100000 and 1000000 downloads</li>
                    <li><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span><span class="glyphicon glyphicon-star"></span>: &gt; 1000000 downloads</li>
                    </ul>
                    Also, one "star" on Packagist will be translated into 100 downloads for the rating system of Packanalyst.
                    </dd>
                </dl>
              </div>
              </div>
            </div>

            <a name="api"></a>
            <div class="panel panel-default" id="api">
              <!-- Default panel contents -->
              <div class="panel-heading">
                  <img class="ico-panel" src="<?= ROOT_URL ?>src/views/css/images/ico-code.png" />
                  <h3 class="text-center">API</h3>
              </div>
              <div class="panel-body">
                <div class="">
                <p><b>In progress!</b> A REST API will be released to query Packanalyst and integrate Packanalyst with third-party
                programs. Mouf will be the first framework to get a native integration with Packanalyst.</p>
                </div>
              </div>
            </div>

            <a name="team"></a>
            <div class="panel panel-default" id="team">
              <!-- Default panel contents -->
              <div class="panel-heading">
                  <img class="ico-panel" src="<?= ROOT_URL ?>src/views/css/images/ico-dna.png" />
                  <h3 class="text-center">Who is behind Packanalyst?</h3>
              </div>
              <div class="panel-body">
                <div class="">
                <p>Packanalyst is a service developed by David Négrier who happens to be the
                lead developer of the <a href="http://mouf-php.com" target="_blank">Mouf framework</a>.
                Mouf is a PHP framework based on dependency injection. The core idea of Mouf is to help bind classes and components
                developed by many developers together. For this vision to come true, we need a set of core interfaces
                (this is the work of the PHP-FIG group), and a tool to find classes implementing those common interfaces
                (hence the development of Packanalyst).
                </p>
                <p>David is CTO of <a href="http://www.thecodingmachine.com" target="_blank">TheCodingMachine</a>, a French
                IT company, who is kindly sponsoring Packanalyst's development and hosting.</p>
                <p>The design and front-end part has been developed by <a href="https://twitter.com/eleveur2pixels" target="_blank">Hugo Averty</a>, project manager for <a href="http://www.thecodingmachine.com" target="_blank">TheCodingMachine</a>. </p>
                <p class="text-center">
                    <a href="http://mouf-php.com" style="margin: 30px"><img src="<?= ROOT_URL ?>src/views/css/images/mouf.png" alt="Mouf" /></a>
                    <a href="http://www.thecodingmachine.com" style="margin: 30px"><img src="<?= ROOT_URL ?>src/views/css/images/tcm.png" alt="TheCodingMachine" /></a>
                </p>
              </div>
              </div>
        </div>
    </div>
</div>
</div>
