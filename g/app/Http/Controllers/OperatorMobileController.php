<?php

namespace App\Http\Controllers;

use App\Repositories\Operator\OperatorMobileRepository;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Request;
use Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Operator Mobile",
 *     description="Menage Mobile Operator"
 * )
 */
class OperatorMobileController extends Controller
{
    protected $operator;

    public function __construct(OperatorMobileRepository $operator)
    {
        $this->middleware("auth:api");
        $this->operator = $operator;
    }
    /**
     * Display a listing of the resource.
     */

     /**
     * @OA\Get(
     *     path="/api/all-operator-mobiles",
     *     summary="Liste de tous les opérateurs mobiles",
     *     tags={"Operator Mobile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des opérateurs mobiles récupérée avec succès",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, utilisateur non connecté",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
     *     )
     * )
     */
    public function index()
    {
        return $this->operator->getAllOperator();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/add-operator-mobile",
     *     summary="Ajouter un nouvel opérateur mobile",
     *     tags={"Operator Mobile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", description="Label de l'opérateur mobile"),
     *             @OA\Property(property="logo_url", type="string", description="URL du logo de l'opérateur mobile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opérateur mobile enregistré avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", description="Message de succès"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête incorrecte, des erreurs de validation sont présentes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", description="Liste des erreurs de validation"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, utilisateur non connecté",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "label"=> "required|string",
            "logo_url"=> "required",
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->all();
            return response()->json(['errors' => $errors], 400);
        }
        
        $this->operator->createOperator($request->all());
        return response()->json(["message"=> "Operateur mobile enregistré avec succès."],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
