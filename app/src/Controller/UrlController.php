<?php

/**
 * Url controller.
 */

namespace App\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Form\Type\UrlType;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\Voter\UrlVoter;
use App\Form\Type\UrlBlockType;
use App\Repository\UrlRepository;

/**
 * Class UrlController.
 */
class UrlController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UrlServiceInterface $urlService Url service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly UrlServiceInterface $urlService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param UrlRepository $urlRepository URL repository
     * @param int           $page          Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url',
        name: 'url_index',
        methods: 'GET'
    )]
    public function index(UrlRepository $urlRepository, #[MapQueryParameter] int $page = 1): Response
    {
        foreach ($urlRepository->findTemporarilyBlocked() as $url) {
            $this->urlService->checkAndUpdateBlockStatus($url);
        }

        $pagination = $this->urlService->getPaginatedList($page);

        return $this->render('url/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * 10 latest URLs action.
     *
     * @param int $limit Number of URLs to retrieve
     * @param int $page  Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/latest',
        name: 'url_latest',
        methods: 'GET'
    )]
    public function latest(int $limit = 10, #[MapQueryParameter] int $page = 1): Response
    {
        $latestUrls = $this->urlService->getLatestUrls($limit, $page);

        return $this->render('url/latest.html.twig', [
            'latestUrls' => $latestUrls,
        ]);
    }

    /**
     * Top 10 URLs action.
     *
     * @param int $limit Number of URLs to retrieve
     * @param int $page  Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/most-popular',
        name: 'url_popular',
        methods: 'GET'
    )]
    public function popular(int $limit = 10, #[MapQueryParameter] int $page = 1): Response
    {
        $popularUrls = $this->urlService->getMostPopularUrls($limit, $page);

        $popularUrlsForChart = $this->urlService->getMostPopularUrlsForChart($limit);

        //        dd($popularUrlsForChart);

        $labels = array_map(fn ($url) => $url->getShortCode(), $popularUrlsForChart);
        $data = array_map(fn ($url) => $url->getClickCount(), $popularUrlsForChart);

        return $this->render('url/popular.html.twig', [
            'popularUrls' => $popularUrls,
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * View action.
     *
     * @param Url $url Url entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/{id}',
        name: 'url_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function view(Url $url): Response
    {
        return $this->render(
            'url/view.html.twig',
            ['url' => $url]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/create',
        name: 'url_create',
        methods: 'GET|POST'
    )]
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $url = new Url();

        if ($user) {
            $url->setAuthor($user);
        }

        $form = $this->createForm(UrlType::class, $url, [
            'is_authenticated' => null !== $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user && !$this->urlService->canCreateForEmail($url->getAuthorEmail())) {
                $this->addFlash('danger', $this->translator->trans('message.dailyLimitReached'));

                return $this->redirectToRoute('url_index');
            }

            $this->urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.createdSuccessfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/{id}/edit',
        name: 'url_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    #[IsGranted(UrlVoter::EDIT, subject: 'url')]
    public function edit(Request $request, Url $url): Response
    {
        if ($url->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.accessDenied')
            );

            return $this->redirectToRoute('url_index');
        }

        $form = $this->createForm(
            UrlType::class,
            $url,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('url_edit', ['id' => $url->getId()]),
                'is_authenticated' => $this->getUser() instanceof UserInterface,
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->save($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.editedSuccessfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/edit.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/{id}/delete',
        name: 'url_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    #[IsGranted(UrlVoter::DELETE, subject: 'url')]
    public function delete(Request $request, Url $url): Response
    {
        if ($url->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
                'danger',
                $this->translator->trans('message.accessDenied')
            );

            return $this->redirectToRoute('url_index');
        }

        $form = $this->createForm(FormType::class, $url, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('url_delete', ['id' => $url->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->delete($url);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deletedSuccessfully')
            );

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/delete.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }

    /**
     * Block action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/{id}/block',
        name: 'url_block',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    #[IsGranted(UrlVoter::BLOCK, subject: 'url')]
    public function block(Request $request, Url $url): Response
    {
        $form = $this->createForm(UrlBlockType::class, $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permanent = $form->get('permanent')->getData();
            $blockedUntil = $url->getBlockedUntil();

            $this->urlService->blockUrl($url, $permanent, $blockedUntil);

            $this->addFlash('success', $this->translator->trans('message.urlBlockedSuccessfully'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render(
            'url/block.html.twig',
            [
                'form' => $form->createView(),
                'url' => $url,
            ]
        );
    }

    /**
     * Unblock action.
     *
     * @param Request $request HTTP request
     * @param Url     $url     Url entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/url/{id}/unblock',
        name: 'url_unblock',
        methods: ['GET', 'POST']
    )]
    #[IsGranted(UrlVoter::BLOCK, subject: 'url')]
    public function unblock(Request $request, Url $url): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('url_unblock', ['id' => $url->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->urlService->unblockUrl($url);
            $this->addFlash('success', $this->translator->trans('message.urlUnblocked'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render('url/unblock.html.twig', [
            'form' => $form->createView(),
            'url' => $url,
        ]);
    }

    /**
     * Unpublished action.
     *
     * @param UrlRepository $urlRepository URL repository
     * @param int           $page          Page number
     *
     * @return Response HTTP response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/url/unpublished',
        name: 'url_unpublished',
        methods: 'GET'
    )]
    public function unpublished(UrlRepository $urlRepository, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->urlService->getPaginatedListUnpublished($page);

        return $this->render('url/unpublished.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Search action.
     *
     * @param Request           $request       HTTP request
     * @param UrlRepository     $urlRepository URL repository
     * @param MapQueryParameter $page          Page number
     *
     * @return Response HTTP response
     */
    #[Route('/url/search', name: 'url_search', methods: ['GET'])]
    public function search(Request $request, UrlRepository $urlRepository, #[MapQueryParameter] int $page = 1): Response
    {
        $query = $request->query->get('q');
        $urls = [];

        if ($query) {
            $pagination = $this->urlService->getPaginatedListSearchByQuery($query, $page);
        }

        return $this->render('url/search.html.twig', [
            'query' => $query,
            'pagination' => $pagination,
        ]);
    }
}
