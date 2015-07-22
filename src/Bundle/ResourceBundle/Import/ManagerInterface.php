<?php
namespace DAG\Bundle\ResourceBundle\Import;

/**
 * Manager Interface
 *
 * @author Dylan Pierce <piercedy@upenn.edu>
 */
use DAG\Bundle\ResourceBundle\Event\ImportEvent;
use DAG\Bundle\ResourceBundle\Import\InitializerInterface;
use DAG\Bundle\ResourceBundle\Import\ConverterInterface;
use DAG\Bundle\ResourceBundle\Import\PersisterInterface;

interface ManagerInterface
{
	public function initialize(ImportEvent $event);

	public function convert(ImportEvent $event);

	public function persist(ImportEvent $event);

	public function setInitializer(InitializerInterface $initializer);

	public function getInitializer();

	public function setPersister(PersisterInterface $persister);

	public function getPersister();

	public function setConverter(ConverterInterface $converter);

	public function getConverter();

}