<?php
namespace Mouf\Packanalyst\Controllers;
				
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Packanalyst\Services\ElasticSearchService;
				
/**
 * This is the controller in charge of managing the first page of the application.
 * 
 * @Component
 */
class RootController extends Controller {
	
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
	
	public function __construct(TemplateInterface $template, HtmlBlock $content, ElasticSearchService $elasticSearchService) {
		$this->template = $template;
		$this->content = $content;
		$this->elasticSearchService = $elasticSearchService;
	}
	
	/**
	 * Page displayed when a user arrives on your web application.
	 * 
	 * @URL /
	 */
	public function index() {
		$this->content->addFile(ROOT_PATH."src/views/root/index.php", $this);
		$this->template->toHtml();
	}
	
	/**
	 * @URL /suggest
	 */
	public function suggest($q) {
		header('Content-type: application/json');
		echo json_encode($this->elasticSearchService->suggestItemName($q));
	}
}