<?php

namespace SwagMigrationApi\Repository;

use SwagMigrationApi\Struct\ProductResult;

class Products extends AbstractRepository
{
    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 500)
    {
//        return $this->getConnection()->createQueryBuilder()
//            ->select('"product", product.*')
//            ->from('s_articles', 'product')
//            ->execute()
//            ->fetchAll(\PDO::FETCH_GROUP)
//        ;
//        $sql = <<<SQL
//    SHOW COLUMNS FROM s_articles
//SQL;-
//
//        $columns = array_keys($this->getConnection()->query($sql)->fetchAll(\PDO::FETCH_GROUP));
//        $selection = '';
//
//        foreach ($columns as $column) {
//            $selection.= 'product'$column
//        }
        $sql = <<<SQL
    SELECT *
FROM s_articles
JOIN s_articles_details on s_articles.id = s_articles_details.articleID
SQL;

        echo '<pre>';
        var_dump($this->getConnection()->query($sql)->fetch());
        die;


    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProductDetails($offset = 0, $limit = 500)
    {
        return $this->getConnection()->createQueryBuilder()
            ->select('"product_detail", product_detail.*')
            ->from('s_articles_details', 'product_detail')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP)
        ;
    }

    public function getProductAttributes()
    {

    }
}