<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

use Doctrine\DBAL\Connection;

class DocumentRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_order_documents', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_order_documents', 'document');
        $this->addTableSelection($query, 's_order_documents', 'document');

        $query->leftJoin('document', 's_order_documents_attributes', 'attributes', 'document.id = attributes.documentID');
        $this->addTableSelection($query, 's_order_documents_attributes', 'attributes');

        $query->leftJoin('document', 's_core_documents', 'documenttype', 'document.type = documenttype.id');
        $this->addTableSelection($query, 's_core_documents', 'documenttype');

        $query->where('document.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('document.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @param string $documentHash
     *
     * @return bool|string
     */
    public function getOrderNumberByDocumentHash($documentHash)
    {
        return $this->connection->createQueryBuilder()
        ->select('docID')
        ->from('s_order_documents', 'document')
        ->where('document.hash = :hash')
        ->setParameter('hash', $documentHash)
        ->execute()
        ->fetchColumn();
    }
}
