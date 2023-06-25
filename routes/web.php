<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
        

    });
    Route::get('/dashboard', 'Admin\MenuController@loadDashboard')->name('dashboard');
    Route::post('/on-stats-change', 'Admin\MenuController@onStatsChange');
    Route::post('/on-top-customer-change', 'Admin\MenuController@onTopCustomerChange');

    Route::prefix('menu')->group(function () {
        // Route::get('/breakfast', 'Admin\MenuController@menuBreakfast')->name('menu-breakfast');
        // Route::get('/lunch', 'Admin\MenuController@lunchBreakfast')->name('menu-lunch');
        Route::get('/side-menu-list', 'Admin\MenuController@sideMenuList')->name('side-menu-list');
        Route::get('/items-availability', 'Admin\MenuController@currentlyUnavailable')->name('items-availability');
        Route::get('/side-menu-grid', 'Admin\MenuController@sideMenuGrid')->name('side-menu-grid');
        Route::get('/side-menu-categories', 'Admin\MenuController@sideMenuCategories')->name('side-menu-categories');
        // Route::get('/details/{id}', 'Admin\MenuController@menuDetails')->name('menu-details');
        Route::get('/menu-type', 'Admin\MenuController@menuTypes')->name('menu-type');
        Route::get('/modifier-group', 'Admin\MenuController@modifierGroup')->name('modifier-group');
        Route::get('/complete-meals', 'Admin\MenuController@loadCompleteMeal')->name('complete-meals');
        Route::get('/other-location-menu/{restaurant_id}', 'Admin\MenuController@loadOtherLocation')->name('other-location-menu');
        /*        Route::get('/rewards-items', 'Admin\MenuController@loadRewardsItem')->name('rewards-items');*/
        /*        Route::get('/bonus', 'Admin\MenuController@loadBonus')->name('bonus');*/
        Route::get('/favorite-name', 'Admin\SettingController@favoriteNamePage')->name('favorite-name');

    });
    Route::prefix('promotions')->group(function () {
        Route::get('/rewards-items', 'Admin\MenuController@loadRewardsItem')->name('rewards-items');
        Route::get('/bonus', 'Admin\MenuController@loadBonus')->name('bonus');
        Route::get('/send-notification', 'Admin\MenuController@loadUserNotification')->name('send-notification');;

    });

    Route::prefix('setting')->group(function () {
        Route::get('/preparation-time', 'Admin\MenuController@loadPrepTime')->name('preparation-time');
        Route::get('/timing', 'Admin\SettingController@timingPage')->name('setting-timing');
        Route::get('/restaurants', 'Admin\SettingController@restaurantsPage')->name('setting-restaurants');
        Route::get('/notifications', 'Admin\SettingController@notificationsPage')->name('notifications');
        Route::get('/pagination-limit', 'Admin\SettingController@loadPaginationLimit')->name('pagination-limit');
        Route::get('/global-settings', 'Admin\SettingController@loadGlobalSettings')->name('tax-settings');
        Route::post('/update-global-settings', 'Admin\SettingController@updateGlobalSettings')->name('update-global-settings');

        Route::get('get-bank-details/{id}','Admin\SettingController@getBankDetails')->name('get-bank-details');
        Route::post('save-bank-details','Admin\SettingController@saveBankDetails')->name('save-bank-details');
    });

    Route::prefix('delivery')->group(function () {
        Route::get('/create', 'Admin\DeliveryController@createView')->name('delivery-create');
        Route::post('/get-quote', 'Admin\DeliveryController@getQuote')->name('get-delivery-quote');
        Route::post('/get-delivery', 'Admin\DeliveryController@getDelivery')->name('get-delivery');
    });

    Route::prefix('users')->group(function () {
        Route::get('/customers', 'Admin\UserController@userPage')->name('customers');
        Route::get('/guests', 'Admin\UserController@guests')->name('guests');
        Route::get('/customer-details/{userId}', 'Admin\UserController@userDetailPage');
        Route::get('/managers', 'Admin\UserController@managerPage')->name('managers');
        Route::get('/server-users', 'Admin\UserController@serverUserPage')->name('server-users');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/list', 'Admin\OrderController@orderListPage')->name('order-list');
        Route::get('/details/{orderId}', 'Admin\OrderController@singleOrderPage')->name('details');
        Route::post('/mark-order-picked/{orderId}', 'Admin\OrderController@markOrderPicked')->name('mark-order-picked');
        /*        Route::get('/transaction', 'Admin\OrderController@loadTransaction')->name('transaction');*/
    });
    Route::prefix('financial')->group(function () {
        Route::get('/transaction', 'Admin\OrderController@loadTransaction')->name('transaction');
        Route::get('/statements', 'Admin\OrderController@loadStatements')->name('statements');
    });

    // Route::post('/add-menu', 'Admin\MenuController@addMenu');
    Route::post('/update-time', 'Admin\SettingController@updateTime');
    Route::post('/add-side-menu-category', 'Admin\MenuController@addNewCategory');
    Route::post('/add-side-menu', 'Admin\MenuController@addNewSideMenu');
    Route::post('/assign-side-menu', 'Admin\MenuController@assignSideMenu');
    Route::get('/logout', 'Admin\AuthController@logout')->name('logout');
    Route::get('/delete-items/{menuId}/{table}', 'Admin\MenuController@deleteMenuOrCategory');
    Route::post('/edit-side-menu-category', 'Admin\MenuController@editNewCategory');
    Route::get('/get-categories/{menuId}/{type}', 'Admin\MenuController@getSideMenuCategories');
    Route::get('/get-menu-types', 'Admin\MenuController@getMenusType');
    Route::post('/add-menu-type', 'Admin\MenuController@addMenuType');
    Route::post('/add-modifier-group', 'Admin\MenuController@addModifierGroup');
    Route::post('upadate-modifiergroup-item-price','Admin\MenuController@updateModGrpItemPrice');
    Route::post('delete-modifiergroup-item','Admin\MenuController@deleteModGrpItem');
    Route::get('/get-modifier-items/{modifierId}/{isWhat}', 'Admin\MenuController@getModifierItems');
    Route::get('/get-category-menus/{categoryId}', 'Admin\MenuController@getCategoriesMenu');
    Route::get('/get-modifier-side-menu/{modifierId}', 'Admin\MenuController@modifierSideMenu');
    Route::post('/add-menu-type-meal', 'Admin\MenuController@addMenuTypeMeals');
    Route::get('/get-selected-meal/{menuId}', 'Admin\MenuController@getSelectedMeal');
    Route::post('/add-new-manager', 'Admin\UserController@addNewManager');
    Route::post('/add-new-server-user', 'Admin\UserController@addNewServerUser');
    Route::post('/update-server-user-status', 'Admin\UserController@updateServerUserStatus');
    Route::get('/on-restaurant-change/{restaurantId}', 'Admin\MenuController@onRestaurantChange');
    Route::post('/update-location', 'Admin\MenuController@updateLocation');
    Route::post('/search-side-menu', 'Admin\MenuController@searchSideMenu');
    Route::post('/search-item-availability','Admin\MenuController@searchItemsAvailability');
    Route::get('/single-side-menu/{menuId}', 'Admin\MenuController@singleSideMenu');
    Route::get('/single-category/{categoryId}', 'Admin\MenuController@singleCategory');
    Route::post('/add-favorite-label', 'Admin\SettingController@addFavoriteLabel');
    Route::get('/delete-favorite-label/{labelId}', 'Admin\SettingController@deleteFavoriteLabel');
    Route::post('/copy-from-another-location', 'Admin\MenuController@copyFromAnotherLocation');
    Route::get('/single-item-modifiers/{item_id}', 'Admin\MenuController@singleItemModifiers');
    Route::get('/get-restaurant-menu/{restaurantId}', 'Admin\MenuController@getRestaurantMenu');
    Route::post('/add-reward-item', 'Admin\MenuController@addRewardsItem');
    Route::get('/reward-item-status/{rewardItemId}/{status}', 'Admin\MenuController@rewardItemStatus');
    Route::get('/get-menu-category/{menuId}', 'Admin\MenuController@getMenuCategory');
    Route::post('/get-sales', 'Admin\MenuController@getSales');
    Route::post('/order-timing-chart', 'Admin\MenuController@getOrderTiming');
    Route::post('/order-pickup-timing-chart', 'Admin\MenuController@getOrderPickupTiming');
    Route::post('/no-of-orders', 'Admin\MenuController@noOfOrders');
    Route::post('/get-earning', 'Admin\MenuController@getEarning');
    Route::post('/preparation-time-setting', 'Admin\MenuController@addLocationPreparationTime');
    Route::post('/search-order', 'Admin\OrderController@searchOrder');
    Route::get('/user-delete/{userId}', 'Admin\UserController@deleteUser');
    Route::get('/reward-delete/{rewardId}/{type}', 'Admin\UserController@deleteReward');
    Route::post('/update-user', 'Admin\UserController@editUsers');
    Route::post('/add-user-reward-points', 'Admin\UserController@addUserRewards');
    Route::get('/get-category-items/{categoryId}', 'Admin\MenuController@getCategoriesItems');
    Route::post('/add-admin-reward', 'Admin\MenuController@addAdminReward');
    Route::post('/edit-order-preparation-time', 'Admin\MenuController@orderPreparationTime');
    Route::post('/create-bonus', 'Admin\MenuController@createBonus');
    Route::post('/get-bonus-users', 'Admin\MenuController@getBonusUser');
    Route::post('/reset-user-password', 'Admin\UserController@resetUserPassword');
    Route::post('/search-category', 'Admin\MenuController@searchCategories');
    Route::post('/search-user', 'Admin\UserController@searchUser');
    Route::post('/search-modifier', 'Admin\MenuController@searchModifiers');
    Route::get('/single-modifier/{modifierId}', 'Admin\MenuController@singleModifier');
    Route::post('/update-manager', 'Admin\UserController@updateManager');
    Route::post('/add-complete-meal', 'Admin\MenuController@addCompleteMeal');
    Route::get('/delete-complete-meal/{menuId}', 'Admin\MenuController@deleteMeals');
    Route::get('/update-complete-meal/{itemId}/{status}', 'Admin\MenuController@updateMeals');
    Route::get('/get-complete-meal-items/{menuId}', 'Admin\MenuController@completeMealItems');
    Route::get('/delete-admin-reward/{rewardId}', 'Admin\MenuController@deleteAdminReward');
    Route::get('/get-single-bonus/{bonusId}', 'Admin\MenuController@getSingleBonus');
    Route::get('/get-single-order/{orderId}', 'Admin\OrderController@singleOrderDetail');
    Route::get('/set-print-queue/{orderId}', 'CloudPrint@addPrintQueue');
    Route::post('/update-row-order', 'Admin\MenuController@updateRowOrder');
    Route::post('/set-notification-text', 'Admin\SettingController@setNotificationText');
    Route::get('/delete-managed-notification/{rowId}', 'Admin\SettingController@deleteNotifications');
    Route::get('/get-transaction', 'Admin\OrderController@getTransactions');
    Route::post('/order-refund', 'Admin\OrderController@orderRefund');
    Route::post('/partial-order-refund', 'Admin\OrderController@partialOrderRefund');
    Route::post('/partial-item-refund', 'Admin\OrderController@partialItemRefund')->name('partial-item-refund');
    Route::post('/set-pagination-limit', 'Admin\SettingController@setPaginationLimit');
    Route::get('/set-restaurant-status/{restaurantId}/{isOpened}', 'Admin\SettingController@restaurantStatus');
    Route::get('/get-users-keyword', 'Admin\MenuController@getUsersByKeyword');
