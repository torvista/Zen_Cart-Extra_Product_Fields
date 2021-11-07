<?php
//Plugin Extra Product Fields
//https://github.com/torvista/Zen_Cart-Extra_Product_Fields
declare(strict_types=1);

/**
 * Class zcObserverPluginExtraProductFields
 */
class zcObserverPluginExtraProductFields extends base
{
    public function __construct()
    {
        //////////////////////////////////
        //products_ean	varchar(13)	utf8mb4_unicode_520_ci
        //products_google_product_category	varchar(6)	utf8mb4_unicode_520_ciop
        //products_mpn varchar(20)	utf8mb4_unicode_520_ci

        $this->extra_product_fields = [];
        $this->extra_product_fields[] = ['name' => 'ean', 'varchar' => 13];
        $this->extra_product_fields[] = ['name' => 'google_product_category', 'varchar' => 6];
        $this->extra_product_fields[] = ['name' => 'mpn', 'varchar' => 20];
        //ADD MORE FIELDS HERE. Also create corresponding language defines for the field label and placeholder

        //$this->install();//uncomment to install new fields, comment out for normal use
        /////////////////////////////////

        $this->attach($this, [
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS', // product page: add field for discount
            'NOTIFY_MODULES_UPDATE_PRODUCT_END'               // update_product: handle extra POST vars to insert in product table
        ]);
    }

    /**
     * @param $class
     * @param $eventID
     * @param $p1 $pInfo
     * @param $p2 $extra_product_inputs
     *            html input field for discount bands
     */
    protected function notify_admin_product_collect_info_extra_inputs(&$class, $eventID, $p1, &$p2): void
    {
        global $db;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $sql = "SELECT products_" . $extra_product_field['name'] . " FROM " . TABLE_PRODUCTS . " WHERE products_id = :productsId";
            $sql = $db->bindVars($sql, ':productsId', $_GET['pID'], 'integer');//GET used, as pInfo does not have products_id set when Back is used from Preview page
            $result = $db->Execute($sql);

            //for label only
            $text = constant('PLUGIN_EXTRA_PRODUCT_FIELDS_LABEL_' . strtoupper($extra_product_field['name']));
            $addl_class = null; //as test is isset, not empty
            $parms = null;
            $field_name = 'products_' . $extra_product_field['name'];

            $placeholder = constant('PLUGIN_EXTRA_PRODUCT_FIELDS_PLACEHOLDER_' . strtoupper($extra_product_field['name']));
            $input = zen_draw_input_field($field_name, $result->fields[$field_name],
                ' class="form-control"
                id="' . $field_name . '"' .
                ' maxlength="' . $extra_product_field['varchar'] . '"' .
                ($placeholder === '' ? '' : ' placeholder="' . htmlspecialchars(stripslashes($placeholder), ENT_COMPAT, CHARSET) . '"'));

            $p2[] = ['label' => compact('text', 'addl_class', 'parms', 'field_name'), 'input' => $input];
        }
    }

    /**
     * @param $class
     * @param $eventID
     * @param $p1
     *           update_product: handle extra POST vars to insert in product table
     */
    protected function notify_modules_update_product_end(&$class, $eventID, $p1): void
    {
        global $db;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $product_extra_field_postname = 'products_' . $extra_product_field['name'];
            $product_extra_field_data = $_POST[$product_extra_field_postname] ?? '';

            $sql = "UPDATE " . TABLE_PRODUCTS . " SET products_" . $extra_product_field['name'] . "= '" . $product_extra_field_data . "' WHERE products_id = " . (int)$_GET['pID'];

            $message = 'Updated product (#' . (int)$_GET['pID'] . '), extra_field products_' . $extra_product_field['name'] . ' ("' . $product_extra_field_data . '")';
            //$messageStack->add_session($message, 'caution');
            $db->Execute($sql);
            zen_record_admin_activity($message, 'notice');
        }
    }

    private function install(): void
    {
        global $db, $messageStack, $sniffer;

        foreach ($this->extra_product_fields as $extra_product_field) {
            if ($sniffer->field_exists(TABLE_PRODUCTS, 'products_' . $extra_product_field['name'])) {
                $messageStack->add('Plugin Extra Product Fields: field "products_' . $extra_product_field['name'] . '" already exists in table "' . TABLE_PRODUCTS . '"');
            } else {
                $db->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD `products_" . $extra_product_field->fields['name'] . "` VARCHAR(" . $extra_product_field['varchar']
                    . ") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT");
                $messageStack->add('Plugin Extra Product Fields: field "products_' . $extra_product_field['name'] . ' added to table "' . TABLE_PRODUCTS . '".', 'success');
            }
        }
    }
}
