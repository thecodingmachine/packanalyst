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
use Mouf\Reflection\MoufPhpDocComment;
use Michelf\MarkdownExtra;
use Mouf\Packanalyst\Widgets\Node;
use Mouf\Packanalyst\Dao\PackageDao;

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
	 * 
	 * @var PackageDao
	 */
	private $packageDao;

	/**
	 * Controller's constructor.
	 * @param LoggerInterface $logger The logger
	 * @param TemplateInterface $template The template used by this controller
	 * @param HtmlBlock $content The main content block of the page
	 * @param Twig_Environment $twig The Twig environment (used to render Twig templates)
	 * @param ItemDao $itemDao
	 * @param PackageDao $packageDao
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
			
			// If this class has never been used, we might want to wonder if the class exists at all.
			if ($graphItems->count() == 0) {
				// Let's go on a 404.
				header("HTTP/1.0 404 Not Found");
				$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/classAnalyzer/404.twig', array("class"=>$q)));
				$this->template->toHtml();
				return;
			}
			
			$rootNodes = [[
				"name"=>$q
			]];
		}
		$graph = new Graph($rootNodes, $graphItems);
		
		// Let's extract the PHPDoc from the latest version (dev version):
		foreach ($rootNodes as $rootNode) {
			if (isset($rootNode['packageVersion']) && strpos($rootNode['packageVersion'], "-dev") !== false) {
				break;
			}
		}
		if ($rootNode && isset($rootNode['phpDoc'])) {
			$docBlock = new MoufPhpDocComment($rootNode['phpDoc']);
			$md = $docBlock->getComment();
			$description = MarkdownExtra::defaultTransform($md)	;
			
			// Let's purify HTML to avoid any attack:
			$config = \HTMLPurifier_Config::createDefault();
			$purifier = new \HTMLPurifier($config);
			$description = $purifier->purify($description);
			
		} else {
			$description = '';
		}
		if ($rootNode && isset($rootNode['type'])) {
			$type = $rootNode['type'];
		} else {
			$type = "class";
		}
		
		// Let's compute the pointer to the source.
		$package = $this->packageDao->get($rootNode['packageName'], $rootNode['packageVersion']);
		$sourceUrl = null;
		if (isset($package['sourceUrl']) && strpos($package['sourceUrl'], 'https://github.com') === 0) {
			if (strpos($package['sourceUrl'], '.git') === strlen($package['sourceUrl'])-4) {
				if (isset($rootNode['fileName']) && $rootNode['fileName']) {
					$sourceUrl = substr($package['sourceUrl'], 0, strlen($package['sourceUrl'])-4);
					$version = str_replace(['dev-', '.x-dev'], ['', ''], $package['packageVersion']);
					$sourceUrl .= '/blob/'.$version.$rootNode['fileName'];
				}
			}
		}
		
		
		// Now, let's find all the classes/interfaces we extend from (recursively...)
		$inheritNodes = $this->getNode($q);
		
		// Let's add the twig file to the template.
		$this->template->setTitle('Packanalyst | '.ucfirst($type).' '.$q);
		$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/classAnalyzer/index.twig', 
				array(
						"class"=>$q, 
						"graph"=>$graph, 
						"description"=>$description, 
						"type"=>$type, 
						"inheritNodes"=>$inheritNodes,
						"sourceUrl"=>$sourceUrl)));
		$this->template->toHtml();
	}
	
	private $inheritedNodes = array();
	
	private function getNode($className) {
		if (isset($inheritedNodes[$className])) {
			return $inheritedNodes[$className];
		}
		
		$nodes = $this->itemDao->getItemsByName($className);
		if ($nodes->hasNext()) {
			$mainNode = $nodes->getNext();
			$type = isset($mainNode['type'])?$mainNode['type']:null;
		} else {
			$type = null;
		}
		
		$htmlNode = new Node($className, $type);
		
		$inherits = array();
		foreach ($nodes as $node) {
			if (isset($node['packageName'])) {
				$htmlNode->registerPackage($node['packageName'], $node['packageVersion']);
			}
			if (isset($node['inherits'])) {
				$inherits = array_merge($inherits, $node['inherits']);
			}
		}
		$inherits = array_keys(array_flip($inherits));
		
		foreach ($inherits as $inherit) {
			$htmlNode->addChild($this->getNode($inherit));
		}
		
		$inheritedNodes[$className] = $htmlNode;
		return $htmlNode;
	}
	
}
