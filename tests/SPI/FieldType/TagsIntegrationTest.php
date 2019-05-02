<?php

namespace Netgen\TagsBundle\Tests\SPI\FieldType;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Tests\FieldType\BaseIntegrationTest;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway\DoctrineStorage as TagsDoctrineStorage;
use Netgen\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags as TagsConverter;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

/**
 * Integration test for legacy storage field types.
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class TagsIntegrationTest extends BaseIntegrationTest
{
    /**
     * Property indicating whether the DB already has been set up.
     *
     * @var bool
     */
    protected static $tagsSetUp = false;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * Only set up once for these read only tests on a large fixture.
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    public function setUp()
    {
        parent::setUp();

        if (!self::$tagsSetUp) {
            $schema = __DIR__ . '/../../_fixtures/schema/schema.' . $this->handler->getName() . '.sql';

            $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($schema)));
            foreach ($queries as $query) {
                $this->handler->exec($query);
            }

            $this->insertDatabaseFixture(__DIR__ . '/../../_fixtures/tags_tree.php');
            self::$tagsSetUp = $this->handler;
        }
    }

    /**
     * Reset DB sequences.
     */
    public function resetSequences()
    {
        parent::resetSequences();

        switch ($this->handler->getName()) {
            case 'pgsql':
                // Update PostgreSQL sequences
                $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents(__DIR__ . '/../../_fixtures/schema/setval.pgsql.sql')));
                foreach ($queries as $query) {
                    $this->handler->exec($query);
                }

                break;
        }
    }

    /**
     * Returns the identifier of the FieldType under test.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'eztags';
    }

    /**
     * Returns the Handler with all necessary objects registered.
     *
     * Returns an instance of the Persistence Handler where the
     * FieldType\Storage has been registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getCustomHandler()
    {
        $this->tagsService = $this->createMock(TagsService::class);

        $fieldType = new TagsType($this->tagsService);

        $fieldType->setTransformationProcessor($this->getTransformationProcessor());
        $fieldType->setEditViews(
            [
                'default' => ['identifier' => 'Default'],
                'select' => ['identifier' => 'Select'],
                'tree' => ['identifier' => 'Tree'],
            ]
        );

        return $this->getHandler(
            'eztags',
            $fieldType,
            new TagsConverter(),
            new TagsStorage(
                new TagsDoctrineStorage(
                    $this->handler->getConnection(),
                    (new LanguageHandlerMock())($this)
                ),
                $this->tagsService
            )
        );
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new FieldTypeConstraints();
    }

    /**
     * Returns the field definition data expected after loading the newly
     * created field definition with the FieldType under test.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'hideRootTag' => false,
                'editView' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
            ]
        );

        $fieldTypeConstraints->validators = [
            'TagsValueValidator' => [
                'subTreeLimit' => 0,
                'maxTags' => 0,
            ],
        ];

        return [
            // The eztags field type does not have any special field definition properties
            ['fieldType', 'eztags'],
            ['fieldTypeConstraints', $fieldTypeConstraints],
        ];
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => [
                    $this->getTagHash1(),
                ],
                'sortKey' => null,
            ]
        );
    }

    /**
     * Asserts that the loaded field data is correct.
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect(Field $field)
    {
        self::assertSame(
            $this->getInitialValue()->externalData,
            $field->value->externalData
        );

        self::assertNull($field->value->data);
        self::assertNull($field->value->sortKey);
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => [
                    $this->getTagHash1(),
                    $this->getTagHash2(),
                ],
                'sortKey' => null,
            ]
        );
    }

    /**
     * Asserts that the updated field data is loaded correct.
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     */
    public function assertUpdatedFieldDataCorrect(Field $field)
    {
        self::assertSame(
            $this->getUpdatedValue()->externalData,
            $field->value->externalData
        );

        self::assertNull($field->value->data);
        self::assertNull($field->value->sortKey);
    }

    /**
     * Returns a hash version of tag for tests.
     *
     * @return array
     */
    protected function getTagHash1()
    {
        return [
            'id' => 40,
            'parent_id' => 7,
            'main_tag_id' => 0,
            'keywords' => ['eng-GB' => 'eztags'],
            'depth' => 3,
            'path_string' => '/8/7/40/',
            'modified' => 1308153110,
            'remote_id' => '182be0c5cdcd5072bb1864cdee4d3d6e',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => ['eng-GB'],
        ];
    }

    /**
     * Returns a hash version of tag for tests.
     *
     * @return array
     */
    protected function getTagHash2()
    {
        return [
            'id' => 8,
            'parent_id' => 0,
            'main_tag_id' => 0,
            'keywords' => ['eng-GB' => 'ez publish'],
            'depth' => 1,
            'path_string' => '/8/',
            'modified' => 1343169159,
            'remote_id' => 'eccbc87e4b5ce2fe28308fd9f2a7baf3',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => ['eng-GB'],
        ];
    }
}
