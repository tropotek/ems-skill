<?php
namespace Skill\Listener;

use Tk\Event\Subscriber;
use Tk\Event\Event;
use Skill\Plugin;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SidebarHandler implements Subscriber
{

    /**
     * @var \App\Db\Course
     */
    private $course = null;

    /**
     * @var \App\Controller\Iface
     */
    protected $controller = null;



    /**
     * CourseDashboardHandler constructor.
     * @param \App\Db\Course $course
     */
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
        /** @var \App\Controller\Staff\CourseDashboard $controller */
        $this->controller = $event->get('controller');
    }

    /**
     * Check the user has access to this controller
     *
     * @param Event $event
     */
    public function onSidebarShow(Event $event)
    {
        if ($this->controller->getUser()->isStudent()) {
            /** @var \App\Ui\Sidebar\Iface $sidebar */
            $sidebar = $event->get('sidebar');
            $course = $this->controller->getCourse();
            $user = $this->controller->getUser();
            if (!$user || !$user->isStudent()) return;

            $collectionList = \Skill\Db\CollectionMap::create()->findFiltered(
                array('courseId' => $course->getId())
            );

            /** @var \Skill\Db\Collection $collection */
            foreach ($collectionList as $collection) {
                $html = '';
                if ($collection->requirePlacement) {        // Results views
                    if ($collection->gradable) {
                        $html = sprintf('<li><a href="%s" title="View %s Results">%s</a></li>',
                            htmlentities(\App\Uri::createCourseUrl('/skillEntryResults.html')->set('collectionId', $collection->getId())->toString()),
                            $collection->name, $collection->name);
                    }
                } else if ($collection->role == \Skill\Db\Collection::ROLE_STUDENT) {
                    /** @var \Skill\Db\Entry $e */
                    $e = \Skill\Db\EntryMap::create()->findFiltered(array(
                                    'collectionId' => $collection->getId(),
                                    'courseId' => $course->getId(),
                                    'userId' => $user->getId())
                    )->current();
                    if ($e && $e->status == \Skill\Db\Entry::STATUS_APPROVED) {
                        $html = sprintf('<li><a href="%s" title="View %s">%s</a></li>',
                            htmlentities(\App\Uri::createCourseUrl('/entryView.html')->set('entryId', $e->getId())->toString()),
                            $collection->name, $collection->name);
                    } else {
                        $html = sprintf('<li><a href="%s" title="Create %s">%s</a></li>',
                            htmlentities(\App\Uri::createCourseUrl('/entryEdit.html')->set('collectionId', $collection->getId())->toString()),
                            $collection->name, $collection->name);
                    }

                }
                if ($html)
                    $sidebar->getTemplate()->appendHtml('menu', $html);
            }
        }
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
            \App\UiEvents::SIDEBAR_SHOW => array('onSidebarShow', 0)
        );
    }
    
}