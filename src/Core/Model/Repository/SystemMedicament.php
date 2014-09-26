<?php
namespace Core\Model\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Core\Helper\SearchHelper;

/**
 * SystemMedicament
 */
class SystemMedicament extends AbstractRepository
{

    public function search($search, $countryId = null, $lang = null)
    {
        $params = func_get_args();
         
        $options = array(
            'tableName'	=> 'system_medicament',
            'modelName'	=> 'Core\Model\SystemMedicament',
            'modelId'	=> 'system_medicament_id'
        );
        
        return SearchHelper::getResuls(
            $this->getEntityManager(), $options, $params
        );
    }
    
    public function searchLaboratory($search, $countryId = null, $lang = null)
    {
        $subSql = "SELECT DISTINCT laboratory, laboratory_searchable_index FROM system_medicament AS p";
        
        if ($countryId) {
            $subSql .= " WHERE country_id = ?";
        } elseif ($lang) {
            $subSql .= " WHERE lang = ?";
        }
        
        $sql = "
            SELECT DISTINCT laboratory AS id, laboratory AS name, ts_rank_cd(to_tsvector('" . SearchHelper::getLanguage($lang) . "', unaccent(p.laboratory)) , query, 32) AS rank
            FROM ({$subSql}) AS p , to_tsquery('" . SearchHelper::getLanguage($lang) . "', unaccent(?)) as query
            WHERE laboratory_searchable_index @@ query
            ORDER BY rank DESC";
        
        $sql .= "";
    
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
    
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
    
        if ($countryId) {
            $query->setParameter(1, $countryId);
        } elseif ($lang) {
            $query->setParameter(1, $lang);
        }
    
        $search = SearchHelper::parseQuery($search);
        $query->setParameter(2, $search);
        
        
        return $query->getResult();
    }
}