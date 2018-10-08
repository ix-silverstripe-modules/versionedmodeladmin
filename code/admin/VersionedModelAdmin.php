<?php
/**
 * this is for page or versioned page Model Admin
 * 
 * data objects like News, Store, Event and Product should be managed in ModelAdmin which extended from VersionedModelAdmin
 * 
 * 
 * For example.
 * 
	class NewsAdmin extends VersionedModelAdmin {
		
		private static $title       = 'News';
		private static $menu_title  = 'News';
		private static $url_segment = 'news';
	
		private static $managed_models  = array('News');
		private static $model_importers = array();
		
	}
 *
 *	
 */

namespace SilverStripe\Internetrix\VersionedModelAdmin;

use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Admin\ModelAdmin;

abstract class VersionedModelAdmin extends ModelAdmin {

	public function init(){
		
		parent::init();
		
		Versioned::reading_stage("Stage");
	}
	
	
	public function getEditForm($id = null, $fields = null){
		$form = parent::getEditForm($id, $fields);
		
		$GridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));
		
		$GridField
			->getConfig()
			->removeComponentsByType('GridFieldDeleteAction')
			->addComponent(new GridFieldVersionedModelDeleteAction())
		;
		
		return $form;
	}
	
	
	
	public function getSearchContext() {
		
		$context = parent::getSearchContext();
		
		$modelClass = $this->modelClass;
			
		if($modelClass::has_extension('Versioned')) {
			$context
				->getFields()
				->push(
					DropdownField::create('q[Status]', 'Status', array('Published' => "Published", 'Unpublished' => "Unpublished"))
						->setHasEmptyDefault(true)
				);
		}
		
		return $context;
	}
	
	public function getList() {
		$list = parent::getList();
		
		$params = $this->request->requestVar('q'); // use this to access search parameters
		
		$modelClass = $this->modelClass;
	
		if($modelClass::has_extension('Versioned')) {
	
			if(isset($params['Status']) && $params['Status']){
				$tableName = $modelClass;
				
				$ids = DB::query("SELECT \"ID\" FROM \"{$tableName}_Live\"")->keyedColumn();
					
				if($params['Status'] == "Published"){
					$list = $list->filter('ID', $ids);
				}else{
					$list = $list->exclude('ID', $ids);
				}
			}
		}
	
		return $list;
	}
	
	
}



?>