# ScalablePress Shopify Integration for Order Syncing + Tracking
Simple webhook integration for automatic order placement from Shopify to ScalablePress + Tracking Information Syncing.
Requirements - Nginx, PHP, MySQL

Installation - 
1. Create a Private app in shopify with Order editing, orders and products access. 
2. Base64 encode apikey:password and use in functions.php authorization header
3. Setup order webhook in Shopify -> Settings -> Notifications -> Order Payment webhook and point to order-webook.php location
4. Base64 Encode ScalablePress API key i.e live_:live_ for use in functions.php
5. Point ScalablePress webhook to scalablepress-order-webhook.php location.
6. Edit shopify API url to your url in all php files.
7. Import sql table structure and enter sql details in functions file

Configuration - 
Design ID for products is retrived from each product's variant SKU. 
Product ID for garment choices is retrieved from productid custom metafield set per product. 
DTG is used by default however embroidery can be set by using embr tag on the product. If no tag is set it defaults to DTG.
Size array can be found in functions.php to convert sml, lg e.t.c into a format that matches your store e.g. S, M, L

Failsafes - 
Every order processed gets saved to database whether it gets ordered or fails. Shopify Order and Scalable api order id are required to update tracking information.
Any orders which fail to process *should* display the api error as well as the order number in the db for manual placement.

Limitations - 
1. Imported orders from apps such as Encased order protection won't trigger order payment webhook on Shopify. Can use Order Created webhook instead if this is a problem for you.
2. No support for Screenprinting
3. If order contains ScalablePress product plus another non-scalablepress product it will fail
4. Multiple shipping methods aren't supported and it will only use your default selection within scalablepress
5. Non-Binded SQL queries so please do not use on a publically accessible server.
6. Orders are searchable via Shopify Order number however PO gets shown as N/A across pagination pages
7. Colours MUST match exactly to the scalable product names and products with no colour variants MUST have a colour variant added otherwise order will fail
8. Cancelled or Edited orders will NOT sync to ScalablePress and will also need it's api order id updated in the database if changes are made to enable tracking to work
