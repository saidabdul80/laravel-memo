<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Saidabdulsalam\LaravelMemo\Tests\TestCase;
use Saidabdulsalam\LaravelMemo\Tests\Models\User;
use Saidabdulsalam\LaravelMemo\Models\Memo;

class MemoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for testing
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_memo()
    {
        $memoData = Memo::factory()->make()->toArray();
        $memoData['status'] = 'SUBMITTED';
        $memoData['type'] = 'REQUEST';

        $response = $this->postJson('/memo', $memoData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('memos', ['title' => $memoData['title']]);
    }

    /** @test */
    public function it_can_update_a_memo()
    {
        $memo = Memo::factory()->create(['owner_id' => $this->user->id, 'owner_type' => get_class($this->user)]);

        $updatedData = [
            'id' => $memo->id,
            'title' => 'Updated Memo',
            'content' => 'This is an updated memo.',
            'type' => 'REQUEST',
            'approvers' => [],
        ];

        $response = $this->postJson('/memo', $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('memos', ['title' => 'Updated Memo']);
    }

    /** @test */
    public function it_can_delete_a_memo()
    {
        $memo = Memo::factory()->create();

        $response = $this->deleteJson("/memo/{$memo->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('memos', ['id' => $memo->id]);
    }
}
