<?php
$config = \App\Config::getInstance();

/** @var \Composer\Autoload\ClassLoader $composer */
$composer = $config->getComposer();
if ($composer)
    $composer->add('Skill\\', dirname(__FILE__));

$routes = $config->getRouteCollection();
if (!$routes) return;


$params = array();

//$params = array('role' => 'admin');
//$routes->add('Skill Admin Settings', new \Tk\Routing\Route('/admin/skill/settings.html', 'Skill\Controller\SystemSettings::doDefault', $params));



//$params = array('role' => array('client', 'staff'));
//$routes->add('Skill Profile Settings', new \Tk\Routing\Route('/client/skill/profileSettings.html', 'Skill\Controller\ProfileSettings::doDefault', $params));

//$routes->add('client-skill-collection-manager', new \Tk\Routing\Route('/skill/collectionManager.html', 'Skill\Controller\Collection\Manager::doDefault', $params));
//$routes->add('client-skill-collection-edit', new \Tk\Routing\Route('/skill/collectionEdit.html', 'Skill\Controller\Collection\Edit::doDefault', $params));
//$routes->add('client-skill-domain-manager', new \Tk\Routing\Route('/skill/domainManager.html', 'Skill\Controller\Domain\Manager::doDefault', $params));
//$routes->add('client-skill-domain-edit', new \Tk\Routing\Route('/skill/domainEdit.html', 'Skill\Controller\Domain\Edit::doDefault', $params));
//$routes->add('client-skill-category-manager', new \Tk\Routing\Route('/skill/categoryManager.html', 'Skill\Controller\Category\Manager::doDefault', $params));
//$routes->add('client-skill-category-edit', new \Tk\Routing\Route('/skill/categoryEdit.html', 'Skill\Controller\Category\Edit::doDefault', $params));
//$routes->add('client-skill-scale-manager', new \Tk\Routing\Route('/skill/scaleManager.html', 'Skill\Controller\Scale\Manager::doDefault', $params));
//$routes->add('client-skill-scale-edit', new \Tk\Routing\Route('/skill/scaleEdit.html', 'Skill\Controller\Scale\Edit::doDefault', $params));
//$routes->add('client-skill-item-manager', new \Tk\Routing\Route('/skill/itemManager.html', 'Skill\Controller\Item\Manager::doDefault', $params));
//$routes->add('client-skill-item-edit', new \Tk\Routing\Route('/skill/itemEdit.html', 'Skill\Controller\Item\Edit::doDefault', $params));

$routes->add('client-skill-collection-manager', new \Tk\Routing\Route('/client/skill/collectionManager.html', 'Skill\Controller\Collection\Manager::doDefault', $params));
$routes->add('client-skill-collection-edit', new \Tk\Routing\Route('/client/skill/collectionEdit.html', 'Skill\Controller\Collection\Edit::doDefault', $params));
$routes->add('client-skill-domain-manager', new \Tk\Routing\Route('/client/skill/domainManager.html', 'Skill\Controller\Domain\Manager::doDefault', $params));
$routes->add('client-skill-domain-edit', new \Tk\Routing\Route('/client/skill/domainEdit.html', 'Skill\Controller\Domain\Edit::doDefault', $params));
$routes->add('client-skill-category-manager', new \Tk\Routing\Route('/client/skill/categoryManager.html', 'Skill\Controller\Category\Manager::doDefault', $params));
$routes->add('client-skill-category-edit', new \Tk\Routing\Route('/client/skill/categoryEdit.html', 'Skill\Controller\Category\Edit::doDefault', $params));
$routes->add('client-skill-scale-manager', new \Tk\Routing\Route('/client/skill/scaleManager.html', 'Skill\Controller\Scale\Manager::doDefault', $params));
$routes->add('client-skill-scale-edit', new \Tk\Routing\Route('/client/skill/scaleEdit.html', 'Skill\Controller\Scale\Edit::doDefault', $params));
$routes->add('client-skill-item-manager', new \Tk\Routing\Route('/client/skill/itemManager.html', 'Skill\Controller\Item\Manager::doDefault', $params));
$routes->add('client-skill-item-edit', new \Tk\Routing\Route('/client/skill/itemEdit.html', 'Skill\Controller\Item\Edit::doDefault', $params));


