<?php
namespace Skill\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Scale extends \Tk\Db\Map\Model
{
    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $profileId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @todo: may not be required, calculate on the fly, using order_by
     * @var float
     */
    //public $value = 0;

    /**
     * @var int
     */
    public $orderBy = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Db\Profile
     */
    private $profile = null;



    /**
     * Course constructor.
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     *
     */
    public function save()
    {
        parent::save();
    }

    /**
     * Get the institution related to this user
     */
    public function getProfile()
    {
        if (!$this->profile) {
            $this->profile = \App\Db\ProfileMap::create()->find($this->profileId);
        }
        return $this->profile;
    }

    /**
     * Get the number value of this scale item
     * Generally this is a percentage of the scale in the list 0% - 100%
     *
     * @return float|int
     */
    public function getValue()
    {
        $list = ScaleMap::create()->findFiltered(array('profileId' => $this->profileId), \Tk\Db\Tool::create('order_by'));
        $cnt = count($list)-1;
        $pos = 0;
        $val = 0;
        /** @var \App\Db\Scale $s */
        foreach ($list as $i => $s) {
            if ($s->getId() == $this->getId()) {
                $pos = $i;
                break;
            }
        }
        if ($cnt > 0 && $pos > 0) {
            $val = round((100/$cnt)*$pos, 2);
        }
        return $val;
    }

    /**
     *
     */
    public function validate()
    {
        $errors = array();

        if ((int)$this->profileId <= 0) {
            $errors['profileId'] = 'Invalid Profile ID';
        }
        if (!$this->name) {
            $errors['name'] = 'Please enter a valid course name';
        }
        
        return $errors;
    }
}