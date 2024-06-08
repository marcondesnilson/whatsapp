<?php

namespace App\Http\Controllers;

use App\Models\Alimento;
use Illuminate\Http\Request;

class AlimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return Alimento::all();
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
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
            return Alimento::create([
                "nome" => $request->nome,
                "calorias" => $request->calorias,
                "proteinas" => $request->proteinas,
                "carboidratos" => $request->carboidratos,
                "gorduras" => $request->gorduras,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $alimento = Alimento::find($id);

            if (!$alimento) {
                $alimento_deletado = Alimento::withTrashed()->find($id);

                if ($alimento_deletado) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Alimento já foi deletado."
                    ]);
                } else {
                    return response()->json([
                        "status" => "error",
                        "message" => "Alimento não encontrado."
                    ]);
                }
            }

            return $alimento;
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
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
            $alimento = Alimento::find($id);

            if (!$alimento) {
                return response()->json([
                    "status" => "error",
                    "message" => "Alimento não encontrado."
                ]);
            }

            $request->nome ? $alimento->nome = $request->nome : null;
            $request->calorias ? $alimento->calorias = $request->calorias : null;
            $request->proteinas ? $alimento->proteinas = $request->proteinas : null;
            $request->carboidratos ? $alimento->carboidratos = $request->carboidratos : null;
            $request->gorduras ? $alimento->gorduras = $request->gorduras : null;

            $alimento->save();

            return response()->json([
                "status" => "success",
                "message" => "Alimento atualizado com sucesso."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $delete = Alimento::where("id", $id)->delete();

            if ($delete) {
                return response()->json([
                    "status" => "success",
                    "message" => "Alimento deletado com sucesso."
                ]);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => "Alimento não encontrado."
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}
