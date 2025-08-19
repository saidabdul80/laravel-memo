<?php

namespace Saidabdulsalam\LaravelMemo\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Saidabdulsalam\LaravelMemo\Tests\TestCase;
use Saidabdulsalam\LaravelMemo\Tests\Models\User;
use Saidabdulsalam\LaravelMemo\Models\Memo;
use Saidabdulsalam\LaravelMemo\Models\MemoApprover;
use Illuminate\Support\Facades\Notification;
use Saidabdulsalam\LaravelMemo\Notifications\MemoAssigned;

class SequentialApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->owner = User::factory()->create();
        $this->actingAs($this->owner);
    }

    /** @test */
    public function approvals_happen_in_sequence_and_notify()
    {
        // create two users as approvers
        $a1 = User::factory()->create();
        $a2 = User::factory()->create();

        $memoData = Memo::factory()->make()->toArray();
        $memoData['status'] = 'SUBMITTED';
        $memoData['type'] = 'REQUEST';
        $memoData['approvers'] = [
            ['approver_id' => $a1->id, 'approver_type' => get_class($a1)],
            ['approver_id' => $a2->id, 'approver_type' => get_class($a2)],
        ];

        $response = $this->postJson('/memo', $memoData);
        $response->assertStatus(201);

        $memo = Memo::first();

        // first approver should be forwarded and notified
        $first = $memo->approvers()->orderBy('id')->first();
        $this->assertEquals(1, $first->forwarded);
        Notification::assertSentTo($a1, MemoAssigned::class);

        // simulate a1 approving
        $this->actingAs($a1);
        $res = $this->postJson('/memo', [
            'id' => $memo->id,
            'approvers' => [
                ['id' => $first->id, 'status' => 'APPROVED']
            ]
        ]);
        $res->assertStatus(200);

        $memo->refresh();
        $second = $memo->approvers()->orderBy('id')->skip(1)->first();
        $this->assertEquals(1, $second->forwarded);
        Notification::assertSentTo($a2, MemoAssigned::class);
    }
}
