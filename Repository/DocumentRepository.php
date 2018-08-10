<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Repository;

class DocumentRepository extends AbstractRepository
{
    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_order_documents', 'document');
        $this->addTableSelection($query, 's_order_documents', 'document');

        $query->leftJoin('document', 's_order_documents_attributes', 'attributes', 'document.id = attributes.documentID');
        $this->addTableSelection($query, 's_order_documents_attributes', 'attributes');

        $query->leftJoin('document', 's_core_documents', 'documenttype', 'document.type = documenttype.id');
        $this->addTableSelection($query, 's_core_documents', 'documenttype');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

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
