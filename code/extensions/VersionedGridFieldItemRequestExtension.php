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

namespace Internetrix\VersionedModelAdmin;

use SilverStripe\Core\Extension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Security;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use UncleCheese\BetterButtons\Actions\BetterButtonCustomAction;


class VersionedGridFieldItemRequestExtension extends Extension {
	
	private static $allowed_actions = array(
		'delete'
	);
	
	public function updateItemEditForm(Form $form) {
		$record 	= $form->getRecord();
		
		if($record->hasExtension('Versioned') && $record instanceof SiteTree){
			$actions 	= $form->Actions();
				
			if($record->ID){
				foreach ($actions as $action){
					if(!$action instanceof BetterButtonCustomAction) $actions->remove($action);
				}
				
				$newActions = $record->getCMSActions();
				foreach ($newActions as $newAction){
					$newAction->setForm($form);
					$actions->push($newAction);
				}
			}else{
				$actions->removeByName('action_publish');
			}
			
			if($record->isPublished()){
				$actions->removeByName('action_delete');
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
	
	public function delete($data, $form) {
		$title = $this->owner->record->Title;
		try {
			if (!$this->owner->record->canDelete()) {
				throw new ValidationException(
					_t('GridFieldDetailForm.DeletePermissionsFailure',"No delete permissions"),0);
			}

			$this->owner->record->delete();
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad', false);
			return $this->owner->getToplevelController()->redirectBack();
		}

		$message = sprintf(
			_t('GridFieldDetailForm.Deleted', 'Deleted %s %s'),
			$this->owner->record->i18n_singular_name(),
			htmlspecialchars($title, ENT_QUOTES)
		);
		
		$toplevelController = $this->owner->getToplevelController();
		if($toplevelController && $toplevelController instanceof LeftAndMain) {
			$backForm = $toplevelController->getEditForm();
			$backForm->sessionMessage($message, 'good', false);
		} else {
			$form->sessionMessage($message, 'good', false);
		}

		//when an item is deleted, redirect to the parent controller
		$controller = $this->owner->getToplevelController();
		$controller->getRequest()->addHeader('X-Pjax', 'Content'); // Force a content refresh

		return $controller->redirect($this->owner->getBacklink(), 302); //redirect back to admin section
	}
	
	public function archive($data, $form) {
		
		if(!$this->owner->record || !$this->owner->record->exists()) {
			throw new SS_HTTPResponse_Exception("Bad record ID", 404);
		}
		
		if(!$this->owner->record->canArchive()) {
			return Security::permissionFailure();
		}
		
		$title = $this->owner->record->Title;
	
		// Archive record
		$this->owner->record->doArchive();
		
		$message = sprintf(
			_t('GridFieldDetailForm.Deleted', 'Deleted %s %s'),
			$this->owner->record->i18n_singular_name(),
			htmlspecialchars($title, ENT_QUOTES)
		);
		
		$toplevelController = $this->owner->getToplevelController();
		if($toplevelController && $toplevelController instanceof LeftAndMain) {
			$backForm = $toplevelController->getEditForm();
			$backForm->sessionMessage($message, 'good', false);
		} else {
			$form->sessionMessage($message, 'good', false);
		}
		
		
		//when an item is deleted, redirect to the parent controller
		$controller = $this->owner->getToplevelController();
		$controller->getRequest()->addHeader('X-Pjax', 'Content'); // Force a content refresh
		
		return $controller->redirect($this->owner->getBacklink(), 302); //redirect back to admin section
	}
	
	public function getToplevelController() {
		$c = $this->owner->popupController;
		while($c && $c instanceof GridFieldDetailForm_ItemRequest) {
			$c = $c->getController();
		}
		return $c;
	}

}