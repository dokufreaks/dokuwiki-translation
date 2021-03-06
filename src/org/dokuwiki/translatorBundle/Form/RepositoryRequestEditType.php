<?php

namespace org\dokuwiki\translatorBundle\Form;



use Gregwar\CaptchaBundle\Type\CaptchaType;
use org\dokuwiki\translatorBundle\Entity\RepositoryEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryRequestEditType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('captcha', CaptchaType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'type' => RepositoryEntity::$TYPE_PLUGIN,
                'validation_groups' => array(RepositoryEntity::$TYPE_PLUGIN)
            )
        );
    }
}
