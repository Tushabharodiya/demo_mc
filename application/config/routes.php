<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Category Mods Functions
$route['category-mods-new'] = 'android/categoryModsNew';
$route['category-mods-view'] = 'android/categoryModsView';
$route['category-mods-view'.'/(:num)'] = 'android/categoryModsView/$1';
$route['category-mods-edit'.'/(:any)'] = 'android/categoryModsEdit/$1';

//Category Addons Functions
$route['category-addons-new'] = 'android/categoryAddonsNew';
$route['category-addons-view'] = 'android/categoryAddonsView';
$route['category-addons-view'.'/(:num)'] = 'android/categoryAddonsView/$1';
$route['category-addons-edit'.'/(:any)'] = 'android/categoryAddonsEdit/$1';

//Category Maps Functions
$route['category-maps-new'] = 'android/categoryMapsNew';
$route['category-maps-view'] = 'android/categoryMapsView';
$route['category-maps-view'.'/(:num)'] = 'android/categoryMapsView/$1';
$route['category-maps-edit'.'/(:any)'] = 'android/categoryMapsEdit/$1';

//Category Seeds Functions
$route['category-seeds-new'] = 'android/categorySeedsNew';
$route['category-seeds-view'] = 'android/categorySeedsView';
$route['category-seeds-view'.'/(:num)'] = 'android/categorySeedsView/$1';
$route['category-seeds-edit'.'/(:any)'] = 'android/categorySeedsEdit/$1';

//Category Textures Functions
$route['category-textures-new'] = 'android/categoryTexturesNew';
$route['category-textures-view'] = 'android/categoryTexturesView';
$route['category-textures-view'.'/(:num)'] = 'android/categoryTexturesView/$1';
$route['category-textures-edit'.'/(:any)'] = 'android/categoryTexturesEdit/$1';

//Category Shaders Functions
$route['category-shaders-new'] = 'android/categoryShadersNew';
$route['category-shaders-view'] = 'android/categoryShadersView';
$route['category-shaders-view'.'/(:num)'] = 'android/categoryShadersView/$1';
$route['category-shaders-edit'.'/(:any)'] = 'android/categoryShadersEdit/$1';

//Category Skin Functions
$route['category-skin-new'] = 'android/categorySkinNew';
$route['category-skin-view'] = 'android/categorySkinView';
$route['category-skin-view'.'/(:num)'] = 'android/categorySkinView/$1';
$route['category-skin-edit'.'/(:any)'] = 'android/categorySkinEdit/$1';

//Mods Functions
$route['mods-new'] = 'android/modsNew';
$route['mods-view'] = 'android/modsView';
$route['mods-view'.'/(:num)'] = 'android/modsView/$1';
$route['mods-edit'.'/(:any)'] = 'android/modsEdit/$1';

//Addons Functions
$route['addons-new'] = 'android/addonsNew';
$route['addons-view'] = 'android/addonsView';
$route['addons-view'.'/(:num)'] = 'android/addonsView/$1';
$route['addons-edit'.'/(:any)'] = 'android/addonsEdit/$1';

//Maps Functions
$route['maps-new'] = 'android/mapsNew';
$route['maps-view'] = 'android/mapsView';
$route['maps-view'.'/(:num)'] = 'android/mapsView/$1';
$route['maps-edit'.'/(:any)'] = 'android/mapsEdit/$1';

//Seeds Functions
$route['seeds-new'] = 'android/seedsNew';
$route['seeds-view'] = 'android/seedsView';
$route['seeds-view'.'/(:num)'] = 'android/seedsView/$1';
$route['seeds-edit'.'/(:any)'] = 'android/seedsEdit/$1';

//Textures Functions
$route['textures-new'] = 'android/texturesNew';
$route['textures-view'] = 'android/texturesView';
$route['textures-view'.'/(:num)'] = 'android/texturesView/$1';
$route['textures-edit'.'/(:any)'] = 'android/texturesEdit/$1';

//Shaders Functions
$route['shaders-new'] = 'android/shadersNew';
$route['shaders-view'] = 'android/shadersView';
$route['shaders-view'.'/(:num)'] = 'android/shadersView/$1';
$route['shaders-edit'.'/(:any)'] = 'android/shadersEdit/$1';

//Skin Functions
$route['skin-new'] = 'android/skinNew';
$route['skin-view'] = 'android/skinView';
$route['skin-view'.'/(:num)'] = 'android/skinView/$1';
$route['skin-edit'.'/(:any)'] = 'android/skinEdit/$1';

//Search Functions
$route['search-view'] = 'android/searchView';
$route['search-view'.'/(:num)'] = 'android/searchView/$1';
$route['search-added-view'] = 'android/searchAddedView';
$route['search-added-view'.'/(:num)'] = 'android/searchAddedView/$1';
$route['search-edit'.'/(:any)'] = 'android/searchEdit/$1';
$route['search-delete'.'/(:any)'] = 'android/searchDelete/$1';

$route['app-new'] = 'notification/appNew';
$route['app-view'] = 'notification/appView';
$route['app-view'.'/(:num)'] = 'notification/appView/$1';
$route['app-edit'.'/(:any)'] = 'notification/appEdit/$1';

$route['notification-new'] = 'notification/notificationNew';
$route['notification-view'] = 'notification/notificationView';
$route['notification-view'.'/(:num)'] = 'notification/notificationView/$1';
$route['notification-edit'.'/(:any)'] = 'notification/notificationEdit/$1';

$route['send-notification-GKGF-5896-AKSD-4583-JADJ-3485-SRDL-8653'] = 'notification/notificationSend_GKGF_5896_AKSD_4583_JADJ_3485_SRDL_8653/';

// Bucket Copy Function
$route['copy-bucket'.'/(:any)'] = 'android/copyData/$1';

//Common Settings
$route['default_controller'] = 'dashboard';
$route['404_override'] = 'error404';
$route['translate_uri_dashes'] = FALSE;



