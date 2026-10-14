===  BT iPay Payments ===
Contributors: bancatransilvania
Donate link: https://btepos.ro/module-ecommerce/
Tags: payment, banca transilvania
Requires at least: 5.9
Requires PHP: 8.1
Tested up to: 7.1
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extinde WooCommerce cu plata prin iPay BT.

== Description ==

Extinde WooCommerce cu plata prin iPay BT. Pentru conectare aveti nevoie de credentiale API de la Banca Transilvania.
Pentru detalii aplicatiiecommerce@btrl.ro

== Installation ==

1. Descărcați fișierul .zip cu codul pluginului pe computer.
2. Accesați secțiunea Pluginuri de pe siteul WordPress.
3. Apăsați butonul „Adăugați nou” din partea de sus a secțiunii Pluginuri.
3. Apăsați butonul „Încărcați pluginul” și selectați fișierul arhivat descărcat în primul pas.
4. În cele din urmă, instalați și activați pluginul.

== Frequently Asked Questions ==
= Cum se instaleaza acest plugin? =
1. Descărcați fișierul .zip cu codul pluginului pe computer.
2. Accesați secțiunea Pluginuri de pe siteul WordPress.
3. Apăsați butonul „Adăugați nou” din partea de sus a secțiunii Pluginuri.
3. Apăsați butonul „Încărcați pluginul” și selectați fișierul arhivat descărcat în primul pas.
4. În cele din urmă, instalați și activați pluginul.

== Screenshots ==
1. Settings page
2. Payment page
== Changelog ==
= 1.0.9 =
Compatibilitate declarată cu WooCommerce 11.2 și WordPress 7.1.
Confirmarea plății nu se mai procesează de două ori când return URL-ul și callback-ul ajung simultan (e-mailuri și scăderi de stoc duplicate).
Comenzile deja plătite sau finalizate nu mai sunt reschimbate de un callback întârziat sau de reaccesarea linkului de return.
Plata este înregistrată (inclusiv cardul salvat și componenta de loialitate) chiar dacă callback-ul a confirmat comanda înaintea paginii de return; un callback APPROVED întârziat nu mai suprascrie o plată deja încasată.
Filtre noi: `bt_ipay_can_update_order_status`, `bt_ipay_payable_order_statuses`, `bt_ipay_return_url`.
Comanda plătită devine "Completed" doar dacă toate produsele sunt bifate "Virtual" (la produsele variabile, fiecare variantă); altfel devine "Processing". Până la 1.0.8, din cauza unei erori, orice comandă devenea "Completed".
Modulul nu mai apare de două ori în lista de pluginuri (intrarea veche v1.0.0); site-urile care o activaseră sunt mutate automat pe intrarea curentă.
= 1.0.8 =
Compatibilitate declarată cu WooCommerce 11 și cu Checkout-ul pe blocuri (Cart & Checkout Blocks).
= 1.0 =
Initial release
== Upgrade Notice ==
= 1.0 =
No upgrade is required