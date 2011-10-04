<?php
/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Page database interface
 *
 * @package	YAPEP
 * @subpackage	DatabaseModule
 * @author		Attila Danch <da@scp.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
interface module_db_interface_Page {

	const TYPE_DEFAULT_DOC = 0;

	const TYPE_MAIN_PAGE = 1;

	const TYPE_FOLDER = 2;

	const TYPE_FOLDER_DOC = 3;

	const TYPE_DOC = 4;

	/**
	 * Returns boxes for given cms_page_id and boxplace_id
	 *
	 * @param integer $pageId
	 * @param integer $boxplaceId
	 * @return array
	 */
	public function getBoxesByPageId($pageId, $boxplaceId);

	/**
	 * Returns params for given cms page box
	 *
	 * @param integer $boxId
	 * @return array
	 */
	public function getBoxParamByBoxId($boxId);

	/**
	 * Returns pages and derived pages
	 *
	 * @return array
	 */
	public function getPages();

	/**
	 * Returns boxplaces
	 *
	 * @return array
	 */
	public function getBoxPlaces();

	/**
	 * Returns the list of main page IDs for a given locale
	 *
	 * @param integer $localeId
	 * @return array
	 */
	public function getMainPageIdsForLocale($localeId);

	/**
	 * Returns the default document page IDs for a given locale
	 *
	 * @var integer $localeId
	 * @return array
	 */
	public function getDefaultDocPageIdsForLocale($localeId);

	/**
	 * Returns all pages, folders and derived pages by locale id
	 *
	 * @param integer $localeId
	 */
	public function getPagesByLocaleId($localeId);

	/**
	 * Returns the specified page's data
	 *
	 * @param integer $pageId
	 * @return array
	 */
	public function getPageData($pageId);

	/**
	 * Returns the boxplaces for a given template
	 *
	 * @param integer $templateId
	 * @return array
	 */
	public function getBoxPlacesByTemplate($templateId);

	/**
	 * Returns the specified boxplace's data
	 *
	 * @param integer $boxplaceId
	 * @return array
	 */
	public function getBoxPlaceById($boxplaceId);

	/**
	 * Adds a new default page (default doc page, or main page) to the specified theme
	 *
	 * @param integer $themeId
	 * @param integer $pageId
	 * @param integer $type
	 * @return integer The id of the relation
	 */
	public function addDefaultPageToTheme($themeId, $pageId, $type);

	/**
	 * Returns the list of themes that the specified page is a default page for
	 *
	 * @param integer $pageId
	 * @param integer $type
	 * @return array
	 */
	public function getDefaultPageThemes($pageId, $type);

	/**
	 * Returns a page-object relation by its id
	 *
	 * @param integer $relId
	 * @return array
	 */
	public function getObjectRelById($relId);

	/**
	 * Deletes a page-object relation by its id
	 *
	 * @param integer $relId
	 */
	public function deleteObjectRel($relId);

	/**
	 * Saves an object and page relation
	 *
	 * @param string $type
	 * @param integer $pageId
	 * @param integer $objectId
	 * @return boolean
	 */
	public function savePageObject($type, $pageId, $objectId);

	/**
	 * Deletes all objects of the specified type for a given page except the ones listed in the $dontDeleteObjects array
	 *
	 * @param integer $type
	 * @param integer $pageId
	 * @param array $dontDeleteObjects
	 * @return string Empty string if successful, the errormessage otherwise
	 */
	public function deletePageObjects($type, $pageId, $dontDeleteObjects = array());

	/**
	 * Returns the Page ID for the specified object using the given theme and type or null if it's not found
	 *
	 * @param integer $objectId
	 * @param integer $themeId
	 * @param integer $type
	 * @return integer
	 */
	public function getPageIdForObject($objectId, $themeId, $type);
}
?>