<?php
/**
 * Kunena Component
 * @package     Kunena.Site
 * @subpackage  Controller.Application
 *
 * @copyright   (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.kunena.org
 **/
defined('_JEXEC') or die;

/**
 * Class ComponentKunenaControllerApplicationAttachmentDefaultDisplay
 *
 * Only implemented on raw format as it's faster to run.
 *
 * @since  3.1
 */
class ComponentKunenaControllerApplicationAttachmentDefaultDisplay extends KunenaControllerApplicationDisplay
{
	/**
	 * Return true if layout exists.
	 *
	 * @return bool
	 */
	public function exists()
	{
		return true;
	}

	/**
	 * Display attachment.
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 * @throws KunenaExceptionAuthorise
	 */
	public function execute()
	{
		$format = $this->input->getWord('format', 'html');
		$id = $this->input->getInt('id', 0);
		$thumb = $this->input->getBool('thumb', false);
		$download = $this->input->getBool('download', false);

		// Run before executing action.
		$this->before();

		if ($format != 'raw' || !$id)
		{
			throw new KunenaExceptionAuthorise(JText::_('COM_KUNENA_NO_ACCESS'), 404);
		}
		elseif ($this->config->board_offline && !$this->me->isAdmin())
		{
			// Forum is offline.
			throw new KunenaExceptionAuthorise(JText::_('COM_KUNENA_FORUM_IS_OFFLINE'), 503);
		}
		elseif ($this->config->regonly && !$this->me->exists())
		{
			// Forum is for registered users only.
			throw new KunenaExceptionAuthorise(JText::_('COM_KUNENA_LOGIN_NOTIFICATION'), 403);
		}

		$attachment = KunenaAttachmentHelper::get($id);
		$attachment->tryAuthorise();

		$path = $attachment->getPath($thumb);

		if ($thumb && !$path)
		{
			$path = $attachment->getPath(false);
		}

		if (!$path)
		{
			// File doesn't exist.
			throw new KunenaExceptionAuthorise(JText::_('COM_KUNENA_NO_ACCESS'), 404);
		}

		if (headers_sent())
		{
			throw new KunenaExceptionAuthorise('HTTP headers were already sent. Sending attachment failed.', 500);
		}

		// Close all output buffers, just in case.
		while(@ob_end_clean());

		// Handle 304 Not Modified
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);

			if ($etag == $attachment->hash)
			{
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT', true, 304);

				// Give fast response.
				flush();
				$this->app->close();
			}
		}


		// Set file headers.
		header('ETag: ' . $attachment->hash);
		header('Pragma: public');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');

		if (!$download && $attachment->isImage())
		{
			// By default display images inline.
			$maxage = 60 * 60;
			header('Cache-Control: maxage=' . $maxage);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxage) . ' GMT');
			header('Content-type: ' . $attachment->filetype);
			header('Content-Disposition: inline; filename="' . $attachment->getFilename(false) . '"');
		}
		else
		{
			// Otherwise force file download.
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment; filename="' . $attachment->getFilename(false) . '"');
		}

		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($path));
		flush();

		// Output the file contents.
		@readfile($path);
		flush();

		$this->app->close();
	}

	/**
	 * Prepare attachment display.
	 *
	 * @return void
	 */
	protected function before()
	{
		// Load language files.
		KunenaFactory::loadLanguage('com_kunena.sys', 'admin');

		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaConfig::getInstance();
		$this->document = JFactory::getDocument();
	}
}
