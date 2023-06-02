<?php

namespace Tests\Feature\Api;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->withHeaders([
           'accept' => 'application/Json'
        ]);
    }

    public function test_can_be_stored()
    {
        $this->withoutExceptionHandling();

        $file = File::create('img_ex.jpg');

        $data = $this->validParams();
        $data['image'] = $file;

        $res = $this->post('/api/posts', $data);

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);

        $res->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image_url' => $post->image_url,
            'created_at' => $post->created_at->format('Y-m-d'),
            'updated_at' => $post->updated_at->format('Y-m-d'),
        ]);
    }

    public function test_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $file = File::create('img_ex.jpg');

        $data = $this->validParams();
        $data['image'] = $file;
        $data['description'] = 'description changed';
        $data['title'] = 'changed';

        $res = $this->patch('/api/posts/' . $post->id, $data);

        $res->assertJson([
            'id' => $post->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'image_url' => 'images/' . $file->hashName(),
        ]);
    }

    public function test_attribute_title_is_required_for_storing_post()
    {
        $data = $this->validParams();
        $data['title'] = '';

        $res = $this->post('/api/posts', $data);

        $res->assertStatus(422);
        $res->assertInvalid('title');
    }

    public function test_attribute_image_is_file_for_storing_post()
    {
        $data = $this->validParams();

        $res = $this->post('/posts', $data);

        $res->assertStatus(422);
        $res->assertInvalid('image');
        $res->assertJsonValidationErrors([
           'image' => 'The image field must be a file.'
        ]);
    }


    /**
     * Valid params for updating or creating a resource
     *
     * @param array $overrides new params
     * @return array Valid params for updating or creating a resource
     */
    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'hello world',
            'description' => "I'm a content",
            'image' => 'img_ex.jpg'
        ], $overrides);
    }
}
