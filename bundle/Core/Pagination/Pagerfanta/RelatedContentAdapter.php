<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for content related to a tag.
 * Will return results as content objects.
 */
class RelatedContentAdapter implements AdapterInterface, TagAdapterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var bool
     */
    protected $returnContentInfo;

    /**
     * @var int
     */
    protected $nbResults;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    protected $sortClauses = [];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion[]
     */
    protected $additionalCriteria = [];

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param bool $returnContentInfo
     */
    public function __construct(TagsService $tagsService, $returnContentInfo = true)
    {
        $this->tagsService = $tagsService;
        $this->returnContentInfo = (bool) $returnContentInfo;
    }

    /**
     * Sets the tag to the adapter.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     */
    public function setSortClauses(array $sortClauses)
    {
        $this->sortClauses = $sortClauses;
    }

    /**
     * Sets additional criteria to be used in search.
     *
     * @param array $additionalCriteria
     */
    public function setAdditionalCriteria(array $additionalCriteria = array())
    {
        $this->additionalCriteria = $additionalCriteria;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getNbResults()
    {
        if (!$this->tag instanceof Tag) {
            return 0;
        }

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);
        }

        return $this->nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function getSlice($offset, $length)
    {
        if (!$this->tag instanceof Tag) {
            return [];
        }

        $relatedContent = $this->tagsService->getRelatedContent(
            $this->tag,
            $offset,
            $length,
            $this->returnContentInfo,
            $this->sortClauses,
            $this->additionalCriteria
        );

        if (!isset($this->nbResults)) {
            $this->nbResults = $this->tagsService->getRelatedContentCount($this->tag, $this->additionalCriteria);
        }

        return $relatedContent;
    }
}
