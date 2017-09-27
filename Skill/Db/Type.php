<?php
namespace Skill\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Type extends \Tk\Db\Map\Model
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
     * @var int
     */
    public $placementTypeId = 0;

    /**
     * @var string
     */
    public $typeGroup = '';

    /**
     * @var string
     */
    public $name = '';

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
     * @var \App\Db\PlacementType
     */
    private $placementType = null;



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
     * @return \App\Db\Profile|null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface
     */
    public function getProfile()
    {
        if (!$this->profile) {
            $this->profile = \App\Db\ProfileMap::create()->find($this->profileId);
        }
        return $this->profile;
    }

    /**
     * @return \App\Db\PlacementType|null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface
     */
    public function getPlacementType()
    {
        if (!$this->placementType) {
            $this->placementType = \App\Db\PlacementTypeMap::create()->find($this->placementTypeId);
        }
        return $this->placementType;
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
//        if ((int)$this->placementTypeId <= 0) {
//            $errors['placementTypeId'] = 'Invalid Placement Type';
//        }
        if (!$this->typeGroup) {
            $errors['typeGroup'] = 'Please enter a valid group';
        }
        if (!$this->name) {
            $errors['name'] = 'Please enter a valid name';
        }
        
        return $errors;
    }
}