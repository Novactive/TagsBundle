<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo;

class Mapper
{
    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(LanguageHandler $languageHandler, LanguageMaskGenerator $languageMaskGenerator)
    {
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Creates a tag from a $data row.
     *
     * @param array $row
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\TagInfo
     */
    public function createTagInfoFromRow(array $row)
    {
        $tagInfo = new TagInfo();

        $tagInfo->id = (int) $row['id'];
        $tagInfo->parentTagId = (int) $row['parent_id'];
        $tagInfo->mainTagId = (int) $row['main_tag_id'];
        $tagInfo->depth = (int) $row['depth'];
        $tagInfo->pathString = $row['path_string'];
        $tagInfo->modificationDate = (int) $row['modified'];
        $tagInfo->priority = $row['priority'];
        $tagInfo->remoteId = $row['remote_id'];
        $tagInfo->alwaysAvailable = ((int) $row['language_mask'] & 1) ? true : false;
        $tagInfo->mainLanguageCode = $this->languageHandler->load($row['main_language_id'])->languageCode;
        $tagInfo->languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask((int) $row['language_mask']);

        return $tagInfo;
    }

    /**
     * Extracts a Tag object from $row.
     *
     * @param array $rows
     *
     * @return \Netgen\TagsBundle\SPI\Persistence\Tags\Tag[]
     */
    public function extractTagListFromRows(array $rows)
    {
        $tagList = [];
        foreach ($rows as $row) {
            $tagId = (int) $row['eztags_id'];
            if (!isset($tagList[$tagId])) {
                $tag = new Tag();
                $tag->id = (int) $row['eztags_id'];
                $tag->parentTagId = (int) $row['eztags_parent_id'];
                $tag->mainTagId = (int) $row['eztags_main_tag_id'];
                $tag->keywords = [];
                $tag->depth = (int) $row['eztags_depth'];
                $tag->pathString = $row['eztags_path_string'];
                $tag->modificationDate = (int) $row['eztags_modified'];
                $tag->priority = $row['eztags_priority'];
                $tag->remoteId = $row['eztags_remote_id'];
                $tag->alwaysAvailable = ((int) $row['eztags_language_mask'] & 1) ? true : false;
                $tag->mainLanguageCode = $this->languageHandler->load($row['eztags_main_language_id'])->languageCode;
                $tag->languageIds = $this->languageMaskGenerator->extractLanguageIdsFromMask((int) $row['eztags_language_mask']);
                $tagList[$tagId] = $tag;
            }

            if (!isset($tagList[$tagId]->keywords[$row['eztags_keyword_locale']])) {
                $tagList[$tagId]->keywords[$row['eztags_keyword_locale']] = $row['eztags_keyword_keyword'];
            }
        }

        return array_values($tagList);
    }
}
