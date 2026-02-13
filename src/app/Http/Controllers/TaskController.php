<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Implementar login"),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="Listar tarefas de um projeto",
     *     description="Retorna todas as tarefas pertencentes a um projeto específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID do projeto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tarefas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarefas listadas com sucesso."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Task")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Projeto não encontrado ou sem acesso",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(int $projectId): JsonResponse
    {
        try {
            $project = Project::join('teams', 'projects.team_id', '=', 'teams.id')
                ->where('projects.id', $projectId)
                ->where('teams.user_id', Auth::id())
                ->select('projects.*')
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado ou você não tem acesso a este projeto.'
                ], 404);
            }

            $tasks = Task::where('project_id', $projectId)->get();

            Log::info('Tarefas listadas com sucesso');

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Tarefas listadas com sucesso.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/projects/{projectId}/tasks",
     *     tags={"Tasks"},
     *     summary="Criar nova tarefa",
     *     description="Cria uma nova tarefa para um projeto específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID do projeto onde a tarefa será criada",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Implementar login", minLength=3, maxLength=255),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="pending", description="Status inicial da tarefa (opcional, padrão: pending)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarefa criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarefa criada com sucesso."),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Projeto não encontrado ou sem acesso",
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
    public function store(Request $request, int $projectId): JsonResponse
    {
        try {
            $project = Project::join('teams', 'projects.team_id', '=', 'teams.id')
                ->where('projects.id', $projectId)
                ->where('teams.user_id', Auth::id())
                ->select('projects.*')
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projeto não encontrado ou você não tem acesso a este projeto.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'status' => ['nullable', 'string', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::create([
                'project_id' => $projectId,
                'name' => $request->name,
                'status' => $request->status ?? 'pending',
            ]);

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Tarefa criada com sucesso.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{projectId}/tasks/{taskId}",
     *     tags={"Tasks"},
     *     summary="Atualizar tarefa",
     *     description="Atualiza nome e/ou status de uma tarefa específica",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID do projeto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID da tarefa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Implementar autenticação", minLength=3, maxLength=255, description="Nome da tarefa (opcional)"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="in_progress", description="Status da tarefa (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarefa atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarefa atualizada com sucesso."),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarefa não encontrada ou sem acesso",
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
    public function update(Request $request, int $projectId, int $taskId): JsonResponse
    {
        try {
            $task = Task::join('projects', 'tasks.project_id', '=', 'projects.id')
                ->join('teams', 'projects.team_id', '=', 'teams.id')
                ->where('tasks.id', $taskId)
                ->where('projects.id', $projectId)
                ->where('teams.user_id', Auth::id())
                ->select('tasks.*')
                ->first();

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada ou você não tem acesso a esta tarefa.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|min:3|max:255',
                'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'in_progress', 'completed', 'cancelled '])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task->update($request->only(['name', 'status']));

            return response()->json([
                'success' => true,
                'data' => $task->fresh(),
                'message' => 'Tarefa atualizada com sucesso.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/projects/{projectId}/tasks/{taskId}/status",
     *     tags={"Tasks"},
     *     summary="Atualizar apenas o status da tarefa",
     *     description="Atualiza especificamente apenas o status de uma tarefa",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         description="ID do projeto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         description="ID da tarefa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status da tarefa atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status da tarefa atualizado com sucesso."),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tarefa não encontrada ou sem acesso",
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
    public function updateStatus(Request $request, int $projectId, int $taskId): JsonResponse
    {
        try {
            $task = Task::join('projects', 'tasks.project_id', '=', 'projects.id')
                ->join('teams', 'projects.team_id', '=', 'teams.id')
                ->where('tasks.id', $taskId)
                ->where('projects.id', $projectId)
                ->where('teams.user_id', Auth::id())
                ->select('tasks.*')
                ->first();

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarefa não encontrada ou você não tem acesso a esta tarefa.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => ['required', 'string', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'data' => $task->fresh(),
                'message' => 'Status da tarefa atualizado com sucesso.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }
}
