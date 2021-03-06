<?php

/**
 * This file is part of The DAG Framework package.
 *
 * (c) University of Pennsylvania
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace DAG\Bundle\OptionBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use DAG\Component\Option\Model\OptionInterface;
use DAG\Component\Option\Model\OptionOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Option value choice type.
 *
 * @author Frank Bardon Jr. <bardonf@upenn.edu>
 */
class OptionValueChoiceType extends AbstractType
{
    /**
     * Option.
     *
     * @var OptionInterface
     */
    private $option;

    /**
     * Sort order key.
     *
     * @var string
     */
    private $sortBy;

    /**
     * Constructor.
     *
     * @param OptionInterface $option
     */
    public function __construct(OptionInterface $option, $sortBy = OptionOrder::DEFAULT_ORDER)
    {
        $this->option = $option;
        $this->sortBy = $sortBy;
    }


    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $view->vars['option'] = $this->option;
        $view->vars['is_addable'] = array_key_exists('data-field-addable', $options['attr'])
            ? (bool) $options['attr']['data-field-addable']
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = $this->option->getValues();

        if (!$choices->first()) {
            throw new MissingOptionsException(
                sprintf(
                    'The option "%s" must have values assigned before it may be used in a form.',
                    $this->option->getName()
                )
            );
        }

        $class = get_class($choices->first());

        // Remove locked values from choice list.
        $choices = $choices->filter(function($choice) {
            return !$choice->isLocked();
        });

        // Sort choice list.
        $choices = OptionOrder::sort($choices, $this->sortBy);

        $resolver
            ->setDefaults(array(
                'class' => $class,
                'choices' => $choices,
                'empty_value' => 'dag.option.form.choose',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $normalizedName = str_replace('-', '_', $this->option->getName());

        return sprintf('dag_%s_option_value', $normalizedName);
    }
}
