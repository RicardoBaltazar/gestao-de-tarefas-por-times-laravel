<?php

namespace Tests\Unit;

use App\Http\Controllers\TaskController;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private TaskController $controller;
    private User $user;
    private Team $team;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new TaskController();
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->project = Project::factory()->create(['team_id' => $this->team->id]);
        
        Auth::login($this->user);
    }

    /** @test */
    public function index_returns_project_tasks_successfully()
    {
        Task::factory()->count(3)->create(['project_id' => $this->project->id]);
        
        $response = $this->controller->index($this->project->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['data']);
    }

    /** @test */
    public function index_fails_for_non_owner_project()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create(['team_id' => $otherTeam->id]);
        
        $response = $this->controller->index($otherProject->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Projeto não encontrado ou você não tem acesso a este projeto.', $data['message']);
    }

    /** @test */
    public function store_creates_task_successfully()
    {
        $request = new Request([
            'name' => 'Test Task',
            'status' => 'pending'
        ]);

        $response = $this->controller->store($request, $this->project->id);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Test Task', $data['data']['name']);
        $this->assertEquals('pending', $data['data']['status']);
        $this->assertEquals($this->project->id, $data['data']['project_id']);
    }

    /** @test */
    public function store_fails_for_non_owner_project()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create(['team_id' => $otherTeam->id]);
        $request = new Request([
            'name' => 'Hacked Task'
        ]);

        $response = $this->controller->store($request, $otherProject->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Projeto não encontrado ou você não tem acesso a este projeto.', $data['message']);
    }

    /** @test */
    public function update_modifies_task_successfully()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);
        $request = new Request([
            'name' => 'Updated Task Name',
            'status' => 'in_progress'
        ]);

        $response = $this->controller->update($request, $this->project->id, $task->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Task Name', $data['data']['name']);
        $this->assertEquals('in_progress', $data['data']['status']);
    }

    /** @test */
    public function update_fails_for_non_owner_task()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create(['team_id' => $otherTeam->id]);
        $otherTask = Task::factory()->create(['project_id' => $otherProject->id]);
        $request = new Request([
            'name' => 'Hacked Update'
        ]);

        $response = $this->controller->update($request, $otherProject->id, $otherTask->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Tarefa não encontrada ou você não tem acesso a esta tarefa.', $data['message']);
    }

    /** @test */
    public function update_status_modifies_task_status_successfully()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);
        $request = new Request([
            'status' => 'completed'
        ]);

        $response = $this->controller->updateStatus($request, $this->project->id, $task->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('completed', $data['data']['status']);
    }

    /** @test */
    public function update_status_fails_for_non_owner_task()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        $otherProject = Project::factory()->create(['team_id' => $otherTeam->id]);
        $otherTask = Task::factory()->create(['project_id' => $otherProject->id]);
        $request = new Request([
            'status' => 'completed'
        ]);

        $response = $this->controller->updateStatus($request, $otherProject->id, $otherTask->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Tarefa não encontrada ou você não tem acesso a esta tarefa.', $data['message']);
    }
}