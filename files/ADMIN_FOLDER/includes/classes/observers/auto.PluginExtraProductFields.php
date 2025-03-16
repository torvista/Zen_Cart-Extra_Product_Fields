<?php

declare(strict_types=1);

/**
 * Plugin Extra Product Fields
 * https://github.com/torvista/Zen_Cart-Extra_Product_Fields
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 16 March 2025 torvista
 */

/**
 * Class zcObserverPluginExtraProductFields
 */
class zcObserverPluginExtraProductFields extends base
{
    public array $extra_product_fields;

    public function __construct()
    {
        // example new product field names
        // Note the value used for varchar will also determine the input field length
        // Note the varchar field is only used for the install method.
        $this->extra_product_fields[] = ['name' => 'products_gtin', 'varchar' => 13];
        $this->extra_product_fields[] = ['name' => 'products_google_product_category', 'varchar' => 6];

        // ADD MORE FIELDS HERE
        // Note that only type VARCHAR can be auto-added using the above format.
        // If you need to use another type, you'll have to code it or add it manually, e.g. with phpMyadmin
        // You will also need to create the corresponding language constant for the field label and placeholder.

        // Enable the install method
        //$this->install();//uncomment to install the new fields, comment out for normal use

        /////////////////////////////////

        $this->attach($this, [
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS', // product page: add field for discount
            'NOTIFY_MODULES_UPDATE_PRODUCT_END'               // update_product: handle extra POST vars to insert in product table
        ]);
    }

    /**
     * Insert the input fields into the Admin Product Edit page
     * @param $class
     * @param $eventID
     * @param $pInfo
     * @param $extra_product_inputs
     */
    protected function notify_admin_product_collect_info_extra_inputs(&$class, $eventID, $pInfo, &$extra_product_inputs): void
    {
        global $db;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $field_name = $extra_product_field['name'];

            // Get database values for an existing product
            // GET is used here as pInfo $pInfo['products_id'] is empty for a NEW product creation
            // and when Back is used from the Product Edit Preview page.
            if (!empty($_GET['pID'])) {
                $sql = "SELECT " . $field_name . " FROM " . TABLE_PRODUCTS . " WHERE products_id = :productsId";
                $sql = $db->bindVars($sql, ':productsId', $_GET['pID'], 'integer');
                $result = $db->Execute($sql);
            }
            // for label only
            // label constant name e.g. PLUGIN_EXTRA_PRODUCT_FIELDS_LABEL_PRODUCTS_GTIN
            $text = constant('PLUGIN_EXTRA_PRODUCT_FIELDS_LABEL_' . strtoupper($field_name));
            $addl_class = null; //as core code tests for these variables use isset instead of empty
            $parms = null;

            if (!empty($_POST[$field_name])) { // Preview->Back button was used: use the new value
                $fieldData = $_POST[$field_name];
            } elseif (!empty($result->fields[$field_name])) { // use value from the database
                $fieldData = $result->fields[$field_name];
            } else {
                $fieldData = '';
            }
            // placeholder constant name e.g. PLUGIN_EXTRA_PRODUCT_FIELDS_PLACEHOLDER_PRODUCTS_GTIN
            $placeholder = constant('PLUGIN_EXTRA_PRODUCT_FIELDS_PLACEHOLDER_' . strtoupper($field_name));
            $field_length = zen_field_length(TABLE_PRODUCTS, $field_name);
            $input = zen_draw_input_field(
                $field_name,
                zen_output_string_protected($fieldData),
                ' class="form-control" id="' . $field_name . '"' .
                ($placeholder === '' ? '' : ' placeholder="' . htmlspecialchars(stripslashes($placeholder), ENT_COMPAT, CHARSET) . '"') .
                ' maxlength="' . $field_length . '"'
            );


        $extra_product_inputs[] = ['label' => compact('text', 'addl_class', 'parms', 'field_name'), 'input' => $input];
        }
    }

    /**
     * update_product: handle the extra POST vars to insert in the product table
     * @param $class
     * @param $eventID
     * @param  array  $p1
     */
    protected function notify_modules_update_product_end(&$class, $eventID, array $p1): void
    {
        global $db;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $product_extra_field_postname = $extra_product_field['name'];
            $product_extra_field_data = $_POST[$product_extra_field_postname] ?? '';
            $sql = 'UPDATE ' . TABLE_PRODUCTS . ' SET ' . $extra_product_field['name'] . "= '" . $product_extra_field_data . "' WHERE products_id = " . (int)$p1['products_id'];
            // $message = 'Updated product (#' . (int)$p1['products_id'] . '), extra_field products_' . $extra_product_field['name'] . ' ("' . $product_extra_field_data . '")';
            //$messageStack->add_session($message, 'caution');
            $db->Execute($sql);
            //  zen_record_admin_activity($message, 'notice');
        }
    }

    /**
     * Install the extra product fields
     * @return void
     */
    private function install(): void
    {
        global $db, $messageStack, $sniffer;
        foreach ($this->extra_product_fields as $extra_product_field) {
            if ($sniffer->field_exists(TABLE_PRODUCTS, $extra_product_field['name'])) {
                $messageStack->add('Plugin Extra Product Fields: field "' . $extra_product_field['name'] . '" already exists in table "' . TABLE_PRODUCTS . '"', 'caution');
            } else {
                $db->Execute(
                    'ALTER TABLE ' . TABLE_PRODUCTS . ' ADD ' . $extra_product_field['name'] . ' VARCHAR(' . (int)$extra_product_field['varchar']
                    . ') NOT NULL DEFAULT ""'
                );
                $messageStack->add('Plugin Extra Product Fields: field "' . $extra_product_field['name'] . ' added to table "' . TABLE_PRODUCTS . '".', 'success');
            }
        }
    }
}
