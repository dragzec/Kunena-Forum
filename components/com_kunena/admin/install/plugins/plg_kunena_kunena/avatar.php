<?php
/**
 * Kunena Plugin
 * @package Kunena.Plugins
 * @subpackage Kunena
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class KunenaAvatarKunena extends KunenaAvatar {
	protected $params = null;

	public function __construct($params) {
		$this->params = $params;
		$this->resize = true;
	}

	public function getEditURL()
	{
		return KunenaRoute::_('index.php?option=com_kunena&view=user&layout=edit');
	}

	protected function _getURL($user, $sizex, $sizey)
	{
		$user = KunenaFactory::getUser($user);
		$avatar = $user->avatar;
		$config = KunenaFactory::getConfig();

		$path = KPATH_MEDIA ."/avatars";
		$origPath = "{$path}/{$avatar}";
		if ( !is_file($origPath)) {
			// If avatar does not exist use default image.
			if ($sizex <= 90) $avatar = 's_nophoto.jpg';
			else $avatar = 'nophoto.jpg';

			// Search from the template.
			$template = KunenaFactory::getTemplate();
			$origPath = JPATH_SITE . '/' . $template->getAvatarPath($avatar);
			$avatar = $template->name .'/'. $avatar;
		}
		$dir = dirname($avatar);
		$file = basename($avatar);
		if ($sizex == $sizey) {
			$resized = "resized/size{$sizex}/{$dir}";
		} else {
			$resized = "resized/size{$sizex}x{$sizey}/{$dir}";
		}

		if ( !is_file( "{$path}/{$resized}/{$file}" ) ) {
			KunenaImageHelper::version($origPath, "{$path}/{$resized}", $file, $sizex, $sizey, intval($config->avatarquality));
		}
		return KURL_MEDIA . "avatars/{$resized}/{$file}";
	}
}
