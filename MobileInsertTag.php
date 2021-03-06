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
 * Class MobileInsertTag
 *
 * Provide methods to replace some new insert tags
 * @copyright  Holger Teichert 2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    mobilelayout
 */
class MobileInsertTag extends Controller
{
  
  public function replaceMobileInsertTags ($strTag)
  {
    global $objPage;
    $return = '';
    // hide if there is no mobile layout
    if (!$objPage->mobileLayout)
    {
      return false;
    }
    $blnMobile = $objPage->isMobile;
    
    $strUrl = $this->Environment->request;
    $strGlue = (strpos($strUrl, '?') === false) ? '?' : '&amp;';
    $strUrlMobile = $strUrl . $strGlue . 'toggle_view=mobile';
    $strUrlDesktop = $strUrl . $strGlue . 'toggle_view=desktop';
    
    $arrTag = explode('::', $strTag, 3);
    $arrCache = array();
    
    switch ($arrTag[0])
    {
      // Mobile/desktop toggle
      
      case 'mobile':
        switch ($arrTag[1])
        {
          case 'toggle':
            if ($blnMobile)
            {
              $return = '<a href="' . $strUrlDesktop . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['toggleDesktop'][1]) . '" class="toggle_view mobile_toggle desktop">' . $GLOBALS['TL_LANG']['MSC']['toggleDesktop'][0] . '</a>';
            }
            else
            {
              $return = '<a href="' . $strUrlMobile . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['toggleMobile'][1]) . '" class="toggle_view mobile_toggle mobile">' . $GLOBALS['TL_LANG']['MSC']['toggleMobile'][0] . '</a>';
            }
            break;
          
          case 'toggle_url':
            $return = ($blnMobile)? $strUrlDesktop : $strUrlMobile;
            break;
          
          case 'toggle_text':
            $return = ($blnMobile)? $GLOBALS['TL_LANG']['MSC']['toggleDesktop'][0] : $GLOBALS['TL_LANG']['MSC']['toggleMobile'][0];
            break;
          
          case 'toggle_title':
            $return = ($blnMobile)? $GLOBALS['TL_LANG']['MSC']['toggleDesktop'][1] : $GLOBALS['TL_LANG']['MSC']['toggleMobile'][1];
            break;
          
          case 'alternatives':
            $alternatives = explode(':', $arrTag[2]);
            if (!is_array($alternatives) && count($alternatives) < 2) {
              $return = false;
            } else {
              $return = ($blnMobile)? $alternatives[1] : $alternatives[0];
            }
            //$return = print_r($alternatives, true); 
            break;
        }
        break;
        
      case 'toggle_view':
        trigger_error('The insert tag "toggle_view" is deprecated. Please use "mobile::toggle" instead.', E_USER_NOTICE);
        $return = $this->replaceMobileInsertTags('mobile::toggle');
        break;
        
      case 'toggle_url':
        trigger_error('The insert tag "toggle_url" is deprecated. Please use "mobile::toggle_url" instead.', E_USER_NOTICE);
        $return = $this->replaceMobileInsertTags('mobile::toggle_url');
        break;
    }
    
    return empty($return)? false : $return;
  }
}