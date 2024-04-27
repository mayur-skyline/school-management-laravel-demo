<?php
namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

abstract class ActionPlanIndexResponse implements Responsable
{
    // public function __construct()
    // {

    // }

    // public function toResponse()
    // {
    //     return response()->json($this->process());
    // }

    public function process()
    {
        return [
            'title' => 'okay'
        ];
    }
}
