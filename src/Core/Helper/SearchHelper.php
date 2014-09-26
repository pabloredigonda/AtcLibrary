<?php
namespace Core\Helper;

use Doctrine\ORM\Query\ResultSetMapping;

class SearchHelper
{
    /**
     * Parses a query to be used in a PostgreSQL fulltext search
     * @param $query  the query string (ie. The Cat is under the table)
     * @result (ie. 'the:* & cat:* & is:* & under:* & the:* & table:*'
     */
    public static function parseQuery($query) {
        
        $suffix = ':* & ';
        $query = strtolower($query);
        $query = preg_replace('/\s+/', ' ', trim($query));
        // FIXME delete utf8_encode when encoding problem was resolved
        $query = utf8_encode($query);
        $terms = explode(' ', $query);
        
        $query = '';
        foreach ($terms as $t) {
            $query .= $t.$suffix; 
        }
        return substr($query, 0, strlen($query) - 2);
    }
    
    /**
     * Returns the search language representation for a given country 
     * @param $lang the language ISO code  (ie. es)
     */
    public static function getLanguage($lang) {
        
        switch (strtolower($lang)) {
        	case 'es' :
        	    return 'spanish';
        	    break;
        	case 'en' :
        	    return 'english';
        	    break;
        	default :
        	    return 'spanish';
        } 
    } 

    private static function _buildQuery( $options, $params )
    {
        $sql = "
        	SELECT {$options['modelId']}, code, code_type, lang, name,
        		ts_rank_cd(to_tsvector('" . self::getLanguage($params['lang']) . "', lower(unaccent(p.name))) , query, 32) AS rank,
        		(
        		    CASE position(lower(unaccent(?)) in lower(unaccent(p.name)))
        		    WHEN 0 THEN 10000
             		    ELSE
             		    position(lower(unaccent(?)) in lower(unaccent(p.name)))
             		END) as pos
        
        	FROM {$options['tableName']} AS p , to_tsquery('" . self::getLanguage($params['lang']) . "', lower(unaccent(?))) as query
        	WHERE visible = TRUE AND textsearchable_index @@ query";
        
        if ($params['countryId']) {
            $sql .= " AND country_id = ?";
        } elseif ($params['lang']) {
            $sql .= " AND lang = ?";
        }
        
        $sql .= " ORDER BY pos ASC, rank DESC, name ASC";
        
        return $sql;
    }
    
    private static function _buildResultSetMapping( $options ) {
    
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($options['modelName'], 'p');
        $rsm->addFieldResult('p', $options['modelId'], 'id');
        $rsm->addFieldResult('p', 'code', 'code');
        $rsm->addFieldResult('p', 'code_type', 'codeType');
        $rsm->addFieldResult('p', 'lang', 'lang');
        $rsm->addFieldResult('p', 'name', 'name');
        
        return $rsm;
    }
    
    public static function getResuls( $em, $options, $params )
    {
        $params = array_combine(array('search', 'countryId', 'lang'), $params);
        
        $sql = self::_buildQuery($options, $params);
        
        $rsm = self::_buildResultSetMapping( $options );
        
        $query = $em->createNativeQuery($sql, $rsm);
        
        $query->setParameter(1, $params['search']);
        $query->setParameter(2, $params['search']);
        $query->setParameter(3, self::parseQuery($params['search']));
        
        if ($params['countryId']) {
            $query->setParameter(4, $params['countryId']);
        } elseif ($params['lang']) {
            $query->setParameter(4, $params['lang']);
        }
        
        return $query->getArrayResult();
    }
    
    
    
    
    
}

?>