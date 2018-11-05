<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Field;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\MalformatDataException;

class ManyToManyAssociationField extends Field implements AssociationInterface
{
    use AssociationTrait;

    /**
     * @var string
     */
    private $referenceDefinition;

    /**
     * @var string
     */
    private $mappingDefinition;

    /**
     * @var string
     */
    private $mappingLocalColumn;

    /**
     * @var string
     */
    private $mappingReferenceColumn;

    /**
     * @var string
     */
    private $sourceColumn;

    /**
     * @var string
     */
    private $referenceColumn;

    public function __construct(
        string $propertyName,
        string $referenceClass,
        string $mappingDefinition,
        bool $loadInBasic,
        string $mappingLocalColumn,
        string $mappingReferenceColumn,
        string $sourceColumn = 'id',
        string $referenceColumn = 'id'
    ) {
        parent::__construct($propertyName);
        $this->referenceDefinition = $referenceClass;
        $this->referenceClass = $mappingDefinition;
        $this->loadInBasic = $loadInBasic;
        $this->mappingDefinition = $mappingDefinition;
        $this->mappingLocalColumn = $mappingLocalColumn;
        $this->mappingReferenceColumn = $mappingReferenceColumn;
        $this->sourceColumn = $sourceColumn;
        $this->referenceColumn = $referenceColumn;
    }

    /**
     * @return string|EntityDefinition
     */
    public function getReferenceDefinition(): string
    {
        return $this->referenceDefinition;
    }

    /**
     * @return string|EntityDefinition
     */
    public function getMappingDefinition(): string
    {
        return $this->mappingDefinition;
    }

    public function getMappingLocalColumn(): string
    {
        return $this->mappingLocalColumn;
    }

    public function getMappingReferenceColumn(): string
    {
        return $this->mappingReferenceColumn;
    }

    public function getLocalField(): string
    {
        return $this->sourceColumn;
    }

    public function getReferenceField(): string
    {
        return $this->referenceColumn;
    }

    protected function invoke(EntityExistence $existence, KeyValuePair $data): \Generator
    {
        $key = $data->getKey();
        $value = $data->getValue();

        if (!\is_array($value)) {
            throw new MalformatDataException($this->path . '/' . $key, 'Value must be an array.');
        }

        $mappingAssociation = $this->getMappingAssociation();

        foreach ($value as $keyValue => $subresources) {
            $mapped = $subresources;
            if ($mappingAssociation) {
                $mapped = $this->map($mappingAssociation, $subresources);
            }

            if (!\is_array($mapped)) {
                throw new MalformatDataException($this->path . '/' . $key, 'Value must be an array.');
            }

            $this->writeResource->extract(
                $mapped,
                $this->referenceClass,
                $this->exceptionStack,
                $this->commandQueue,
                $this->writeContext,
                $this->fieldExtenderCollection,
                $this->path . '/' . $key . '/' . $keyValue
            );
        }

        return;
        yield __CLASS__ => __METHOD__;
    }

    private function getMappingAssociation(): ?ManyToOneAssociationField
    {
        $associations = $this->getReferenceClass()::getFields()->filterInstance(ManyToOneAssociationField::class);

        /** @var ManyToOneAssociationField $association */
        foreach ($associations as $association) {
            if ($association->getStorageName() === $this->getMappingReferenceColumn()) {
                return $association;
            }
        }

        return null;
    }

    private function map(ManyToOneAssociationField $association, $data): array
    {
        // not only foreign key provided? data is provided as insert or update command
        if (\count($data) > 1) {
            return [$association->getPropertyName() => $data];
        }

        // no id provided? data is provided as insert command (like create category in same request with the product)
        if (!isset($data[$association->getReferenceField()])) {
            return [$association->getPropertyName() => $data];
        }

        //only foreign key provided? entity should only be linked
        /*e.g
            [
                categories => [
                    ['id' => {id}],
                    ['id' => {id}]
                ]
            ]
        */
        /** @var ManyToOneAssociationField $association */
        $fk = $this->referenceClass::getFields()->getByStorageName(
            $association->getStorageName()
        );

        if (!$fk) {
            trigger_error(sprintf('Foreign key for association %s not found', $association->getPropertyName()));

            return [$association->getPropertyName() => $data];
        }

        /* @var FkField $fk */
        return [$fk->getPropertyName() => $data[$association->getReferenceField()]];
    }
}