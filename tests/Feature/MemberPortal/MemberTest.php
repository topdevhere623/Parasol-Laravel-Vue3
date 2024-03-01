<?php

namespace Tests\Feature\MemberPortal;

use Tests\TestCase;

class MemberTest extends TestCase
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
            'Authorization' => 'Bearer '.$response->json('token'),
        ]);
    }

    /**  @test */
    public function get_members_full_list_with_paginate()
    {
        $response = $this->get('/api/members');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'result']);

        $data = $response->json('result');
        $user = [];
        if ($count = count($data['data'])) {
            $index = rand(0, $count - 1);
            $user = $data['data'][$index];
        }
        return $user;
    }

}
