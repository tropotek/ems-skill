<?php
namespace Skill\Db;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class ReportingMap extends \App\Db\Mapper
{



    /**
     * Mapper constructor.
     *
     * @param \Tk\Db\Pdo|null $db
     * @throws \Exception
     * @throws \Tk\Db\Exception
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->setMarkDeleted('');           // Default to have a del field (This will only mark the record deleted)
        $this->setAlias('');
    }


    /**
     * Find filtered records
     *
     * @param array $filter
     * @param \Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|Item[]
     * @throws \Exception
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        if (!$tool) $tool = \Tk\Db\Tool::create();

        $select = <<<SQL
sc.id as 'collection_id', sc.name as 'collection_name',

se.id as 'entry_id', se.title as 'entry_title',

si.id as 'item_id', si.uid as 'item_uid', si.question as 'item_question',

cat.id as 'category_id', cat.name as 'category_name',

sd.id as 'domain_id', sd.name as 'domain_name',

s.id as 'subject_id', s.name as 'subject_name',

u.id as 'user_id', u.name as 'user_name', u.uid as 'user_uid', u.email as 'user_email',

p.id as 'placement_id', p.date_start as 'placement_dateStart', p.date_end as 'placement_dateEnd', CONCAT(u.name, '@', c.name) as 'placement_title',

pt.name as 'placement_type_name', 

c.id as 'company_id', c.name as 'company_name',

sup.id as 'supervisor_id', sup.name as 'supervisor_name',

IFNULL(sv.value, '0') as 'item_value'

SQL;

        $from = <<<SQL
          `skill_collection` sc
LEFT JOIN skill_entry se ON (sc.id = se.collection_id)
LEFT JOIN `subject` s ON (sc.subject_id = s.id)
LEFT JOIN `user` u ON (se.user_id = u.id)
LEFT JOIN `placement` p ON (se.placement_id = p.id)
LEFT JOIN `placement_type` pt ON (p.placement_type_id = pt.id)
LEFT JOIN `company` c ON (p.`company_id` = c.id)
LEFT JOIN `supervisor` sup ON (p.supervisor_id = sup.id)

LEFT JOIN `skill_item` si ON (sc.id = si.collection_id)
LEFT JOIN `skill_category` cat ON (si.category_id = cat.id)
LEFT JOIN `skill_domain` sd ON (si.domain_id = sd.id)

LEFT JOIN `skill_value` sv ON (se.id = sv.entry_id AND si.id = sv.item_id)

SQL;

        $where = <<<SQL
se.id IS NOT NULL AND 
SQL;



        if (!empty($filter['collectionId'])) {
            $where .= sprintf('sc.uid = %s AND ', (int)$filter['collectionId']);
        }

        if (!empty($filter['itemId'])) {
            $w = $this->makeMultiQuery($filter['itemId'], 'si.uid', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['categoryId'])) {
            $w = $this->makeMultiQuery($filter['categoryId'], 'cat.uid', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['domainId'])) {
            $w = $this->makeMultiQuery($filter['domainId'], 'sd.uid', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['scaleId'])) {
            $w = $this->makeMultiQuery($filter['scaleId'], 'sv.value', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }




        // Include zero values in the results
        if (!empty($filter['excludeZero'])) {
            $where .= sprintf('sv.value IS NOT NULL AND sv.value > 0 ');
        }

        if (!empty($filter['subjectId'])) {
            $w = $this->makeMultiQuery($filter['subjectId'], 's.id', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['companyId'])) {
            $w = $this->makeMultiQuery($filter['companyId'], 'c.id', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if (!empty($filter['supervisorId'])) {
            $w = $this->makeMultiQuery($filter['supervisorId'], 'sup.id', 'OR');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }


        if (!empty($filter['dateStart']) && !empty($filter['dateEnd'])) {     // Contains
            /** @var \DateTime $dateStart */
            $dateStart = \Tk\Date::floor(\Tk\Date::createFormDate($filter['dateStart']));
            /** @var \DateTime $dateEnd */
            $dateEnd = \Tk\Date::floor(\Tk\Date::createFormDate($filter['dateEnd']));

            $where .= sprintf('((p.date_start >= %s AND ', $this->quote($dateStart->format(\Tk\Date::FORMAT_ISO_DATETIME)) );
            $where .= sprintf('p.date_start <= %s) OR ', $this->quote($dateEnd->format(\Tk\Date::FORMAT_ISO_DATETIME)) );

