<?php

namespace App\Http\Controllers\MoovOperation;

use App\Http\Controllers\Controller;
use App\Services\Moov\TransfertMoovService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
// /**
//  * @OA\Info(
//  *     title="Webcoom API docs",
//  *     version="1.0",
//  *     description="Webcoom service for sending money to all"
//  * )
//  */



class TransfertController extends Controller
{
    protected $tr;

    /**
     * @OA\Tag(
     *     name="Transfert Moov",
     *     description="Operations related to Moov money transfer"
     * )
     */
    public function __construct(TransfertMoovService $tr)
    {
        $this->tr = $tr;
    }

    /**
     * @OA\Post(
     *     path="/api/cashout",
     *     summary="Send money to a friend",
     *     tags={"Transfert Moov"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"msisdnThatReceive", "amount", "referenceid"},
     *             @OA\Property(property="msisdnThatReceive", type="string",example="63882818", description="Recipient's identifier"),
     *             @OA\Property(property="amount", type="string", example="100",description="Amount to send"),
     *             @OA\Property(property="referenceid", type="string", example="10000",description="Reference ID for the transfer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Money sent successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="The provided parameters are invalid or missing."))
     * 
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string",example="Internal server error."))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function sendMonneyOfFriend(Request $request)
    {
       if ($request->msisdnThatReceive ) {
        # code...
       } else {
         try {
        $result = $this->tr->transfertFlooz($request->msisdnThatReceive, $request->amount, $request->referenceid);

        // Check if the result is not null or not what you expect
        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'Transfert failled'], 404);
        }

        // If everything is fine, return the success response
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Exception $e) {
        // Handle any exception that may occur and return an error response
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
       }

    }

    /**
     * @OA\Post(
     *     path="/api/get-balance",
     *     summary="Get user's mobile balance",
     *     tags={"Transfert Moov"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"msisdn"},
     *             @OA\Property(property="msisdn", type="string", description="User's mobile number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User's mobile balance retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
   public function getUserMobileBalance(Request $request)
{
    // Attempt to get the balance and store in $result
    try {
        $result = $this->tr->getBalance($request->msisdn);

        // Check if the result is not null or not what you expect
        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'Balance not found'], 404);
        }

        // If everything is fine, return the success response
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Exception $e) {
        // Handle any exception that may occur and return an error response
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * @OA\Post(
     *     path="/api/mobile-status",
     *     summary="Get user's mobile status",
     *     tags={"Transfert Moov"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(property="mobile", type="string", description="User's mobile number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User's mobile status retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function getUserMobilestatus(Request $request)
    {
        
        try {
        $result = $this->tr->getMobileStatus($request->msisdn);

        // Check if the result is not null or not what you expect
        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'Balance not found'], 404);
        }

        // If everything is fine, return the success response
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Exception $e) {
        // Handle any exception that may occur and return an error response
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
        
    }

        /**
     * @OA\Post(
     *     path="/api/cash-in-transaction",
     *     summary="Withdraw by ussd without pendding",
     *     tags={"Withdraw Moov"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"msisdn", "amount"},
     *             @OA\Property(property="msisdn", type="string", description="The number on which the withdrawal is to be made"),
     *             @OA\Property(property="amount", type="number", description="Amount to Withdraw"),
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Withdraw  with success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */

    public function getCashByFlooz(Request $request)
    {
    // Attempt to get the balance and store in $result
    try {
        $result = $this->tr->getCashByFlooz($request->msisdn , $request->amount);

        // Check if the result is not null or not what you expect
        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'transaction failled'], 404);
        }

        // If everything is fine, return the success response
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Exception $e) {
        // Handle any exception that may occur and return an error response
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);}}



        /**
     * @OA\Post(
     *     path="/api/get-cash-by-push",
     *     summary="Withdraw by ussd with pendding",
     *     tags={"Withdraw Moov"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"msisdn", "amount"},
     *             @OA\Property(property="msisdn", type="string", description="The number on which the withdrawal is to be made"),
     *             @OA\Property(property="amount", type="number", description="Amount to Withdraw"),
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Withdraw  with success"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function pushTransaction(Request $request)
    {
    // Attempt to get the balance and store in $result
    try {
        $result = $this->tr->pushTransaction($request->msisdn , $request->amount ,$request->message ,$request->referenceId  );

        // Check if the result is not null or not what you expect
        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'transaction failled'], 404);
        }

        // If everything is fine, return the success response
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Exception $e) {
        // Handle any exception that may occur and return an error response
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);}}
    
}
