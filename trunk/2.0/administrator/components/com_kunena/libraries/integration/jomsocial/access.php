<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2010 Kunena Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 *
 **/
//
// Dont allow direct linking
defined( '_JEXEC' ) or die('');

class KunenaAccessJomSocial extends KunenaAccess {
	protected $joomlaAccess = null;
	protected $integration = null;

	public function __construct() {
		$this->integration = KunenaIntegration::getInstance ('jomsocial');
		if (! $this->integration || ! $this->integration->isLoaded())
			return;
		$this->joomlaAccess = KunenaAccess::getInstance('joomla');
		$this->priority = 50;
	}

	protected function loadAdmins() {
		$list = $this->joomlaAccess->loadAdmins();

		$db = JFactory::getDBO();
		$query	= "SELECT g.memberid AS userid, c.id AS catid
			FROM #__kunena_categories AS c
			INNER JOIN #__community_groups_members AS g ON c.accesstype='jomsocial' AND c.access=g.groupid
			WHERE c.published=1 AND g.approved=1 AND g.permissions={$db->Quote( COMMUNITY_GROUP_ADMIN )}";
		$db->setQuery( $query );
		$jslist = (array) $db->loadObjectList ();
		KunenaError::checkDatabaseError ();

		return parent::loadAdmins(array_merge($list, $jslist));
	}

	protected function loadModerators() {
		return parent::loadModerators($this->joomlaAccess->loadModerators());
	}

	protected function loadAllowedCategories($userid) {
		$allowed = $this->joomlaAccess->getAllowedCategories($userid);

		$db = JFactory::getDBO();
		$query	= "SELECT c.id FROM #__kunena_categories AS c
			INNER JOIN #__community_groups_members AS g ON c.accesstype='jomsocial' AND c.access=g.groupid
			WHERE c.published=1 AND g.approved=1 AND g.memberid={$db->quote($userid)}";
		$db->setQuery( $query );
		$list = (array) $db->loadResultArray ();
		KunenaError::checkDatabaseError ();

		foreach ( $list as $catid ) {
			$allowed [$catid] = $catid;
		}
		return $allowed;
	}

	protected function checkSubscribers($topic, &$userids) {
		$category = $topic->getCategory();
		if ($category->accesstype != 'jomsocial') {
			$this->joomlaAccess->checkSubscribers($topic, $userids);
		}
	}
}