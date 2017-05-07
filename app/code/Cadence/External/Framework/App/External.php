<?php
namespace Cadence\External\Framework\App;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event;
use Magento\Framework\Filesystem;

/**
 * @author Alan Barber <alan@cadence-labs.com>
 * Provide an external application handler
 * so that the Magento API is exposed to WordPress
 */
class External implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var AreaList
     */
    protected $_areaList;

    /**
     * @var RequestHttp
     */
    protected $_request;

    /**
     * @var ConfigLoaderInterface
     */
    protected $_configLoader;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var ResponseHttp
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cadence\External\Model\Integration
     */
    protected $_externalIntegration;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Event\Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param Filesystem $filesystem,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Event\Manager $eventManager,
        AreaList $areaList,
        RequestHttp $request,
        ResponseHttp $response,
        ConfigLoaderInterface $configLoader,
        State $state,
        Filesystem $filesystem,
        \Magento\Framework\Registry $registry
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_areaList = $areaList;
        $this->_request = $request;
        $this->_response = $response;
        $this->_configLoader = $configLoader;
        $this->_state = $state;
        $this->_filesystem = $filesystem;
        $this->registry = $registry;
    }

    /**
     * Add new dependency
     *
     * @return \Psr\Log\LoggerInterface
     *
     * @deprecated
     */
    private function getLogger()
    {
        if (!$this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Run application
     *
     * @throws \InvalidArgumentException
     * @return \Cadence\External\Model\Integration
     */
    public function launch(array $initParams = [])
    {
        $this->_state->setAreaCode('frontend');
        $this->_objectManager->configure($this->_configLoader->load('frontend'));

        $this->_eventManager->dispatch('cadence_wp_external_app_launch', [
            'app' => $this,
            'request' => $this->_request
        ]);

        $this->_externalIntegration = $this->_objectManager->get('\Cadence\External\Model\Integration');

        $this->_externalIntegration->configure($initParams);

        return $this->_externalIntegration;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        return $this->handleExternalException($bootstrap, $exception);
    }

    public function handleExternalException(Bootstrap $bootstrap, \Exception $exception)
    {
        // We just want to display an error within the external Application
        // Do not hijack request flow
        $errorMessage = '<div>' . $exception->getMessage() . '</div>';
        if ($bootstrap->isDeveloperMode()) {
            // Don't display a full trace if not dev mode for security reasons
            $errorMessage .= '<div>' . $exception->getTraceAsString() . '</div>';
        }
        $error = <<<HTML
<div class="cadence-wp-framework-app-errors">
    <p>Uh-oh! Something has gone wrong with the Cadence Magento 2 / External Integration. Please see below for full details:</p>
    <ul>
        <li>
            {$errorMessage}
        </li>

    </ul>
</div>
HTML;
        echo $error . "<br/><br/>\n\n";
        return true;
    }
}