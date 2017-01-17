<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Admin related
	|--------------------------------------------------------------------------
	*/

  "avangate_payments" => "For your convenience, we offer secure and reliable payments with :avangate.",
	"subscription" => "Subscription",
	"manage_subscription" => "Manage subscription",
	"manage_payment_account" => "Cancel subscriptions or manage payment settings <a href=\":url\" target=\"_blank\" style=\"font-weight: bold\">here</a>.",
	"user_plans" => "User Plans",
	"plan" => "Plan",
	"add_plan" => "Add plan",
	"new_plan" => "New plan",
	"new_plan_created" => "New plan successfully created",
	"edit_plan" => "Edit plan",
	"delete_plan" => "Delete plan",
	"delete_plan_restricted" => "You can't delete this plan because it's used by one or more users.",
	"monthly" => "Monthly",
	"annual" => "Annual",
	"annual_info" => "Price per month in case of annual payment",
	"pricing" => "Pricing",
	"pricing_info" => "Only the monthly price will show. Only if Avangate is NOT active, users can choose for the annual option after upgrading. If you have configured no payment providers, no pricing or limitations will be shown.",
	"per_month" => "Per month",
	"per_mo" => "/mo",
	"featured" => "Featured",
	"currency" => "Currency",
	"team_management" => "Team management",
	"widgets" => "Widgets",
	"widgets_info" => "These widgets are available for the mobile apps.",
	"unlimited" => "Unlimited",
	"limitations" => "Limitations",
	"interactions_info" => "This is currently not a hard cap. Nothing will happen when this amount is exceeded.",
	"apps" => "Apps",
	"external_urls_info" => "If the urls below are not empty, these will be opened when a user orders or upgrades an account. Product ID is currently only used for Avangate. Leave empty if you want to use internal payments like bank payment, PayPal or 2Checkout.",
	"order_url" => "Order URL",
	"order_url_info" => "URL to open when user orders plan. (Optional)",
	"upgrade_url" => "Upgrade URL",
	"upgrade_url_info" => "URL to open when user upgrades plan. (Optional)",
	"product_id" => "Product ID",
	"product_id_info" => "Product identifier. (Optional, required for Avangate)",
	"max_apps" => "Max apps",
	"max_sites" => "Max sites",
	"max_beacons" => "Max beacons",
	"max_geofences" => "Max regions",
	"max_boards" => "Max notification boards",
	"max_scenarios" => "Max scenarios (per board)",
	"max_scenarios_reached" => "The maximum amount of scenarios has been reached. Please upgrade your account to increase the maximum amount of scenarios per board.",
	"disk_space" => "Disk space",
	"all" => "All",
	"never" => "Never",
	"upgrade" => "Upgrade",
  "upgrade_before_link" => "Processing the payment may take a couple of minutes. Refresh the system or login again to see your new subscription active.",
	"upgrade_title" => "Upgrade your account",
	"upgrade_msg" => "The limit of your account has been reached. Please upgrade to increase your limits.",
	"upgrade_msg_feature" => "This feature is not available for your current plan. Please upgrade.",
	"upgrade_button" => "Click here to manage your account",
	"your_current_plan" => "You're currently on the :plan plan.",
	"contact_us_for_account" => "Contact us for more information about your account.",
	"this_plan_expires" => "This plan expires :expiration_date.",
	"this_plan_has_expired" => "This plan has expired at :expiration_date.",
	"click_here_to_manage_plan" => "Click here to manage your subscription.",
	"subscription" => "Subscription",
	"support" => "Support",
	"support_info" => "For example 'Mail', 'Phone', 'Training'",
	"order_now" => "Order Now",
	"order_plan" => "Order plan",
	"extend_subscription" => "Extend Subscription",
	"payment_method" => "Payment method",
	"next" => "Next",
	"confirm_order" => "Confirm order",
	"make_payment" => "Make payment",
	"order_plan_for" => "Order the :plan plan for:",
	"upgrade_plan_for" => "Upgrade to the :plan plan for:",
	"subscription_ends_on" => "Subscription ends on :date",
	"invoice" => "Invoice",
	"invoices" => "Invoices",
	"no_invoices" => "You have no invoices.",
	"total" => "Total",
	"order_line" => ":plan package until :date",
	"date" => "Date",
	"thank_you" => "Thank you",
	"thank_you_message" => "Thank you for your purchase! A confirmation mail has been sent to your email address.",
	"account_purchased" => "Account purchased",
	"purchase" => "Purchase",
	"return_to_your_account" => "Return to your account",
	"one_month" => "One month",
	"one_year" => "One year",
	"account_expired" => "Account expired",
	"expires" => "Expires",
	"line_total" => "Line total",
	"order" => "Order",
	"account_is_expired" => "This account has expired. Please login to activate your subscription.",
	"recepient" => "Recepient",
	"update_status" => "Update status",

	/*
	|--------------------------------------------------------------------------
	| Resellers
	|--------------------------------------------------------------------------
	*/

	"reseller" => "Reseller",
	"resellers" => "Resellers",
	"new_reseller" => "New reseller",
	"edit_reseller" => "Edit reseller",
	"contact" => "Contact",
	"business" => "Business",
	"contact_name" => "Contact name",
	"contact_mail" => "Contact mail",
	"phone" => "Phone",
	"address1" => "Address 1",
	"address2" => "Address 2",
	"zip" => "Zip",
	"city" => "City",
	"country" => "Country",
	"domain" => "Domain",
	"email_configuration" => "SMTP Email Configuration",
	"app_configuration" => "App Configuration",
	"contact_information" => "Contact Information",
	"mail_from_address" => "Mail address",
	"mail_from_name" => "Mail name",
	"smtp_host" => "SMTP host",
	"smtp_port" => "SMTP port",
	"business" => "Business",
	"encryption" => "Encryption",
	"app_name" => "App name",
	"app_link_ios" => "iTunes url",
	"app_link_android" => "Google Play url",
	"delete_reseller_confirm" => "This will delete the reseller, all attached users and data. You will have to manually delete all uploaded files.\\n\\nThis cannot be undone.",
  "app_configuration_info" => "This is the default app that's shown on the dashboard and adviced when people visit content with another app. Leave empty for no default app.",
  "reseller_active_info" => "If a reseller is inactive, the url is disabled and none of the users can login",
  "reseller_domain_info" => "The CNAME must point to <strong>:host</strong>",
  "reseller_domain_master_info" => "Don't change the master domain unless you know what you're doing.",
  "new_reseller_created" => "New reseller successfully created",
  "reseller_info" => "This user manages the reseller account",

  "white_label" => "White label",
  "branding" => "Branding",

	/*
	|--------------------------------------------------------------------------
	| Admin purchases
	|--------------------------------------------------------------------------
	*/

	"purchases" => "Purchases",
	"amount" => "Amount",
	"purchase_date" => "Purchase date",
	"status" => "Status",

	/*
	|--------------------------------------------------------------------------
	| Order confirmation mail
	|--------------------------------------------------------------------------
	*/

	"confirmation_subject" => "Thank you for your order",
	"confirmation_body" => "Hi :name,<br><br>Thank you for your order.",

	/*
	|--------------------------------------------------------------------------
	| Bank
	|--------------------------------------------------------------------------
	*/

	"bank" => "Bank Transfer",
	"bank_title" => "Pay by Bank Transfer",
	"bank_info" => "Pay the invoice by bank within 7 days",
	"bank_image" => "bank-transfer.png",

	/*
	|--------------------------------------------------------------------------
	| PayPal
	|--------------------------------------------------------------------------
	*/

	"paypal" => "PayPal",
	"paypal_title" => "Pay with PayPal",
	"paypal_info" => "",
	"paypal_image" => "paypal.jpg",

	/*
	|--------------------------------------------------------------------------
	| 2Checkout
	|--------------------------------------------------------------------------
	*/

	"2checkout" => "2Checkout",
	"2checkout_title" => "Pay with 2Checkout",
	"2checkout_info" => "",
	"2checkout_image" => "2checkout.png",

	/*
	|--------------------------------------------------------------------------
	| User Administration
	|--------------------------------------------------------------------------
	*/

	"user_administration" => "User Administration",
	"system_administration" => "Admin",
	"id" => "ID",
	"cant_delete_owner" => "You can't delete the root owner",
	"update_password_info" => "Leave empty to keep current password",

	/*
	|--------------------------------------------------------------------------
	| Website
	|--------------------------------------------------------------------------
	*/

	"website" => "Website",
	"website_settings" => "Website settings",
	"templates" => "Templates",
	"edit_template" => "Edit template",
	"set_as_active" => "Set as active",
	"favicon" => "Favicon",
	"page_title" => "Page title",
	"description" => "Description",
	"content" => "Content",

	/*
	|--------------------------------------------------------------------------
	| CMS
	|--------------------------------------------------------------------------
	*/

	"cms" => "CMS",
	"favicon" => "Favicon",
	"logo" => "Logo",
	"system_reset" => "System reset",
);
