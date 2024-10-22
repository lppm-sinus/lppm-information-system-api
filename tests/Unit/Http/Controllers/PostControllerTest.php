<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\PostController;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $postController;
    protected $user;
    protected $user_query;
    protected $page;
    protected $category;
    protected $media_category;
    protected $journal_category;
    protected $file_category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postController = new PostController();

        $this->refreshDatabase();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, CategorySeeder::class, PageSeeder::class]);

        $this->user = User::first();
        $this->user_query = User::query();
        $this->page = Page::first();
        $this->category = Category::first();
        $this->media_category = Category::where('slug', 'media')->first();
        $this->journal_category = Category::where('slug', 'journal')->first();
        $this->file_category = Category::where('slug', 'file')->first();

        // Ensure we have the necessary data
        if (!$this->user || !$this->page || !$this->category || !$this->media_category || !$this->journal_category || !$this->file_category) {
            $this->fail('Required seeded data is missing. Check your seeders.');
        }
    }

    public function testCreatePostWithValidData()
    {
        Storage::fake('public');

        $this->actingAs($this->user);

        $response = $this->post('/api/posts/', [
            'title' => 'Test Post',
            'container' => 'Test Container',
            'page_id' => $this->page->id,
            'category_id' => $this->category->id,
            'status' => 'published'
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'container' => 'Test Container',
            'author_id' => $this->user->id,
            'page_id' => $this->page->id,
            'category_id' => $this->category->id,
            'status' => 'published'
        ]);
    }

    public function testCreatePostWithInvalidData()
    {
        $this->actingAs($this->user);

        $response = $this->post('/api/posts', [
            'title' => 'Te',
            'container' => '',
            'page_id' => 999,
            'category_id' => 999,
            'status' => ''
        ]);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = $response->getData(true);

        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('title', $responseData['errors']);
        $this->assertArrayHasKey('container', $responseData['errors']);
        $this->assertArrayHasKey('page_id', $responseData['errors']);
        $this->assertArrayHasKey('category_id', $responseData['errors']);
        $this->assertArrayHasKey('status', $responseData['errors']);

    }

    public function testCreateMediaPostWithoutImage()
    {
        $this->actingAs($this->user);

        $response = $this->post('/api/posts', [
            'title' => 'Media Post',
            'container' => 'Media Container',
            'page_id' => $this->page->id,
            'category_id' => $this->media_category->id,
            'status' => 'published'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Image required for media category.', $response->getData(true)['message']);
    }

    public function testCreateJournalPostWithoutLink()
    {

        $this->actingAs($this->user);

        $response = $this->post('/api/posts/', [
            'title' => 'Journal Post',
            'container' => 'Journal Container',
            'page_id' => $this->page->id,
            'category_id' => $this->journal_category->id,
            'status' => 'published'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Link required for journal category.', $response->getData(true)['message']);
    }

    public function testCreateFilePostWithoutFile()
    {
        $this->actingAs($this->user);

        $response = $this->post('/api/posts/', [
            'title' => 'File Post',
            'container' => 'File Container',
            'page_id' => $this->page->id,
            'category_id' => $this->file_category->id,
            'status' => 'published'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('File required for file category.', $response->getData(true)['message']);
    }

    public function testCreatePostWithImage()
    {
        Storage::fake('public');

        $this->actingAs($this->user);

        $image = UploadedFile::fake()->image('test.jpg');

        $response = $this->post('/api/posts', [
            'title' => 'Image Post',
            'container' => 'Image Container',
            'page_id' => $this->page->id,
            'category_id' => $this->media_category->id,
            'status' => 'published',
            'image' => $image
        ]);

        if ($response->getStatusCode() !== 201) {
            dd($response->getContent());
        }
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotNull($response->getData(true)['data']['image_url']);
        Storage::disk('public')->assertExists($response->getData(true)['data']['image_url']);
    }

    public function testCreatePostWithFile()
    {
        Storage::fake('public');

        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('journal.pdf', 100);

        $response = $this->post('/api/posts', [
            'title' => 'File Post',
            'page_id' => $this->page->id,
            'category_id' => $this->file_category->id,
            'status' => 'published',
            'file' => $file
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertNotNull($response->getData(true)['data']['file_url']);
        Storage::disk('public')->assertExists($response->getData(true)['data']['file_url']);
    }

    public function testGetPostsForSuperAdmin()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $category = $this->category;
        $mediaCategory = $this->media_category;

        Post::factory()->count(10)->create([
            'category_id' => $category->id
        ]);

        Post::factory()->count(5)->create([
            'category_id' => $mediaCategory->id
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'container',
                            'category_id',
                            'category_slug',
                            'page_title',
                            'author_name',
                            'image_url',
                            'file_url',
                            'link_url',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
            ]);

        $this->assertEquals(10, count($response->json('data.data')));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function testGetPostsForRegularUser()
    {
        $this->actingAs($this->user);

        $category = $this->category;
        $mediaCategory = $this->media_category;

        Post::factory()->count(10)->create([
            'category_id' => $category->id
        ]);

        Post::factory()->count(5)->create([
            'category_id' => $mediaCategory->id
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'container',
                            'category_id',
                            'category_slug',
                            'page_title',
                            'author_name',
                            'image_url',
                            'file_url',
                            'link_url',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertEquals(10, count($response->json('data.data')));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function testGetPostsWithSearch()
    {
        $this->actingAs($this->user);

        $search_term = 'Sejarah';

        $category = $this->category;

        Post::factory()->create([
            'title' => $search_term . ' Post',
            'container' => 'This is test search post',
            'category_id' => $category->id
        ]);

        Post::factory()->count(5)->create([
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/posts?search=' . $search_term);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'container',
                            'category_id',
                            'category_slug',
                            'page_title',
                            'author_name',
                            'image_url',
                            'file_url',
                            'link_url',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals($search_term . ' Post', $response->json('data.data.0.title'));
    }

    public function testGetPostsWithImageUrl()
    {
        $this->actingAs($this->user);

        // Create posts with images
        Post::factory()->count(3)->withImage()->create([
            'category_id' => $this->media_category->id
        ]);

        // Create posts without images
        Post::factory()->count(2)->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);

        $postsWithImages = collect($response->json('data.data'))
            ->filter(function ($post) {
                return !empty($post['image_url']);
            });

        $this->assertCount(3, $postsWithImages);
        $postsWithImages->each(function ($post) {
            $this->assertStringStartsWith('/storage/images/', $post['image_url']);
        });
    }

    public function testGetPostsWithFileUrl()
    {
        $this->actingAs($this->user);

        // Create posts with files
        Post::factory()->count(4)->withFile()->create([
            'category_id' => $this->file_category->id
        ]);

        // Create posts without files
        Post::factory()->count(3)->create([
            'category_id' => $this->file_category->id
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);

        $postsWithFiles = collect($response->json('data.data'))
            ->filter(function ($post) {
                return !empty($post['file_url']);
            });

        $this->assertCount(4, $postsWithFiles);
        $postsWithFiles->each(function ($post) {
            $this->assertStringStartsWith('/storage/files/', $post['file_url']);
        });
    }


    public function testGetPostsWithPagination()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $category = $this->category;
        $mediaCategory = $this->media_category;

        Post::factory()->count(10)->create([
            'category_id' => $category->id
        ]);

        Post::factory()->count(5)->create([
            'category_id' => $mediaCategory->id
        ]);

        $response = $this->getJson('/api/posts?page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
            ]);

        $this->assertEquals(2, $response->json('data.current_page'));
        $this->assertEquals(5, count($response->json('data.data')));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function testGetPostsByPageSlugWithValidSlug()
    {
        $page = Page::factory()->create(['slug' => 'test-page']);
        $category = $this->category;
        Post::factory()->count(3)->create([
            'page_id' => $page->id,
            'category_id' => $category->id,
            'status' => 'published'
        ]);

        $response = $this->getJson('/api/posts/by-page/' . $page->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'container',
                        'category_id',
                        'category_slug',
                        'page_title',
                        'image_url',
                        'file_url',
                        'link_url'
                    ]
                ]
            ]);

        $this->assertEquals(3, count($response->json('data')));
        $this->assertEquals($page->title, $response->json('data.0.page_title'));
        $this->assertEquals($category->slug, $response->json('data.0.category_slug'));
    }

    public function testGetPostsByPageSlugWithInvalidSlug()
    {
        $response = $this->getJson('/api/posts/by-page/invalid-slug');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Page slug not found.'
            ]);
    }

    public function testGetPostsByPageSlugWithNoPublishedPosts()
    {
        $page = Page::factory()->create(['slug' => 'test-page']);
        Post::factory()->count(3)->create([
            'page_id' => $page->id,
            'category_id' => $this->category->id,
            'status' => 'draft'
        ]);

        $response = $this->getJson('/api/posts/by-page/' . $page->slug);
        // dd($response);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Posts data retrieved successfully.',
                'data' => []
            ]);
    }

    public function testGetPostsByPageSlugWithImageAndFileUrls()
    {
        $page = Page::factory()->create(['slug' => 'test-page']);
        $category = $this->category;
        $post = Post::factory()->create([
            'page_id' => $page->id,
            'category_id' => $category->id,
            'status' => 'published',
            'image_url' => 'images/test.jpg',
            'file_url' => 'files/test.pdf'
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('images/test.jpg', 'fake image content');
        Storage::disk('public')->put('files/test.pdf', 'fake file content');

        $response = $this->getJson('/api/posts/by-page/' . $page->slug);

        $response->assertStatus(200);
        $this->assertStringStartsWith('/storage/images/', $response->json('data.0.image_url'));
        $this->assertStringStartsWith('/storage/files/', $response->json('data.0.file_url'));
    }

    public function testGetPostsByPageSlugWithMixedStatuses()
    {
        $page = Page::factory()->create(['slug' => 'test-page']);
        $category = $this->category;
        Post::factory()->create([
            'page_id' => $page->id,
            'category_id' => $category->id,
            'status' => 'published'
        ]);
        Post::factory()->create([
            'page_id' => $page->id,
            'category_id' => $category->id,
            'status' => 'draft'
        ]);

        $response = $this->getJson('/api/posts/by-page/' . $page->slug);

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('published', Post::find($response->json('data.0.id'))->status);
    }

    public function testGetPostByIDWithValidID()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $post = Post::factory()->create([
            'category_id' => $this->category->id,
        ]);
        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'container',
                    'category' => ['id', 'name', 'slug'],
                    'author' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Post data retrieved successfully.', $response->json('message'));
        $this->assertEquals($post->id, $response->json('data.id'));
    }

    public function testGetPostByIDWithInvalidID()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $invalidId = 9999;
        $response = $this->getJson("/api/posts/{$invalidId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Post not found'
            ]);
    }

    public function testGetPostByIDReturnsCorrectRelations()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $category = $this->category;
        $author = $this->user;
        $post = Post::factory()->create([
            'category_id' => $category->id,
            'author_id' => $author->id
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'category' => ['id', 'name', 'slug'],
                    'author' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertEquals($category->id, $response->json('data.category.id'));
        $this->assertEquals($author->id, $response->json('data.author.id'));
    }

    public function testGetPostByIDWithDeletedPost()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $post = Post::factory()->create([
            'category_id' => $this->category->id,
        ]);
        $postId = $post->id;
        $post->delete();

        $response = $this->getJson("/api/posts/{$postId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Post not found'
            ]);
    }

    public function testGetPostByIDPerformance()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $post = Post::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $startTime = microtime(true);
        $response = $this->getJson("/api/posts/{$post->id}");
        $endTime = microtime(true);

        $response->assertStatus(200);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $this->assertLessThan(500, $executionTime, 'The request took longer than 500ms.');
    }

    public function testUpdatePostWithValidData()
    {
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'container' => 'Updated Container',
            'page_id' => $this->page->id,
            'category_id' => $this->category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully.',
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'container' => 'Updated Container',
        ]);
    }

    public function testUpdatePostWithInvalidData()
    {
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'A',
            'container' => '',
            'page_id' => 9999,
            'category_id' => 9999,
            'status' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'container', 'page_id', 'category_id', 'status']);
    }

    public function testUpdatePostUnauthorized()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'author_id' => User::factory()->create()->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'container' => 'Updated Container',
            'page_id' => $this->page->id,
            'category_id' => $this->category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function testUpdatePostNotFound()
    {
        $this->actingAs($this->user);

        $response = $this->patchJson("/api/posts/9999", [
            'title' => 'Updated Title',
            'container' => 'Updated Container',
            'page_id' => $this->page->id,
            'category_id' => $this->category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Post not found.',
            ]);
    }

    public function testUpdateMediaPostWithoutImage()
    {
        $user = User::factory()->create();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->media_category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Media Post',
            'container' => 'Updated Media Container',
            'page_id' => $this->page->id,
            'category_id' => $this->media_category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Image required for media category.',
            ]);
    }

    public function testUpdateJournalPostWithoutLink()
    {
        $user = User::factory()->create();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->journal_category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Journal Post',
            'container' => 'Updated Journal Container',
            'page_id' => $this->page->id,
            'category_id' => $this->journal_category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Link required for journal category.',
            ]);
    }

    public function testUpdateFilePostWithoutFile()
    {
        $user = User::factory()->create();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->file_category->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated File Post',
            'container' => 'Updated File Container',
            'page_id' => $this->page->id,
            'category_id' => $this->file_category->id,
            'status' => 'published'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'File required for file category.',
            ]);
    }

    public function testUpdatePostWithNewImage()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->media_category->id,
            'image_url' => 'old_image.jpg'
        ]);

        $newImage = UploadedFile::fake()->image('new_image.jpg');

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Image Post',
            'container' => 'Updated Image Container',
            'page_id' => $this->page->id,
            'category_id' => $this->media_category->id,
            'status' => 'published',
            'image' => $newImage
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.image_url'));
        $this->assertNotEquals('old_image.jpg', $response->json('data.image_url'));
        Storage::disk('public')->assertExists($response->json('data.image_url'));
        Storage::disk('public')->assertMissing('old_image.jpg');
    }

    public function testUpdatePostWithNewFile()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->file_category->id,
            'file_url' => 'old_file.pdf'
        ]);

        $newFile = UploadedFile::fake()->create('new_file.pdf', 100);

        $response = $this->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated File Post',
            'container' => 'Updated File Container',
            'page_id' => $this->page->id,
            'category_id' => $this->file_category->id,
            'status' => 'published',
            'file' => $newFile
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.file_url'));
        $this->assertNotEquals('old_file.pdf', $response->json('data.file_url'));
        Storage::disk('public')->assertExists($response->json('data.file_url'));
        Storage::disk('public')->assertMissing('old_file.pdf');
    }

    public function testDeletePostSuccessfully()
    {
        $user = User::factory()->createOne();
        $this->actingAs($this->user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post data deleted successfully.'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function testDeletePostNotFound()
    {
        $this->actingAs($this->user);

        $response = $this->deleteJson("/api/posts/9999");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Post not found.'
            ]);
    }

    public function testDeletePostUnauthorized()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'author_id' => User::factory()->create()->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.'
            ]);
    }

    public function testDeletePostWithImage()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->media_category,
            'image_url' => 'test-image.jpg'
        ]);

        Storage::fake('public');

        Storage::disk('public')->put('test-image.jpg', 'dummy content');

        $response = $this->deleteJson('/api/posts/' . $post->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post data deleted successfully.'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);

        Storage::disk('public')->assertMissing('test-image.jpg');
    }

    public function testDeletePostWithFile()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user);

        $post = Post::factory()->create([
            'author_id' => $user->id,
            'category_id' => $this->media_category,
            'file_url' => 'test-file.pdf'
        ]);

        Storage::fake('public');

        Storage::disk('public')->put('test-file.pdf', 'dummy content');

        $response = $this->deleteJson('/api/posts/' . $post->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post data deleted successfully.'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);

        Storage::disk('public')->assertMissing('test-file.pdf');
    }

    public function testDeletePostAsSuperadmin()
    {
        $superadmin = User::role('superadmin')->first();
        $this->actingAs($superadmin);

        $post = Post::factory()->create([
            'author_id' => User::factory()->create()->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post data deleted successfully.'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function testFormatValidationErrors()
    {
        $validator = Validator::make(
            ['title' => ''],
            ['title' => 'required']
        );

        $response = $this->postController->formatValidationErrors($validator);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertArrayHasKey('title', $response->getData(true)['errors']);
    }
}
