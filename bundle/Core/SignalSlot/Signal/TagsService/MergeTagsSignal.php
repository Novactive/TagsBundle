<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class MergeTagsSignal extends Signal
{
    /**
     * Tag ID.
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Parent Tag ID.
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Target tag ID.
     *
     * @var mixed
     */
    public $targetTagId;

    /**
     * Parent Tag ID.
     *
     * @var mixed
     */
    public $targetParentTagId;
}
