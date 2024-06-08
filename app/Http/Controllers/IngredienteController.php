<?php

namespace App\Http\Controllers;

use App\Models\Ingrediente;
use Illuminate\Http\Request;

class IngredienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $ingredientes = Ingrediente::with('alimento')->get();

            $tabelafinal = array(
                'calorias' => 0,
                'proteinas' => 0,
                'carboidratos' => 0,
                'gorduras' => 0,
            );
            

            $ingredientes = $ingredientes->map(function ($ingrediente) use (&$tabelafinal){

                $tabelafinal['calorias'] += $ingrediente->alimento->calorias/1000 * $ingrediente->quantidade;
                $tabelafinal['proteinas'] += $ingrediente->alimento->proteinas/1000 * $ingrediente->quantidade;
                $tabelafinal['carboidratos'] += $ingrediente->alimento->carboidratos/1000 * $ingrediente->quantidade;
                $tabelafinal['gorduras'] += $ingrediente->alimento->gorduras/1000 * $ingrediente->quantidade;

                return [
                    'id' => $ingrediente->id,
                    'alimento' => $ingrediente->alimento->nome,
                    'quantidade' => $ingrediente->quantidade,
                    'calorias' => $ingrediente->alimento->calorias/1000 * $ingrediente->quantidade,
                    'proteinas' => $ingrediente->alimento->proteinas/1000 * $ingrediente->quantidade,
                    'carboidratos' => $ingrediente->alimento->carboidratos/1000 * $ingrediente->quantidade,
                    'gorduras' => $ingrediente->alimento->gorduras/1000 * $ingrediente->quantidade,
                ];
            });

            return [
                'ingredientes' => $ingredientes,
                'tabelafinal' => $tabelafinal,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
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
    public function store(Request $request)
    {
        try {
            return Ingrediente::create([
                'alimento_id' => $request->alimento_id,
                'quantidade' => $request->quantidade,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $ingrediente = Ingrediente::where('id', $id)->with('alimento')->first();

            if (!$ingrediente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingrediente não encontrado.',
                ]);
            }
            
            return [
                'id' => $ingrediente->id,
                'alimento' => $ingrediente->alimento->nome,
                'quantidade' => $ingrediente->quantidade,
                'calorias' => $ingrediente->alimento->calorias/1000 * $ingrediente->quantidade,
                'proteinas' => $ingrediente->alimento->proteinas/1000 * $ingrediente->quantidade,
                'carboidratos' => $ingrediente->alimento->carboidratos/1000 * $ingrediente->quantidade,
                'gorduras' => $ingrediente->alimento->gorduras/1000 * $ingrediente->quantidade,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
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
        try {
            $ingrediente = Ingrediente::find($id);

            if (!$ingrediente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingrediente não encontrado.',
                ]);
            }

            $ingrediente->update([
                'alimento_id' => $request->alimento_id,
                'quantidade' => $request->quantidade,
            ]);

            return $ingrediente;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $ingrediente = Ingrediente::find($id);

            if (!$ingrediente) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingrediente não encontrado.',
                ]);
            }

            $ingrediente->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ingrediente deletado com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
