<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', 'API\AuthController@login')->middleware('throttle:30,1');
Route::post('/server-login', 'API\AuthController@ServerLogin')->middleware('throttle:30,1');
Route::post('/register', 'API\AuthController@register')->middleware('throttle:30,1');;
Route::post('/facebook-login', 'API\AuthController@facebookLogin')->middleware('throttle:30,1');
Route::post('/google-login', 'API\AuthController@googleLogin')->middleware('throttle:30,1');;
Route::get('/get-menu-type/{restaurantId}', 'API\MenuController@getMenuType');
Route::get('/single-menu-type/{menuId}', 'API\MenuController@singleMenuType');
Route::get('/get-menu/{typeId}', 'API\MenuController@getMenu');
Route::get('/get-menu-by-category/{typeId}/{categoryId}', 'API\MenuController@getMenuByCategory');
Route::get('/get-restaurants', 'API\MenuController@getRestaurants');
Route::post('/get-restaurants-distance', 'API\MenuController@getRestaurantsDistance');
Route::post('/in-bound-restaurants', 'API\MenuController@getInboundRestaurants');
Route::post('/get-delivery-restaurant', 'Admin\DeliveryController@getDeliveryRestaurant');
Route::post('/reset-delivery', 'API\CartController@resetDeliveryFee');
Route::get('/get-timezone', 'API\MenuController@getTimezone');
Route::get('/get-main-menu-modifiers/{menuId}', 'API\MenuController@getMenuModifiers');
Route::get('/get-edit-cart-modifiers/{id}', 'API\MenuController@getEditCartModifiers');
Route::get('/get-single-menu/{itemId}/{menuId}', 'API\MenuController@singleMenuDetails');
Route::get('/get-cart-item-details/{id}', 'API\MenuController@getSingleCartDetails');
Route::get('/get-menu-type-meal/{menuTypeId}', 'API\MenuController@getMenuTypeMeals');
Route::post('/add-to-cart', 'API\CartController@addCart');
Route::get('/remove-from-cart/{itemId}', 'API\CartController@removeCart');
Route::get('/get-my-cart', 'API\CartController@getCart');
Route::post('/empty-cart', 'API\CartController@emptyCart');
Route::post('/guest-empty-cart', 'API\CartController@emptyCartGuest');
Route::post('/update-my-cart', 'API\CartController@updateCart');
Route::post('/duplicate-item-in-cart', 'API\CartController@duplicateCart');
Route::get('/get-single-restaurant/{restaurantId}', 'API\MenuController@singleRestaurantDetails');
Route::post('/send-otp', 'API\UserController@sendOtp');
Route::post('/reset-password', 'API\UserController@resetPassword')->middleware('throttle:30,1');
Route::get('/email', 'API\OrderController@testEmail');
Route::get('/get-most-ordered-menu/{type}', 'API\MenuController@getMostOrderedMenu');
Route::get('/get-timing', 'API\CartController@getTiming');
Route::get('/get-restaurant-timing', 'API\RestaurantController@getRestaurantTimings');
Route::post('/contact-us', 'API\UserController@contactUs');
Route::get('/validate-menu-timing/{menuId}', 'API\MenuController@validateMenuTiming');
Route::post('/cart/update-quantity', 'API\CartController@increaseCartQuantity');
Route::post('/validate-cart-items', 'API\CartController@validateCartItems');
Route::post('/validate-restaurant-items', 'API\CartController@validateRestaurantItems');
Route::post('/clear-unavailable-items', 'API\CartController@clearUnavailableItems');
// CARD ENDPOINTS
Route::get('/cards','API\CardController@getCards');
Route::get('/card/{id}','API\CardController@getCardById');
Route::get('/featured-cards','API\CardController@featuredCards');
Route::post('/franchise-inquiry','API\RestaurantController@franchiseInquiry')->middleware('throttle:30,1');
Route::middleware('auth:api')->group(function () {
    Route::get('/get-favorite-menu/{typeId}', 'API\MenuController@getFavoriteMenu');
    Route::post('/select-preference', 'API\UserController@selectPreference');
    Route::get('/get-profile', 'API\UserController@getProfile');
    Route::post('/update-profile', 'API\UserController@updateProfile');
    Route::get('/membership', 'API\UserController@userMembership');
    Route::post('/confirm-order', 'API\OrderController@confirmOrder');
    Route::get('/get-order-history', 'API\OrderController@orderHistory');
    Route::get('/get-favorite-label', 'API\OrderController@FavoriteLabels');
    Route::post('/mark-as-favorite', 'API\OrderController@markAsFavorite');
    Route::get('/get-my-rewards', 'API\OrderController@myRewards');
    Route::post('/favorite-restaurant', 'API\MenuController@FavoriteRestaurant');
    Route::post('/favorite-item', 'API\MenuController@markItemFavorite');
    Route::get('/get-reward-items/{flag}', 'API\OrderController@getRewardItems');
    Route::get('/get-coupons', 'API\OrderController@getCoupon');
    Route::get('/get-single-order/{orderId}', 'API\OrderController@getSingleOrder');
    Route::post('/change-password', 'API\UserController@changePassword');
    Route::post('/place-order-feedback', 'API\UserController@placeOrderFeedback');
    Route::post('/restaurant-preference', 'API\UserController@setRestaurantPreference');
    Route::post('/set-user-address', 'API\UserController@setUserAddress');
    Route::get('/delete-my-account', 'API\UserController@deleteMyAccount');
    Route::post('/subscription-preference', 'API\UserController@setSubscriptionPreference');
    Route::get('/get-preference', 'API\UserController@getMyPreferences');
    Route::get('/logout', 'API\UserController@doLogout');
    Route::post('/add-favorite-label', 'API\UserController@addFavoriteLabel');
    Route::post('/make-re-order', 'API\CartController@reOrder');
    Route::get('/get-bonus', 'API\UserController@getBonus');
    Route::post('/device-preference', 'API\UserController@setDevicePreference');
    Route::get('/apply-reward/{couponId}', 'API\CartController@applyReward');
    Route::get('/apply-bonus/{bonusId}', 'API\CartController@applyBonus');
    Route::get('/remove-reward', 'API\CartController@removeReward');
    Route::get('/remove-bonus', 'API\CartController@removeBonus');
    Route::get('/get-active-orders', 'API\OrderController@getActiveOrders');
    Route::post('/save-card', 'API\PaymentController@saveCard');
    Route::get('/set-card-default/{cardId}','API\PaymentController@setCardDefault');
    Route::get('/get-cards', 'API\PaymentController@getCards');
    Route::post('/delete-card', 'API\BtPaymentController@deleteCard');
    Route::post('/make-order-payment', 'API\PaymentController@makeOrderPayment');
    Route::get('/recent-restaurant', 'API\MenuController@getRecentOrderRestaurant');

    // Braintree Endpoints
    Route::post('/create-customer-paymethod','API\BtPaymentController@createCustomerPayMethod');
    Route::post('/bt-save-card','API\BtPaymentController@saveCard');
    Route::post('/bt-delete-card','API\BtPaymentController@deleteCard');
    Route::post('bt-make-order-payment','API\BtPaymentController@makeOrderPayment');
    Route::post('bt-guest-order-payment','API\BtPaymentController@guestMakeOrderPayment');
    Route::get('bt-make-refund/{id}','API\BtPaymentController@makeRefund');

    // STRIPE
    Route::post('/stripe/create-customer-paymethod','API\StripePaymentController@createCustomerPayMethod');
    Route::post('/stripe/delete-card', 'API\StripePaymentController@deleteCard');
    // USER ADDRESS
    Route::get('/user/addresses','API\UserController@getUserAddresses');
    Route::get('/user/billing-addresses','API\UserController@getAllBillingAddresses');
    Route::post('/user/address','API\UserController@storeUserAddress');
    Route::get('/user/address/{id}','API\UserController@getUserAddress');
    Route::delete('/user/address/{id}','API\UserController@deleteUserAddress');
    Route::get('/user/address/default/{id}','API\UserController@setAddressDefault');

    // GIFT CARD/FALAFEL CARD

    Route::post('/purchase-falafel-card','API\CardController@purchaseFalafelCard');
    Route::get('/user-falafel-cards','API\CardController@getFalafelCards');
    Route::get('/falafel-card/{id}','API\CardController@getFalafelCardById');
    Route::post('/add-money-card','API\CardController@addMoneyCard');
    Route::post('/purchase-gift-card','API\CardController@purchaseGiftCard');
    Route::post('/redeem-gift-card','API\CardController@redeemGiftCard');

    // MANAGE CARD
    Route::post('/falafel-card/update-nickname','API\CardController@updateCardNickname');
    Route::post('/falafel-card/update-default-status','API\CardController@updateCardDefaultStatus');
    Route::delete('/falafel-card/delete/{id}','API\CardController@deleteFalafelCard');
    Route::post('/falafel-card/transfer-amount','API\CardController@transferFalafelAmount');
    Route::get('/falafel-card-history','API\CardController@getCardHistory');

    // REWARD ITEMS
    Route::get('/reward/items','API\RewardController@getRewardItems');

    // SERVER APIS
    Route::post('/server/confirm-order', 'API\ServerController@confirmOrder');
    Route::get('/server/orders', 'API\ServerController@getServerUserOrders');
    Route::post('/server/add-points', 'API\ServerController@addUserPoints');
    Route::post('/server/verify-card', 'API\ServerController@verifyCard');
    Route::get('/server/user-info/{userId}', 'API\ServerController@getUserInfo');
});

Route::get('/notification', 'API\OrderController@testNotification');
Route::get('/get-customers-favorite', 'API\OrderController@customerFavorites');
Route::post('/guest-checkout', 'API\PaymentController@guestCheckout');
Route::get('/get-single-order-guest/{orderId}', 'API\OrderController@getSingleOrderGuest');
Route::get('/all-reward-items', 'API\RewardController@getAllRewardItems');

Route::post('test-payment',[\App\Http\Controllers\API\StripePaymentController::class,'testPayment']);
