<?php
namespace org\dokuwiki\translatorBundle\Services\Repository;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use org\dokuwiki\translatorBundle\Entity\LanguageNameEntity;
use org\dokuwiki\translatorBundle\Entity\LanguageNameEntityRepository;
use org\dokuwiki\translatorBundle\Entity\LanguageStatsEntity;
use org\dokuwiki\translatorBundle\Entity\LanguageStatsEntityRepository;
use org\dokuwiki\translatorBundle\Entity\RepositoryEntity;
use org\dokuwiki\translatorBundle\Services\Language\LocalText;

class RepositoryStats {

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LanguageStatsEntityRepository
     */
    private $languageStatsRepository;

    /**
     * @var LanguageNameEntityRepository
     */
    private $languageNameRepository;

    function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
        $this->languageStatsRepository = $entityManager->getRepository('dokuwikiTranslatorBundle:LanguageStatsEntity');
        $this->languageNameRepository = $entityManager->getRepository('dokuwikiTranslatorBundle:LanguageNameEntity');
    }

    /**
     * Clear all language statistics of this repository
     *
     * @param RepositoryEntity $entity
     */
    public function clearStats(RepositoryEntity $entity) {
        $this->languageStatsRepository->clearStats($entity);
    }

    /**
     * Create new language statistics for this repository
     *
     * @param LocalText[] $translations combined array with all translations
     * @param RepositoryEntity $repository Repository the translation belongs to
     *
     * @throws OptimisticLockException
     */
    public function createStats($translations, RepositoryEntity $repository) {
        $scores = array();
        if (!isset($translations['en'])) {
            echo 'none created ';
            return;
        }

        foreach ($translations as $language => $translation) {
            $scores[$language] = $this->calcStatsForLanguage($translation);
        }

        if($scores['en'] === 0 ) {
            echo 'zero English strings available ';
            return;
        }

        $max = $scores['en'];
        foreach ($scores as $language => $score) {
            $statsEntity = new LanguageStatsEntity();
            $statsEntity->setRepository($repository);
            $statsEntity->setCompletionPercent(floor((100*$score) / $max));
            $statsEntity->setLanguage($this->getLanguageEntityByCode($language));
            $this->entityManager->persist($statsEntity);
        }
        $this->entityManager->flush();
    }

    /**
     * Search for LanguageNameEntity, if not existing it is created
     *
     * @param string $languageCode
     * @return LanguageNameEntity
     */
    private function getLanguageEntityByCode($languageCode) {
        try {
            return $this->languageNameRepository->getLanguageByCode($languageCode);
        } catch (NoResultException $e) {
            $languageName = new LanguageNameEntity();
            $languageName->setCode($languageCode);
            $languageName->setName($languageCode);
            $this->entityManager->persist($languageName);
            return $languageName;
        }
    }

    /**
     * Count strings from all language files of language
     *
     * @param LocalText $translation
     * @return int
     */
    private function calcStatsForLanguage($translation) {
        $value = 0;
        foreach ($translation as $path => $languageFile) {
            $value += $this->getTranslationValue($languageFile);
        }
        return $value;
    }

    /**
     * Count strings per language file
     *
     * @param LocalText $languageFile
     * @return int
     */
    private function getTranslationValue($languageFile) {
        if ($languageFile->getType() == LocalText::$TYPE_MARKUP) {
            return 1;
        }
        return count($languageFile->getContent());
    }

}
