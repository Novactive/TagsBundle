<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\AbstractSlot;
use Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService\AddSynonymSignal;

class AddSynonymSlot extends AbstractSlot
{
    /**
     * @param AddSynonymSignal $signal
     *
     * @return string[]
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'tag-' . $signal->tagId,
            'tag-' . $signal->parentTagId,
            'parent-tag-' . $signal->parentTagId,
        ];
    }

    /**
     * @param Signal $signal
     *
     * @return bool
     */
    protected function supports(Signal $signal)
    {
        return $signal instanceof AddSynonymSignal;
    }
}
