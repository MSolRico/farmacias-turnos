<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmacia;
use Illuminate\Http\Request;

class FarmaciaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('buscar');
        $ciudadId = $request->input('ciudad');

        $farmacias = Farmacia::with('ciudad')
            ->when($busqueda, function ($query, $busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('direccion', 'like', "%{$busqueda}%");
                });
            })
            ->when($ciudadId, function ($query, $ciudadId) {
                $query->where('id_ciudad', $ciudadId);
            })
            ->orderBy('nombre')
            ->get();

        $ciudades = \App\Models\Ciudad::orderBy('nombre_ciudad')->get();

        return view('admin.farmacias.index', compact(
            'farmacias',
            'busqueda',
            'ciudades',
            'ciudadId'
        ));
    }

    public function edit(Farmacia $farmacia)
    {
        $farmacia->load('ciudad');

        return view('admin.farmacias.edit', compact('farmacia'));
    }

    public function update(Request $request, Farmacia $farmacia)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $farmacia->update($datos);

        return redirect()
            ->route('admin.farmacias.index')
            ->with('success', 'Farmacia actualizada correctamente.');
    }
}
