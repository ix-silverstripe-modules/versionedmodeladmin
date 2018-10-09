VersionedModelAdmin
=======================================

this is for page or versioned page Model Admin

data objects like News, Store, Event and Product should be managed in ModelAdmin which extended from VersionedModelAdmin

Maintainer Contact
------------------
*  Guy Watson (<guy.watson@internetrix.com.au>)
*  Stewart Wilson (<stewart.wilson@internetrix.com.au>)

## Requirements

SilverStripe \^4.0

## Dependencies

The VersionedGridFieldItemRequestExtension  extension will only be applied if the Better Buttons module exists. 
This is because VersionedGridFieldItemRequestExtension introduces form actions that are defined in the betterbuttons extension GridFieldBetterButtonsItemRequest

## Example

	class NewsAdmin extends VersionedModelAdmin {
		
		private static $title       = 'News';
		private static $menu_title  = 'News';
		private static $url_segment = 'news';
	
		private static $managed_models  = array('News');
		private static $model_importers = array();
		
	}
    