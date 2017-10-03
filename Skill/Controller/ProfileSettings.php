<?php
namespace Skill\Controller;

use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * Class Contact
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ProfileSettings extends \App\Controller\AdminIface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Tk\Db\Data|null
     */
    protected $data = null;

    /**
     * @var \App\Db\Profile
     */
    private $profile = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Sample Plugin - Course Profile Settings');
    }

    /**
     * doDefault
     *
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        /** @var \Skill\Plugin $plugin */
        $plugin = \Skill\Plugin::getInstance();

        $this->profile = \App\Db\ProfileMap::create()->find($request->get('zoneId'));
        $this->data = \Tk\Db\Data::create($plugin->getName() . '.course.profile', $this->profile->getId());

        $this->form = \App\Factory::createForm('formEdit');
        $this->form->setParam('renderer', \App\Factory::createFormRenderer($this->form));

        $this->form->addField(new Field\Checkbox('plugin.enableSa'))->setCheckboxLabel('Enable Self Assessment');
        $this->form->addField(new Field\Checkbox('plugin.enableResults'))->setCheckboxLabel('Enable Students View Results');
        $this->form->addField(new Field\Input('plugin.confirm'))->setLabel('Confirmation Text');
        $this->form->addField(new Field\Textarea('plugin.instructions'))->setLabel('Instructions')->addCss('mce');
        
        $this->form->addField(new Event\Button('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Button('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\LinkButton('cancel', \App\Factory::getCrumbs()->getBackUrl()));

        $this->form->load($this->data->toArray());
        $this->form->execute();

    }

    /**
     * doSubmit()
     *
     * @param Form $form
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();
        $this->data->replace($values);
        
//        if (empty($values['plugin.title']) || strlen($values['plugin.title']) < 3) {
//            $form->addFieldError('plugin.title', 'Please enter your name');
//        }
//        if (empty($values['plugin.email']) || !filter_var($values['plugin.email'], \FILTER_VALIDATE_EMAIL)) {
//            $form->addFieldError('plugin.email', 'Please enter a valid email address');
//        }
        
        if ($this->form->hasErrors()) {
            return;
        }
        
        $this->data->save();
        
        \Tk\Alert::addSuccess('Settings saved.');
        if ($form->getTriggeredEvent()->getName() == 'update') {
            \App\Uri::createHomeUrl('/course/profilePlugins.html')->set('profileId', $this->profile->getId())->redirect();
        }
        \Tk\Uri::create()->redirect();
    }

    /**
     * show()
     *
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();
        
        // Render the form
        $template->insertTemplate($this->form->getId(), $this->form->getParam('renderer')->show()->getTemplate());

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div var="content">
  
  <div class="panel panel-default">
    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-cog"></i> Settings</h4></div>
    <div class="panel-body">
      <div var="formEdit"></div>
    </div>
  </div>
  
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }
}