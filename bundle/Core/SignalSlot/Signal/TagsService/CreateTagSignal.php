<?php

namespace Netgen\TagsBundle\Core\SignalSlot\Signal\TagsService;

use eZ\Publish\Core\SignalSlot\Signal;

class CreateTagSignal extends Signal
{
    /**
     *
     * Tag priority.
     *
     * Position of the tag among its siblings when sorted using priority
     * sort order.
     *
     * @var mixed
     */
    public $priority;

    /**
     * Tag ID.
     *
     * @var mixed
     */
    public $tagId;

    /**
     * Parent tag ID.
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Tag keywords in the available languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @var string[]
     */
    public $keywords;

    /**
     * The main language code for the tag.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;
}
