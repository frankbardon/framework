<?php

/**
 * This file is part of the Accard package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\ResourceBundle\Import;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DAG\Bundle\ResourceBundle\Event\ImportEvent;
use DAG\Bundle\ResourceBundle\Import\Events;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Import runner.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 * @author Dylan Pierce <piercedy@upenn.edu>
 */
class Runner
{
    protected $manager;
    protected $factory;
    protected $registry;
    protected $option;
    protected $import;
    protected $patient;
    protected $diagnosis;

    public function __construct(ResourceResolvingFactory $factory,
                                Registry $registry)
    {
        $this->factory = $factory;
        $this->registry = $registry;

        $this->option = $this->factory->resolveResource('option', ResourceInterface::NONE);
        $this->import = $this->factory->resolveResource('import', ResourceInterface::NONE);
        $this->patient = $this->factory->resolveResource('patient', ResourceInterface::NONE);
        $this->diagnosis = $this->factory->resolveResource('diagnosis', ResourceInterface::NONE);
    }

    /**
     * Run importer.
     *
     * @param ImporterInterface|string $importer
     * @return array|ImportTargetInterface[]
     */
    public function run($importer)
    {
        if (!$importer instanceof ImporterInterface) {
            $importer = $this->registry->getImporter($importer);
        }

        $manager = $this->manager;
        $subjectName = $importer->getSubject();

        $subject = $this->factory->resolveResource($subjectName, ResourceInterface::SUBJECT);
        $target = $this->factory->resolveResource('import_' . $subjectName, ResourceInterface::TARGET);
        $event = new ImportEvent($subject, $target);
        $resolver = new OptionsResolver();
        $this->configureDefaultResolver($resolver, $subject, $target);
        $event->setImporter($importer);
        $event->setHistory($this->import->getRepository()->getAllFor($importer->getName()));

        $manager->initialize($event);

        $importer->configureResolver($resolver);
        $event->setRecords($importer->run($resolver));

        $manager->convert($event);
        $manager->persist($event);
        $event->setImporter(null);

        return $event->getRecords();
    }

    /**
     * Get manager.
     *
     * @return ManagerInterface $manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set manager.
     *
     * @param ManagerInterface $manager
     * @return $this
     */
    public function setManager(ManagerInterface $manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Add default resolver values.
     *
     * @param OptionsResolverInterface $resolver
     * @param ResourceInterface $subject
     * @param ResourceInterface $target
     */
    private function configureDefaultResolver(OptionsResolverInterface $resolver,
                                              ResourceInterface $subject,
                                              ResourceInterface $target)
    {
        $resolver->setDefaults(array(
            'previous_record' => false,
            'subject_resource' => $subject,
            'target_resource' => $target,
            'option_resource' => $this->option,
            'import_resource' => $this->import,
            'patient_resource' => $this->patient,
            'diagnosis_resource' => $this->diagnosis,
        ));

        $resolver->setRequired(array('identifier', 'previous_record', 'import_description'));

        $resolver->setAllowedTypes('identifier', array('array', 'string'));
        $resolver->setAllowedTypes('subject_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('target_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('option_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('import_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('patient_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('diagnosis_resource', array('DAG\Bundle\ResourceBundle\Import\ResourceInterface'));
        $resolver->setAllowedTypes('import_description', 'string');
    }
}