            $where .= sprintf('(p.date_end <= %s AND ', $this->quote($dateStart->format(\Tk\Date::FORMAT_ISO_DATETIME)) );
            $where .= sprintf('p.date_end >= %s)) AND ', $this->quote($dateEnd->format(\Tk\Date::FORMAT_ISO_DATETIME)) );

        } else if (!empty($filter['dateStart'])) {
            /** @var \DateTime $date */
            $date = \Tk\Date::floor(\Tk\Date::createFormDate($filter['dateStart']));
            $where .= sprintf('p.date_start >= %s AND ', $this->quote($date->format(\Tk\Date::FORMAT_ISO_DATETIME)) );
        } else if (!empty($filter['dateEnd'])) {
            /** @var \DateTime $date */
            $date = \Tk\Date::floor(\Tk\Date::createFormDate($filter['dateEnd']));
            $where .= sprintf('p.date_end <= %s AND ', $this->quote($date->format(\Tk\Date::FORMAT_ISO_DATETIME)) );
        }


        if ($where) {
            $where = substr($where, 0, -4);
        }

        $res = $this->selectFrom($from, $where, $tool, $select);
        //vd($this->getDb()->getLastQuery());
        return $res;
    }






























    /**
     *
     * @param $collectionId
     * @param $subjectId
     * @param bool $valueOnly
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findSubjectAverages($collectionId, $subjectId, $valueOnly = false, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        // Faster query
        $sql = <<<SQL
SELECT * 
FROM (
    SELECT a.domain_id, a.label, c.scale, a.weight, SUM(a.average)/b.count as 'avg', (SUM(a.average)/b.count)*a.weight as 'weighted_avg'
    FROM
      (
        SELECT c.id as 'item_id', a.collection_id, a.id as 'entry_id', d.id as 'domain_id', 
            d.label, c.question, ROUND(AVG(b.value), 2) as 'average', d.order_by, d.weight
        FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id), skill_value b, skill_item c, skill_domain d
        WHERE a.del = 0 AND c.del = 0 AND d.del = 0 AND
            a.id = b.entry_id AND b.value > 0 AND
            a.collection_id = ? AND 
            a.subject_id = ? AND 
            a.status = '$entryStatus' AND
              (p.status IS NULL OR p.status = '$placementStatus') AND 
            b.item_id = c.id AND
            c.domain_id = d.id
        GROUP BY b.item_id
        ORDER BY d.order_by, c.order_by
      ) a,
      (
        SELECT a.domain_id, COUNT(a.id) as 'count'
        FROM skill_item a
        GROUP BY a.domain_id
      ) b,
      (
        SELECT a.collection_id, COUNT(a.id)-1 as 'scale'
        FROM skill_scale a
        GROUP BY a.collection_id
      ) c
    WHERE
      a.domain_id = b.domain_id AND
      c.collection_id = a.collection_id
    GROUP BY a.domain_id
    ORDER BY a.order_by ) a

SQL;

        $stm = $this->getDb()->prepare($sql);
        $stm->bindParam(1, $collectionId);
        $stm->bindParam(2, $subjectId);

        $stm->execute();
        $arr = $stm->fetchAll();
        if ($valueOnly) {
            $arr1 = array();
            foreach ($arr as $obj) {
                $arr1[$obj->domain_id] = $obj->avg;
            }
            $arr = $arr1;
        }
        return $arr;
    }


    /**
     *
     *
     * @param $collectionId
     * @param $subjectId
     * @param int $userId
     * @param bool $valueOnly
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array|int
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findStudentResult($collectionId, $subjectId, $userId = 0, $valueOnly = false, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $usql = '';
        if ($userId) {
            $usql = ' AND a.user_id = ? ';
        }

        $sql = <<<SQL
SELECT a.collection_id, a.user_id, a.subject_id, SUM(a.weighted_avg) / a.scale AS 'subject_result'
  FROM
    (SELECT a.collection_id, a.user_id, a.subject_id, a.domain_id, a.label, c.scale, a.weight,
      SUM(a.average) / b.count AS 'avg', (SUM(a.average) / b.count) * a.weight AS 'weighted_avg'
    FROM
      (
        SELECT a.collection_id, a.subject_id, a.user_id, c.id AS 'item_id', a.id AS 'entry_id', d.id AS 'domain_id',
          d.label, c.question, ROUND(AVG(b.value), 2) AS 'average', d.order_by, d.weight
        FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id), skill_value b, skill_item c, skill_domain d
        WHERE
          a.del = 0 AND c.del = 0 AND d.del = 0 AND
              a.id = b.entry_id AND b.value > 0 AND
              a.status = '$entryStatus' AND
              (p.status IS NULL OR p.status = '$placementStatus') AND 
              b.item_id = c.id AND
              c.domain_id = d.id
        GROUP BY a.collection_id, a.subject_id, a.user_id, b.item_id
        ORDER BY d.order_by, c.order_by
      ) a,
      (
        SELECT a.domain_id, COUNT(a.id) AS 'count'
        FROM skill_item a
        GROUP BY a.domain_id
      ) b,
      (
        SELECT a.collection_id, COUNT(a.id) - 1 AS 'scale'
        FROM skill_scale a
        GROUP BY a.collection_id
      ) c
    WHERE
      a.domain_id = b.domain_id AND
          c.collection_id = a.collection_id

    GROUP BY a.collection_id, a.subject_id, a.user_id, a.domain_id
    ORDER BY a.order_by
    ) a
  WHERE
    a.collection_id = ? AND a.subject_id = ? $usql
  GROUP BY a.collection_id, a.subject_id, a.user_id
SQL;

        $stm = $this->getDb()->prepare($sql);
        $stm->bindParam(1, $collectionId);
        $stm->bindParam(2, $subjectId);
        if ($usql)
            $stm->bindParam(3, $userId);

        $stm->execute();
        $arr = $stm->fetchAll();
        if ($valueOnly) {
            if (!count($arr) && $userId) return 0;
            $arr1 = array();
            foreach ($arr as $obj) {
                if ($userId) return $obj->subject_result;
                $arr1[$obj->user_id] = $obj->subject_result;
            }
            $arr = $arr1;
        }
        return $arr;
    }


    /**
     * @param $collectionId
     * @param $subjectId
     * @param int $userId
     * @param bool $valueOnly
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array|int
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findStudentResultNew($collectionId, $subjectId, $userId = 0, $valueOnly = false, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $usql = '';
        if ($userId) {
            $usql = ' AND a.user_id = ? ';
        }

        $sql = <<<SQL
SELECT a.collection_id, a.user_id, a.subject_id, SUM(a.weighted_avg) / a.scale_count AS 'subject_result'
  FROM
    (SELECT a.collection_id, a.user_id, a.subject_id, a.domain_id, a.label, a.weight, a.domain_count, a.scale_count,
      SUM(a.average) / a.domain_count AS 'avg',
      (SUM(a.average) / a.domain_count) * a.weight AS 'weighted_avg'
    FROM
      (
        SELECT a.collection_id, a.subject_id, a.user_id, c.id AS 'item_id', a.id AS 'entry_id', d.id AS 'domain_id', d.order_by, d.weight,
          d.label, c.question, ROUND(AVG(b.value), 2) AS 'average', e.domain_count, f.scale_count

        FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id),
            skill_value b, skill_item c, skill_domain d,
          (
            SELECT a.id, COUNT(a.id) AS 'domain_count'
            FROM skill_domain a
            WHERE a.collection_id = $collectionId
            GROUP BY a.id
          ) e,
          (
            SELECT a.collection_id, COUNT(a.id) - 1 AS 'scale_count'
            FROM skill_scale a
            WHERE a.collection_id = $collectionId
            GROUP BY a.collection_id
          ) f
        WHERE
          a.del = 0 AND c.del = 0 AND d.del = 0 AND
              a.id = b.entry_id AND b.value > 0 AND
              a.status = '$entryStatus' AND
              (p.status IS NULL OR p.status = '$placementStatus') AND
              b.item_id = c.id AND
              c.domain_id = d.id
        GROUP BY a.collection_id, a.subject_id, a.user_id, b.item_id
        ORDER BY d.order_by, c.order_by
      ) a
    WHERE 1

    GROUP BY a.collection_id, a.subject_id, a.user_id, a.domain_id
    ORDER BY a.order_by
    ) a
  WHERE
    a.collection_id = ? AND a.subject_id = ? $usql
  GROUP BY a.collection_id, a.subject_id, a.user_id
SQL;

        $stm = $this->getDb()->prepare($sql);
        $stm->bindParam(1, $collectionId);
        $stm->bindParam(2, $subjectId);
        if ($usql)
            $stm->bindParam(3, $userId);

        $stm->execute();
        $arr = $stm->fetchAll();
        if ($valueOnly) {
            if (!count($arr) && $userId) return 0;
            $arr1 = array();
            foreach ($arr as $obj) {
                if ($userId) return $obj->subject_result;
                $arr1[$obj->user_id] = $obj->subject_result;
            }
            $arr = $arr1;
        }
        return $arr;
    }

    /**
     * Find the student results by brake-down of domains
     * Find the average and total results for the student
     *
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findStudentResults($filter, $tool = null, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $collectionId = (int)$filter['collectionId'];
        $subjectId = (int)$filter['subjectId'];

        $usql = '';
        if (!empty($filter['userId'])) {
            $usql = ' a.user_id = ' . (int)$filter['userId'] . ' AND ';
        }

        // Query
        $toolStr = '';
        if ($tool) {
            $toolStr = $tool->toSql();
        }

        $filterStr = '';
        if (!empty($filter['uid']))
            $filterStr = 'a.uid = ' . $this->getDb()->quote($filter['uid']) . ' AND ';


        /*
         * See: https://stackoverflow.com/questions/17964078/mysql-query-to-dynamically-convert-rows-to-columns-on-the-basis-of-two-columns
         */
        $sql = <<<SQL
