<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use App\Database;
use App\Controllers\GroupController;
use App\Controllers\MessageController;

class ChatTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        // Create the Slim App for testing
        $this->app = AppFactory::create();

        // Register routes
        $this->app->post('/groups', [new GroupController(), 'createGroup']);
        $this->app->get('/groups/{id}/messages', [new MessageController(), 'listMessages']);

        // Reset the database to a clean state
        $pdo = Database::getConnection();
        $pdo->exec("DELETE FROM messages");
        $pdo->exec("DELETE FROM group_user");
        $pdo->exec("DELETE FROM groups");
        $pdo->exec("DELETE FROM users");
    }

    public function testCreateGroup()
    {
        // Create a group with a POST request
        $request = $this->createRequest('POST', '/groups', ['name' => 'TestGroup']);
        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $body = (string)$response->getBody();
        $data = json_decode($body, true);
        

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('TestGroup', $data['name']);
    }

    // Helper method to create a request in a test
    private function createRequest(string $method, string $path, array $body = []): \Slim\Psr7\Request
    {
        $request = $this->app->getContainer()->get('requestFactory')->createRequest($method, $path);
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $requestBody = json_encode($body);
            $request = $request->withHeader('Content-Type', 'application/json');
            $request->getBody()->write($requestBody);
            $request->getBody()->rewind();
        }
        return $request;
    }
}
