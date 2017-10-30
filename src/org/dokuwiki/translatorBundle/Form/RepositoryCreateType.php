<?php

namespace org\dokuwiki\translatorBundle\Form;

use org\dokuwiki\translatorBundle\Entity\RepositoryEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RepositoryCreateType extends AbstractType {

    public static $ACTION_CREATE = 'create';
    public static $ACTION_EDIT = 'edit';


    public function buildForm(FormBuilderInterface $builder, array $options) {
        if($options['action'] == RepositoryCreateType::$ACTION_CREATE) {
            $builder->add('name', 'text', array('label' => ucfirst($options['type']) . ' name'));
        }
        $builder->add('email', 'text', array('label' => 'E-mail'));
        $builder->add('url', 'text', array('label' => 'Git clone url'));
        $builder->add('branch', 'text', array('label' => 'Main branch'));
        $builder->add('englishReadonly', 'checkbox', array('label' => 'English Readonly', 'required' => false));
        $builder->add('captcha', 'captcha');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName() {
        return 'repository';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
            array(
                'type' => RepositoryEntity::$TYPE_PLUGIN,
                'validation_groups' => array(RepositoryEntity::$TYPE_PLUGIN),
                'action' => RepositoryCreateType::$ACTION_CREATE
            )
        );
    }
}
