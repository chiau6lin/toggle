<?php

namespace MilesChou\Toggle;

use MilesChou\Toggle\Concerns\ContextTrait;
use MilesChou\Toggle\Concerns\FacadeTrait;
use MilesChou\Toggle\Concerns\FeatureTrait;
use MilesChou\Toggle\Concerns\GroupTrait;

class Manager
{
    use ContextTrait;
    use FacadeTrait;
    use FeatureTrait;
    use GroupTrait;

    /**
     * @param string $providerDriver
     * @return ProviderInterface
     */
    public function export($providerDriver)
    {
        if (!class_exists($providerDriver)) {
            throw new \RuntimeException("Unknown class {$providerDriver}");
        }

        /** @var ProviderInterface $persistentProvider */
        $persistentProvider = new $providerDriver();

        if (!$persistentProvider instanceof ProviderInterface) {
            throw new \RuntimeException('Driver must instance of Provider');
        }

        return $persistentProvider
            ->setFeatures($this->features)
            ->setGroups($this->groups);
    }

    /**
     * @param ProviderInterface $persistentProvider
     * @param bool $clean
     */
    public function import(ProviderInterface $persistentProvider, $clean = true)
    {
        if ($clean) {
            $this->cleanFeature();
            $this->cleanGroup();
        }

        $features = $persistentProvider->getFeatures();

        foreach ($features as $name => $feature) {
            $result = $feature['result'];

            $this->addFeature($name, Feature::create()->setProcessedResult($result));
        }

        $groups = $persistentProvider->getGroups();

        foreach ($groups as $name => $group) {
            $list = $this->normalizeFeatureMap($group['list']);
            $result = $group['result'];

            $this->addGroup($name, Group::create($list)->setProcessedResult($result));
        }
    }

    /**
     * @param string $featureName
     * @param null|Context $context
     * @return bool
     */
    public function isActive($featureName, Context $context = null)
    {
        if (!array_key_exists($featureName, $this->features)) {
            throw new \RuntimeException("Feature '{$featureName}' is not found");
        }

        if (null === $context) {
            $context = $this->context;
        }

        return $this->features[$featureName]->isActive($context);
    }

    /**
     * @param string $groupName
     * @param null|Context $context
     * @return string
     */
    public function select($groupName, Context $context = null)
    {
        if (!array_key_exists($groupName, $this->groups)) {
            throw new \RuntimeException("Group '{$groupName}' is not found");
        }

        if (null === $context) {
            $context = $this->context;
        }

        return $this->groups[$groupName]->select($context);
    }
}
