<?php
namespace Skill\Listener;

use Tk\Event\Subscriber;
use Tk\Event\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @todo; we still need to implement this
 */
class PlacementManagerButtonHandler implements Subscriber
{

    /**
     * @var \App\Db\Course
     */
    private $course = null;


    public function __construct($course)
    {
        $this->course = $course;
    }

    /**
     * Check the user has access to this controller
     *
     * @param Event $event
     */
    public function onControllerInit(Event $event)
    {
        $plugin = \Skill\Plugin::getInstance();
        $config = $plugin->getConfig();

        /** @var \App\Controller\Placement\Edit $controller */
        $controller = $event->get('controller');
        if ($controller instanceof \App\Controller\Placement\Manager) {

            $collectionList = \Skill\Db\CollectionMap::create()->findFiltered(array('profileId' => $this->course->profileId, 'active' => true));
            $actionsCell = $controller->getActionsCell();

            /** @var \Skill\Db\Collection $collection */
            foreach ($collectionList as $collection) {
                $url = \App\Uri::create('/skill/entryEdit.html')->set('collectionId', $collection->getId());
                $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create($collection->name, $url, $collection->icon))
                    ->setOnShow(function ($cell, $obj, $button) use ($collection) {
                        /* @var $obj \App\Db\Placement */
                        /* @var $button \Tk\Table\Cell\ActionButton */
                        $button->getUrl()->set('placementId', $obj->getId());
                        if (!$collection->isAvailable($obj)) {
                            $button->setVisible(false);
                            return;
                        }

                        $entry = \Skill\Db\EntryMap::create()->findFiltered(array('collectionId' => $collection->getId(),
                            'placementId' => $obj->getId()))->current();
                        if ($entry) {
                            $button->addCss('btn-default');
                        } else {
                            $button->addCss('btn-primary');
                        }
                    });

            }
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param Event $event
     */
    public function onControllerShow(Event $event)
    {
        $plugin = \Skill\Plugin::getInstance();
        $config = $plugin->getConfig();
        //$config->getLog()->info($plugin->getName() . ': onControllerShow(\'profile\', '.$this->profileId.') ');
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::CONTROLLER_INIT => array('onControllerInit', 0),
            \Tk\PageEvents::CONTROLLER_SHOW => array('onControllerShow', 0)
        );
    }

}