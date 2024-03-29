<?php
/**
 *
 *
 * @version	$Rev: 10719 $
 */

/**
 *
 *
 * @arg			folder "root_folder" "Gyoker konyvtar" 0 "" 0
 * @arg			folderlist "include_folders" "Keresesben befoglalt mappak" 0 "" 0
 * @arg			folderlist "exclude_folders" "Keresesbol kihagyott mappak" 0 "" 0
 * @arg			text "doc_count" "Oldalankenti dokumentumok szama" 0 "6" 0
 * @version	$Rev: 10719 $
 */
class module_box_ArticleSearch extends sys_BoxModule {
	protected function main() {
		if (!$_GET['search']) {
			return $this->smartyFetch();
		}
		$doc = new module_doc_Article(getPersistClass('Article'));
		$docCount = $doc->getFindDocCount($this->argArr['locale_id'], $this->argArr['root_folder'], $_GET['search'], explode(',',$this->argArr['include_folders']), explode(',',$this->argArr['exclude_folders']));

		$maxPages = ceil($docCount / $this->argArr['doc_count']);
		$currentPage = (int)$_GET['page'];
		if ($currentPage > $maxPages) {
			$currentPage = $maxPages;
		}
		if ($currentPage<=0) {
			$currentPage = 1;
		}
		$offset = ($currentPage - 1) * $this->argArr['doc_count'];

		$this->smarty->assign('docCount', $docCount);
		$this->smarty->assign('currentPage', $currentPage);
		$this->smarty->assign('maxPages', $maxPages);

		$docs = $doc->findDoc($this->argArr['locale_id'], $this->argArr['root_folder'], $_GET['search'], $this->argArr['doc_count'], $offset, explode(',',$this->argArr['include_folders']), explode(',', $this->argArr['exclude_folders']));
		$docData = array();
		foreach($docs as $doc) {
			$docData[] = $doc->getDocData();
		}

        $this->smarty->assign('startItem', $offset+1);
        if ($currentPage >= $maxPages) {
            $this->smarty->assign('endItem', $docCount);
        } else {
            $this->smarty->assign('endItem', $offset+$this->argArr['doc_count']);
        }
		$this->smarty->assign('docData', $docData);
		return $this->smartyFetch();
	}
}
?>