<?php

/**
 * Access denied listener.
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Exception\UrlBlockedException;

/**
 * Class AccessDeniedListener.
 */
class AccessDeniedListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
     * Constructor.
     *
     * @param RouterInterface     $router       Router
     * @param TranslatorInterface $translator   Translator
     * @param RequestStack        $requestStack Request stack
     */
    public function __construct(private readonly RouterInterface $router, private readonly TranslatorInterface $translator, private readonly RequestStack $requestStack)
    {
    }

    /**
     * Handles access denied exceptions.
     *
     * @param ExceptionEvent $event The event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException
            || $exception instanceof AccessDeniedHttpException) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request && $request->getSession()) {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    $this->translator->trans('message.accessDenied')
                );
            }

            $event->setResponse(new RedirectResponse($this->router->generate('url_index')));
        }

        if ($exception instanceof UrlBlockedException) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request && $request->getSession()) {
                $request->getSession()->getFlashBag()->add(
                    'danger',
                    $this->translator->trans('message.urlUnavailable')
                );
            }

            $event->setResponse(new RedirectResponse($this->router->generate('url_index')));
        }
    }

    /**
     * Returns the list of events this subscriber wants to listen to.
     *
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [\Symfony\Component\HttpKernel\KernelEvents::EXCEPTION => 'onKernelException'];
    }
}
