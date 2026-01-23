<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(int $teamId): JsonResponse
    {
        try {
            $team = Team::where('id', $teamId)
                        ->where('user_id', Auth::id())
                        ->first();

            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time não encontrado ou você não tem acesso a este time.'
                ], 404);
            }

            $projects = Project::where('team_id', $teamId)->get();

            return response()->json([
                'success' => true,
                'data' => $projects,
                'message' => 'Projetos listados com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    public function store(Request $request, int $teamId): JsonResponse
    {
        try {
            $team = Team::where('id', $teamId)
                        ->where('user_id', Auth::id())
                        ->first();

            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time não encontrado ou você não tem acesso a este time.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $project = Project::create([
                'team_id' => $teamId,
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'data' => $project,
                'message' => 'Projeto criado com sucesso.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }
}