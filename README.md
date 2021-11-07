# Zen Cart - Extra Product Fields
## Compatible: Zen Cart 1.57 / php 8.1

This is an Admin Observer which adds extra product fields to the database and the corresponding fields in the admin Product Edit page.

The observer installs:
EAN, Google Product Category, MPN fields ino the database.
They are included as examples...if they do not suit your purpose, it is easy to modify them as required and do repeat testing on your developement server FIRST.

Look at the first section of the observer and do some educated guessing. Since you are testing on a development server, no harm can be done...

## Installation

1. Use a development installation to test.
2. Backup the database.
3. Copy files to their corresponding locations in the admin folder. No core files should be overwritten.
4. In the observer, UNcomment
$this->install();
to allow the installation.
5. Refresh the admin page. Messages should be displayed indication the successful installation of the new fields in the database.
6. Refresh the admin page again. Messages should be displayed indicating that the new fields are already in the database.
7. In the observer, comment out
$this->install();
to prevent these messages.
8. Edit a product to check the new fields are displayed and entries are saved on Update.

## Uninstall
Remove the observer and language file(s).
Use the sql in the Admin SQL patch tool or phpMyAdmin. Obviously modify this to suit your modifications.

        /*uninstall sql
        ALTER TABLE `products` DROP `products_ean;
        ALTER TABLE `products` DROP `products_google_product_category;
        ALTER TABLE `products` DROP `products_mpn;
        */

## Todo (by others)
Add support for products that use a plugin for attribute stock control: the attributes should have these custom fields, not just the base product.

### Changelog
7/11/2021: Initial effort.