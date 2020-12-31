<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\AbstractSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MoveSubtreeSignal;

class MoveSubtreeSlot extends AbstractSlot
{
    /**
     * @param MoveSubtreeSignal $signal
     *
     * @return string[]
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'tag-' . $signal->sourceTagId,
            'tag-' . $signal->sourceParentTagId,
            'tag-' . $signal->targetParentTagId,
            'parent-tag-' . $signal->sourceParentTagId,
            'parent-tag-' . $signal->targetParentTagId,
        ];
    }
    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof MoveSubtreeSignal;
    }
}
