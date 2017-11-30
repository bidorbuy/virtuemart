# VirtueMart bidorbuy Store Integrator

### Compatibility

| Product | PHP version |Joomla v2.5.28 | Joomla v2.5.28 | Joomla v3.6.5|Joomla v3.6.5| Joomla v3.7.0|
| ------- | --- | --- | --- | --- | --- | --- |
| Store Integrator-2.0.13 |5.4| ✓ VM v2.6.8 | ✓ VM v2.6.17|✓ VM v3.2.0 (PHP70)|✓ VM v3.2.1(PHP70)|✓ VM v3.2.2 (PHP70)|
| Store Integrator-2.0.12 |5.4| ✓ VM v2.6.8 | ✓ VM v2.6.17|✓ VM v3.2.0 (PHP70)|✓ VM v3.2.1(PHP70)|✓ VM v3.2.2 (PHP70)|
| Store Integrator-2.0.11 |5.4| ✓ VM v2.6.8 | ✓ VM v2.6.17|-|-|-|
| Store Integrator-2.0.10 | 5.4|✓ VM v2.6.8 | ✓ VM v2.6.17|-|-|-|
| Store Integrator-2.0.9  | 5.4|✓ VM v2.6.8 | ✓ VM v2.6.17|-|-|-|
| Store Integrator-2.0.8  | 5.4|✓ VM v2.6.8 | ✓ VM v2.6.17|-|-|-|
| Store Integrator-2.0.7  | 5.3|✓ VM v2.6.8 | ✓ VM v2.6.17|-|-|-|


### Description

The bidorbuy Store Integrator allows you to get products from your online store listed on bidorbuy quickly and easily.
Expose your products to the bidorbuy audience - one of the largest audiences of online shoppers in South Africa Store updates will be fed through to bidorbuy automatically, within 24 hours so you can be sure that your store is in sync within your bidorbuy listings. All products will appear as Buy Now listings. There is no listing fee just a small commission on successful sales. View [fees](https://support.bidorbuy.co.za/index.php?/Knowledgebase/Article/View/22/0/fee-rate-card---what-we-charge). Select as many product categories to list on bidorbuy as you like. No technical requirements necessary.

To make use of this plugin, you'll need to be an advanced seller on bidorbuy.
 * [Register on bidorbuy](https://www.bidorbuy.co.za/jsp/registration/UserRegistration.jsp?action=Modify)
 * [Apply to become an advanced seller](https://www.bidorbuy.co.za/jsp/seller/registration/UserSellersRequest.jsp)
 * Once you integrate with bidorbuy, you will be contacted by a bidorbuy representative to guide you through the process.

### System requirements

PHP: 5.4 

PHP extensions: curl, mbstring

### Installation

1. From the backend of your Joomla site (administration) select Extension Manager: Install.
2. Click the Browse button and select the extension package (a zip file package) on your local machine.
3. Click the Upload Upload & Install button.
4. Check: 'bidorbuy Store Integrator' component and 'System - MVC Override (for Bidorbuy Store Integrator)' plugin should be enabled.

### Uninstallation

1. Go to Extension Manager > Manage.
2. Uninstall the bidorbuy Store Integrator component.
3. Uninstall the MVC Override (for Bidorbuy Store Integrator) plugin. 

### Upgrade

To upgrade the plugin, please:
1. Re-install the archive (please, look through the installation chapter).
2. Do a Reset export data.

### Configuration

1. Log in to control panel as administrator.
2. Navigate to Components > bidorbuy Store-Integrator.
3. Set the export criteria.
4. Press the `Save` button.
5. Press the `Export` button.
6. Press the `Download` button.
7. Share Export Links with bidorbuy.
8. To display BAA fields on the setting page add '&baa=1' to URL in address bar.
9. For export products without category, enable two options in VirtueMart > Configuration:
 - Show uncategorised parent products in search results and modules
 - Show uncategorised child products in search results and modules