<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Library\Book;
use App\Models\User\Student;

class LibraryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_books()
    {
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/library/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'author', 'isbn']
                    ]
                ]
            ]);
    }

    public function test_can_issue_book()
    {
        $book = Book::factory()->create(['available_copies' => 5]);
        $student = Student::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/library/issue', [
                'book_id' => $book->id,
                'student_id' => $student->id,
                'due_date' => now()->addDays(14)->toDateString()
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(4, $book->fresh()->available_copies);
    }

    public function test_can_return_book()
    {
        $book = Book::factory()->create(['available_copies' => 4]);
        $student = Student::factory()->create();
        
        $issue = $book->issues()->create([
            'student_id' => $student->id,
            'issue_date' => now()->subDays(7)->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'issued'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/library/return', [
                'issue_id' => $issue->id
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(5, $book->fresh()->available_copies);
        $this->assertEquals('returned', $issue->fresh()->status);
    }
}