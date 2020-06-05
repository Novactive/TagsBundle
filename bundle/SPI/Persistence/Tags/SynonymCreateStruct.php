<?php

namespace Netgen\TagsBundle\SPI\Persistence\Tags;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * This class represents a value for creating a synonym.
 */
class SynonymCreateStruct extends ValueObject
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
     * The ID of the main tag for which the new synonym should be created.
     *
     * @required
     *
     * @var mixed
     */
    public $mainTagId;

    /**
     * The main language code for the tag.
     *
     * @required
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * Tag keywords in the target languages
     * Eg. array( "cro-HR" => "Hrvatska", "eng-GB" => "Croatia" ).
     *
     * @required
     *
     * @var string[]
     */
    public $keywords;

    /**
     * A global unique ID of the tag.
     *
     * @var string
     */
    public $remoteId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable;
}
