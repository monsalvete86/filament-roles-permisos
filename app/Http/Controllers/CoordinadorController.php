<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoordinadorController extends Controller
{
    public function update(Request $request, User $coordinador)
    {
        // Validación
        $request->validate([
            'coordinados' => 'array',
            'coordinados.*' => 'exists:users,id',
        ]);

        // Sincronizar la relación con la tabla pivote
        $coordinador->digitadores()->sync($request->input('coordinados'));

        return redirect()->back()->with('success', 'Digitadores actualizados con éxito.');
    }
}
