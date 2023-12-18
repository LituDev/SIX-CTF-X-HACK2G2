<?php

namespace App\Entity\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\IdGenerator;
use Doctrine\ORM\Exception\EntityMissingAssignedId;
use Ramsey\Uuid\UuidInterface;

class AssignedGenerator implements IdGenerator
{
    public function generate(DocumentManager $dm, object $document): UuidInterface
    {
        $class      = $dm->getClassMetadata(get_class($document));
        $idFields   = $class->getIdentifierFieldNames();
        $identifier = [];

        foreach ($idFields as $idField) {
            $value = $class->getFieldValue($entity, $idField);

            if (! isset($value)) {
                throw EntityMissingAssignedId::forField($entity, $idField);
            }

            if (isset($class->associationMappings[$idField])) {
                // NOTE: Single Columns as associated identifiers only allowed - this constraint it is enforced.
                $value = $em->getUnitOfWork()->getSingleIdentifierValue($value);
            }

            $identifier[$idField] = $value;
        }

        return $identifier;
    }
}
