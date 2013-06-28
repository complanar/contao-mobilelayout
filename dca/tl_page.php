<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Mobile Layout
 * Copyright (C) 2013 Holger Teichert
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @copyright  Holger Teichert 2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    mobilelayout
 * @license    LGPL
 */

/**
 * Table tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['includeLayout'] = str_replace('layout', 'layout,mobileLayout', $GLOBALS['TL_DCA']['tl_page']['subpalettes']['includeLayout']);

if (isset($GLOBALS['TL_DCA']['tl_page']['fields']['layout']['eval']['tl_class']) and strpos($GLOBALS['TL_DCA']['tl_page']['fields']['layout']['eval']['tl_class'], 'w50') == -1) {
    $GLOBALS['TL_DCA']['tl_page']['fields']['layout']['eval']['tl_class'] = 'w50';
} else {
    $GLOBALS['TL_DCA']['tl_page']['fields']['layout']['eval']['tl_class'] .= ' w50';
}

$GLOBALS['TL_DCA']['tl_page']['fields']['mobileLayout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['mobileLayout'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'              => 'tl_layout.name',
  'options_callback'        => array('tl_page', 'getPageLayouts'),
  'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50')
);
