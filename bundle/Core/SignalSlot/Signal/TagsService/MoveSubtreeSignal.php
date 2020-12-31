<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class MoveSubtreeSignal extends Signal
{
    /**
     * Source tag ID.
     *
     * @var mixed
     */
    public $sourceTagId;
    
    /**
     * Parent Tag ID.
     *
     * @var mixed
     */
    public $sourceParentTagId;

    /**
     * Target parent tag ID.
     *
     * @var mixed
     */
    public $targetParentTagId;
}
