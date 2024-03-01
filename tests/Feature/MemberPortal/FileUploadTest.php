<?php

namespace Tests\Feature\MemberPortal;

use App\Models\Member\Member;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadTest extends TestCase
{
    /**  @test */
    public function successful_upload_file()
    {
        $this->withoutExceptionHandling();

        $response = $this->json('post', '/api/photo/upload', [
            'file' => UploadedFile::fake()->image('random.jpg')
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'filename']);

        Storage::assertExists(Member::getFilePath('avatar') . '/original/' . $response->json('filename'));
    }
}
