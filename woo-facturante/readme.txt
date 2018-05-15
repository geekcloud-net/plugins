=== Woo Facturante ===
Contributors: fuegoyamana
Tags: Facturante, WooCommerce
Requires at least: 4.7.1
Tested up to: 4.7.4
Stable tag: 0.1.53
License: GPLv3 or later License
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 2.6.4
WC tested up: 3.2.5

Este plugin conecta tu tienda WooCommerce con Facturante para hacer factura electr&oacute;nica en Argentina.
Requiere una cuenta en [Facturante](https://facturante.com/).

* Este plugin en su versión gratuita, sólo es compatible con tiendas que tengan la configuración Activar Impuestos desactivada.

* Es necesario tener instalada la extensión SOAP en su servidor.


== Installation ==
1. Subir los archivos del plugin a su directorio de plugins de wordpress (normalmente  /wp-content/plugins/) o instalar desde el instalador de plugins de Wordpress.
2. Activar el plugin en la  pantalla \'Plugins\'  de WordPress.
3. Usar el tab WooCommerce->Ajustes->Facturante  para configurar el plugin.


== Screenshots ==
1. Configuraci&oacute;n
2. Pantalla de pedido
3. Listado de pedidos

== Changelog ==

= 0.1.53 =
* Removed innecesary option from settings, updated translation, improved detection of IVA plugin. 

= 0.1.52 =
* Added support for taxes plugin.

= 0.1.51 =
* State and city are now sent separately to the API

= 0.1.5 =
* Fees are now included in the invoices.

= 0.1.49 =
* Added hooks and filters for future plugin addons

= 0.1.48 =
* Warning if SOAP is not enabled in server.

= 0.1.47 =
* Fixed bug with discounts.

= 0.1.46 =
* Better compatibility with discounts plugins.

= 0.1.45 =
* If billing organization is present in checkout form it will be used in the invoice in place of name and lastname.
* Added warning in settings if calculate taxes is active.

= 0.1.44 =
* Removed DNI field in register form.

= 0.1.43 =
* Added impositive treatment selector in settings and support for monotributistas and iva exento
* Cupon's discounts now reflects correctly in invoices.

= 0.1.42 =
* Added improved readme.txt file

= 0.1.3 =
* Avoid popup blockers in chrome.
* Added sandbox mode option.
* Fixed compatibility issues with woocommerce 3.0
* Added customer\'s state and city data to invoices.
* Fixed but with unit price showing incorrectly in invoices.

= 0.1.2 =
* Fixed bug about IVA being empty.
= 1.0.1 =
* Improved security fixes.
* DNI checkout field shows correctly in emails.
* Added DNI field numeric validation.

= 0.1.0 =
* Initial release.
