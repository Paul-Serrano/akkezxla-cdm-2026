<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointsTest extends TestCase
{
    public function test_health_endpoint_returns_ok_json(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonMissingPath('timestamp');
    }

    public function test_ready_endpoint_returns_ok_json_with_timestamp(): void
    {
        $this->getJson('/ready')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
            ]);
    }
}