// Staff Only
//$params = array('role' => array('staff'));
$routes->add('skill-entry-collection-manager', new \Tk\Routing\Route('/staff/{subjectCode}/entryCollectionManager.html', 'Skill\Controller\Entry\CollectionManager::doDefault', $params));
$routes->add('skill-entry-manager', new \Tk\Routing\Route('/staff/{subjectCode}/entryManager.html', 'Skill\Controller\Entry\Manager::doDefault', $params));
$routes->add('skill-entry-edit', new \Tk\Routing\Route('/staff/{subjectCode}/entryEdit.html', 'Skill\Controller\Entry\Edit::doDefault', $params));
$routes->add('skill-entry-view', new \Tk\Routing\Route('/staff/{subjectCode}/entryView.html', 'Skill\Controller\Entry\View::doDefault', $params));
$routes->add('skill-entry-results-staff', new \Tk\Routing\Route('/staff/{subjectCode}/entryResults.html', 'Skill\Controller\Entry\Results::doDefault', $params));
$routes->add('skill-entry-report', new \Tk\Routing\Route('/staff/{subjectCode}/collectionReport.html', 'Skill\Controller\Collection\Report::doDefault', $params));

$routes->add('staff-skill-collection-manager', new \Tk\Routing\Route('/staff/skill/collectionManager.html', 'Skill\Controller\Collection\Manager::doDefault', $params));
$routes->add('staff-skill-collection-edit', new \Tk\Routing\Route('/staff/skill/collectionEdit.html', 'Skill\Controller\Collection\Edit::doDefault', $params));
$routes->add('staff-skill-domain-manager', new \Tk\Routing\Route('/staff/skill/domainManager.html', 'Skill\Controller\Domain\Manager::doDefault', $params));
$routes->add('staff-skill-domain-edit', new \Tk\Routing\Route('/staff/skill/domainEdit.html', 'Skill\Controller\Domain\Edit::doDefault', $params));
$routes->add('staff-skill-category-manager', new \Tk\Routing\Route('/staff/skill/categoryManager.html', 'Skill\Controller\Category\Manager::doDefault', $params));
$routes->add('staff-skill-category-edit', new \Tk\Routing\Route('/staff/skill/categoryEdit.html', 'Skill\Controller\Category\Edit::doDefault', $params));
$routes->add('staff-skill-scale-manager', new \Tk\Routing\Route('/staff/skill/scaleManager.html', 'Skill\Controller\Scale\Manager::doDefault', $params));
$routes->add('staff-skill-scale-edit', new \Tk\Routing\Route('/staff/skill/scaleEdit.html', 'Skill\Controller\Scale\Edit::doDefault', $params));
$routes->add('staff-skill-item-manager', new \Tk\Routing\Route('/staff/skill/itemManager.html', 'Skill\Controller\Item\Manager::doDefault', $params));
$routes->add('staff-skill-item-edit', new \Tk\Routing\Route('/staff/skill/itemEdit.html', 'Skill\Controller\Item\Edit::doDefault', $params));

// Student Only
$params = array('role' => array('student'));
$routes->add('skill-entry-edit-student', new \Tk\Routing\Route('/student/{subjectCode}/entryEdit.html', 'Skill\Controller\Entry\Edit::doDefault', $params));
$routes->add('skill-entry-view-student', new \Tk\Routing\Route('/student/{subjectCode}/entryView.html', 'Skill\Controller\Entry\View::doDefault', $params));
$routes->add('skill-entry-results-student', new \Tk\Routing\Route('/student/{subjectCode}/entryResults.html', 'Skill\Controller\Entry\Results::doDefault', $params));


// Guest Pages
$routes->add('guest-skill-entry-submit', new \Tk\Routing\Route('/inst/{institutionHash}/skillEdit.html', 'Skill\Controller\Entry\Edit::doPublicSubmission'));
// Temp bridging page, remove after Aug 2018
$routes->add('guest-goals-redirect', new \Tk\Routing\Route('/goals.html', 'Skill\Controller\Entry\Goals::doDefault'));



