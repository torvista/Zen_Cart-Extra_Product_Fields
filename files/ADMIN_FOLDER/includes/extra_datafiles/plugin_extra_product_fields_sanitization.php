<?php
declare(strict_types=1);

/**
 * Plugin Extra Product Fields
 * Sanitize POST variable products_description2
 *
 * https://github.com/torvista/Zen_Cart-Extra_Product_Fields
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version plugin_extra_product_fields_sanitization.php 23 Jan 2026 torvista
 */

if (class_exists('AdminRequestSanitizer') && method_exists('AdminRequestSanitizer', 'getInstance')) {
    $plugin_extra_product_fields_sanitizer = AdminRequestSanitizer::getInstance();
    $plugin_extra_product_fields_sanitizer->addSimpleSanitization('PRODUCT_DESC_REGEX', ['products_description2']);
}
