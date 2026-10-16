=== Tessera Store ===
Version: 1.8.6
Typography: Vazir font loaded from CDN and applied across theme, WooCommerce and Elementor content while retaining responsive component sizes.

RTL WooCommerce-ready classic theme with responsive hierarchical category mega menu, front-end account views, product reviews, searchable paginated article archive, branded loading indicator and redesigned footer.

Requires: WordPress 6.x+
Recommended: WooCommerce, Elementor / Elementor Pro, Contact Form 7, Yoast SEO or Rank Math, WPML or Polylang.

WooCommerce is the data source for products, cart, checkout, orders, customer accounts and reviews. On first load after WooCommerce is active, the theme seeds demo categories and products with free-to-use stock photos. It also creates sample shopping-guide articles so the article archive and comments can be reviewed immediately. Demo products use TESRA-DEMO SKUs and are kept separate from customer products.

Elementor theme locations are registered with fallback support for the native Tessera header/footer so Elementor Pro templates can replace them without breaking the theme. Login, phone-based registration and logout use front-end pages and WordPress authentication APIs. The forgot-password screen supports a three-step phone verification flow; local development shows an OTP for testing, while real SMS delivery requires an SMS-provider integration via the tesra_send_sms_reset_code filter. Configure footer social URLs and license images from Appearance > Customize > Tessera Footer Settings.


Version 1.7.4
- Restored the complete 1.7.2 design and assets after reports of visual breakage.
- Fixed loader shutdown independently from the main JavaScript initialization.
- Hardened wishlist and session-storage initialization without changing the page styling.


Version 1.8.2
- Added phone-required account registration and optional email.
- Added phone-based password-reset screens with expiring one-time codes and local-only test codes; real SMS needs provider integration.
- Routed product search to the styled WooCommerce product-results template and added a designed empty-results state.
- Improved footer license placeholders without representing them as official issued certifications.

Version 1.8.6
- Improved product rating stars and switched product feedback to the theme comment template.
- Refined comment submission for signed-in users and refreshed product/gallery and WooCommerce notice styling.
- Reduced repeated demo-review setup, added password-reset request throttling, and sanitized contact reply-to input.
- Switched default homepage banner fallbacks to bundled local demo assets and refined UI transitions.

