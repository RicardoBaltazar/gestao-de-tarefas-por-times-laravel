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

class TaskController extends Controller
{
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

            Log::channel('external')->info('Tarefas listadas com sucesso');

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