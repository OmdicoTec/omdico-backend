<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// message from amirmahdi
Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [\App\Http\Controllers\Api\v1\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\v1\AuthController::class, 'login'])->name('login');
    Route::middleware('auth:api')->get('/logout', [\App\Http\Controllers\Api\v1\AuthController::class, 'logout']);
    // user profile
    Route::middleware('auth:api')->get('/user', [\App\Http\Controllers\Api\v1\AuthController::class, 'index']);
    Route::middleware('auth:api')->get('/me', [\App\Http\Controllers\Api\v1\AuthController::class, 'index']);

    Route::middleware('auth:api')->get('/user_app', function (Request $request) {
        $user = $request->user();
        $token = $user->createToken('MyAppToken')->accessToken;
        return response()->json(['user' => $user, 'token' => $token]);
    });

    Route::middleware('auth:api')->get('/checkAuth', function (Request $request) {
        if ($request->user()) {
            return response()->json(['valid' => true])->setStatusCode(200);
        }
        // status code for invalid token is 401
        return response()->json(['valid' => false])->setStatusCode(401);
    });
});

// Version 2 of the API

Route::group(['prefix' => 'v2'], function () {

    // user register with phone number and password
    Route::middleware(['throttle:6,1'])->post('/register', [\App\Http\Controllers\Api\v2\AuthController::class, 'register']);

    // step 1, get the phone number and password and user get disable token and receive sms OTP code
    Route::middleware(['throttle:3,5'])->post('/login', [\App\Http\Controllers\Api\v2\SmsController::class, 'generateToken']);
    // step 2, get the phone number and OTP code and user get access token (Middleware: verify.login)
    Route::middleware(['auth:api', 'throttle:6,1'])->post('/login_OTP', [\App\Http\Controllers\Api\v2\SmsController::class, 'enableToken']);

    // logout
    Route::middleware(['auth:api'])->get('/logout', [\App\Http\Controllers\Api\v2\AuthController::class, 'logout']);

    // SMS verification route __invoke method
    Route::middleware(['auth:api', 'throttle:6,1'])->post('/verify-mobile', [\App\Http\Controllers\Api\v2\VerifyMobileController::class, '__invoke']);


    Route::any('/unAuthorized', function () {
        return response()->json(['message' => 'احراز هویت نشده'], 401);
    })->name('unAuthorized');

    // user info
    Route::middleware(['auth:api', 'verify.mobile:all'])->get('/me', [\App\Http\Controllers\Api\v2\AuthController::class, 'index']);


    // route group for supplier only
    Route::group(['middleware' => ['auth:api', 'verify.mobile:all', 'role:admin,supplier']], function () {
        // user upload image
        # TODO: add  it for all users to user this
        Route::post('/user/upload/image', [\App\Http\Controllers\Users\UploadController::class, 'uploadImage']);
        // create product
        Route::post('/user/supplier/product/create', [\App\Http\Controllers\Users\Supplier\ProductController::class, 'storeBySupplier']);
        // Purchase request routes
        # For suppliers to be able to see the purchase requests and set the offer
        Route::get('/user/supplier/purchase/requests/list', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class, 'getSupplierList']);
        # Get purchase request details for supplier
        Route::get('/user/supplier/purchase/request/show/active/details/{id}', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class, 'getActivePurchaseDetails']);

        // Supplier products interested controllers
        # List:
        Route::get('/user/supplier/products/interested/list', [\App\Http\Controllers\Api\v2\User\SupplierProductController::class, 'index']);
        Route::delete('/user/supplier/products/interested/delete/{id}', [\App\Http\Controllers\Api\v2\User\SupplierProductController::class, 'destroy']);
        Route::put('/user/supplier/products/interested/add/{id}', [\App\Http\Controllers\Api\v2\User\SupplierProductController::class, 'store']);

        // Search
        # search in the invoice maker
        Route::post('/user/supplier/tenders/make/invoice/products/search', [\App\Http\Controllers\Users\Supplier\ProductController::class ,'searchProductsForInvoice']);

        // Supplier Invoice
        # make invoice
        Route::post('/user/suppliers/request/make/offer', [\App\Http\Controllers\Users\Supplier\InvoiceController::class, 'createInvoice']);

    });
    // route group for users that have role seller=supplier or customer (Buyer in nuxt app)
    Route::group(['middleware' => ['auth:api', 'verify.mobile:all', 'role:admin,supplier,customer']], function () {

        /**
         * Seller profile routes
         *
         * Method contain for user: store, show, edit
         *
         * Method contain for admin: index, show, edit, update, approve, reject
         *
         * Note: seller have one more route for create legal info
         */
        // user upload file
        // Route::post('/user/file/store', [\App\Http\Controllers\FileController::class, 'store']); // disabled, but used in other routes

        // user store nature info
        Route::post('/user/nature/store', [\App\Http\Controllers\NaturalInfoController::class, 'store']);
        Route::get('/user/nature/show', [\App\Http\Controllers\NaturalInfoController::class, 'show']);
        Route::post('/user/nature/edit', [\App\Http\Controllers\NaturalInfoController::class, 'edit']);

        // user store Store info
        Route::post('/user/store/store', [\App\Http\Controllers\StoreInfoController::class, 'store']);
        Route::get('/user/store/show', [\App\Http\Controllers\StoreInfoController::class, 'show']);
        Route::post('/user/store/edit', [\App\Http\Controllers\StoreInfoController::class, 'edit']);

        // user address info
        Route::post('/user/address/store', [\App\Http\Controllers\AddressInfoController::class, 'store']);
        Route::get('/user/address/show', [\App\Http\Controllers\AddressInfoController::class, 'show']);
        Route::post('/user/address/edit', [\App\Http\Controllers\AddressInfoController::class, 'edit']);

        // user financial info
        Route::post('/user/financial/store', [\App\Http\Controllers\FinanceInfoController::class, 'store']);
        Route::get('/user/financial/show', [\App\Http\Controllers\FinanceInfoController::class, 'show']);
        Route::post('/user/financial/edit', [\App\Http\Controllers\FinanceInfoController::class, 'edit']);

        // user document info
        Route::post('/user/document/store', [\App\Http\Controllers\DocumentsInfoController::class, 'store']);
        Route::get('/user/document/show', [\App\Http\Controllers\DocumentsInfoController::class, 'show']);
        Route::post('/user/document/edit', [\App\Http\Controllers\DocumentsInfoController::class, 'edit']);

        // user contact info
        // TODO: change contact to contract FUCK!
        Route::post('/user/contract/store', [\App\Http\Controllers\ContractInfoController::class, 'store']);
        Route::get('/user/contract/show', [\App\Http\Controllers\ContractInfoController::class, 'show']);
        Route::post('/user/contract/edit', [\App\Http\Controllers\ContractInfoController::class, 'edit']);

        // user legal info, must check user type
        Route::post('/user/legal/store', [\App\Http\Controllers\LegalInfoController::class, 'store']);
        Route::get('/user/legal/show', [\App\Http\Controllers\LegalInfoController::class, 'show']);
        Route::post('/user/legal/edit', [\App\Http\Controllers\LegalInfoController::class, 'edit']);

        // Purchase request routes
        # from panel
        Route::post('/user/purchase/request/create', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class, 'create']);
        # from shopping pages Directive
        Route::post('/user/shop/purchase/request/create/{cart}', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class, 'directPurchaseRequest']);
        # For slef user after create request
        Route::get('/user/purchase/request/list/{status?}', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class, 'userList']);


        // Basketing
        Route::post('/user/basket', [\App\Http\Controllers\Api\v2\User\CartsController::class, 'basket'])->middleware(['throttle:30,1']);

        // Delivery Address
        # get user delivery address
        Route::get('/user/delivery/list', [\App\Http\Controllers\AddressController::class, 'index'])->middleware(['throttle:30,1']);
        Route::post('/user/delivery', [\App\Http\Controllers\AddressController::class, 'store'])->middleware(['throttle:5,1']);

        // Offers
        # List of offers for customer
        Route::get('/user/offer/list/invoice/{id}', [\App\Http\Controllers\Users\OffersController::class, 'customerOffers'])->middleware(['throttle:30,1']);
        Route::put('/user/offer/list/invoice/{id}', [\App\Http\Controllers\Users\OffersController::class, 'customerOfferConfirmInvoice'])->middleware(['throttle:10,1']);
    });

    // route group for admin
    Route::group(['middleware' => ['auth:api', 'verify.mobile:all', 'role:admin']], function () {
        // List of suppliers
        Route::get('/admin/users/list/supplier', [\App\Http\Controllers\Users\Admin\UsersController::class, 'supllierList']);
        // Status of supplier
        Route::get('/admin/users/status/supplier/{id}', [\App\Http\Controllers\Users\Admin\UsersController::class, 'supplierStatus']);
        // List of customers
        Route::get('/admin/users/list/customer', [\App\Http\Controllers\Users\Admin\UsersController::class, 'customerList']);
        // Status of customer
        Route::get('/admin/users/status/customer/{id}', [\App\Http\Controllers\Users\Admin\UsersController::class, 'customerStatus']);

        // Add user by admin: supllier or customer
        Route::post('/admin/users/add', [\App\Http\Controllers\Users\Admin\UsersController::class, 'addUser']);

        // Add Category
        Route::post('/admin/category/add', [\App\Http\Controllers\CategoryController::class, 'store']);
        // Show Category
        Route::get('/admin/category/show', [\App\Http\Controllers\CategoryController::class, 'show']);
        # only for admin panel to edit category recursive
        Route::get('/admin/category/showRecursive', [\App\Http\Controllers\CategoryController::class, 'recursive']);
        Route::get('/admin/category/showAll', [\App\Http\Controllers\CategoryController::class, 'showAll']);
        // Edit Category
        Route::post('/admin/category/update/{id}', [\App\Http\Controllers\CategoryController::class, 'update']);
        // Delete Category
        Route::delete('/admin/category/delete/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy']);
        // get category with characteristics features
        Route::get('/admin/category/get/features', [\App\Http\Controllers\CharacteristicsController::class, 'getCategoryFeatures']);

        // status info users
        Route::get('/admin/users/status/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'status']);

        // Supplier show info
        Route::get('/admin/users/supplier/nature/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'natureShow']);
        Route::get('/admin/users/supplier/store/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'storeShow']);
        Route::get('/admin/users/supplier/address/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'addressShow']);
        Route::get('/admin/users/supplier/financial/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'financeShow']);
        Route::get('/admin/users/supplier/document/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'documentShow']);
        Route::get('/admin/users/supplier/contract/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'contractShow']);
        Route::get('/admin/users/supplier/legal/show/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'legalShow']);
        // Admin note/hint to Supplier
        Route::post('/admin/users/supplier/report/note/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'reportNote']);
        // Admin approve Supplier info
        Route::post('/admin/users/supplier/approve/{user}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'approve']);
        // Admin update Supplier info
        Route::post('/admin/users/supplier/update/{user}/{table_name}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'update']);

        // List of all suppliers
        Route::get('/admin/suppliers/list', [\App\Http\Controllers\Users\Admin\UsersController::class, 'supplierPlainList']);

        # Product routes
        // List of all products
        Route::get('/admin/products/list', [\App\Http\Controllers\ProductController::class, 'listAllProducts']);
        // Get product by id
        Route::get('/admin/products/show/{product}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'productShow']);
        // Update product by id
        Route::post('/admin/products/update/{product}', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'productUpdate']);
        // Create new product
        Route::post('/admin/products/create', [\App\Http\Controllers\Users\Admin\SupplierController::class, 'productStore']);
        // remove existing product
        Route::delete('/admin/products/delete/{product}', [\App\Http\Controllers\ProductController::class, 'destroy']);

        // feature creation
        Route::post('/admin/products/add/feature/{category_id}', [\App\Http\Controllers\CharacteristicsController::class, 'createFeature']);
        // edit feature
        Route::post('/admin/products/edit/feature/{id}', [\App\Http\Controllers\CharacteristicsController::class, 'editFeature']);
        // delete feature
        Route::delete('/admin/products/delete/feature/{id}', [\App\Http\Controllers\CharacteristicsController::class, 'deleteFeature']);

        // Purchase request routes
        Route::get('/admin/purchase/request/list/{status?}', [\App\Http\Controllers\Api\v2\Admin\PurchaseRequestsController::class, 'listOfPurchase']);
        # get information purchase request
        Route::get('/admin/purchase/request/show/{status}/{purchase_id}', [\App\Http\Controllers\Api\v2\Admin\PurchaseRequestsController::class, 'getPurchaseRequestInformation']);
        # update status information purchase request
        Route::post('/admin/purchase/request/update/active_status', [\App\Http\Controllers\Api\v2\Admin\PurchaseRequestsController::class, 'doActiveStatus']);
        # Delete purchase request
        Route::post('/admin/purchase/request/delete', [\App\Http\Controllers\Api\v2\Admin\PurchaseRequestsController::class, 'doDeleteOnlyPending']);

        // Keywords generator
        # list of keyword
        Route::get('/admin/keyword/{type}/{id}', [\App\Http\Controllers\Api\v2\Admin\KeywordController::class, 'index'])
        ->whereIn('type', ['category', 'product']);
        # add keyword
        Route::post('/admin/keyword/add/{type}/{id}', [\App\Http\Controllers\Api\v2\Admin\KeywordController::class, 'create'])
        ->whereIn('type', ['category', 'product']);
        # destroy keyword (Keyword is id)
        Route::delete('/admin/keyword/delete/{Keyword}', [\App\Http\Controllers\Api\v2\Admin\KeywordController::class, 'destroy']);

        // Products seometa
        # get product seometa
        Route::get('/admin/product/seometa/{productid}', [\App\Http\Controllers\ProductmetaseoController::class, 'index']);
        Route::post('/admin/product/seometa/{productid}', [\App\Http\Controllers\ProductmetaseoController::class, 'store']);

        // TODO: Test
        // Route::post('/test', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class,'create']);
        // Route::post('/test/mylist', [\App\Http\Controllers\Api\v2\User\PurchaseRequestsController::class,'usreList']);
    });

    // test
    // Route::get('/test', [\App\Http\Controllers\Api\v2\AuthController::class, 'test']);
    // Test: check status info table for user
    // Route::get('/test2', [\App\Http\Controllers\StatusInfoController::class, 'filledStatus']);
    // Route::middleware(['auth:api', 'verify.mobile:all', 'role:seller,customer'])->get('/test2', [\App\Http\Controllers\FileController::class, 'destroyTemporaryFiles']);

    // List of last products
    # show product list with categories
    Route::get('/products/list/shop', [\App\Http\Controllers\ProductController::class, 'index']);
    # category product list
    Route::get('/fa/category/{slug}', [\App\Http\Controllers\ProductController::class, 'categoryProducts']);

    Route::get('/products/show/{product}', [\App\Http\Controllers\ProductController::class, 'show']);
    // List of all categories confirmed
    Route::get('/categories/list', [\App\Http\Controllers\CategoryController::class, 'index']);
    // List of all Provinces Iran
    Route::get('/province/iran', [\App\Http\Controllers\ProvinceController::class, 'indexIran']);

    // slug generator + filter // TIV
    Route::get('/slugify', [\App\Http\Controllers\UtilsController::class, 'slugify']);
    Route::get('/recommend/generator/config/{title}', [\App\Http\Controllers\CharacteristicsController::class, 'getGeneratorConfig']);
    Route::post('/recommend/generator/search/{title}', [\App\Http\Controllers\CharacteristicsController::class, 'getGeneratorSearch']);
    Route::get('/recommend/generator/{title}/{id}', [\App\Http\Controllers\CharacteristicsController::class, 'store']);

    // Basketing for ghost users
    Route::post('/ghost/basket', [\App\Http\Controllers\Api\v2\User\CartsController::class, 'basketGhost'])->middleware(['throttle:10,1']);

    // Route::get('/test', [\App\Http\Controllers\CharacteristicsController::class,'test']);
    // Route::get('/test/search', [\App\Http\Controllers\CharacteristicsController::class,'search']);

    // Torob API
    Route::get('/torob/secretapi_OSNXSG153SD1', [\App\Http\Controllers\Api\v2\Torob\ProductController::class, 'index']);
    Route::get('/omdico/sitemap_product_hep12TDGNDSTN', [\App\Http\Controllers\Api\v2\Torob\ProductController::class, 'products']);
});


Route::get('/pay', [\App\Http\Controllers\PayController::class, 'pay']);
Route::post('/pay/callback', [\App\Http\Controllers\PayController::class, 'callback'])->name('pay.callback');
Route::get('/pay/callback', [\App\Http\Controllers\PayController::class, 'callback']);
