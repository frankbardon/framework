<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\SettingsBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;
use Doctrine\Common\Collections\Collection;
use DAG\Bundle\SettingsBundle\Templating\Helper\SettingsHelper;

/**
 * DAG settings Twig extension.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class SettingsExtension extends Twig_Extension
{
    /**
     * Settings templating helper.
     *
     * @var SettingsHelper
     */
    private $helper;


    /**
     * Constructor.
     *
     * @param SettingsHelper $helper
     */
    public function __construct(SettingsHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('dag_settings', array($this, 'getSettings')),
            new Twig_SimpleFunction('dag_setting', array($this, 'getParameter')),
        );
    }

    /**
     * Get all settings within a namespace.
     *
     * @param string $namespace
     * @return Collection
     */
    public function getSettings($namespace)
    {
        return $this->helper->getSettings($namespace);
    }

    /**
     * Get setting by name (using dot notation).
     *
     * @param string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->helper->getParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dag_settings';
    }
}
