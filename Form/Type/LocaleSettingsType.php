<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Form\Type;

use DateUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Locale settings form type.
 */
class LocaleSettingsType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('multilingual', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Activate multilingual features'),
                'required' => false
            ])
            ->add('languageurl', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Prepend language to URL'),
                'expanded' => true,
                'choices' => [
                    $translator->__('Always') => 1,
                    $translator->__('Only for non-default languages') => 0,
                ],
                'choices_as_values' => true
            ])
            ->add('language_detect', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Automatically detect language from browser settings'),
                'required' => false,
                'help' => $translator->__('If this is checked, Zikula tries to serve the language requested by browser (if that language available and allowed by the multi-lingual settings). If users set their personal language preference, then this setting will be overriden by their personal preference.')
            ])
            ->add('language_i18n', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Default language to use for this site'),
                'choices' => $options['languages'],
                'choices_as_values' => true
            ])
            ->add('timezone_offset', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [ // @todo convert to TimezoneType
                'choices' => $options['timezones'],
                'label' => $translator->__('Time zone for anonymous guests'),
                'help' => $translator->__('Server time zone') . ': ' . DateUtil::getTimezoneAbbr()
            ])
            ->add('idnnames', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Allow IDN domain names'),
                'help' => [
                    $translator->__('This only applies to legacy variable validation. The system itself has native IDN support.'),
                    $translator->__('Notice: With IDN domains, special characters are allowed in e-mail addresses and URLs.')
                ]
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_localesettings';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'languages' => ['English' => 'en'],
            'timezones' => [0 => 'GMT']
        ]);
    }
}
