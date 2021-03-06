<?php
/**
 * Kunena Component
* @package Kunena.Template.Crypsis
* @subpackage BBCode
*
* @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.org
**/
defined ( '_JEXEC' ) or die ();

/** @var KunenaAttachment $attachment */
$attachment = $this->attachment;

if (!$attachment->isImage()) return;

$config = KunenaConfig::getInstance();

$attributesLink = $config->lightbox ? ' class="fancybox-button" rel="fancybox-button"' : '';
$attributesImg = ' style="max-height:' . (int) $config->imageheight . 'px;"';
?>

<a href="<?php echo $attachment->getUrl(); ?>" title="<?php echo $attachment->getFilename(); ?>"<?php echo $attributesLink; ?>>
	<img src="<?php echo $attachment->getUrl(); ?>"<?php echo $attributesImg; ?> alt="" />
</a>
