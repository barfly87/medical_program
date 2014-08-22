﻿/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.editorConfig = function( config )
{
	//Kamal Soni changed 'htdocs/js/ckeditor_3_0/plugins/stylescombo/styles/default.js' to configure 
    //styles which was not possible to do in this file because of iframe within iframe issue in firefox
    
    //CONFIGURATION 
    
    //SCAYT
	config.scayt_autoStartup = true;
	config.disableNativeSpellChecker = false;
	config.scayt_uiTabs = '1,1,1';
    //config.scayt_sLang = 'en_US' ;
	
	//HEIGHT WIDTH OF THE BOX
	HEIGHT = window.innerHeight ?
                 window.innerHeight :
                     ((document.documentElement.clientHeight == 0) ? 
            		  document.body.clientHeight:
            				document.documentElement.clientHeight ); 
	WIDTH = window.innerWidth ?
            window.innerWidth : 
                ((document.documentElement.clientWidth == 0) ? 
                  document.body.clientWidth :  
                  document.documentElement.clientWidth );
	HEIGHT = Math.floor(HEIGHT * 0.3);
	WIDTH = Math.floor(WIDTH * 0.7);
	config.height = HEIGHT;
	config.width = WIDTH;
	
	config.uiColor = '#FFEBCF';
	config.contentsCss = BASE_URL + '/js/ckeditor_3_0/ckeditor_config.css';
	config.menu_subMenuDelay = 0;
	config.dialog_backgroundCoverOpacity = 0.7;
	config.dialog_backgroundCoverColor = 'rgb(159, 159, 159)';
	
	config.format_tags="p;h2;pre;address;div";
	config.format_h2 = {element:'h2',attributes:{'class':'ckeditor_h2'}};
	config.format_p = { element : 'p', attributes : { 'class' : 'ckeditor_p' } };
	config.p = { element : 'p', attributes : { 'class' : 'ckeditor_p' } };

	config.format_div = {element:'div',attributes:{'class':'ckeditor_div'}};	
	config.fontSize_sizes = "8/8px;10/10px;12/12px;14/14px;16/16px";
	config.colorButton_colors = '2f1d03,306fdf,666666,dfae6f,a3d7ff,fa9300,ff0600,ffd79f,ffebcf,fff1df,ffffff';
	config.extraPlugins = 'pluginTidy';
    config.toolbar = 'MyToolbar';
    config.toolbar_MyToolbar =	
	[
	    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','SpellChecker'],
	    //['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','SpellChecker', 'Scayt'],
	    
	    ['Find','Replace','-','SelectAll','RemoveFormat'],
	    ['Maximize', 'ShowBlocks','-','Source','Preview'],
	    '/',
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Table','HorizontalRule','SpecialChar','PageBreak'],
	    '/',
	    ['Styles','Format','FontSize'],
	    ['TextColor','BGColor'],
	    ['Link','Unlink','Anchor','-','Image','Flash','-','Print'],
	    ['TidyButton']
	];
};

/*
CKEDITOR.addStylesSet('default',[{name:'Big',element:'big'},
                                 {name:'Small',element:'small'},
                                 {name:'Typewriter',element:'tt'},
                                 {name:'Computer Code',element:'code'},
                                 {name:'Keyboard Phrase',element:'kbd'},
                                 {name:'Sample Text',element:'samp'},
                                 {name:'Variable',element:'var'},
                                 {name:'Deleted Text',element:'del'},
                                 {name:'Inserted Text',element:'ins'},
                                 {name:'Cited Work',element:'cite'},
                                 {name:'Inline Quotation',element:'q'},
                                 {name:'Language: RTL',element:'span',attributes:{dir:'rtl'}},
                                 {name:'Language: LTR',element:'span',attributes:{dir:'ltr'}},
                                 {name:'Image on Left',element:'img',attributes:{style:'padding: 5px; margin-right: 5px',border:'2',align:'left'}},
                                 {name:'Image on Right',element:'img',attributes:{style:'padding: 5px; margin-left: 5px',border:'2',align:'right'}}
                                 ]);
CKEDITOR.addStylesSet('my_styles',	[
										{name:'h1',element:'h1'},
										{name:'h2',element:'h2'},
										{name:'h3',element:'h3'}
									]);
//CKEDITOR.config.toolbar_Minimal=[ ['Source','Bold','Italic','Underline'] ];
config.stylesCombo_stylesSet = 'my_styles';*/
/*
config.enterMode = CKEDITOR.ENTER_BR;
This is the default value for the toolbar which I have changed to above.
config.toolbar = 'MyToolbar';
config.toolbar_MyToolbar =
[
    ['Source','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks','-','About']
];

In the file 
compass/htdocs/js/ckeditor_3_0/plugins/stylescombo/styles/default.js
The default addStyleSet is as under.
CKEDITOR.addStylesSet('default',[{name:'Blue Title',element:'h3',styles:{color:'Blue'}},{name:'Red Title',element:'h3',styles:{color:'Red'}},{name:'Marker: Yellow',element:'span',styles:{'background-color':'Yellow'}},{name:'Marker: Green',element:'span',styles:{'background-color':'Lime'}},{name:'Big',element:'big'},{name:'Small',element:'small'},{name:'Typewriter',element:'tt'},{name:'Computer Code',element:'code'},{name:'Keyboard Phrase',element:'kbd'},{name:'Sample Text',element:'samp'},{name:'Variable',element:'var'},{name:'Deleted Text',element:'del'},{name:'Inserted Text',element:'ins'},{name:'Cited Work',element:'cite'},{name:'Inline Quotation',element:'q'},{name:'Language: RTL',element:'span',attributes:{dir:'rtl'}},{name:'Language: LTR',element:'span',attributes:{dir:'ltr'}},{name:'Image on Left',element:'img',attributes:{style:'padding: 5px; margin-right: 5px',border:'2',align:'left'}},{name:'Image on Right',element:'img',attributes:{style:'padding: 5px; margin-left: 5px',border:'2',align:'right'}}]);
And added custom one above. Horray !!	
 */
