# Zen Cart - Extra Product Fields

This is an Admin Observer which adds extra product fields to the database and the corresponding fields in the Admin Product Edit page.

## Compatibility
It should work from Zen Cart 157 onwards and is in use with the Zen Cart development branch (currently 2.2.0) and php 8.4.

## Installation
Out of the box, the observer installs:
Google GTIN, Google Product Category fields into the database.
They are included as examples...if they do not suit your purpose, it is easy to modify them as required and do repeat testing on your development server FIRST.

Look at the first section of the observer and do some educated guessing. Since you are testing on a development server, no harm can be done...

1. Use a development installation to test.
2. Backup the database.
3. Copy files to their corresponding locations in the admin folder. No core files should be overwritten.  
Note that for the storefront side, files are included as examples of use of these new fields: you do not have to use them.
4. In the observer, UNcomment
$this->install();
to allow the installation.
5. Refresh the admin page.  
Messages should be displayed indication the successful installation of the new fields in the database.
6. Refresh the admin page again.  
Messages should be displayed indicating that the new fields are already in the database.
7. In the observer, comment out
$this->install();
to prevent these messages.
8. Edit a product to check the new fields are displayed and entries are saved on Update.

## Structured Data
If you are using the Structured Data Plugin from here:
https://github.com/torvista/Zen_Cart-Structured_Data

the Google Product Category field in the products table was originally named 
'google_product_category'
instead of
'products_google_product_category'
So you may get an error in the admin product edit page related to that. If so, rename the field:

ALTER TABLE products CHANGE google_product_category products_google_product_category VARCHAR(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';

## Support
Report issues here:

https://github.com/torvista/Zen_Cart-Extra_Product_Fields

## Uninstall
Remove the observer and language file(s).
Use the sql in the Admin SQL patch tool or phpMyAdmin. Obviously modify this to suit your modifications.


        ALTER TABLE `products` DROP `products_gtin;
        ALTER TABLE `products` DROP `products_google_product_category;


## Extra fields (GTIN, MPN, GPC) for POSM
Not included but I've done it. If you want this, request it in the GitHub Issues.

### Changelog
Last update 16th March 2025.

See the GitHub commits.


