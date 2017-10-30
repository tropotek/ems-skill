<?php
namespace Skill\Controller\Entry;

use App\Controller\AdminEditIface;
use App\Controller\AdminIface;
use Dom\Template;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Results extends AdminIface
{

    /**
     * @var \App\Db\User
     */
    protected $user = null;

    /**
     * @var \Skill\Db\Collection
     */
    protected $collection = null;




    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Results');
    }

    /**
     *
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        $this->user = \App\Db\UserMap::create()->find($request->get('userId'));
        $this->collection = \Skill\Db\CollectionMap::create()->find($request->get('collectionId'));

        
        
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        if ($this->collection->icon) {
            $template->addCss('icon', $this->collection->icon);
        }
        $panelTitle = sprintf('%s Results for `%s`', $this->collection->name, $this->user->name);
        $template->insertText('panel-title', $panelTitle);
        
        
        $entryList = \Skill\Db\EntryMap::create()->findFiltered(array('userId' => $this->user->getId(), 'collectionId' => $this->collection->getId(), 'courseId' => $this->getCourse()->getId(), 'status' => \Skill\Db\Entry::STATUS_APPROVED), \Tk\Db\Tool::create('created DESC'));
        $template->insertText('entryCount', $entryList->count());
        $studentResult =  \Skill\Db\ReportingMap::create()->findStudentResult($this->collection->getId(), $this->getCourse()->getId(), $this->user->getId(), true);
        
        $template->insertText('avg', sprintf('%.2f / %d', $studentResult*$this->collection->getScaleLength()-1, $this->collection->getScaleLength()-1));
        $template->insertText('grade', sprintf('%.2f / %d', $studentResult*$this->collection->maxGrade, $this->collection->maxGrade));
        $template->insertText('gradePcnt', sprintf('%.2f', $studentResult*100) . '%');
        
        
        $domainResults = \Skill\Db\ReportingMap::create()->findDomainAverages($this->collection->getId(), $this->getCourse()->getId(), $this->user->getId());
        // TODO: Could look at a pie chart for this infomation
        foreach ($domainResults as $obj) {
            /** @var \Skill\Db\Domain $domain */
            $domain = \Skill\Db\DomainMap::create()->find($obj->domain_id);
            $row = $template->getRepeat('domain-row');
            $row->insertText('name', $domain->name . ' (' .$domain->label. ')');
            $row->insertText('avg', sprintf('%.2f', $obj->avg));
            $row->insertText('grade', sprintf('%.2f', ($obj->avg/$obj->scale)*$this->collection->maxGrade));
            $row->insertText('weight', round($obj->weight*100) . '%');
            $row->appendRepeat();
        }
        
        $itemResults = \Skill\Db\ReportingMap::create()->findItemAverages($this->collection->getId(), $this->getCourse()->getId(), $this->user->getId());
        /** @var \Skill\Db\Category $category */
        $category = null;
        /** @var \Dom\Repeat $catRow */
        $catRow = null;
        foreach ($itemResults as $i => $obj) {
            if (!$category || $category->getId() != $obj->category_id) {
                $category = \Skill\Db\CategoryMap::create()->find($obj->category_id);
                if (!$category) continue;
                if ($catRow) $catRow->appendRepeat();
                $catRow = $template->getRepeat('category-row');

                $catRow->insertText('name', $category->name . '(' . $obj->label . ')');
                // TODO: The label should not be here but on individual questions, fix it when it becomes an issue 
                // $catRow->insertText('name', $category->name);
            }
            $row = $catRow->getRepeat('item-row');
            $row->insertText('lineNo', $i.'. ');
            $row->insertText('question', $obj->question);
            $row->insertText('result', $obj->avg);
            if ($obj->avg <= 0) {
                $row->addCss('result', 'zero');
            }
            $row->appendRepeat();
        }
        
        
        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="EntryResults">

<style media="all">
.EntryResults .category-row {
  border: 1px solid #EEE;
  border-radius: 5px;
  background: #FEFEFE;
  margin: 10px 0px;
  box-shadow: 1px 1px 2px #CCC;
  padding: 10px 0px;
}
.EntryResults .category-row {
  
}
.EntryResults .item-row {
  border: 1px solid #EEE;
  border-radius: 5px;
  background: #FFF;
  margin: 5px;
  padding: 5px;
  font-size: 1.1em;
}
.EntryResults .item-row:nth-child(even) {
  background-color: {$this->collection->color};
}
.EntryResults .item-row .question {
  padding-left: 20px;
  margin: 5px 0px;
}
.EntryResults .item-row .question .lineNo {
  display: inline-block;
  padding-right: 10px;
}
.EntryResults .item-row .answer {
  text-align: right;
}

.EntryResults .item-row .result.zero {
  color: #999;
}

</style>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title"><i class="fa fa-eye" var="icon"></i> <span var="panel-title">Skill Entry Results</span>
      </h4>
    </div>
    <div class="panel-body">

      <div class="col-md-6">
      
      <table class="table keyvalue-table">
        <tbody>
        <tr>
          <td class="kv-key"><i class="fa fa-hashtag kv-icon kv-icon-default"></i> Placements Assessed</td>
          <td class="kv-value" var="entryCount">0</td>
        </tr>
        <tr>
          <td class="kv-key"><i class="fa fa-exchange kv-icon kv-icon-tertiary"></i> Average Response</td>
          <td class="kv-value" var="avg">0</td>
        </tr>
        <tr>
          <td class="kv-key"><i class="fa fa-graduation-cap kv-icon kv-icon-primary"></i> Calculated Grade</td>
          <td class="kv-value" var="grade">0.0</td>
        </tr>
        <tr>
          <td class="kv-key"><i class="fa fa-percent kv-icon kv-icon-secondary"></i> Calculated Grade %</td>
          <td class="kv-value" var="gradePcnt">0.00%</td>
        </tr>
        </tbody>
      </table>
      </div>
      <div class="col-md-6">
      <table class="table table-bordered">
        <tr>
          <th>Domain</th>
          <th>Avg.</th>
          <th>Weight</th>
          <th>Grade</th>
        </tr>
        <tr repeat="domain-row" var="domain-row">
          <td var="name"></td>
          <td var="avg"></td>
          <td var="weight"></td>
          <td var="grade"></td>
        </tr>
      </table>
      </div>


      <div class="col-xs-12 category-row clearfix" repeat="category-row">
        <div class="col-xs-12">
          <div><h4 class="category-name" var="name">Category Name</h4></div>
          <div class="row item-row" repeat="item-row" var="item-row">
            <div class="col-xs-10 question"><span class="lineNo" var="lineNo">0.</span> <span var="question"></span></div>
            <div class="col-xs-2 text-center"><span class="result" var="result">0.00</span></div>
          </div>
        </div>
      </div>


    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}