<?php

namespace Tests\Unit;

use App\Http\Controllers\TeamController;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private TeamController $controller;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new TeamController();
        $this->user = User::factory()->create();
        
        Auth::login($this->user);
    }

    /** @test */
    public function index_returns_user_teams_successfully()
    {
        Team::factory()->count(3)->create(['user_id' => $this->user->id]);
        Team::factory()->count(2)->create();

        $response = $this->controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['data']);
    }

    /** @test */
    public function store_creates_team_successfully()
    {
        $request = new Request([
            'name' => 'Test Team'
        ]);

        $response = $this->controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Test Team', $data['data']['name']);
        $this->assertEquals($this->user->id, $data['data']['user_id']);
    }

    /** @test */
    public function store_fails_with_invalid_data()
    {
        $request = new Request([
            'name' => 'ab'
        ]);

        $response = $this->controller->store($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function show_returns_team_successfully()
    {
        $team = Team::factory()->create(['user_id' => $this->user->id]);

        $response = $this->controller->show($team->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($team->id, $data['data']['id']);
    }

    /** @test */
    public function show_fails_for_non_owner()
    {
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->controller->show($team->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    /** @test */
    public function update_modifies_team_successfully()
    {
        $team = Team::factory()->create(['user_id' => $this->user->id]);
        $request = new Request(['name' => 'Updated Name']);

        $response = $this->controller->update($request, $team->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Name', $data['data']['name']);
    }

    /** @test */
    public function update_fails_for_non_owner()
    {
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $otherUser->id]);
        $request = new Request(['name' => 'Hacked Name']);

        $response = $this->controller->update($request, $team->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    /** @test */
    public function destroy_removes_team_successfully()
    {
        $team = Team::factory()->create(['user_id' => $this->user->id]);

        $response = $this->controller->destroy($team->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    /** @test */
    public function destroy_fails_for_non_owner()
    {
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->controller->destroy($team->id);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }
}