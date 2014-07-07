<?php
namespace Mouf\Packanalyst\Controllers;
				
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Psr\Log\LoggerInterface;
use \Twig_Environment;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Mouf\Packanalyst\Repositories\ItemNameRepository;
use Mouf\Packanalyst\Widgets\Graph;
use Mouf\Packanalyst\Dao\ItemDao;

/**
 * TODO: write controller comment
 */
class ClassAnalyzerController extends Controller {

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

	/**
	 * 
	 * @var ItemDao
	 */
	private $itemDao;

	/**
	 * Controller's constructor.
	 * @param LoggerInterface $logger The logger
	 * @param TemplateInterface $template The template used by this controller
	 * @param HtmlBlock $content The main content block of the page
	 * @param Twig_Environment $twig The Twig environment (used to render Twig templates)
	 * @param ItemDao $itemDao
	 */
	public function __construct(LoggerInterface $logger, TemplateInterface $template, HtmlBlock $content, Twig_Environment $twig, ItemDao $itemDao) {
		$this->logger = $logger;
		$this->template = $template;
		$this->content = $content;
		$this->twig = $twig;
		$this->itemDao = $itemDao;
	}
	
	/**
	 * @URL class	 
	 * @Get
	 * @param string $q
	 */
	public function index($q) {
		
		// Remove front \
		$q = ltrim($q, '\\');
		
		$graphItems = $this->itemDao->findItemsInheriting($q);
		$rootNodes = $this->itemDao->getItemsByName($q);
		// If there is no root node (for instance if the class is "Exception")
		if ($rootNodes->count() == 0) {
			$rootNodes = [[
				"name"=>$q
			]];
		}
		$graph = new Graph($rootNodes, $graphItems);
		
		// Let's add the twig file to the template.
		$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/classAnalyzer/index.twig', array("class"=>$q, "graph"=>$graph)));
		$this->template->toHtml();
	}
}
