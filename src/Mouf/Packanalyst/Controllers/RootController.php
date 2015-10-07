<?php

namespace Mouf\Packanalyst\Controllers;

use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Packanalyst\Services\ElasticSearchService;
use Mouf\Packanalyst\Dao\PackageDao;
use Mouf\Packanalyst\Dao\ItemDao;
use Mouf\Html\Renderer\Twig\TwigTemplate;

/**
 * This is the controller in charge of managing the first page of the application.
 */
class RootController extends Controller
{
    /**
     * The template used by the controller.
     *
     * @var TemplateInterface
     */
    private $template;

    /**
     * This object represents the block of main content of the web page.
     *
     * @var HtmlBlock
     */
    private $content;

    private $elasticSearchService;
    private $packageDao;
    private $itemDao;
    private $twig;

    public function __construct(TemplateInterface $template, HtmlBlock $content, ElasticSearchService $elasticSearchService,
            PackageDao $packageDao, ItemDao $itemDao, \Twig_Environment $twig)
    {
        $this->template = $template;
        $this->content = $content;
        $this->elasticSearchService = $elasticSearchService;
        $this->packageDao = $packageDao;
        $this->itemDao = $itemDao;
        $this->twig = $twig;
    }

    /**
     * Page displayed when a user arrives on your web application.
     *
     * @URL /
     */
    public function index()
    {
        $this->template->setTitle('Packanalyst | Explore PHP classes from Packagist');
        //$this->template->setContainerClass('homeContainer container');
        $this->template->setContainerClass('homeContainer');
        array_shift(\Mouf::getBootstrapNavBar()->children);
        array_shift(\Mouf::getBootstrapNavBar()->children);
        $this->content->addFile(ROOT_PATH.'src/views/root/index.php', $this);
        $this->template->toHtml();
    }

    /**
     * @URL /suggest
     */
    public function suggest($q)
    {
        header('Content-type: application/json');

        $results = $this->elasticSearchService->suggestItemName2($q);

        $jsonArr = array_map(function ($item) {
            return [
                'value' => $item['_source']['name'],
            ];
        }, $results['hits']);

        echo json_encode($jsonArr);
    }

    /**
     * @URL /search
     *
     * @param string $q The query string
     */
    public function search($q, $page = 0)
    {
        // If query is a valid item of package, let's go to the dedicated page.
        $item = $this->itemDao->getItemsByName($q);
        if ($item->count() != 0) {
            header('Location: '.ROOT_URL.'class?q='.urlencode($q));

            return;
        }

        $package = $this->packageDao->getLatestPackage($q);
        if ($package != null) {
            // Let's grab the first
            header('Location: '.ROOT_URL.'package?name='.urlencode($q).'&version='.$package['packageVersion']);

            return;
        }

        $searchResults = $this->elasticSearchService->suggestItemName2($q, 50, $page * 50);
        $totalCount = $searchResults['total'];
        $hits = $searchResults['hits'];
        $nbPages = floor($totalCount / 50);

        $this->template->setTitle('Packanalyst | Search results for '.$q);

        \Mouf::getSearchBlock()->setSearch($q);

        $this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/root/search.twig',
                array(
                        'searchResults' => $hits,
                        'totalCount' => $totalCount,
                        'query' => $q,
                        'nbPages' => $nbPages,
                        'page' => $page,
        )));
        $this->template->toHtml();
    }
}