//    Route::get('/send-notification', 'Admin\MenuController@loadUserNotification');
    Route::post('/send-notification-action', 'Admin\MenuController@sendBulkNotification');
    Route::get('/change-item-to-unavailable/{itemId}', 'Admin\MenuController@changeItemToUnavailable');
    Route::get('/change-item-to-available/{itemId}', 'Admin\MenuController@changeItemToAvailable');
    Route::get('/all-items-unavailable', 'Admin\MenuController@allItemToUnavailable');
    Route::get('/all-items-available', 'Admin\MenuController@allItemToAvailable');


    // CLOVER SYNC
    Route::prefix('clover')->group(function () {
        Route::get('/clover-items-mapping','Admin\CloverController@cloverItemsMapping');
        Route::get('/fetch-item/{type}', 'Admin\MenuController@syncClover');
        Route::get('/push-item/{type}', 'Admin\CloverController@pushData');
    });

    // SETTINGS
    Route::get('/settings/timings', 'Admin\SettingController@loadTimingView')->name('res_timings');
    Route::post('/settings/menu-timing-update', 'Admin\SettingController@updateMenuTimingRes');
    Route::post('/settings/kitchen-offline-range', 'Admin\SettingController@updateOfflineDates')->name('updateOfflineDates');
    Route::get('/settings/kitchen-offline-range-delete/{id}', 'Admin\SettingController@updateOfflineDatesDelete')->name('updateOfflineDates');

    // CARDS
    Route::get('/card/user-cards', 'Admin\CardController@loadUserCards')->name('user_cards');
    Route::get('/card/user-card/{id}', 'Admin\CardController@loadUserCard')->name('user_card');
    Route::get('/card/gift-cards', 'Admin\CardController@loadGiftCards')->name('gift_cards');
    Route::post('/card/recharge', 'Admin\CardController@cardRecharge')->name('card_recharge');
});
Route::get('/remove-fcm-table', 'RealTimeController@removeFcmTable');
Route::get('/test-fcm', 'RealTimeController@test');

Route::get('/login', 'Admin\AuthController@showLoginForm')->middleware('guest');
Route::post('/clover-app-data', 'Admin\SettingController@saveCloverAppData')->middleware('guest');

Route::post('/login', 'Admin\AuthController@login')->middleware('guest')->name('login');
Route::get('/apps', 'Admin\AuthController@appRedirect');
Route::get('forget-password','Admin\AuthController@forgetPassword');
Route::get('reset-password/{token}','Admin\AuthController@resetPassword');
Route::post('reset-password','Admin\AuthController@resetAdminPassword');
Route::any('/postmates-status-webhook', 'Admin\DeliveryController@getStatusFromPM');
Route::any('/handle-printer-call', 'CloudPrint@handlePrinterCalls');
Route::any('/test-stripe', 'API\StripePaymentController@testWallet');


Route::get('/get-files', function () {
    $files = array_diff(scandir(public_path('storage/images/menu-images')), array('..', '.'));
    foreach ($files as $key => $value) {
        $files[$key] = 'images/menu-images/' . $value;
    }
    return $files;
});
