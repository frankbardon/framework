<?php

/**
 * This file is part of the Accard package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\ResourceBundle\Twig;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RouterInterface;

/**
 * Accard resource Twig extension.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class ResourceExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $paginateTemplate;

    /**
     * @var string
     */
    private $sortingTemplate;

    /**
     * @var array
     */
    private $accardRouteParams = array();

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $importSignals;

    /**
     * Constructor.
     *
     * @param RouterInterface $router
     * @param array $importSignals
     * @param string $paginateTemplate
     * @param string $sortingTemplate
     */
    public function __construct(RouterInterface $router, array $importSignals, $paginateTemplate, $sortingTemplate)
    {
        $this->router = $router;
        $this->importSignals = $importSignals;
        $this->paginateTemplate = $paginateTemplate;
        $this->sortingTemplate = $sortingTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'accard_resource_sort',
                array($this, 'renderSortingLink'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'accard_resource_paginate',
                array($this, 'renderPaginateSelect'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array('accard_import_signals' => $this->importSignals);
    }

    /**
     * @return array
     */
    public function getImportSignals()
    {
        return $this->importSignals;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function fetchRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $this->request = $event->getRequest();

        $routeParams = $this->request->attributes->get('_route_params', array());
        if (array_key_exists('_accard', $routeParams)) {
            $this->accardRouteParams = $routeParams['_accard'];
        }
    }

    /**
     * @param \Twig_Environment $twig
     * @param string            $property
     * @param null|string       $label
     * @param string            $order
     * @param array             $options
     *
     * @return string
     */
    public function renderSortingLink(\Twig_Environment $twig, $property, $label = null, $order = 'asc', array $options = array())
    {
        if (array_key_exists('sortable', $this->accardRouteParams) && !$this->accardRouteParams['sortable']) {
            return $label;
        }

        $options = $this->getOptions($options, $this->sortingTemplate);
        $sorting = $this->request->get('sorting');
        $currentOrder = null;
        if (null !== $sorting && isset($sorting[$property])) {
            $currentOrder = $sorting[$property];
            $order        = 'asc' === $sorting[$property] ? 'desc' : 'asc';
        } else {
            if ('asc' !== $order && 'desc' !== $order) {
                $order = 'asc';
            }

            $sorting = array('id' => 'asc');
            if (isset($this->accardRouteParams['sorting'])) {
                $sorting = $this->accardRouteParams['sorting'];
            }
        }

        $url = $this->router->generate(
            $this->getRouteName($options['route']),
            $this->getRouteParams(
                array('sorting' => array($property => $order)),
                $options['route_params']
            )
        );

        return $twig->render($options['template'], array(
            'url'          => $url,
            'label'        => null === $label ? $property : $label,
            'icon'         => $property == key($sorting),
            'currentOrder' => $currentOrder,
        ));
    }

    /**
     * @param \Twig_Environment $twig
     * @param Pagerfanta        $paginator
     * @param array             $limitOptions
     * @param array             $options
     *
     * @return string
     */
    public function renderPaginateSelect(\Twig_Environment $twig, Pagerfanta $paginator, array $limitOptions, array $options = array())
    {
        if (array_key_exists('paginate', $this->accardRouteParams) && is_integer($this->accardRouteParams['paginate'])) {
            $options = $this->getOptions($options, $this->paginateTemplate);
            $paginateName = 'limit';

            $limits = array();
            foreach ($limitOptions as $limit) {
                $routeParams = $this->getRouteParams(
                    array($paginateName => $limit),
                    $options['route_params']
                );

                if (array_key_exists('page', $routeParams)) {
                    $routeParams['page'] = 1;
                }

                $limits[$limit] = $this->router->generate(
                    $this->getRouteName($options['route']),
                    $routeParams
                );
            }

            return $twig->render($options['template'], array(
                'paginator' => $paginator,
                'limits'    => $limits,
            ));
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'accard_resource';
    }

    /**
     * @param array $params
     * @param array $default
     *
     * @return array
     */
    private function getRouteParams(array $params = array(), array $default = array())
    {
        return array_merge(
            $this->request->query->all(),
            $this->request->attributes->get('_route_params'),
            array_merge($params, $default)
        );
    }

    /**
     * @param null|string $route
     *
     * @return mixed|null
     */
    private function getRouteName($route = null)
    {
        return null === $route ? $this->request->attributes->get('_route') : $route;
    }

    /**
     * @param array  $options
     * @param string $defaultTemplate
     *
     * @return array
     */
    private function getOptions(array $options, $defaultTemplate)
    {
        if (!array_key_exists('template', $options)) {
            $options['template'] = $defaultTemplate;
        }

        if (!array_key_exists('route', $options)) {
            $options['route'] = null;
        }

        if (!array_key_exists('route_params', $options)) {
            $options['route_params'] = array();
        }

        return $options;
    }
}
