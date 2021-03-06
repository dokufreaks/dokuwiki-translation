<?php

namespace org\dokuwiki\translatorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TranslationUpdateEntityRepository extends EntityRepository {

    public function getPendingTranslationUpdates() {
        $query = $this->getEntityManager()->createQuery(
            'SELECT job
             FROM dokuwikiTranslatorBundle:TranslationUpdateEntity job
             JOIN job.repository repository
             WHERE job.state = :state'
        );
        $query->setParameter('state', TranslationUpdateEntity::$STATE_UNDONE);
        return $query->getResult();
    }

}
