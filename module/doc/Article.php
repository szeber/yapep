<?php

/**
 * This file is part of YAPEP.
 *
 * @package	YAPEP
 * @subpackage	DocModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */

/**
 * Article document class
 *
 * @package	YAPEP
 * @subpackage	DocModule
 * @author		Zsolt Szeberenyi <szeber@svinformatika.hu>
 * @copyright	2008 The YAPEP Project All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version	$Rev$
 */
class module_doc_Article extends sys_DocModule
{

    public function getRelatedArticles ()
    {
        $data = $this->objectDb->getRelList($this->docData['FullObject']['id'],
            module_db_interface_Article::REL_ARTICLE);
        return $data;
    }

    public function getRelatedFiles ()
    {
        $data = $this->objectDb->getRelList($this->docData['FullObject']['id'],
            module_db_interface_Article::REL_FILE);
        $assetDb = getPersistClass('Asset');
        return $assetDb->addAssetTypeData($data);
    }

    public function getRelatedPictures ()
    {
        $data = $this->objectDb->getRelList($this->docData['FullObject']['id'],
            module_db_interface_Article::REL_PICTURE);
        return $data;
    }
}
?>