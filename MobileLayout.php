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
 * Class MobileLayout
 *
 * Provide methods to handle an additional mobile layout
 * @copyright  Holger Teichert 2013
 * @author     Holger Teichert <post@complanar.de>
 * @package    mobilelayout
 */
class MobileLayout extends PageRegular
{
  /**
   * Load mobileLayout from parent pages
   * 
   * @access protected
   * @param Database_Result
   * @return Database_Result
   */
  protected function getParentMobileLayout(Database_Result $objPage) 
  {
    $pid = $objPage->pid;
    $type = $objPage->type;
    // Inherit the settings
    if ($objPage->type == 'root')
    {
      $objParentPage = $objPage; // see #4610
    }
    else
    {
      do
      {
        $objParentPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                        ->limit(1)
                        ->execute($pid);

        if ($objParentPage->numRows < 1)
        {
          break;
        }

        $pid = $objParentPage->pid;
        $type = $objParentPage->type;

        if (!$objPage->mobileLayout && $objParentPage->includeLayout)
        {
          $objPage->mobileLayout = $objParentPage->mobileLayout;
        }
      }
      while ($pid > 0 && $type != 'root');
    }
    return $objPage;
  }
  
  /**
   * Decide wether a page is in mobile or in desktop view
   * @access public
   * @param Database_Result
   * @return boolean
   */
  public function pageMobile(Database_Result &$objPage) 
  {
    // Return value if already set
    if (isset($objPage->isMobile)) {
      return $objPage->isMobile;
    }
    
    $objPage = $this->getParentMobileLayout($objPage);
    $blnMobile = ($objPage->mobileLayout && $this->Environment->agent->mobile);
    // Set the cookie
    if (isset($_GET['toggle_view']))
    {
      if ($this->Input->get('toggle_view') == 'mobile')
      {
        $this->setCookie('TL_VIEW', 'mobile', 0);
      }
      else
      {
        $this->setCookie('TL_VIEW', 'desktop', 0);
      }

      $this->redirect($this->getReferer());
    }
    
    // Override the autodetected value
    if ($this->Input->cookie('TL_VIEW') == 'mobile' && $objPage->mobileLayout)
    {
      $blnMobile = true;
    }
    elseif ($this->Input->cookie('TL_VIEW') == 'desktop')
    {
      $blnMobile = false;
    }
    
    $objPage->isMobile = $blnMobile;
    return $blnMobile;
  }
  
  /**
   * Generate a mobile page
   * 
   * @access public
   * @param Database_Result
   * @param Database_Result
   * @param PageRegular
   * @return  void
   */
  public function generatePage(Database_Result &$objPage, Database_Result &$objLayout, PageRegular $objPageRegular)
  {
    $blnMobile  = $this->pageMobile($objPage);
    
    if(!$blnMobile)
      return;

    $intId = $blnMobile ? $objPage->mobileLayout : $objPage->layout;
    $objLayout = $this->getPageLayout($intId);
    
    $objPage->template = ($objLayout->template != '') ? $objLayout->template : 'fe_page';
    $objPage->templateGroup = $objLayout->templates;

    // Store the output format
    list($strFormat, $strVariant) = explode('_', $objLayout->doctype);
    $objPage->outputFormat = $strFormat;
    $objPage->outputVariant = $strVariant;

    // Initialize the template
    $objPageRegular->createTemplate($objPage, $objLayout);

    // Initialize modules and sections
    $arrCustomSections = array();
    $arrSections = array('header', 'left', 'right', 'main', 'footer');
    $arrModules = deserialize($objLayout->modules);

    // Generate all modules
    foreach ($arrSections as $k) {
      $objPageRegular->Template->$k = '';
    }
    foreach ($arrModules as $arrModule)
    {
      if (in_array($arrModule['col'], $arrSections))
      {
        // Filter active sections (see #3273)
        if ($arrModule['col'] == 'header' && !$objLayout->header)
        {
          continue;
        }
        if ($arrModule['col'] == 'left' && $objLayout->cols != '2cll' && $objLayout->cols != '3cl')
        {
          continue;
        }
        if ($arrModule['col'] == 'right' && $objLayout->cols != '2clr' && $objLayout->cols != '3cl')
        {
          continue;
        }
        if ($arrModule['col'] == 'footer' && !$objLayout->footer)
        {
          continue;
        }

        $objPageRegular->Template->$arrModule['col'] .= $this->getFrontendModule($arrModule['mod'], $arrModule['col']);
      }
      else
      {
        $arrCustomSections[$arrModule['col']] .= $this->getFrontendModule($arrModule['mod'], $arrModule['col']);
      }
    }

    $objPageRegular->Template->sections = $arrCustomSections;
  }
  
  /**
   * Filter content elements by visibility for desktop or mobile devices
   * 
   * @access public
   * @param Database_Result
   * @param String
   * @return  String
   */
  public function filterByMobility($objElement, $strBuffer)
  {
    if ( TL_MODE == 'BE')
    {
      return $strBuffer;
    }
    
    global $objPage;
    $blnMobile  = $this->pageMobile($objPage);
    
    if ( $objPage->isMobile && $objElement->hideonmobiles || !$objPage->isMobile && $objElement->hideondesktops )
    {
      $strBuffer = '';
    }
    
    return $strBuffer;
  }
}