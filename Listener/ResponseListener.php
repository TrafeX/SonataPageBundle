<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $selector;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $
     */
    public function __construct(CmsManagerSelectorInterface $selector)
    {
        $this->selector = $selector;
    }

    /**
     * filter the `core.response` event to decorated the action
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cmsManager  = $this->selector->retrieve();

        if (!$cmsManager) {
            return;
        }

        $response    = $event->getResponse();
        $requestType = $event->getRequestType();
        $request     = $event->getRequest();

        if ($cmsManager->isDecorable($request, $requestType, $response)) {
            $page = $cmsManager->defineCurrentPage($request);

            // only decorate hybrid page and page with decorate = true
            if ($page && $page->isHybrid() && $page->getDecorate()) {
                $parameters = array(
                    'content'     => $response->getContent(),
                );

                $response = $cmsManager->renderPage($page, $parameters, $response);
            }
        }

        $event->setResponse($response);
    }
}