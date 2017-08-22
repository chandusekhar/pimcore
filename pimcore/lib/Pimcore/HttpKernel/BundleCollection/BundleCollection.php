<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\HttpKernel\BundleCollection;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleCollection
{
    /**
     * @var ItemInterface[]
     */
    private $items = [];

    /**
     * @var array
     */
    private $bundleIdentifiers = [];

    /**
     * @var ItemInterface[]
     */
    private $itemsByEnvironment = [];

    /**
     * Adds a collection item
     *
     * @param ItemInterface $item
     *
     * @return self
     */
    public function add(ItemInterface $item): self
    {
        $identifier = $item->getBundleIdentifier();
        if (in_array($identifier, $this->bundleIdentifiers)) {
            throw new \LogicException(sprintf('Trying to register the bundle "%s" multiple times', $identifier));
        }

        $this->bundleIdentifiers[] = $identifier;
        $this->items[$item->getPriority()][] = $item;

        return $this;
    }

    /**
     * Checks if a specific item is registered
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasItem(string $identifier)
    {
        return in_array($identifier, $this->bundleIdentifiers);
    }

    /**
     * Returns all collection items ordered by priority and optionally filtered by matching environment
     *
     * @param string|null $environment
     *
     * @return ItemInterface[]
     */
    public function getItems(string $environment = null): array
    {
        $cacheKey = '_all';
        if (null !== $environment) {
            $cacheKey = $environment;
        }

        if (isset($this->itemsByEnvironment[$cacheKey])) {
            return $this->itemsByEnvironment[$cacheKey];
        }

        $priorities = array_keys($this->items);
        rsort($priorities); // highest priority first

        $items = [];
        foreach ($priorities as $priority) {
            /** @var Item $item */
            foreach ($this->items[$priority] as $item) {
                if (null !== $environment && !$item->matchesEnvironment($environment)) {
                    continue;
                }

                $items[] = $item;
            }
        }

        $this->itemsByEnvironment[$cacheKey] = $items;

        return $items;
    }

    /**
     * Returns all bundle identifiers
     *
     * @return array
     */
    public function getIdentifiers(): array
    {
        return $this->bundleIdentifiers;
    }

    /**
     * Adds a bundle
     *
     * @param BundleInterface $bundle
     * @param int $priority
     * @param array $environments
     *
     * @return self
     */
    public function addBundle(BundleInterface $bundle, int $priority = 0, array $environments = []): self
    {
        return $this->add(new Item($bundle, $priority, $environments));
    }

    /**
     * Adds a collection of bundles with the same priority and environments
     *
     * @param BundleInterface[] $bundles
     * @param int $priority
     * @param array $environments
     *
     * @return BundleCollection
     */
    public function addBundles(array $bundles, int $priority = 0, array $environments = []): self
    {
        foreach ($bundles as $bundle) {
            $this->addBundle($bundle, $priority, $environments);
        }

        return $this;
    }

    /**
     * Get bundles matching environment ordered by priority
     *
     * @param string $environment
     *
     * @return BundleInterface[]
     */
    public function getBundles(string $environment): array
    {
        $bundles = [];
        foreach ($this->getItems($environment) as $item) {
            $bundles[] = $item->getBundle();
        }

        return $bundles;
    }
}
