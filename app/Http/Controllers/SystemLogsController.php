<?php

namespace App\Http\Controllers;

class SystemLogsController extends Controller
{

    const MODEL = "App\SystemLog";

    use RESTActions;
    public function createLog($operation, $operationId = null, $personId, $date, $notes)
    {
        $data = array();
        $data["operation"] = $operation;
        $data["operation_id"] = $operationId;
        $data["person_id"] = $personId;
        $data["atdate"] = $date;
        $data["notes"] = $notes;
        $m = self::MODEL;
        $m::create($data->all());
    }
}
