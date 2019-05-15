<?php

namespace Netgen\TagsBundle\API\Repository\Values\Tags;

/**
 * This class represents a value for creating a tag.
 */
class TagCreateStruct extends TagStruct
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
     * The ID of the parent tag under which the new tag should be created.
     *
     * Required
     *
     * @var mixed
     */
    public $parentTagId;

    /**
     * Indicates if the tag is shown in the main language if it's not present in an other requested language.
     *
     * @var bool
     */
    public $alwaysAvailable = true;
}
