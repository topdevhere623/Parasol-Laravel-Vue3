<?php

namespace Tests\Feature\MemberPortal;

use App\Models\Member\Member;
use App\Models\Payments\Payment;
use App\Models\Program;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $response = $this->postJson('/api/login', [
            'email'    => config('advplus.test.program_login'),
            'password' => config('advplus.test.program_password'),
        ]);
        $this->withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $response->json('token'),
        ]);
    }

    /**  @test */
    public function store_member_with_correct_data(): array
    {
        $this->withoutExceptionHandling();
        $data = $this->getStoreData();
        $response = $this->post('/api/members', $data);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'id']);
        $this->assertEquals($response->json('id') >= 0, true);
        return ['id' => $response->json('id'), 'data' => $data];
    }

    /**
     * @test
     * @depends store_member_with_correct_data
     */
    public function can_show_correct_data_by_id_after_create(array $array): array
    {
        $this->can_show_correct_data_by_id($array);
        return $array;
    }

    /**
     * @test
     * @depends can_show_correct_data_by_id_after_create
     */
    public function can_update_user_data(array $array): array
    {
        $newArray = $array;
        $newArray['data']['last_name'] = $this->faker->lastName;
        $newArray['data']['partner']['last_name'] = $this->faker->lastName;
        $response = $this->patch('/api/members/' . $array['id'], $newArray['data']);
        $response->assertStatus(200);
        $this->assertEquals($response->json('status'), 'success');
        return $newArray;
    }

    /**
     * @test
     * @depends can_update_user_data
     */
    public function can_show_correct_data_by_id_after_update(array $array)
    {
        $this->can_show_correct_data_by_id($array);
        return $array;
    }

    /**
     * @test
     * @depends can_show_correct_data_by_id_after_update
     */
    public function can_correct_delete_member(array $array)
    {
        $response = $this->delete('/api/members/' . $array['id']);
        $response->assertStatus(200);
        $this->assertEquals($response->json('status'), 'success');
        return $array;
    }

    /**
     * @test
     * @depends can_correct_delete_member
     */
    public function check_member_by_id_after_deleted(array $array)
    {
        $response = $this->get('/api/members/' . $array['id']);
        $response->assertStatus(200);
        $this->assertEquals($response->json('status'), 'error');
    }

    protected function can_show_correct_data_by_id(array $array)
    {
        $response = $this->get('/api/members/' . $array['id']);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'user']);
        $user = $response->json('user');
        $partner = $response->json('partner');
        $this->assertEquals($array['id'], $user['id']);
        $this->assertEquals(strtolower($array['data']['first_name']), strtolower($user['first_name']));
        $this->assertEquals(strtolower($array['data']['last_name']), strtolower($user['last_name']));
        $this->assertEquals($array['data']['email'], $user['email']);
        $this->assertEquals($array['data']['phone'], $user['phone']);
        $this->assertEquals($array['data']['photo'], $user['photo']);
        $this->assertEquals(strtolower($array['data']['partner']['first_name']), strtolower($partner['first_name']));
        $this->assertEquals(strtolower($array['data']['partner']['last_name']), strtolower($partner['last_name']));
        $this->assertEquals($array['data']['partner']['email'], $partner['email']);
        $this->assertEquals($array['data']['partner']['phone'], $partner['phone']);
        $this->assertEquals($array['data']['partner']['photo'], $partner['photo']);
    }

    protected function getStoreData(): array
    {
        $price = ceil($this->faker->numberBetween($min = 1500, $max = 6000) / 100) * 100;
        $user = [
            'first_name'  => $this->faker->firstName,
            'last_name'   => $this->faker->lastName,
            'phone'       => $this->faker->phoneNumber,
            'photo'       => $this->faker->word(),
            'email'       => $this->faker->unique()->safeEmail,
            'start_date'  => $this->faker->date('Y-m-d'),
            'subtotal'    => $price,
            'total_price' => $price,
            'tax'         => $price * Payment::VAT,
        ];
        $partner = [
            'partner' =>
                [
                    'photo'      => $this->faker->word(),
                    'first_name' => $this->faker->firstName,
                    'last_name'  => $this->faker->lastName,
                    'email'      => $this->faker->unique()->safeEmail,
                    'phone'      => $this->faker->phoneNumber,
                ],
        ];
        $kids = [
            'kids' => [
                [
                    'first_name' => $this->faker->firstName,
                    'last_name'  => $this->faker->lastName,
                    'birthday'   => $this->faker->date('Y-m-d'),
                ],
                [
                    'first_name' => $this->faker->firstName,
                    'last_name'  => $this->faker->lastName,
                    'birthday'   => $this->faker->date('Y-m-d'),
                ],
            ],
        ];
        $junior = [
            'junior' => [
                [
                    'first_name' => $this->faker->firstName,
                    'last_name'  => $this->faker->lastName,
                    'email'      => $this->faker->unique()->safeEmail,
                    'phone'      => $this->faker->phoneNumber,
                    'birthday'   => $this->faker->date('Y-m-d'),
                    'photo'      => $this->faker->word(),
                ],
                [
                    'first_name' => $this->faker->firstName,
                    'last_name'  => $this->faker->lastName,
                    'email'      => $this->faker->unique()->safeEmail,
                    'phone'      => $this->faker->phoneNumber,
                    'birthday'   => $this->faker->date('Y-m-d'),
                    'photo'      => $this->faker->word(),
                ],
            ],
        ];
        $clubs = ['clubs' => \DB::table('clubs')->limit(rand(1, 5))->get()->pluck('id')];
        $billing = [
            'billing' =>
                [
                    'first_name'   => $this->faker->firstName,
                    'last_name'    => $this->faker->lastName,
                    'company_name' => $this->faker->company,
                    'country'      => 'United Arab Emirates',
                    'city'         => 'Dubai',
                    'street'       => $this->faker->streetName,
                ],
        ];
        return array_merge($user, $partner, $kids, $junior, $clubs, $billing);
    }
}
