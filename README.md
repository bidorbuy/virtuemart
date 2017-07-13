# VirtueMart bidorbuy Store Integrator

### Compatibility
Requires at least: Joomla-v 2.x (VirtueMart- v.1.9)

Tested up to: Joomla-v 2.5 (VirtueMart- v.2.6.17)

### Description
The bidorbuy Store Integrator allows you to get products from your online store listed on bidorbuy quickly and easily.
Expose your products to the bidorbuy audience - one of the largest audiences of online shoppers in South Africa Store updates will be fed through to bidorbuy automatically, within 24 hours so you can be sure that your store is in sync within your bidorbuy listings. All products will appear as Buy Now listings. There is no listing fee just a small commission on successful sales. View [fees](https://support.bidorbuy.co.za/index.php?/Knowledgebase/Article/View/22/0/fee-rate-card---what-we-charge). Select as many product categories to list on bidorbuy as you like. No technical requirements necessary.

To make use of this plugin, you'll need to be an advanced seller on bidorbuy.
 * [Register on bidorbuy](https://www.bidorbuy.co.za/jsp/registration/UserRegistration.jsp?action=Modify)
 * [Apply to become an advanced seller](https://www.bidorbuy.co.za/jsp/seller/registration/UserSellersRequest.jsp)
 * Once you integrate with bidorbuy, you will be contacted by a bidorbuy representative to guide you through the process.

### System requirements

PHP: 5.3.29	 

PHP extensions: curl, mbstring

### Installation

1. From the backend of your Joomla site (administration) select Extension Manager: Install.
2. Click the Browse button and select the extension package (a zip file package) on your local machine.
3. Click the Upload Upload & Install button.
4. Check: 'bidorbuy Store Integrator' component and 'System - MVC Override (for Bidorbuy Store Integrator)' plugin should be enabled.

### Uninstallation

1. Go to Extension Manager>Manage.
2. Uninstall the  bidorbuy Store Integrator component.
3. Uninstall the MVC Override (for Bidorbuy Store Integrator) plugin. 

### Upgrade

To upgrade the plugin, please: 1. Re-install the archive (please, look through the installation chapter). 2. Do a Reset export data.

### Configuration

1. Log in to control panel as administrator.
2. Navigate to Components > `bidorbuy Store-Integrator` section.
3. Set the export criteria.
4. Press the`Save` button.
5. Press the`Export` button.
6. Press the`Download` button.
7. Share Export Links with bidorbuy.
8. To display BAA fields on the setting page add '&baa=1' to URL in address bar.

### Changelog

#### 2.0.8
* Fixed error in query (1292): Incorrect datetime value: '0000-00-00 00:00:00' for column 'row_modified_on' at row 1.
* Fixed error in query (1055): Expression #1 of SELECT list is not in GROUP BY clause and contains nonaggregated column.
* Fixed issue when "$this->dbLink->execute" hides the real error messages.
* Fixed issue when bobsi tables are created always with random charset instead of utf8_unicode_ci.
* Fixed issue when export process is interrupted by zlib extension.

_[Updated on June 06, 2017]_

#### 2.0.7
* Added an appropriate warning on the Store Integrator setting page about EOL(End-of-life) of export non HTTP URL to the tradefeed file.
* Added a flag to display BAA fields (to display BAA fields on the setting page add '&baa=1' to URL in the address bar). 

_[Updated on March 07, 2017]_

#### 2.0.6
* Improved the upgrade process.
* Added missing `Launch` buttons on the Settings page.

_[Updated on December 29, 2016]_

#### 2.0.5
* Added support of multiple images.
* Added support of images from product description.
* Added the possibility to open PHP info from the store Integrator settings page.

_[Updated on December 20, 2016]_

#### 2.0.4
* Added additional improvements for Store Integrator Settings page.
* Added new feature: if product has weight attribute, the product name should contain this attribute value.
* Fixed an issue when tradefeed is invalid to being parsed with Invalid byte 1 of 1-byte UTF-8 sequence.
* Fixed an issue when Store Integrator cuts the long name of categories in Export Criteria section.
* End of support of Joomla 1.5.x.

 _[Updated on November 18, 2016]_

#### 2.0.3
* Added a possibility to export the category slug in the XML (VirtueMart2.5).
* Fixed an issue of empty XML after changing the settings.
* Fixed an issue when it is impossible to download log after its removal.
* Fixed an issue when extra character `&` added to the export URL.
* Corrected the export link length: it was too long.
* Added an error message if "mysqli" extension is not loaded.

_[Updated on October 21, 2016]_

#### 2.0.2
* Renamed `Export Filename` to `Export filename` field on settings page.
* Added an extra core feature: check whether ReadFile function is activated during the plugin installation.

_[Updated on August 24, 2016]_

#### 2.0.1
* Fixed a bug when on certain occasions disabled products were still exported.
* Fixed a Joomla Cache Issue: when the integrator did not save settings. 
* Added an possibility to check the plugin version.
* Added `Reset export data` link to a plugin settings page.

_[Updated on April 25, 2016]_

#### 2.0.0
* Added an ability to display the plugin version.
* Fixed a bug when on certain occasions disabled products were still exported.

_[Updated on September 15, 2015]_

#### 1.0
* First release.

_[Released on April 10, 2014]_