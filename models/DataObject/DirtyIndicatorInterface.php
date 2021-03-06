<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    Element
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Model\DataObject;

interface DirtyIndicatorInterface
{
    /**
     * @return bool
     */
    public function hasDirtyFields();

    /**
     * @param $key
     *
     * @return bool
     */
    public function isFieldDirty($key);

    /**
     * marks the given field as dirty
     *
     * @param $field
     * @param bool $dirty
     *
     * @return mixed
     */
    public function markFieldDirty($field, $dirty = true);

    public function resetDirtyMap();
}
