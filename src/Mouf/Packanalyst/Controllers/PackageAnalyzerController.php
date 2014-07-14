<?php
namespace Mouf\Packanalyst\Controllers;
				
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Psr\Log\LoggerInterface;
use \Twig_Environment;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Mouf\Packanalyst\Dao\ItemDao;
use Mouf\Packanalyst\Dao\PackageDao;

/**
 * TODO: write controller comment
 */
class PackageAnalyzerController extends Controller {

	/**
	 * The logger used by this controller.
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * The template used by this controller.
	 * @var TemplateInterface
	 */
	private $template;
	
	/**
	 * The main content block of the page.
	 * @var HtmlBlock
	 */
	private $content;
	
	/**
	 * The Twig environment (used to render Twig templates).
	 * @var Twig_Environment
	 */
	private $twig;

	private $itemDao;
	private $packageDao;

	/**
	 * Controller's constructor.
	 * @param LoggerInterface $logger The logger
	 * @param TemplateInterface $template The template used by this controller
	 * @param HtmlBlock $content The main content block of the page
	 * @param Twig_Environment $twig The Twig environment (used to render Twig templates)
	 */
	public function __construct(LoggerInterface $logger, TemplateInterface $template, HtmlBlock $content, Twig_Environment $twig, ItemDao $itemDao, PackageDao $packageDao) {
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
	 * @param string $name
	 * @param string $version
	 */
	public function index($name, $version) {
		$package = $this->packageDao->get($name, $version);
		$itemsList = $this->itemDao->findItemsByPackageVersion($name, $version);
		
		// Let's add the twig file to the template.
		$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/packageAnalyzer/index.twig', array("package"=>$package, "itemsList"=>$itemsList)));
		$this->template->toHtml();
	}
}
