<?php

declare(strict_types=1);

/**
 * Plugin Extra Product Fields
 * with WIP code to handle the existence of Numinix Product Fields
 *
 * https://github.com/torvista/Zen_Cart-Extra_Product_Fields
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version auto.PluginExtraProductFields.php 23 Jan 2026 torvista
 */

/**
 * Class zcObserverPluginExtraProductFields
 */
class zcObserverPluginExtraProductFields extends base
{
    protected array $extra_product_fields = [];
    //Numinix Product Fields
    protected array $numinix_product_fields = [];

    public function __construct()
    {
        // example new product field names
        // Note the value used for varchar will also determine the input field length
        // Note the varchar field is only used for the install method.
        //$this->extra_product_fields[] = ['field_name' => 'products_gtin', 'varchar' => 13];
        //$this->extra_product_fields[] = ['field_name' => 'products_google_product_category', 'varchar' => 6];

        // ADD MORE FIELDS HERE
        // Note that only type VARCHAR can be auto-added using the above format.
        // If you need to use another type, you'll have to code it or add it manually, e.g. with phpMyadmin
        // You will also need to create the corresponding language constant for the field label and placeholder.

        //Numinex Product Fields
        //products_condition => Condition:
        //products_description2 => Description 2:
        //products_msrp => MSRP Price:
        //products_wholesale => Wholesale Price:
        $this->numinix_product_fields[] = ['field_name' => 'products_condition', 'table' => TABLE_PRODUCTS];
        $this->numinix_product_fields[] = ['field_name' => 'products_description2', 'table' => TABLE_PRODUCTS_DESCRIPTION];
        $this->numinix_product_fields[] = ['field_name' => 'products_msrp', 'table' => TABLE_PRODUCTS];
        $this->numinix_product_fields[] = ['field_name' => 'products_wholesale', 'table' => TABLE_PRODUCTS];

        // Enable the install method ONLY FOR TESTING OF NPF ON A NEW DB
        //$this->install();//uncomment to install the new fields, comment out for normal use

        /////////////////////////////////

        $this->attach($this, [
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS', // Edit Product page core notifier located after Product Name(s) to display extra input fields
            'NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS_CUSTOM1', // Edit Product page custom notifier 1 located as per custom collect_info.php to display extra input fields
            'NOTIFY_MODULES_UPDATE_PRODUCT_END'               // post-Edit Product page/update_product: parse the extra POST vars to insert in the database
        ]);
    }

