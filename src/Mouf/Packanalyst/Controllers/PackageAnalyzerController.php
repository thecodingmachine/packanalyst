<?php

namespace Mouf\Packanalyst\Controllers;

use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Psr\Log\LoggerInterface;
use Twig_Environment;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Mouf\Packanalyst\Dao\ItemDao;
use Mouf\Packanalyst\Dao\PackageDao;

/**
 * Controller displaying package page.
 */
class PackageAnalyzerController extends Controller
{
    /**
     * The logger used by this controller.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The template used by this controller.
     *
     * @var TemplateInterface
     */
    private $template;

    /**
     * The main content block of the page.
     *
     * @var HtmlBlock
     */
    private $content;

    /**
     * The Twig environment (used to render Twig templates).
     *
     * @var Twig_Environment
     */
    private $twig;

    private $itemDao;
    private $packageDao;

    /**
     * Controller's constructor.
     *
     * @param LoggerInterface   $logger   The logger
     * @param TemplateInterface $template The template used by this controller
     * @param HtmlBlock         $content  The main content block of the page
     * @param Twig_Environment  $twig     The Twig environment (used to render Twig templates)
     */
    public function __construct(LoggerInterface $logger, TemplateInterface $template, HtmlBlock $content, Twig_Environment $twig, ItemDao $itemDao, PackageDao $packageDao)
    {
        $this->logger = $logger;
        $this->template = $template;
        $this->content = $content;
        $this->twig = $twig;
        $this->itemDao = $itemDao;
        $this->packageDao = $packageDao;
    }

    /**
     * @URL package
     * @Get
     *
     * @param string $name
     * @param string $version
     */
    public function index($name, $version = null)
    {
        if ($version != null) {
            $package = $this->packageDao->get($name, $version);
        } else {
            $package = $this->packageDao->getLatestPackage($name);
            if (isset($package['packageVersion'])) {
                $version = $package['packageVersion'];
            }
        }

        if (!$package) {
            header('HTTP/1.0 404 Not Found');
            $this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/packageAnalyzer/404.twig', array('packageName' => $name, 'packageVersion' => $version)));
            $this->template->toHtml();

            return;
        }

        $allPackages = $this->packageDao->getPackagesByName($name);
        $otherVersions = [];
        foreach ($allPackages as $package2) {
            if ($package2['packageVersion'] != $version) {
                $otherVersions[] = $package2['packageVersion'];
            }
        }

        $itemsList = $this->itemDao->findItemsByPackageVersion($name, $version);

        // Let's sort alphabetically.
        $itemsList->sort(['name' => 1]);

        $this->template->setTitle('Packanalyst | Package '.$name.' ('.$version.')');

        \Mouf::getSearchBlock()->setSearch($name);

        // Let's add the twig file to the template.
        $this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/packageAnalyzer/index.twig', array('package' => $package, 'itemsList' => $itemsList, 'otherVersions' => $otherVersions)));
        $this->template->toHtml();
    }
}
