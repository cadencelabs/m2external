<?php
namespace Cadence\External\Framework\View\Result;
/**
 * @author Alan Barber <alan@cadence-labs.com>
 */
class Page extends \Magento\Framework\View\Result\Page
{
    /**
     * @return $this
     */
    public function getPageComponents()
    {
        $this->pageConfig->publicBuild();
        $config = $this->getConfig();
        $this->addDefaultBodyClasses();
        $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
        $requireJs = $this->getLayout()->getBlock('require.js');

        return [
            'requireJs' => $requireJs ? $requireJs->toHtml() : null,
            'headContent' => $this->pageConfigRenderer->renderHeadContent(),
            'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
            'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
            'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
            'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
            'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif')
        ];
    }
}