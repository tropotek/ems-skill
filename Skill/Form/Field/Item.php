<?php
namespace Skill\Form\Field;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Item extends \Tk\Form\Field\Input
{
    /**
     * @var int
     */
    private static $incr = 1;

    /**
     * @var null|\Skill\Db\Item
     */
    protected $item = null;



    /**
     * __construct
     *
     * @param \Skill\Db\Item $item
     */
    public function __construct($item)
    {
        parent::__construct('item-'.$item->getId());
        $this->item = $item;
        $this->setFieldset($this->item->getCategory()->name);
    }
    
    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // TODO: setup slider javascript etc
        $template->appendCssUrl(\Tk\Uri::create('/plugin/ems-skill/assets/bootstrap-slider/src/less/bootstrap-slider.less'));
        $template->appendJsUrl(\Tk\Uri::create('/plugin/ems-skill/assets/bootstrap-slider/dist/bootstrap-slider.js'));

        $template->insertText('uid', self::$incr.'.');
        $template->insertText('question', $this->item->question);
        $list = \Skill\Db\ScaleMap::create()->findFiltered(array('collectionId' => $this->item->collectionId))->toArray('name');
        $template->setAttr('element', 'data-slider-labels', implode(',', $list));

        self::$incr++;
        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="skill-item row" var="item">
  <div class="col-md-8 skill-item-name">
    <span class="uid" var="uid">1.</span>
    <span for="fid-cb" var="question">Skill item question or description text</span>
  </div>
  <div class="col-md-4 skill-input">
    <input type="text" name="item-00" class="form-control skill-input-field tk-skillSlider" value="0" var="element"/>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}