    /**
     * Insert/display the input fields into the Admin Product Edit page
     * @param $class
     * @param $eventID
     * @param $pInfo
     * @param $extra_product_inputs
     * $zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS', $pInfo, $extra_product_inputs);
     */
    protected function notify_admin_product_collect_info_extra_inputs(&$class, $eventID, $pInfo, &$extra_product_inputs): void
    {
        global $db, $messageStack, $sniffer;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $field_name = $extra_product_field['field_name'];

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
     * Insert/display the input fields into the Admin Product Edit page at a custom location.
     * Use an override /product/collect.php and add the custom notifier where desired.
     *
     * @param $class
     * @param $eventID
     * @param $pInfo
     * @param $extra_product_inputs
     *
     * $zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS_CUSTOM1', $pInfo, $extra_product_inputs);
     */
    protected function notify_admin_product_collect_info_extra_inputs_custom1(&$class, $eventID, $pInfo, &$extra_product_inputs): void
    {
        global $db, $languages, $messageStack, $sniffer;

        // Handle Numinix Extra Product Fields
        foreach ($this->numinix_product_fields as $npf) {
            if (!$sniffer->field_exists($npf['table'], $npf['field_name'])) {
                $messageStack->add('Plugin Extra Product Fields: Numinix Product field "' . $npf['field_name'] . '" not found in table "' . $npf['table'] . '"', 'error');
                continue;
            }

            // For label and placeholder text
            // You may substitute $label_text with a constant/custom text as required
            switch ($npf['field_name']) {
                case 'products_description2':
                    for ($i = 0, $n = count($languages); $i < $n; $i++) {
                        $label_text = defined('TEXT_PRODUCTS_DESCRIPTION2') ? TEXT_PRODUCTS_DESCRIPTION2 : '"products_description2" (no constant defined)' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);;
                        $placeholder_text = '';
                    }
                    break;
                case 'products_condition':
                    $label_text = defined('TEXT_PRODUCTS_CONDITION') ? TEXT_PRODUCTS_CONDITION : '"products_condition" (no constant defined)';
                    $placeholder_text = $label_text;
                    break;
                case 'products_msrp':
                    $label_text = defined('TEXT_PRODUCTS_MSRP') ? TEXT_PRODUCTS_MSRP : '"products_msrp" (no constant defined)';
                    $placeholder_text = $label_text;
                    break;
                case 'products_wholesale':
                    //TEXT_PRODUCTS_WHOLESALE_PRICE exists in ZC core, so is shown with a colon...this needs a different constant
                    $label_text = defined('TEXT_PRODUCTS_WHOLESALE_PRICE') ? TEXT_PRODUCTS_WHOLESALE_PRICE : '"products_wholesale" (no constant defined)';
                    $placeholder_text = $label_text;
                    break;
                default:
                    $label_text = 'NPF field name "' . $npf['field_name'] . '" not handled. Fix me around line ' . __LINE__ . '!';
                    $placeholder_text = $label_text;
            }

            $addl_class = null; // The core code tests for these variables: use isset instead of empty
            $parms = null;

            // The Preview->Back button was used: use the new value
            if (!empty($_POST[$npf['field_name']])) {
                $fieldData = $_POST[$npf['field_name']];
                // or use value from the database
            } elseif (!empty($result->fields[$npf['field_name']])) {
                $fieldData = $result->fields[$npf['field_name']];
            } else {
                $fieldData = '';
            }

            $field_length = zen_field_length($npf['table'], $npf['field_name']);

            switch ($npf['field_name']) {
                case 'products_description2':
                    // POST only comes from the Back button on the Preview page: product details new/existing are created
                    // If POST is not set, $pInfo is set. $pInfo->products_id = '' (new product) or an id for an existing product.
                    // NOTE that this field needs to be sanitized in the PRODUCT_DESC_REGEX group, as defined in
                    // \admin\includes\extra_datafiles\plugin_extra_product_fields_sanitization.php
                    // Otherwise the HTML added by a HTML Editor gets mangled.
                    for ($i = 0, $n = count($languages); $i < $n; $i++) {
                        $input = zen_draw_textarea_field(
                            'products_description2[' . $languages[$i]['id'] . ']',
                            'soft',
                            '100',
                            '30',
                            htmlspecialchars(
                                (isset($products_description2[$languages[$i]['id']]))
                                    ? stripslashes($products_description2[$languages[$i]['id']])
                                    : $this->npf_get_products_description2($pInfo->products_id, $languages[$i]['id']),
                                ENT_COMPAT,
                                CHARSET,
                                true
                            ),
                            'class="editorHook form-control"'
                        );
                    }
                    break;

                case 'products_condition':
                case 'products_msrp':
                case 'products_wholesale':
                default:
                    $input = zen_draw_input_field(
                        $npf['field_name'],
                        zen_output_string_protected($fieldData),
                        ' class="form-control" id="' . $npf['field_name'] . '"' .
                        ($placeholder_text === '' ? '' : ' placeholder="' . htmlspecialchars(stripslashes($placeholder_text), ENT_COMPAT, CHARSET) . '"') .
                        ' maxlength="' . $field_length . '"'
                    );
            }
            $field_name = $npf['field_name'];
            $text = $label_text;
            $extra_product_inputs[] = ['label' => compact('text', 'addl_class', 'parms', 'field_name'), 'input' => $input];
        }
    }

