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
        if (!array_key_exists($groupName, $this->group)) {
            throw new \RuntimeException("Group '{$groupName}' is not found");
        }

        if (null === $context) {
            $context = $this->context;
        }

        return $this->group[$groupName]->select($context);
    }

    /**
     * @param string $name
     * @param callable|null $processor
     * @return static
     */
    public function withFeature($name, $processor = null)
    {
        $clone = clone $this;

        $clone->addFeature($name, $processor);

        return $clone;
    }

    /**
     * @param string $name
     * @param array $features
     * @param callable|null $processor
     * @return Manager
     */
    public function withGroup($name, $features, $processor = null)
    {
        $clone = clone $this;

        $clone->addGroup($name, $features, $processor);

        return $clone;
    }
}
