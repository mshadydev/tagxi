<?php

namespace App\Base\Constants\Setting;

class Settings
{
    const EMAIL = 'email';
    const SMS = 'sms';
    const LOGO = 'logo';
    const FAVICON = 'favicon';
    const LOGINBG = 'loginbg';
    const NAV_COLOR = 'nav_color';
    const SIDEBAR_COLOR = 'sidebar_color';
    const SIDEBARTXT_COLOR = 'sidebar_text_color';
    const GOOGLE_BROWSER_KEY ='google_browser_key';
    const APP_NAME ='app_name';
    const SERVICE_TAX ='service_tax';
    const ADMIN_COMMISSION_TYPE ='admin_commission_type';
    const ADMIN_COMMISSION ='admin_commission';
    const WALLET_MIN_AMOUNT_FOR_TRIP ='wallet_min_amount_for_trip';
    const WALLET_MIN_AMOUNT_TO_ADD ='wallet_min_amount_to_add';
    const WALLET_MAX_AMOUNT_TO_ADD ='wallet_max_amount_to_add';
    const WALLET_MAX_AMOUNT_TO_BALANCE ='wallet_max_amount_to_balance';
    const TWILLO_ACCOUNT_SID ='twillo_account_sid';
    const TWILLO_AUTH_TOKEN ='twillo_auth_token';
    const TWILLO_NUMBER ='twillo_number';
    const HEAD_OFFICE_NUMBER ='head_office_number';
    const HELP_EMAIL_ADDRESS ='help_email_address';
    const BRAINTREE_ENVIRONMENT ='braintree_environment';
    const BRAINTREE_MERCHANT_ID ='braintree_merchant_id';
    const BRAINTREE_PUBLIC_KEY ='braintree_public_key';
    const BRAINTREE_PRIVATE_KEY ='braintree_private_key';
    const BRAINTREE_MASTER_MERCHANT ='braintree_master_merchant';
    const BRAINTREE_DEFAULT_MERCHANT ='braintree_default_merchant';
    const DRIVER_SEARCH_RADIUS='driver_search_radius';
    const MINIMUM_TIME_FOR_SEARCH_DRIVERS_FOR_SCHEDULE_RIDE='minimum_time_for_search_drivers_for_schedule_ride';
    const REFERRAL_COMMISION_FOR_USER='referral_commision_for_user';
    const REFERRAL_COMMISION_FOR_DRIVER='referral_commision_for_driver';
    const MINIMUM_TRIPS_SHOULD_COMPLETE_TO_REFER_DRIVERS='minimum_trips_should_complete_to_refer_drivers';
    const GOOGLE_MAP_KEY='google_map_key';
    const MAPBOX_KEY='map_box_key';
    const ENABLE_BRAIN_TREE='enable_brain_tree';
    const DRIVER_WALLET_MINIMUM_AMOUNT_TO_GET_ORDER='driver-wallet-minimum-amount-to-get-order';
    const OWNER_WALLET_MINIMUM_AMOUNT_TO_GET_ORDER='owner-wallet-minimum-amount-to-get-order';
    const FIREBASE_DB_URL='firebase-db-url';
    const FIREBASE_API_KEY='firebase-api-key';
    const FIREBASE_AUTH_DOMAIN='firebase-auth-domain';
    const FIREBASE_PROJECT_ID='firebase-project-id';
    const FIREBASE_STORAGE_BUCKET='firebase-storage-bucket';
    const FIREBASE_MESSAGIN_SENDER_ID='firebase-messaging-sender-id';
    const FIREBASE_APP_ID='firebase-app-id';
    const FIREBASE_MEASUREMENT_ID='firebase-measurement-id';

    const ENABLE_PAYSTACK='enable-paystack';
    const PAYSTACK_ENVIRONMENT='paystack-environment';
    const PAYSTACK_TEST_SECRET_KEY='paystack-test-secret-key';
    const PAYSTACK_PRODUCTION_SECRET_KEY='paystack-production-secret-key';

    const ENABLE_FLUTTER_WAVE='enable-flutter-wave';
    const FLUTTER_WAVE_ENVIRONMENT='flutter-wave-environment';
    const FLUTTER_WAVE_TEST_SECRET_KEY='flutter-wave-test-secret-key';
    const FLUTTER_WAVE_PRODUCTION_SECRET_KEY='flutter-wave-production-secret-key';

    const ENABLE_STRIPE='enable-stripe';
    const STRIPE_ENVIRONMENT='stripe-environment';

    const STRIPE_TEST_SECRET_KEY='stripe_test_secret_key';
    const STRIPE_LIVE_SECRET_KEY='stripe_live_secret_key';

