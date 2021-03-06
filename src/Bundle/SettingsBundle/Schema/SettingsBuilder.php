<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\SettingsBundle\Schema;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use DAG\Bundle\SettingsBundle\Transformer\ParameterTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Settings builder.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class SettingsBuilder extends OptionsResolver implements SettingsBuilderInterface
{
    /**
     * Transformers.
     *
     * @var Collection|ParameterTransformerInterface[]
     */
    protected $transformers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->transformers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setTransformer($parameterName, ParameterTransformerInterface $transformer)
    {
        $this->transformers[$parameterName] = $transformer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformers()
    {
        return $this->transformers;
    }
}
