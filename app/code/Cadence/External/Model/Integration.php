<?php
namespace Cadence\External\Model;
use Cadence;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\View;
use Magento\Framework\App\Response\Http as ResponseHttp;

/**
 * @author Alan Barber <alan@cadence-labs.com>
 */
class Integration
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    protected $structure;

    /**
     * @var array
     */
    protected $_initParams;

    /**
     * @var array
     */
    protected $_pageComponents;

    public function __construct(
        View $view,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Layout\Data\Structure $structure
    )
    {
        $this->view = $view;
        $this->design = $design;
        $this->structure = $structure;
    }

    /**
     * @param array $initParams
     * @return $this
     * @throws \Exception
     */
    public function configure(array $initParams = [])
    {
        if (!isset($initParams['theme'])) {
            throw new \Exception("Cannot run Magento integration without theme!");
        }

        $this->_initParams = $initParams;

        $this->initView();

        return $this;
    }

    /**
     * @param $name
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getBlock($name)
    {
        return $this->getLayout()->getBlock($name);
    }

    public function getElementHtml($name)
    {
        return $this->getLayout()->renderElement($name);
    }

    /**
     * @param $name
     * @return string
     */
    public function getBlockHtml($name)
    {
        return $this->getBlock($name)->toHtml();
    }

    /**
     * @param $name
     * @return string
     */
    public function getContainerHtml($name)
    {
        return $this->getElementHtml($name);
    }

    /**
     * @return \Magento\Framework\View\Layout
     */
    public function getLayout()
    {
        return $this->view->getLayout();
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function getPage()
    {
        return $this->view->getPage();
    }

    /**
     * @param $param
     * @return null
     */
    public function getInitParam($param)
    {
        return $this->_initParams[$param] ?? null;
    }

    /**
     * @param $key
     * @return null
     */
    public function getPageComponent($key)
    {
        return $this->getPageComponents()[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getPageComponents()
    {
        if (is_null($this->_pageComponents)) {
            $this->initView();
        }
        returN $this->_pageComponents;
    }

    /**
     */
    public function initView()
    {
        $this->design->setDesignTheme($this->getInitParam('theme'));

        $this->view->loadLayout([
            'default',
            'external'
        ]);

        $this->layout = $this->view->getLayout();

        /**
         * @see Cadence\External\Framework\View\Result\Page::getPageComponents
         */
        $this->_pageComponents = $this->view->getPage()->getPageComponents();


//        print_r(array_keys($this->view->getLayout()->getAllBlocks()));

        return $this;
    }
}