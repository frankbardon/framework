<?php

/**
 * This file is part of the Accard package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\FieldBundle\DependencyInjection;

use DAG\Bundle\ResourceBundle\DependencyInjection\AbstractResourceExtension;
use DAG\Bundle\ResourceBundle\AccardResourceBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Accard field bundle extension.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class AccardFieldExtension extends AbstractResourceExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $this->configure($config, new Configuration(), $container, self::CONFIGURE_LOADER | self::CONFIGURE_DATABASE | self::CONFIGURE_PARAMETERS | self::CONFIGURE_VALIDATORS);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $config, ContainerBuilder $container)
    {
        $convertedConfig = array();
        $subjects = array();

        foreach ($config['classes'] as $subject => $parameters) {
            $subjects[$subject] = $parameters;
            unset($parameters['subject']);

            foreach ($parameters as $resource => $classes) {
                $convertedConfig[$subject.'_'.$resource] = $classes;
            }

            $this->createSubjectServices($container, $config['driver'], $subject, $convertedConfig);

            if (!isset($config['validation_groups'][$subject]['field'])) {
                $config['validation_groups'][$subject]['field'] = array('accard');
            }
            if (!isset($config['validation_groups'][$subject]['field_value'])) {
                $config['validation_groups'][$subject]['field_value'] = array('accard');
            }
        }

        $container->setParameter('accard.field.subjects', $subjects);

        $config['classes'] = $convertedConfig;
        $convertedConfig = array();

        foreach ($config['validation_groups'] as $subject => $parameters) {
            foreach ($parameters as $resource => $validationGroups) {
                $convertedConfig[$subject.'_'.$resource] = $validationGroups;
            }
        }

        $config['validation_groups'] = $convertedConfig;

        return $config;
    }

    /**
     * Create services for every subject.
     *
     * @param ContainerBuilder $container
     * @param string           $driver
     * @param string           $subject
     * @param array            $config
     */
    private function createSubjectServices(ContainerBuilder $container, $driver, $subject, array $config)
    {
        $fieldAlias = $subject.'_field';
        $fieldValueAlias = $subject.'_field_value';

        $fieldClasses = $config[$fieldAlias];
        $fieldValueClasses = $config[$fieldValueAlias];

        // Field repository.
        $repositoryClasses = array(
            AccardResourceBundle::DRIVER_DOCTRINE_ORM => 'DAG\Bundle\FieldBundle\Doctrine\ORM\FieldRepository',
        );
        $repositoryClass = sprintf('accard.repository.%s.class', $fieldAlias);
        if (!$container->hasParameter($repositoryClass)) {
            $container->setParameter($repositoryClass, $repositoryClasses[$driver]);
        }

        // Field form.
        $fieldFormType = new Definition($fieldClasses['form']);
        $fieldFormType
            ->setArguments(array($subject, $fieldClasses['model'], '%accard.validation_group.'.$fieldAlias.'%'))
            ->addTag('form.type', array('alias' => 'accard_'.$fieldAlias))
        ;

        $container->setDefinition('accard.form.type.'.$fieldAlias, $fieldFormType);

        // Field form choice.
        $choiceTypeClasses = array(
            AccardResourceBundle::DRIVER_DOCTRINE_ORM => 'DAG\Bundle\FieldBundle\Form\Type\FieldEntityChoiceType'
        );

        $fieldChoiceFormType = new Definition($choiceTypeClasses[$driver]);
        $fieldChoiceFormType
            ->setArguments(array($subject, $fieldClasses['model']))
            ->addTag('form.type', array('alias' => 'accard_'.$fieldAlias.'_choice'))
        ;

        $container->setDefinition('accard.form.type.'.$fieldAlias.'_choice', $fieldChoiceFormType);

        // Field value form.
        $fieldValueFormType = new Definition($fieldValueClasses['form']);
        $fieldValueFormType
            ->setArguments(array($subject, $fieldValueClasses['model'], '%accard.validation_group.'.$fieldValueAlias.'%'))
            ->addTag('form.type', array('alias' => 'accard_'.$fieldValueAlias))
        ;

        $container->setDefinition('accard.form.type.'.$fieldValueAlias, $fieldValueFormType);

        // Field provider.
        $fieldProvider = new Definition('DAG\Component\Field\Provider\FieldProvider');
        $fieldProvider->addArgument(new Reference('accard.repository.'.$fieldAlias));

        $container->setDefinition('accard.provider.'.$fieldAlias, $fieldProvider);
    }
}