SELECT a.collection_id, a.user_id, a.domain_id, a.label, a.label_name, b.weight, a.name, a.uid, a.entry_count, a.domain_count, c.max_grade,
  ROUND(AVG(a.avg_zero), 2) as 'avg_zero',
  (ROUND(AVG(a.avg_zero), 2)*(c.max_grade/d.scale)) as 'grade_zero',
  
  ROUND(AVG(a.avg), 2) as 'avg',
  (ROUND(AVG(a.avg), 2)*(c.max_grade/d.scale)) as 'grade'
FROM
  (
    SELECT a.collection_id, a.subject_id, a.user_id, d.domain_id, a.weight, c.item_id, d.question, a.domain_count,
           IFNULL(ROUND(AVG(NULLIF(c.value, 0)), 2), 0) AS 'avg',   -- Standard Avg 
           IFNULL(ROUND(AVG(c.value), 2), 0) AS 'avg_zero',         -- Avg with zero values
           d.order_by, a.label, a.label_name, a.name, a.uid, COUNT(a.entry_id) as 'entry_count'
    FROM
      (
        SELECT a.collection_id, a.subject_id, a.user_id, a.entry_id, b.domain_id, c.id as 'item_id', c.name as 'label_name', 
               c.label, b.question, b.order_by, a.name, a.uid, c.weight, a.domain_count
        FROM
          (
            SELECT a.collection_id, a.subject_id, a.user_id, b.id as 'entry_id', a.name, a.uid, e.domain_count
            FROM
              (
                SELECT $collectionId as 'collection_id', b.subject_id, a.id as 'user_id', a.name, a.uid
                FROM user a, subject_has_student b
                WHERE a.id = b.user_id AND  a.del = 0
                      -- AND a.id = 1494           # user id
                      AND b.subject_id = $subjectId      # subject id
                GROUP BY a.id, b.subject_id
                ORDER BY b.subject_id
              ) a,
              (
                SELECT a.collection_id, COUNT(a.id)-1 as 'domain_count'
                FROM skill_domain a
                WHERE a.collection_id = $collectionId
              ) e,
              skill_entry b LEFT JOIN placement p ON (b.placement_id = p.id)
            WHERE
              b.del = 0 AND
              a.user_id = b.user_id AND
              b.status = '$entryStatus' AND
              (p.status IS NULL OR p.status = '$placementStatus') AND 
              b.collection_id = a.collection_id AND
              b.subject_id = a.subject_id AND
              b.user_id = a.user_id
          ) a,
          skill_item b, skill_domain c
        WHERE
          b.del = 0 AND c.del = 0 AND
          c.active = 1 AND
          a.collection_id = b.collection_id AND
          b.domain_id = c.id
        ORDER BY a.user_id, b.order_by
      ) a,
      skill_value c, skill_item d
    WHERE
      d.del = 0 AND
      a.entry_id = c.entry_id AND
      c.item_id = d.id AND
      a.domain_id = d.domain_id
    -- AND a.label = 'CS'
    -- AND c.value > 0
    GROUP BY a.user_id, c.item_id
    ORDER BY a.user_id, d.order_by
  ) a,
  skill_domain b,
  skill_collection c,
  (
    SELECT a.collection_id, COUNT(a.id)-1 as 'scale'
    FROM skill_scale a
    GROUP BY a.collection_id
  ) d

