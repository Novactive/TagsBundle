<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\AbstractSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\MergeTagsSignal;

class MergeTagsSlot extends AbstractSlot
{
    /**
     * @param MergeTagsSignal $signal
     *
     * @return string[]
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'tag-' . $signal->tagId,
            'tag-' . $signal->targetTagId,
            'tag-' . $signal->parentTagId,
            'tag-' . $signal->targetParentTagId,
            'parent-tag-' . $signal->parentTagId,
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
        return $signal instanceof MergeTagsSignal;
    }
}
