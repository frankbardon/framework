<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\ResourceBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use DAG\Component\Resource\Repository\RepositoryInterface;
use DAG\Bundle\ResourceBundle\ExpressionLanguage\AccardLanguage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * DAG Framework base resource controller.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class ResourceController extends FOSRestController implements InitializableController
{
    /**
     * Controller configuration.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Flash helper.
     *
     * @var FlashHelper
     */
    protected $flashHelper;

    /**
     * Domain manager.
     *
     * @var DomainManager
     */
    protected $domainManager;

    /**
     * Resource resolver.
     *
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * Redirect handler.
     *
     * @var RedirectHandler
     */
    protected $redirectHandler;

    /**
     * State machine graph.
     *
     * @var string
     */
    protected $stateMachineGraph;


    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        // Initialize the expression language.
        // This may not be the best place to put this...
        AccardLanguage::setExpressionLanguage($container->get('dag.expression_language'));

        $this->resourceResolver = new ResourceResolver($this->config);

        if (null !== $container) {
            $this->redirectHandler = new RedirectHandler($this->config, $container->get('router'));
            $this->flashHelper = new FlashHelper(
                $this->config,
                $container->get('translator'),
                $container->get('session')
            );
            $this->domainManager = new DomainManager(
                $container->get($this->config->getServiceName('manager')),
                $container->get('event_dispatcher'),
                $this->flashHelper,
                $this->config
            );

            $authChecker = $this->get('security.authorization_checker');

            if ($this->getUser() && $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                $this->actionLogger = new ActionLogger(
                    $this->config,
                    $this->getUser(),
                    $container->get('dag.manager.log')
                );
            }
        }
    }

    /**
     * Resource send action.
     *
     * This action is used to return a resource via API when called.
     *
     * @param Request $request
     * @return Response
     */
    public function sendAction(Request $request)
    {
        // Reserved...
    }

    /**
     * Resource index action.
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $criteria = $this->config->getCriteria();
        $sorting = $this->config->getSorting();

        $repository = $this->getRepository();

        if ($this->config->isPaginated()) {
            $resources = $this->resourceResolver->getResource(
                $repository,
                'createPaginator',
                array($criteria, $sorting)
            );
            $resources->setCurrentPage($request->get('page', 1), true, true);
            $resources->setMaxPerPage($this->config->getPaginationMaxPerPage());

            if ($this->config->isApiRequest()) {
                $resources = $this->getPagerfantaFactory()->createRepresentation(
                    $resources,
                    new Route(
                        $request->attributes->get('_route'),
                        array_merge($request->attributes->get('_route_params'), $request->query->all())
                    )
                );
            }
        } else {
            $resources = $this->resourceResolver->getResource(
                $repository,
                'findBy',
                array($criteria, $sorting, $this->config->getLimit())
            );
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('index.html'))
            ->setTemplateVar($this->config->getPluralResourceName())
            ->setData($resources)
        ;

        $response = $this->handleView($view);

        if ($response->isSuccessful()) {
            $this->actionLogger && $this->actionLogger->indexLog();
        }

        return $this->prepareResponse($response);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showAction(Request $request)
    {
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('show.html'))
            ->setTemplateVar($this->config->getResourceName())
            ->setData($this->findOr404($request))
        ;

        $response = $this->handleView($view);

        if ($response->isSuccessful()) {
            $this->actionLogger && $this->actionLogger->showLog();
        }

        return $this->prepareResponse($response);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $resource = $this->createNew();
        $form = $this->getForm($resource);

        if ($form->handleRequest($request)->isValid()) {
            $resource = $this->domainManager->create($resource);

            $this->actionLogger && $this->actionLogger->createLog();

            if (null === $resource) {
                return $this->redirectHandler->redirectToIndex();
            }

            // On success, add last saved resource to session for later use...
            $session = $this->get('session');
            $session->set('dag-last-created-resource-type', $this->config->getResourceName(), true);
            $session->set('dag-last-created-resource-id', $resource->getId(), true);

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('create.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView()
            ))
        ;

        $response = $this->handleView($view);

        if ($response->isSuccessful() && !$form->isSubmitted()) {
            $this->actionLogger && $this->actionLogger->newLog();
        }

        return $this->prepareResponse($response);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request)
    {
        $resource = $this->findOr404($request);
        $form = $this->getForm($resource);
        $method = $request->getMethod();

        if (in_array($method, array('POST', 'PUT', 'PATCH')) &&
            $form->submit($request, !$request->isMethod('PATCH'))->isValid()) {
            $this->domainManager->update($resource);

            if ($form->isValid()) {
                $this->actionLogger && $this->actionLogger->updateLog();
            }

            // On success, add last saved resource to session for later use...
            $session = $this->get('session');
            $session->set('dag-last-updated-resource-type', $this->config->getResourceName(), true);
            $session->set('dag-last-updated-resource-id', $resource->getId(), true);

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate('update.html'))
            ->setData(array(
                $this->config->getResourceName() => $resource,
                'form'                           => $form->createView()
            ))
        ;

        $response = $this->handleView($view);

        if ($response->isSuccessful() && !$form->isSubmitted()) {
            $this->actionLogger && $this->actionLogger->editLog();
        }

        return $response;
    }

    /**
     * @param  Request          $request
     * @return RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $resource = $this->findOr404($request);
        $clonedResource = $this->domainManager->delete($resource);
        $response = $this->redirectHandler->redirectTo($clonedResource);

        if ($response->isSuccessful() || $response->isRedirection()) {
           $this->actionLogger && $this->actionLogger->deleteLog();
        }

        return $response;
    }

    /**
     * @return object
     */
    public function createNew()
    {
        return $this->resourceResolver->createResource($this->getRepository(), 'createNew');
    }

    /**
     * @param object|null $resource
     * @return FormInterface
     */
    public function getForm($resource = null)
    {
        if ($this->config->isApiRequest()) {
            return $this->container->get('form.factory')->createNamed('', $this->config->getFormType(), $resource);
        }

        return $this->createForm($this->config->getFormType(), $resource);
    }

    /**
     * @param Request $request
     * @param array   $criteria
     * @return object
     * @throws NotFoundHttpException
     */
    public function findOr404(Request $request, array $criteria = array())
    {
        if ($request->get('slug')) {
            $default = array('slug' => $request->get('slug'));
        } elseif ($request->get('id')) {
            $default = array('id' => $request->get('id'));
        } else {
            $default = array();
        }

        $criteria = array_merge($default, $criteria);

        if (!$resource = $this->resourceResolver->getResource(
            $this->getRepository(),
            'findOneBy',
            array($this->config->getCriteria($criteria)))
        ) {
            throw new NotFoundHttpException(
                sprintf(
                    'Requested %s does not exist with these criteria: %s.',
                    $this->config->getResourceName(),
                    json_encode($this->config->getCriteria($criteria))
                )
            );
        }

        return $resource;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->get($this->config->getServiceName('repository'));
    }

    protected function getPagerfantaFactory()
    {
        return new PagerfantaFactory();
    }

    protected function handleView(View $view)
    {
        $handler = $this->get('fos_rest.view_handler');
        $context = $view->getSerializationContext();

        if ($groups = $this->config->getSerializationGroups()) {
            $context->setGroups($groups);
        }

        if ($version = $this->config->getSerializationVersion()) {
            $context->setVersion($version);
        }

        return $handler->handle($view);
    }

    private function prepareResponse(Response $response)
    {
        if (!$session = $this->get('session')) {
            return $response;
        }

        $response->headers->set('DAG-Last-Created-Resource-Type', $session->get('dag-last-created-resource-type'));
        $response->headers->set('DAG-Last-Created-Resource-Id', $session->get('dag-last-created-resource-id'));
        $response->headers->set('DAG-Last-Updated-Resource-Type', $session->get('dag-last-updated-resource-type'));
        $response->headers->set('DAG-Last-Updated-Resource-Id', $session->get('dag-last-updated-resource-id'));
        $response->headers->set('DAG-Last-Deleted-Resource-Type', $session->get('dag-last-deleted-resource-type'));
        $response->headers->set('DAG-Last-Deleted-Resource-Id', $session->get('dag-last-deleted-resource-id'));

        return $response;
    }
}
