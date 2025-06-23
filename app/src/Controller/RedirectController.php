<?php

/**
 * Redirect controller.
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UrlRepository;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UrlServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RedirectController.
 */
class RedirectController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UrlServiceInterface $urlService URL service
     * @param TranslatorInterface $translator Translaor
     */
    public function __construct(private readonly UrlServiceInterface $urlService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Handle URL redirection based on short code.
     *
     * @param string        $shortCode     The short code to redirect
     * @param UrlRepository $urlRepository The URL repository
     *
     * @return Response HTTP response
     */
    #[Route(
        '/r/{shortCode}',
        name: 'url_redirect',
        requirements: [
            'shortCode' => '[A-Za-z0-9]{1,32}$',
        ],
        methods: ['GET'],
    )]
    public function handleRedirect(string $shortCode, UrlRepository $urlRepository): Response
    {
        $url = $urlRepository->findOneBy(['shortCode' => $shortCode]);

        if (null === $url) {
            $this->addFlash('danger', $this->translator->trans('message.urlDoesNotExist'));

            return $this->redirectToRoute('url_index');
        }

        if (!$url->isPublished()) {
            $this->addFlash('danger', $this->translator->trans('message.urlUnpublished'));

            return $this->redirectToRoute('url_index');
        }

        if ($url->isBlocked()) {
            $this->urlService->checkAndUpdateBlockStatus($url);

            if ($url->isBlocked()) {
                $this->addFlash('danger', $this->translator->trans('message.urlBlocked'));

                return $this->redirectToRoute('url_index');
            }
        }

        $url->setClickCount($url->getClickCount() + 1);
        $urlRepository->save($url);

        return $this->redirect($url->getOriginalUrl());
    }
}
