<?php

namespace Tests\Feature\MemberPortal;

use Tests\TestCase;

class ClubTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $response = $this->postJson('/api/login', [
            'email' => config('advplus.test.program_login'),
            'password' => config('advplus.test.program_password'),
        ]);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);
    }

    /**  @test */
    public function get_clubs_list()
    {
        $response = $this->get('/api/club/list');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'result']);

        $data = $response->json('result');

        $dataIds = array_column($data, 'id');

        return count($dataIds) ? $dataIds[array_rand($dataIds)] : '';
    }

    /**
     * @test
     * @depends get_clubs_list
     */
    public function get_correct_club_data_by_id(int $id)
    {
        $response = $this->get('/api/club/list/' . $id);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'result']);

        $data = $response->json('result');

        $this->assertEquals($data['id'], $id);
    }
}
