<?php

use DBA\Hashlist;
use DBA\HashlistHashlist;
use DBA\JoinFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

/** @var Login $LOGIN */
/** @var array $OBJECTS */

if (!$LOGIN->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}
else if ($LOGIN->getLevel() < DAccessLevel::READ_ONLY) {
  $TEMPLATE = new Template("restricted");
  $OBJECTS['pageTitle'] = "Hashtopussy - Restricted";
  die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("superhashlists/index");
$MENU->setActive("lists_super");

if (isset($_GET['new'])) {
  $TEMPLATE = new Template("superhashlists/new");
  $MENU->setActive("lists_snew");
  $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "<>");
  $OBJECTS['lists'] = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
  $OBJECTS['pageTitle'] = "Hashtopussy - Create Superhashlist";
}
else {
  $qF = new QueryFilter(Hashlist::FORMAT, DHashlistFormat::SUPERHASHLIST, "=");
  $lists = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF));
  $OBJECTS['lists'] = $lists;
  $subLists = new DataSet();
  foreach ($lists as $list) {
    $qF = new QueryFilter(HashlistHashlist::PARENT_HASHLIST_ID, $list->getId(), "=", $FACTORIES::getHashlistHashlistFactory());
    $jF = new JoinFilter($FACTORIES::getHashlistHashlistFactory(), HashlistHashlist::HASHLIST_ID, Hashlist::HASHLIST_ID);
    $ll = $FACTORIES::getHashlistFactory()->filter(array($FACTORIES::FILTER => $qF, $FACTORIES::JOIN => $jF));
    $subLists->addValue($list->getId(), $ll[$FACTORIES::getHashlistFactory()->getModelName()]);
  }
  $OBJECTS['subLists'] = $subLists;
  $OBJECTS['pageTitle'] = "Hashtopussy - Superhashlists";
}

$hashtypes = new DataSet();
$types = $FACTORIES::getHashTypeFactory()->filter(array());
foreach ($types as $type) {
  $hashtypes->addValue($type->getId(), $type->getDescription());
}
$OBJECTS['hashtypes'] = $hashtypes;

echo $TEMPLATE->render($OBJECTS);




