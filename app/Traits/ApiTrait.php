<?php

    namespace App\Traits;

    trait ApiTrait{
        protected function apiResponse($status, $data){
            return Response()->json([
                'status' => $status,
                'data' => $data
            ]);
        }
    }
