<?php

namespace Tests\Feature\MemberPortal;

use Tests\TestCase;

class AuthProgramTest extends TestCase
{
    /**  @test */
    public function users_can_authenticate_using_the_login()
    {
        $this->withoutExceptionHandling();

        $response = $this->postJson('/api/login', [
            'email' => config('advplus.test.program_login'),
            'password' => config('advplus.test.program_password'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'token']);
    }

    /**  @test */
    public function users_can_not_authenticate_with_invalid_password()
    {
        $response = $this->post('api/login', [
            'email' => config('advplus.test.program_login'),
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertStatus(401);
    }

    /**  @test */
    public function user_can_un_authenticate_with_token()
    {
        $response = $this->postJson('/api/login', [
            'email' => config('advplus.test.program_login'),
            'password' => config('advplus.test.program_password'),
        ]);

        $new_response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $response->json('token'),
        ])->post('/api/logout');

        $new_response->assertStatus(200);
    }

    /**  @test */
//    public function user_can_not_un_authenticate_without_token()
//    {
//        $response = $this->post('/api/logout');
//
//        $response->assertUnauthorized();
//    }
}
