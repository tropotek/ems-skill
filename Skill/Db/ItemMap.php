<?php
namespace Skill\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
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
            $this->dbMap->addPropertyMap(new Db\Integer('profileId', 'profile_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('categoryId', 'category_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('domainId', 'domain_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
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
            $this->formMap->addPropertyMap(new Form\Integer('profileId'));
            $this->formMap->addPropertyMap(new Form\Integer('categoryId'));
            $this->formMap->addPropertyMap(new Form\Integer('domainId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Boolean('publish'));
        }
        return $this->formMap;
    }

    /**
     * @param string $name
     * @param int $profileId
     * @param int $categoryId
     * @return null|Item|\Tk\Db\ModelInterface
     */
    public function findByName($name, $profileId, $categoryId = null) {
        $filter = array('profileId' => $profileId, 'name' => $name);
        if ($categoryId !== null) {
            $filter['categoryId'] = $categoryId;
        }
        return $this->findFiltered($filter)->current();
    }

    /**
     * Find filtered records
     *
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $from = sprintf('%s a ', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            //$w .= sprintf('a.description LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['profileId'])) {
            $where .= sprintf('a.profile_id = %s AND ', (int)$filter['profileId']);
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

        if (!empty($filter['name'])) {
            $where .= sprintf('a.name = %s AND ', $this->quote($filter['name']));
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


    /**
     * @param int $itemId
     * @param int $typeId
     * @return boolean
     */
    public function hasType($itemId, $typeId)
    {
        $sql = sprintf('SELECT * FROM item_has_type WHERE item_id = %d AND type_id = %d', (int)$itemId, (int)$typeId);
        return ($this->getDb()->query($sql)->rowCount() > 0);
    }

    /**
     * @param int $itemId
     * @param int $typeId
     */
    public function removeType($itemId, $typeId = null)
    {
        if ($typeId !== null) {
            $query = sprintf('DELETE FROM item_has_type WHERE item_id = %d AND type_id = %d', (int)$itemId, (int)$typeId);
        } else {
            $query = sprintf('DELETE FROM item_has_type WHERE item_id = %d ', (int)$itemId);
        }
        $this->getDb()->exec($query);
    }

    /**
     * @param int $itemId
     * @param int $typeId
     */
    public function addType($itemId, $typeId)
    {
        if ($this->hasType($itemId, $typeId)) return;
        $query = sprintf('INSERT INTO item_has_type (item_id, type_id)  VALUES (%d, %d) ', (int)$itemId, (int)$typeId);
        $this->getDb()->exec($query);
    }



}