    const ENABLE_CASH_FREE='enable-cashfree';
    const CASH_FREE_ENVIRONMENT ='cash_free_environment';
    const CASH_FREE_TEST_APP_ID = 'cash_free_app_id';
    const CASH_FREE_PRODUCTION_APP_ID = 'cash_free_production_app_id';
    const CASH_FREE_SECRET_KEY='cash_free_secret_key';
    const CASH_FREE_PRODUCTION_SECRET_KEY='cash_free_production_secret_key';
    const CASH_FREE_TEST_CLIENT_ID_FOR_PAYOUT = 'cash_free_test_app_id_for_payout';
    const CASH_FREE_PRODUCTION_CLIENT_ID_FOR_PAYOUT = 'cash_free_production_app_id_for_payout';
    const CASH_FREE_TEST_CLIENT_SECRET_FOR_PAYOUT = 'cash_free_test_secret_for_payout';
    const CASH_FREE_PRODUCTION_CLIENT_SECRET_FOR_PAYOUT = 'cash_free_production_secret_for_payout';
    const ENABLE_RAZOR_PAY='enable-razor-pay';
    const RAZOR_PAY_ENVIRONMENT='razor_pay_environment';
    const ENABLE_PAYMOB='enable-paymob';
    const ENABLE_RENTAL_RIDE ='enable_rental_ride';
    const ENABLE_OTP_TRIPSTART ='enable_otp_tripstart';
    const ENABLE_DELIVERY_START_AND_END_OF_RIDE = 'enable_delivery_start_and_end_of_ride';
    const STRIPE_TEST_PUBLISHABLE_KEY='stripe_test_publishable_key';
    const STRIPE_LIVE_PUBLISHABLE_KEY='stripe_live_publishable_key';
    const RAZOR_PAY_TEST_API_KEY='razor_pay_test_api_key';
    const RAZOR_PAY_LIVE_API_KEY='razor_pay_live_api_key';
    const PAYSTACK_TEST_PUBLISHABLE_KEY='paystack_test_publishable_key';
    const PAYSTACK_PRODUCTION_PUBLISHABLE_KEY='paystack_production_publishable_key';
    const ENABLE_DIGITAL_SIGNATURE_AT_THE_END_OF_RIDE = 'enable_digital_signatur_at_the_end_of_ride';
    const CURRENCY = 'currency_code';
    const CURRENCY_SYMBOL='currency_symbol';

    const SHOW_RENTAL_RIDE_FEATURE='show_rental_ride_feature';
    const SHOW_RIDE_OTP_FEATURE='show_ride_otp_feature';
    const SHOW_RIDE_LATER_FEATURE='show_ride_later_feature';

    const ENABLE_SHIPMENT_LOAD_FEATURE='enable_shipment_load_feature';
    const ENABLE_SHIPMENT_UNLOAD_FEATURE='enable_shipment_unload_feature';
    const ENABLE_DIGITAL_SIGNATURE='enable_digital_signature';

    
    const DEFAULT_COUNTRY_CODE_FOR_MOBILE_APP='default_country_code_for_mobile_app';
    const DEFAULT__LANGUAGE_CODE_FOR_MOBILE_APP='default_Language_code_for_mobile_app';
    const USER_CAN_MAKE_A_RIDE_AFTER_X_MINIUTES='user_can_make_a_ride_after_x_miniutes';
    const TRIP_ACCEPT_REJECT_DURATION_FOR_DRIVER='trip_accept_reject_duration_for_driver';
    const GOOGLE_MAP_KEY_FOR_DISTANCE_MATRIX='google_map_key_for_distance_matrix';
    const MAXIMUM_TIME_FOR_FIND_DRIVERS_FOR_REGULAR_RIDE='maximum_time_for_find_drivers_for_regular_ride';
    const MAXIMUM_TIME_FOR_FIND_DRIVERS_FOR_BITTING_RIDE= 'maximum_time_for_find_drivers_for_bitting_ride';
    const DEFAULT_LAT='default_latitude';
    const DEFAULT_LONG='default_longitude';
    const GOOGLE_SHEET_ID='google_sheet_id';

    const ENABLE_KHALTI_PAY='enable-khalti-pay';
    const KHALTI_PAY_ENVIRONMENT='khalti_pay_environment';
    const KHALTI_PAY_TEST_API_KEY='khalti_pay_test_api_key';
    const KHALTI_PAY_LIVE_API_KEY='khalti_pay_live_api_key';

    const MINIMUM_WALLET_AMOUNT_FOR_TRANSFER='minimum_wallet_amount_for_transfer';
    const CONTACT_US_MOBILE1='contact_us_mobile1';
    const CONTACT_US_MOBILE2='contact_us_mobile2';
    const CONTACT_US_LINK='contact_us_link';
    const SHOW_RIDE_WITHOUT_DESTINATION='show_ride_without_destination';
    const SHOW_WALLET_FEATURE_ON_MOBILE_APP='show_wallet_feature_on_mobile_app';
    const SHOW_BANK_INFO_FEATURE_ON_MOBILE_APP='show_bank_info_feature_on_mobile_app';   
    const ENABLE_MY_ROUTE_BOOKING_FEATURE='enable_my_route_booking_feature';
    const HOW_MANY_TIMES_A_DRIVER_TIMES_A_DRIVER_CAN_ENABLE_THE_MY_ROUTE_BOOKING_PER_DAY='how_many_times_a_driver_can_enable_the_my_route_booking_per_day';
    const ENABLE_MODULES_FOR_APPLICATIONS = 'enable_modules_for_applications';
    const TRIP_DISPTACH_TYPE='trip_dispatch_type';
/*mail configuration*/

    const MAIL_MAILER='mail_mailer';
    const MAIL_HOST='mail_host';
    const MAIL_PORT='mail_port';
    const MAIL_USERNAME='mail_username';
    const MAIL_PASSWORD='mail_password';
    const MAIL_ENCRYPTION='mail_encryption';
    const MAIL_FROM_ADDRESS='mail_from_address';
    const MAIL_FROM_NAME='mail_from_name';    
        
}
