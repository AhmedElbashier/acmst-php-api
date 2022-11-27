<?php

namespace App\Http\Controllers;

use App\SystemLog;

class SystemLogs
{

    public function createLog($operation, $operationId = null, $personId, $date, $notes)
    {
        $data = array();
        $data["operation"] = $operation;
        $data["operation_id"] = $operationId;
        $data["person_id"] = $personId;
        $data["atdate"] = $date;
        $data["notes"] = $notes;
        SystemLog::create($data);
    }
}
