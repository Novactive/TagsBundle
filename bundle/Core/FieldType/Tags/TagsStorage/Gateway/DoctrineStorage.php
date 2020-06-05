<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;
use PDO;

class DoctrineStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(Connection $connection, LanguageHandler $languageHandler)
    {
        $this->connection = $connection;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Stores the tags in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field)
    {
        foreach ($field->value->externalData as $priority => $tag) {
            $insertQuery = $this->connection->createQueryBuilder();
            $insertQuery
                ->insert($this->connection->quoteIdentifier('eztags_attribute_link'))
                ->values(
                    [
                        'keyword_id' => ':keyword_id',
                        'objectattribute_id' => ':objectattribute_id',
                        'objectattribute_version' => ':objectattribute_version',
                        'object_id' => ':object_id',
                        'priority' => ':priority',
                    ]
                )
                ->setParameter(':keyword_id', $tag['id'], PDO::PARAM_INT)
                ->setParameter(':objectattribute_id', $field->id, PDO::PARAM_INT)
                ->setParameter(':objectattribute_version', $versionInfo->versionNo, PDO::PARAM_INT)
                ->setParameter(':object_id', $versionInfo->contentInfo->id, PDO::PARAM_INT)
                ->setParameter(':priority', $priority, PDO::PARAM_INT);

            $insertQuery->execute();
        }
    }

    /**
     * Gets the tags stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field)
    {
        $field->value->externalData = $this->loadFieldData($field->id, $versionInfo->versionNo);
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->connection->quoteIdentifier('eztags_attribute_link'))
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('objectattribute_id', [':objectattribute_id']),
                    $query->expr()->eq('objectattribute_version', ':objectattribute_version')
                )
            )
            ->setParameter(':objectattribute_id', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':objectattribute_version', $versionInfo->versionNo, PDO::PARAM_INT);

        $query->execute();
    }

    /**
     * Returns the data for the given $fieldId and $versionNo.
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return array
     */
    protected function loadFieldData($fieldId, $versionNo)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                // Tag
                'DISTINCT t.id AS eztags_id',
                't.parent_id AS eztags_parent_id',
                't.main_tag_id AS eztags_main_tag_id',
                't.keyword AS eztags_keyword',
                't.depth AS eztags_depth',
                't.path_string AS eztags_path_string',
                't.modified AS eztags_modified',
                't.priority AS eztags_priority',
                't.remote_id AS eztags_remote_id',
                't.main_language_id AS eztags_main_language_id',
                't.language_mask AS eztags_language_mask',
                // Tag keywords
                'k.keyword AS eztags_keyword_keyword',
                'k.locale AS eztags_keyword_locale',
                // Tag attribute links
                'tal.priority AS eztags_attribute_link_priority'
            )
            ->from($this->connection->quoteIdentifier('eztags'), 't')
            ->innerJoin(
                't',
                $this->connection->quoteIdentifier('eztags_attribute_link'),
                'tal',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('t.id'),
                    $this->connection->quoteIdentifier('tal.keyword_id')
                )
            )
            ->innerJoin(
                't',
                $this->connection->quoteIdentifier('eztags_keyword'),
                'k',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('t.id'),
                    $this->connection->quoteIdentifier('k.keyword_id')
                )
            )->where(
                $query->expr()->andX(
                    $query->expr()->eq('tal.objectattribute_id', ':objectattribute_id'),
                    $query->expr()->eq('tal.objectattribute_version', ':objectattribute_version')
                )
            )
            ->setParameter(':objectattribute_id', $fieldId, PDO::PARAM_INT)
            ->setParameter(':objectattribute_version', $versionNo, PDO::PARAM_INT)
            ->orderBy($this->connection->quoteIdentifier('tal.priority'), 'ASC');

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $tagList = [];
        foreach ($rows as $row) {
            $tagId = (int) $row['eztags_id'];
            if (!isset($tagList[$tagId])) {
                $tagList[$tagId] = [];
                $tagList[$tagId]['id'] = (int) $row['eztags_id'];
                $tagList[$tagId]['parent_id'] = (int) $row['eztags_parent_id'];
                $tagList[$tagId]['main_tag_id'] = (int) $row['eztags_main_tag_id'];
                $tagList[$tagId]['keywords'] = [];
                $tagList[$tagId]['depth'] = (int) $row['eztags_depth'];
                $tagList[$tagId]['path_string'] = $row['eztags_path_string'];
                $tagList[$tagId]['modified'] = (int) $row['eztags_modified'];
                $tagList[$tagId]['remote_id'] = $row['eztags_remote_id'];
                $tagList[$tagId]['priority'] = $row['eztags_priority'];
                $tagList[$tagId]['always_available'] = ((int) $row['eztags_language_mask'] & 1) ? true : false;
                $tagList[$tagId]['main_language_code'] = $this->languageHandler->load($row['eztags_main_language_id'])->languageCode;
                $tagList[$tagId]['language_codes'] = [];
            }

            if (!isset($tagList[$tagId]['keywords'][$row['eztags_keyword_locale']])) {
                $tagList[$tagId]['keywords'][$row['eztags_keyword_locale']] = $row['eztags_keyword_keyword'];
            }

            if (!in_array($row['eztags_keyword_locale'], $tagList[$tagId]['language_codes'], true)) {
                $tagList[$tagId]['language_codes'][] = $row['eztags_keyword_locale'];
            }
        }

        return array_values($tagList);
    }
}
