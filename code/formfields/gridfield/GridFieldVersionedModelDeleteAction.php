<?php

namespace SilverStripe\Internetrix\VersionedModelAdmin;

use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\CMS\Model\SiteTree;

class GridFieldVersionedModelDeleteAction extends GridFieldDeleteAction {

	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param mixed $arguments
	 * @param array $data - form data
	 * @return void
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if($actionName == 'deleterecord' || $actionName == 'unlinkrelation') {
			$item = $gridField->getList()->byID($arguments['RecordID']);
			if(!$item) {
				return;
			}

			if($actionName == 'deleterecord') {
				$member = Member::currentUserID();

				if($item instanceof SiteTree){
					//if it's a page. call doUnpublish() before delete it.
					if( ! $item->canDeleteFromLive($member)){
						throw new ValidationException('No delete permissions for live site',0);
					}
						
					$item->doUnpublish();
						
				}elseif ($item->hasExtension('Versioned')){
					//if data object has versioned extension. delete the live record first.

					$item->deleteFromStage('Live');
				}

				//ok. do the normal delete().
				if(!$item->canDelete()) {
					throw new ValidationException(
							_t('GridFieldAction_Delete.DeletePermissionsFailure',"No delete permissions"),0);
				}

				$item->delete();
			} else {
				if(!$item->canEdit()) {
					throw new ValidationException(
							_t('GridFieldAction_Delete.EditPermissionsFailure',"No permission to unlink record"),0);
				}

				$gridField->getList()->remove($item);
			}
		}
	}

}