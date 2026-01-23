<?php

namespace Tests\Unit;

use App\Http\Controllers\ProjectController;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private ProjectController $controller;
    private User $user;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new ProjectController();
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        
        Auth::login($this->user);
    }

    /** @test */
    public function index_returns_team_projects_successfully()
    {
        Project::factory()->count(3)->create(['team_id' => $this->team->id]);
        
        $response = $this->controller->index($this->team->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['data']);
    }

    /** @test */
    public function index_fails_for_non_owner_team()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->controller->index($otherTeam->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Time não encontrado ou você não tem acesso a este time.', $data['message']);
    }

    /** @test */
    public function store_creates_project_successfully()
    {
        $request = new Request([
            'name' => 'Test Project'
        ]);

        $response = $this->controller->store($request, $this->team->id);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Test Project', $data['data']['name']);
        $this->assertEquals($this->team->id, $data['data']['team_id']);
    }

    /** @test */
    public function store_fails_for_non_owner_team()
    {
        $otherUser = User::factory()->create();
        $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
        $request = new Request([
            'name' => 'Hacked Project'
        ]);

        $response = $this->controller->store($request, $otherTeam->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Time não encontrado ou você não tem acesso a este time.', $data['message']);
    }
}