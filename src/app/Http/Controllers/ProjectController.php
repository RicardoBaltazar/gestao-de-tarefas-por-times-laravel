<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Projeto Mobile App"),
 *     @OA\Property(property="team_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/teams/{teamId}/projects",
     *     tags={"Projects"},
     *     summary="Listar projetos de um time",
     *     description="Retorna todos os projetos pertencentes a um time específico do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="teamId",
     *         in="path",
     *         required=true,
     *         description="ID do time",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de projetos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Projetos listados com sucesso."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Project")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Time não encontrado ou sem acesso",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/teams/{teamId}/projects",
     *     tags={"Projects"},
     *     summary="Criar novo projeto",
     *     description="Cria um novo projeto para um time específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="teamId",
     *         in="path",
     *         required=true,
     *         description="ID do time onde o projeto será criado",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Projeto Mobile App", minLength=3, maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Projeto criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Projeto criado com sucesso."),
     *             @OA\Property(property="data", ref="#/components/schemas/Project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Time não encontrado ou sem acesso",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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