WHERE a.domain_id = b.id AND
    $filterStr
    c.id = a.collection_id AND
    $usql      -- AND a.user_id = 1494           # user id
    d.collection_id = a.collection_id
    

GROUP BY a.user_id, b.id

SQL;
        $sql .= $toolStr;

/* returns:
1	1494	1	PD	Personal And Professional Development	0.05	10.00	4.45	8.9	Aaron Adno	637920
1	1494	3	SB	Scientific Basis Of Clinical Practice	0.2	10.00	4.15	8.3	Aaron Adno	637920
1	1494	4	CS	Clinical Skills	0.5	10.00	2.29	4.58	Aaron Adno	637920
1	1494	6	AW	Ethics And Animal Welfare	0.2	10.00	2.29	4.58	Aaron Adno	637920
1	1494	7	BIOS	Biosecurity And Population Health	0.05	10.00	4	8	Aaron Adno	637920
*/
/* We Want
Student Number	Name	PD	SB	CS	AW	BIOS	PD Grade	SB Grade	CS Grade	AW Grade	BIOS Grade	Total 100%
637920	Aaron Adno	4.45	4.15	2.29	2.29	4.00	8.90	8.30	4.58	4.57	8.00	57.11
*/

        $stm = $this->getDb()->prepare($sql);
        $stm->execute();

        $arr = array();
        $total = 0;
        $totalGrade = 0;
        foreach ($stm as $i => $row) {
            if (!array_key_exists($row->user_id, $arr)) {
                $arr[$row->user_id] = array(
                    'userId' => $row->user_id,
                    'uid' => $row->uid,
                    'name' => $row->name,
                    'maxGrade' => $row->max_grade,
                    'domain_count' => $row->domain_count,
                    'total' => 0,
                    'total_grade' => 0,
                    'total_zero' => 0,
                    'total_grade_zero' => 0
                );
            }
            $arr[$row->user_id][$row->label] = $row->avg;
            $arr[$row->user_id][$row->label.'_grade'] = $row->grade;
            $arr[$row->user_id][$row->label.'_weight'] = $row->weight;
            $arr[$row->user_id]['total'] += $row->avg;
            $arr[$row->user_id]['total_grade'] += $row->grade;
            $arr[$row->user_id]['total_zero'] += $row->avg_zero;
            $arr[$row->user_id]['total_grade_zero'] += $row->grade_zero;
        }
        // Not Correct (Try to fix this)
        foreach ($arr as $uid => $row) {
            $arr[$uid]['total'] = round($row['total'] / $row['domain_count'], 2);
            $arr[$uid]['total_grade'] = round($row['total_grade'] / $row['domain_count'], 2);
            $arr[$uid]['total_zero'] = round($row['total_zero'] / $row['domain_count'], 2);
            $arr[$uid]['total_grade_zero'] = round($row['total_grade_zero'] / $row['domain_count'], 2);
        }

        return $arr;
    }


    /**
     *
     * @param $collectionId
     * @param $subjectId
     * @param int $userId
     * @param bool $valueOnly
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findDomainAverages($collectionId, $subjectId, $userId = 0, $valueOnly = false, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $usql = '';
        if ($userId) {
            $usql = ' a.user_id = ? AND ';
        }
        
        $sql = <<<SQL
SELECT a.domain_id, a.label, c.scale, a.weight, SUM(a.average)/b.count AS 'avg', a.order_by
FROM
  (
    SELECT a.collection_id, a.subject_id, a.user_id, c.id AS 'item_id', a.id AS 'entry_id', d.id AS 'domain_id', d.label, c.question,
      ROUND(AVG(b.value), 2) AS 'average', d.order_by, d.weight
    FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id), skill_value b, skill_item c, skill_domain d
    WHERE
      a.del = 0 AND c.del = 0 AND d.del = 0 AND
          a.id = b.entry_id AND b.value > 0 AND
          a.status = '$entryStatus' AND
          (p.status IS NULL OR p.status = '$placementStatus') AND 
          b.item_id = c.id AND
          c.domain_id = d.id
    GROUP BY a.collection_id, a.subject_id, a.user_id, b.item_id
    ORDER BY d.order_by, c.order_by
  ) a,
  (
    SELECT a.domain_id, COUNT(a.id) AS 'count'
    FROM skill_item a
    GROUP BY a.domain_id
  ) b,
  (
    SELECT a.collection_id, COUNT(a.id)-1 as 'scale'
    FROM skill_scale a
    GROUP BY a.collection_id
  ) c
WHERE
  a.collection_id = ? AND 
  a.subject_id = ? AND 
  $usql 
  a.domain_id = b.domain_id AND
  c.collection_id = a.collection_id

GROUP BY a.domain_id
ORDER BY a.order_by;
SQL;
        
        // Faster query
        $sql = <<<SQL
SELECT a.domain_id, a.label, c.scale, a.weight, SUM(a.average)/b.count as 'avg', (SUM(a.average)/b.count)*a.weight as 'weighted_avg'
FROM
  (
    SELECT c.id as 'item_id', a.collection_id, a.id as 'entry_id', d.id as 'domain_id', d.label, c.question, ROUND(AVG(b.value), 2) as 'average', d.order_by, d.weight
    FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id), skill_value b, skill_item c, skill_domain d
    WHERE a.del = 0 AND c.del = 0 AND d.del = 0 AND
        a.id = b.entry_id AND b.value > 0 AND
        a.status = '$entryStatus' AND
        (p.status IS NULL OR p.status = '$placementStatus') AND 
        a.collection_id = ? AND 
        a.subject_id = ? AND 
        $usql
        b.item_id = c.id AND
        c.domain_id = d.id
    GROUP BY b.item_id
    ORDER BY d.order_by, c.order_by
  ) a,
  (
    SELECT a.domain_id, COUNT(a.id) as 'count'
    FROM skill_item a
    GROUP BY a.domain_id
  ) b,
  (
    SELECT a.collection_id, COUNT(a.id)-1 as 'scale'
    FROM skill_scale a
    GROUP BY a.collection_id
  ) c
WHERE
  a.domain_id = b.domain_id AND
  c.collection_id = a.collection_id
GROUP BY a.domain_id
ORDER BY a.order_by
SQL;
        $stm = $this->getDb()->prepare($sql);
        $stm->bindParam(1, $collectionId);
        $stm->bindParam(2, $subjectId);
        if ($usql)
            $stm->bindParam(3, $userId);
        
        $stm->execute();
        $arr = $stm->fetchAll();
        $arr1 = array();
        if ($valueOnly) {
            foreach ($arr as $obj) {
                $arr1[$obj->domain_id] = $obj->avg;
            }
        } else {
            foreach ($arr as $obj) {
                $arr1[$obj->domain_id] = $obj;
            }
        }
        return $arr1;
    }

    /**
     *
     * @param $collectionId
     * @param $subjectId
     * @param null $userId
     * @param bool $valueOnly If true then only the itemId and average is return as an array key,value pair
     * @param string $entryStatus
     * @param string $placementStatus
     * @return array
     * @throws \Tk\Db\Exception
     * @deprecated
     */
    public function findItemAverages($collectionId, $subjectId, $userId = null, $valueOnly = false, $entryStatus = 'approved', $placementStatus = 'completed')
    {
        $usql = '';
        if ($userId) {
            $usql = ' AND a.user_id = ?';
        }
        
        $sql = <<<SQL
SELECT a.id AS 'entry_id', c.id AS 'item_id', c.category_id, d.id AS 'domain_id', d.label, c.question,
  ROUND(AVG(b.value), 2) AS 'avg', c.order_by
FROM skill_entry a LEFT JOIN placement p ON (a.placement_id = p.id), skill_value b, skill_item c, skill_domain d
WHERE
  a.del = 0 AND c.del = 0 AND d.del = 0 AND
      a.id = b.entry_id AND
      a.status = '$entryStatus' AND
      (p.status IS NULL OR p.status = '$placementStatus') AND 
      a.collection_id = ? AND 
      a.subject_id = ? 
      $usql AND
      b.item_id = c.id AND b.value > 0 AND
      c.domain_id = d.id
GROUP BY b.item_id
ORDER BY d.order_by, c.order_by
SQL;
        $stm = $this->getDb()->prepare($sql);

        $stm->bindParam(1, $collectionId);
        $stm->bindParam(2, $subjectId);
        if ($usql)
            $stm->bindParam(3, $userId);
        $stm->execute();
        $arr = $stm->fetchAll();

        $arr1 = array();
        foreach ($arr as $obj) {
            if ($valueOnly) {
                $arr1[$obj->item_id] = $obj->avg;
            } else {
                $arr1[$obj->item_id] = $obj;
            }

        }
        return $arr1;
    }
    
}