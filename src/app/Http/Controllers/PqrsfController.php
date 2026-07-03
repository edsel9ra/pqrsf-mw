<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePqrsfRequest;
use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Services\FormFieldService;

class PqrsfController extends Controller
{
    public function create()
    {
        $sedes = Sede::where('activo', true)->orderBy('nombre')->get();
        $fields = FormFieldService::activeFields();

        return view('pqrsf.form', compact('sedes', 'fields'));
    }

    public function store(StorePqrsfRequest $request)
    {
        $data = $request->normalizedData();

        PqrsfSubmission::create([
            'sede_id' => $data['sede_id'],
            'field_values' => $data,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('pqrsf.gracias');
    }

    public function gracias()
    {
        return view('pqrsf.gracias');
    }
}