    /**
     * update_product: handle the extra POST vars to insert in the product table
     * @param $class
     * @param $eventID
     * @param  array  $p1
     * $zco_notifier->notify('NOTIFY_MODULES_UPDATE_PRODUCT_END', ['action' => $action, 'products_id' => $products_id]);
     */
    protected function notify_modules_update_product_end(&$class, $eventID, array $p1): void
    {
        global $db, $messageStack;
        foreach ($this->extra_product_fields as $extra_product_field) {
            $product_extra_field_postname = $extra_product_field['field_name'];
            $product_extra_field_data = $_POST[$product_extra_field_postname] ?? '';
            $sql = 'UPDATE ' . TABLE_PRODUCTS . ' SET ' . $extra_product_field['field_name'] . "= '" . $product_extra_field_data . "' WHERE products_id = " . (int)$p1['products_id'];
            // $message = 'Updated product (#' . (int)$p1['products_id'] . '), extra_field products_' . $extra_product_field['field_name'] . ' ("' . $product_extra_field_data . '")';
            //$messageStack->add_session($message, 'caution');
            $db->Execute($sql);
            //  zen_record_admin_activity($message, 'notice');
        }

        //Numinix Product Fields
        foreach ($this->numinix_product_fields as $npf) {
            switch ($npf['field_name']) {
                case 'products_description2':
                    $languages = zen_get_languages();
                    for ($i = 0, $n = count($languages); $i < $n; $i++) {
                        $language_id = $languages[$i]['id'];
                        $product_extra_field_data = $_POST['products_description2'][$language_id];
                        $sql_data_array = [
                            'products_description2' => zen_db_prepare_input($product_extra_field_data),
                        ];
                        zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', 'products_id = ' . (int)$p1['products_id'] . ' AND language_id = ' . (int)$language_id);
                    }
                    break;
                case 'products_condition':
                case 'products_msrp':
                case 'products_wholesale':
                default:
                    $product_extra_field_data = zen_db_prepare_input($_POST[$npf['field_name']] ?? '');
                    $sql = 'UPDATE ' . $npf['table'] . ' SET ' . $npf['field_name'] . "= '" . $product_extra_field_data . "' WHERE products_id = " . (int)$p1['products_id'];
            }
            $message = 'Updated product (#' . (int)$p1['products_id'] . '), extra_field products_' . $npf['field_name'] . ' ("' . $product_extra_field_data . '")';
            //$messageStack->add_session($message, 'caution');
            $db->Execute($sql);
            //  zen_record_admin_activity($message, 'notice');
        }
    }

    /**
     * Return product description2, based on specified language (or current lang if not specified)
     * Copied from zen_get_products_description
     *
     * @param  int  $product_id
     * @param  int  $language_id
     * @return string
     */
    public function npf_get_products_description2($product_id, $language_id = null): string
    {
        global $zco_notifier;

        $product = new Product((int)$product_id);
        $data = $product->getDataForLanguage((int)$language_id);

        //Allow an observer to modify the description
        $zco_notifier->notify('NOTIFY_GET_PRODUCTS_DESCRIPTION2', $product_id, $data);
        return $data['products_description2'] ?? '';
    }

    /**
     * Install the extra product fields
     * @return void
     */
    private function install(): void
    {
        global $db, $messageStack, $sniffer;
        foreach ($this->extra_product_fields as $extra_product_field) {
            if ($sniffer->field_exists(TABLE_PRODUCTS, $extra_product_field['field_name'])) {
                $messageStack->add('Plugin Extra Product Fields: field "' . $extra_product_field['field_name'] . '" already exists in table "' . TABLE_PRODUCTS . '"', 'caution');
            } else {
                $db->Execute(
                    'ALTER TABLE ' . TABLE_PRODUCTS . ' ADD ' . $extra_product_field['field_name'] . ' VARCHAR(' . (int)$extra_product_field['varchar']
                    . ') NOT NULL DEFAULT ""'
                );
                $messageStack->add('Plugin Extra Product Fields: field "' . $extra_product_field['field_name'] . ' added to table "' . TABLE_PRODUCTS . '".', 'success');
            }
        }
        //install of fields for testing purposes only
        //return
        foreach ($this->numinix_product_fields as $npf) {
            if ($sniffer->field_exists($npf['table'], $npf['field_name'])) {
                $messageStack->add('Numinix Extra Product Fields: field "' . $npf['field_name'] . '" already exists in table "' . $npf['table'] . '"', 'caution');
            } else {
                switch ($npf['field_name']) {
                    case 'products_description2':
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD ' . $npf['field_name'] . ' TEXT NOT NULL DEFAULT ""';
                        break;
                    case 'products_condition':
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD ' . $npf['field_name'] . ' VARCHAR(32) NULL default "New"';
                        break;
                    case 'products_msrp':
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD ' . $npf['field_name'] . ' VARCHAR(150) DEFAULT "0"';
                        break;
                    case 'products_wholesale':
                        /*from Numinix Install
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD products_price_w VARCHAR(150) DEFAULT "0"';
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD wholesale_price VARCHAR(150) DEFAULT "0"';
                        But products_price_w is now a ZC core field
                        */
                        //Jeandret upgrade field
                        $sql = 'ALTER TABLE ' . $npf['table'] . ' ADD products_wholesale VARCHAR(150) DEFAULT "0"';
                        break;
                }
                $db->Execute($sql);
                $messageStack->add('Numinix Extra Product Fields: field "' . $npf['field_name'] . ' added to table "' . $npf['table'] . '".', 'success');
            }
        }
    }
}
