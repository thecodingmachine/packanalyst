<?php
namespace Mouf\Packanalyst\Controllers;
				
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Psr\Log\LoggerInterface;
use \Twig_Environment;
use Mouf\Html\Renderer\Twig\TwigTemplate;

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
	 * Controller's constructor.
	 * @param LoggerInterface $logger The logger
	 * @param TemplateInterface $template The template used by this controller
	 * @param HtmlBlock $content The main content block of the page
	 * @param Twig_Environment $twig The Twig environment (used to render Twig templates)
	 */
	public function __construct(LoggerInterface $logger, TemplateInterface $template, HtmlBlock $content, Twig_Environment $twig) {
		$this->logger = $logger;
		$this->template = $template;
		$this->content = $content;
		$this->twig = $twig;
	}
	
	/**
	 * @URL class	 
	 * @Get
	 * @param string $q
	 */
	public function index($q) {
		
		// Let's add the twig file to the template.
		$this->content->addHtmlElement(new TwigTemplate($this->twig, 'src/views/classAnalyzer/index.twig', array("class"=>$q)));
		$this->template->toHtml();
		
		/*
		 
		  START n=node:itemNameIdx(name="Thumbor\\UrlTest") MATCH n-[r2:`inherits`|`is-a`*..6]-(x)  RETURN n,r2,x LIMIT 100
		  
		  START n=node:itemNameIdx(name="Thumbor\\UrlTest") MATCH n-[r*..6]-(x) 
		  WHERE ALL(rel in r WHERE type(rel) = 'inherits' OR type(rel) = 'is_a')
		  RETURN n,r,x LIMIT 100
		  
		  
		  START n=node:itemNameIdx(name="Thumbor\\UrlTest") MATCH n-[r*..6]-(x) 
		  WHERE type(r) = 'inherits' OR type(r) = 'is_a'
		  RETURN n,r,x LIMIT 100
		  
		  START n=node:itemNameIdx(name="Thumbor\\UrlTest") MATCH n<-[r:`is-a`]-(x:Item),x-[r2:`inherits`]->y,y<-[r3]-z
		  RETURN n,r,x,r2,y,r3,z LIMIT 100
		  
		  
		  START n=node:itemNameIdx(name="Thumbor\\UrlTest") MATCH p = n<-[r:`is-a`]-(x:Item)-[r2:`inherits`]->y
		  MATCH p
		   
		   
		   */
		 
	}
}
