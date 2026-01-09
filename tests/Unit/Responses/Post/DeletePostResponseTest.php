<?php

namespace Tests\Unit\Responses\Post;

use App\Http\Responses\Post\DeletePostResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePostResponseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_return_success_response(): void
    {
        $response = DeletePostResponse::success();

        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Post removido com sucesso.', $data['message']);
    }
}
