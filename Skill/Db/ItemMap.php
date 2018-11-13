<?php
namespace Skill\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ItemMap extends \App\Db\Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->setTable('skill_item');
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('uid'));
            $this->dbMap->addPropertyMap(new Db\Integer('collectionId', 'collection_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('categoryId', 'category_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('domainId', 'domain_id'));
            $this->dbMap->addPropertyMap(new Db\Text('question'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Boolean('publish'));
            $this->dbMap->addPropertyMap(new Db\Integer('orderBy', 'order_by'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));
        }
        return $this->dbMap;
    }

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Integer('uid'));
            $this->formMap->addPropertyMap(new Form\Integer('collectionId'));
            $this->formMap->addPropertyMap(new Form\Integer('categoryId'));
            $this->formMap->addPropertyMap(new Form\Integer('domainId'));
            $this->formMap->addPropertyMap(new Form\Text('question'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Boolean('publish'));
        }
        return $this->formMap;
    }

    /**
     * Get a basic un weighted average of an entry
     *
     * @param int $userId
     * @param int $itemId
     * @param string $entryStatus
     * @param string $placementStatus
     * @return float
     * @throws \Tk\Db\Exception
     */
    public function findAverage($userId, $itemId, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $db = $this->getDb();

        $sql = <<<SQL
SELECT AVG(b.`value`) as 'avg'
FROM  skill_entry a LEFT JOIN skill_value b ON (a.id = b.entry_id) LEFT JOIN placement c ON (a.placement_id = c.id)
WHERE a.user_id = ? AND b.item_id = ? AND b.value > 0 AND a.`status` = ?
SQL;
        if ($placementStatus)
            $sql .= ' AND c.`status` = ?';

        $stmt = $db->prepare($sql);
        if ($placementStatus)
            $stmt->execute(array((int)$userId, (int)$itemId, $entryStatus, $placementStatus));
        else
            $stmt->execute(array((int)$userId, (int)$itemId, $entryStatus));

        $avg = 0.0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (isset($row['avg'])) {
                $avg = (float)$row['avg'];
            }
        }
        return $avg;
    }

    /**
     * Find filtered records
     *
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject|Item[]
     * @throws \Exception
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        if (!$tool) $tool = \Tk\Db\Tool::create('orderBy');
        $from = sprintf('%s a ', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.question LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.description LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }


        if (!empty($filter['uid'])) {
            $where .= sprintf('a.uid = %s AND ', $this->quote($filter['uid']));
        }
        if (!empty($filter['collectionId'])) {
            $where .= sprintf('a.collection_id = %s AND ', (int)$filter['collectionId']);
        }

        if (!empty($filter['categoryId'])) {
            $where .= sprintf('a.category_id = %s AND ', (int)$filter['categoryId']);
        }

        if (!empty($filter['typeId'])) {
            if (!is_array($filter['typeId'])) $filter['typeId'] = array($filter['typeId']);
            foreach ($filter['typeId'] as $i => $tid) {
                $a = 'b'.$i;
                $from .= "\n    " . sprintf('INNER JOIN %s %s ON (a.id = %s.item_id AND %s.type_id = %s ) ',
                        $this->quoteParameter('item_has_type'), $a, $a, $a, (int)$tid);
            }
        }

        if (!empty($filter['type']) && is_array($filter['type'])) {
            $i = 0;
            foreach ($filter['type'] as $typeGroup => $typeId) {
                $a = 'd' . $i;
                $w = $this->makeMultiQuery($typeId, $a.'.type_id', 'OR');
                $from .= "\n    " . sprintf('INNER JOIN %s %s ON (a.id = %s.item_id AND (%s)) ',
                        $this->quoteParameter('item_has_type'), $a, $a, $w);
                $i++;
            }
        }

        if (!empty($filter['domainId'])) {
            $w = $this->makeMultiQuery($filter['domainId'], 'a.domain_id', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['entryId'])) {
            $from .= sprintf(', %s c', $this->quoteParameter('skill_selected'));
            $where .= sprintf('a.id = c.item_id AND c.entry_id = %s AND ', (int)$filter['entryId']);
        }

        if (!empty($filter['question'])) {
            $where .= sprintf('a.question = %s AND ', $this->quote($filter['question']));
        }

        if (!empty($filter['publish'])) {
            $where .= sprintf('a.publish = %s AND ', (int)$filter['publish']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }
        
        
        $res = $this->selectFrom($from, $where, $tool);
        //vd($this->getDb()->getLastQuery());
        return $res;
    }




}