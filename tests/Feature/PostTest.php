<?php

namespace Tests\Feature;

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
    }

    public function test_can_be_stored()
    {
        $this->withoutExceptionHandling();

        $file = File::create('img_ex.jpg');

        $data = $this->validParams();
        $data['image'] = $file;

        $res = $this->post('/posts', $data);

        $res->assertOk();

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);
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

        $res = $this->patch('/posts/' . $post->id, $data);

        $res->assertOk();

        $updatedPost = Post::first();
        $this->assertEquals($data['title'], $updatedPost->title);
        $this->assertEquals($data['description'], $updatedPost->description);
        $this->assertEquals('images/' . $file->hashName(), $updatedPost->image_url);

        $this->assertEquals($post->id, $updatedPost->id);

    }

    public function test_attribute_title_is_required_for_storing_post()
    {
        $data = $this->validParams();
        $data['title'] = '';

        $res = $this->post('/posts', $data);

        $res->assertRedirect();
        $res->assertInvalid('title');
    }

    public function test_attribute_image_is_file_for_storing_post()
    {
        $data = $this->validParams();

        $res = $this->post('/posts', $data);

        $res->assertRedirect();
        $res->assertInvalid('image');
    }

    public function test_response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $res = $this->get('/posts');

        $res->assertViewIs('posts.index');

        $res->assertSeeText('View page');

        $titles = $posts->pluck('title')->toArray();
        $res->assertSeeText('View page');
        $res->assertSeeText($titles);
    }

    public function test_response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $res = $this->get('/posts/' . $post->id);

        $res->assertSeeText('Show post page');
        $res->assertViewIs('posts.show');
        $res->assertSeeText($post->title);
        $res->assertSeeText($post->description);
    }

    public function test_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $post = Post::factory()->create();
        $res = $this->actingAs($user)->delete('/posts/' . $post->id);

        $res->assertOk();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_post_can_be_deleted_by_only_auth_user()
    {
        $post = Post::factory()->create();
        $res = $this->delete('/posts/' . $post->id);
        $res->assertRedirect();

        $this->assertDatabaseCount('posts', 1);
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
