/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.allowedContent = true;
	config.entities  = false;
	config.basicEntities = true;
	config.entities_greek = false;
	config.entities_latin = false;
	config.language = 'cs';
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';


    config.filebrowserImageBrowseUrl = PATHS['basePath'] + '/www/intra/lib/kcfinder/browse.php?opener=ckeditor&type=image-inline';
    config.filebrowserUploadUrl = PATHS['basePath'] + '/www/intra/lib/kcfinder/browse.php?opener=ckeditor&type=document';
    config.filebrowserBrowseUrl = PATHS['basePath'] + '/www/intra/lib/kcfinder/browse.php?opener=ckeditor&type=document';
};
