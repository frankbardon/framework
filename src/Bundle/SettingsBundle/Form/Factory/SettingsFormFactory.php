<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\SettingsBundle\Form\Factory;

use DAG\Bundle\SettingsBundle\Schema\SchemaRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Settings form factory.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class SettingsFormFactory implements SettingsFormFactoryInterface
{
    /**
     * Schema registry.
     *
     * @var SchemaRegistryInterface
     */
    protected $schemaRegistry;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;


    /**
     * Constructor.
     *
     * @param SchemaRegistryInterface $schemaRegistry
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(SchemaRegistryInterface $schemaRegistry,
                                FormFactoryInterface $formFactory)
    {
        $this->schemaRegistry = $schemaRegistry;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create($namespace)
    {
        $schema = $this->schemaRegistry->getSchema($namespace);
        $extensions = $this->schemaRegistry->getExtensions($namespace);
        $builder = $this->formFactory->createBuilder('form', null, array('data_class' => null));

        $schema->buildForm($builder);

        foreach ($extensions as $extension) {
            $extension->buildForm($builder);
        }

        return $builder->getForm();
    }
}
