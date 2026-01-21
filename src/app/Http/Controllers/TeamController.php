<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    /**
     * Display a listing of the authenticated user's teams.
     */
    public function index(): JsonResponse
    {
        $teams = Team::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|min:3',
            ]);

            $team = Team::create([
                'name' => $validated['name'],
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time criado com sucesso',
                'data' => $team
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Display the specified team.
     */
    public function show($id): JsonResponse
    {
        $team = Team::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Time não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $team
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $team = Team::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Time não encontrado'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|min:3',
            ]);

            $team->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Time atualizado com sucesso',
                'data' => $team
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified team.
     */
    public function destroy($id): JsonResponse
    {
        $team = Team::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Time não encontrado'
            ], 404);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time removido com sucesso'
        ]);
    }
}