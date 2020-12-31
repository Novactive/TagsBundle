<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\AbstractSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\CopySubtreeSignal;

class CopySubtreeSlot extends AbstractSlot
{
    /**
     * @param CopySubtreeSignal $signal
     *
     * @return string[]
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'tag-' . $signal->newTagId,
            'tag-' . $signal->targetParentTagId,
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
        return $signal instanceof CopySubtreeSignal;
    }
}
