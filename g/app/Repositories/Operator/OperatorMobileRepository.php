<?php
namespace App\Repositories\Operator;

use App\Models\OperatorMobile;

class OperatorMobileRepository
{
    protected $model;
    public function __construct(OperatorMobile $model)
    {
        $this->model = $model;
    }

    public function getAllOperator()
    {
        return response()->json(["operatorMobile" => $this->model->all()],200);
    }

    public function createOperator(array $data)
    {
        $fileName = time() . '_' . $data["logo_url"]->getClientOriginalName();
        $filePath = $data["logo_url"]->storeAs('operator', $fileName, 'public');
        $this->model->create([
            "label"=> $data["label"],
            "logo_url"=> $filePath,
        ]);
    }
}