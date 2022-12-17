<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/**********************  User *********************************************** */

Route::post('/register',[ApiController::class,'register']);
Route::post('/login',[ApiController::class,'userLogin']);

/*********************** Shop ************************************************ */

Route::post('/shop_register',[ApiController::class,'shopRegister']);
Route::post('/shop_login',[ApiController::class,'shopLogin']);

/********************** Fraud ************************************************ */

Route::post('/fraud_register',[ApiController::class,'fraudRegister']);
Route::post('/search_fraud',[ApiController::class,'searchFraud']);
Route::post('/fruad_by_shop',[ApiController::class,'fraudsByShop']);

/*******************  Transaction  ******************************************* */

Route::post('/transaction_register',[ApiController::class,'transactionRegister']);
Route::post('/get_transaction',[ApiController::class,'getTransaction']);

/*******************  Subscription  ****************************************** */

Route::post('/subscription_register',[ApiController::class,'subscriptionRegister']);
Route::post('/get_subscription',[ApiController::class,'getsubscription']);

/******************************************************************************  */










