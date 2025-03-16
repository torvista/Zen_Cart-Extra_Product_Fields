<?php

declare(strict_types=1);

/**
 * Plugin Extra Product Fields
 * https://github.com/torvista/Zen_Cart-Extra_Product_Fields
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 16 March 2025 torvista
*/

$define = [
// GTIN
    'PLUGIN_EXTRA_PRODUCT_FIELDS_LABEL_PRODUCTS_GTIN' => 'EAN',
    'PLUGIN_EXTRA_PRODUCT_FIELDS_PLACEHOLDER_PRODUCTS_GTIN' => 'EAN (13 numbers)',
// Google Product Category
    'PLUGIN_EXTRA_PRODUCT_FIELDS_LABEL_PRODUCTS_GOOGLE_PRODUCT_CATEGORY' => 'Google Product Category (<a href="https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.xls" target="_blank" title="Google taxonomy en-US Excel">Excel</a> | <a href="https://www.google.com/basepages/producttype/taxonomy.en-US.txt" target="_blank" title="Google taxonomy en-US TXT">TXT</a>)',
    'PLUGIN_EXTRA_PRODUCT_FIELDS_PLACEHOLDER_PRODUCTS_GOOGLE_PRODUCT_CATEGORY' => '6 numbers',
//add more constants following the pattern: use the new fieldname as suffix
];

return $define;
