<?php
/**
 * 
 * This extension changes the model admin actions to those that we are use to seeing the CMSMain
 * The BetterButtons module is required so that these actions will actually work in a model admin. 
 * Please see GridFieldBetterButtonsItemRequest.php
 * 
 * @author  guy.watson@internetrix.com.au
 * @package versionedmodeladmin
 */
class VersionedGridFieldItemRequestExtension extends Extension {
	
	public function updateItemEditForm($form) {

		$record 	= $form->getRecord();
		
		if($record->hasExtension('Versioned')){
			$actions 	= $form->Actions();
				
			foreach ($actions as $action){
				$actions->remove($action);
			}
				
			$newActions = $record->getCMSActions();
			foreach ($newActions as $newAction){
				$actions->push($newAction);
			}
				
			// Find and remove action menus that have no actions.
			if ($actions && $actions->Count()) {
				$tabset = $actions->fieldByName('ActionMenus');
				if ($tabset) {
					foreach ($tabset->getChildren() as $tab) {
						if (!$tab->getChildren()->count()) {
							$tabset->removeByName($tab->getName());
						}
					}
				}
			}
				
			$actionsFlattened = $actions->dataFields();
			if($actionsFlattened) foreach($actionsFlattened as $action) $action->setUseButtonTag(true);
			
			$form->addExtraClass('cms-edit-form');
			$form->setActions($actions);
		}
		
	}

}
