<?php

/**
 * Bright Cloud Studio's Isotope List Ordered Products
 *
 * Copyright (C) 2023 Bright Cloud Studio
 *
 * @package    bright-cloud-studio/isotope-list-ordered-products
 * @link       https://www.brightcloudstudio.com/
 * @license    http://opensource.org/licenses/lgpl-3.0.html
**/


// Palette for our module
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_list_ordered_products'] = '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_listingSortField,iso_listingSortDirection,enableBatchAdd,enableOrderedFilter;{redirect_legend},iso_link_primary,iso_jump_first,iso_addProductJumpTo,iso_wishlistJumpTo,moss_shareProtocolJumpTo,moss_deleteProtocolJumpTo,moss_shareSavedListJumpTo,moss_deleteSavedListJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_disable_options,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['fields']['enableBatchAdd'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['enableBatchAdd'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);
$GLOBALS['TL_DCA']['tl_module']['fields']['enableOrderedFilter'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['enableOrderedFilter'],
    'exclude'                   => true,
    'inputType'                 => 'checkbox',
    'eval'                      => array('tl_class'=>'w50'),
    'sql'                       => "char(1) NOT NULL default ''",
);
