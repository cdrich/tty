<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MoovOperation\TransfertController;
use App\Http\Controllers\MtnOperation\DepositOperation;
use App\Http\Controllers\OperatorMobileController;
use App\Http\Controllers\TransactionNumberController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\CodeChekController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Operation\TransactionController;
use App\Http\Controllers\MoovToMoovTransactionControlleur;
use App\Http\Controllers\MoovToMtnTransactionControlleur;
use App\Http\Controllers\MtnToMtnTransactionControlleur;
use App\Http\Controllers\MtnToMoovTransactionControlleur;
use App\Services\Mtn\GenerateMtnToken;
use App\Services\Mtn\AuthMtnService;
use App\Services\Mtn\TransactionMtnService;

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

Route::group(
    ["prefix" => "auth", "middleware" => ["api"]],
    function ($router) {
        Route::post("/login", [AuthController::class, 'login'])->name('login');
        Route::post("/register", [AuthController::class, 'register'])->name('register');
        Route::post("/logout", [AuthController::class, 'logout']);
        Route::post("/refresh", [AuthController::class, 'refresh']);
        Route::post("/verify-credential-code", [AuthController::class, 'verifyCredentialCode'])->name('verify');
        Route::get("/user-profile", [AuthController::class, 'userProfile']);
        Route::post('/change-password', [AuthController::class, 'updatePassword']);
        Route::post("/verifyCode", [AuthController::class, 'verifyCode']);
    }
);
Route::post('/withdraw', 'App\Http\Controllers\WithdrawalController@withdraw');
/** All endpoint */
Route::group(['prefix' => '', 'middleware' => ['api']], function ($router) {
    Route::get('/all-transaction-numbers', [TransactionNumberController::class, 'index']);
    Route::post('/add-transaction-number', [TransactionNumberController::class, 'store']);
    Route::patch('/update-transaction-number/{id}', [TransactionNumberController::class, 'update']);
    Route::post('/delete-transaction-number/{id}', [TransactionNumberController::class, 'destroy']);

    Route::get('/all-operator-mobiles', [OperatorMobileController::class, 'index']);
    Route::post('/authorize-transaction-number', [TransactionNumberController::class, 'generateCodeAfterSave']);

    // Transaction controller
    Route::post('/create-deposit', [TransactionController::class, 'createDeposite']);
    Route::get('/get-user-transaction-history', [TransactionController::class, 'getUserTransactionHistorique']);
});
// MOOV Routes 
Route::post('/add-operator-mobile', [OperatorMobileController::class, 'store']);
Route::post('/cashout', [TransfertController::class, 'sendMonneyOfFriend']);
Route::post('/get-balance', [TransfertController::class, 'getUserMobileBalance']);
Route::post('/mobile-status', [TransfertController::class, 'getUserMobilestatus']);
Route::post('/cash-in-transaction', [TransfertController::class, 'getCashByFlooz']);
Route::post('/get-cash-by-push', [TransfertController::class, 'pushTransaction']);
Route::post('/push-cash-moov-to-moov', [MoovToMoovTransactionControlleur::class, 'performTransaction']);
Route::post('/push-cash-moov-to-mtn', [MoovToMtnTransactionControlleur::class, 'performTransaction']);


// MTN Routes 
Route::post('/mtn-deposit', [DepositOperation::class, 'makeRequestToPay']);
Route::post('/token', [GenerateMtnToken::class, 'getApiToken']);
Route::get('/token', [AuthMtnService::class, 'getMtnBarrearTokenForDisbursement']);

Route::post('/request-to-pay', [TransactionMtnService::class, 'deposit']);
Route::post('/get-account-balance', [TransactionMtnService::class, 'getAccountBallance']);
Route::post('/get-user-basic-infos', [TransactionMtnService::class, 'getPaymentStatus']);
// Route::post('/get-user-basic-infos', [TransactionMtnService::class, 'getUserBasicInfo']);
Route::post('/push-cash-mtn-to-mtn', [MtnToMtnTransactionControlleur::class, 'performTransaction']);
Route::post('/push-cash-mtn-to-mtn', [MtnToMoovTransactionControlleur::class, 'performTransaction']);




//reset password
Route::post('password-forgot',  [ForgotPasswordController::class, 'forgotPassword']);
Route::post('password-code-check', [CodeChekController::class, 'checkCode']);
Route::post('password-reset', [ResetPasswordController::class, 'resetPassword']);
// 4r