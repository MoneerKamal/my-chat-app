<?php

namespace App\Controllers;

use App\Models\Message;
use App\Models\Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageController
{
    public function sendMessage(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
        

        // I expect a custom header for the username
        $username = $request->getHeaderLine('X-User-Name');
        if (!$username) {
            return $this->errorResponse($response, "Username is required in 'X-User-Name' header", 400);
        }
         // Get message content
         $body = $request->getParsedBody();
         $content = $body['content'] ?? null;
         if (!$content) {
             return $this->errorResponse($response, "Message content is required", 400);
         }

        // Make sure the user exists (create if needed)
        $user = UserController::getOrCreateUser($username);

        // Check if the group exists
        $group=Group::getGroupById($groupId);   
        if ($group==null) {
           return $this->errorResponse($response, "Group not found", 404);
       }

        // Check if user is member of the group
        if (!$group->inGroup($user->id)) {
            return $this->errorResponse($response, "User not in this group. Please join first.", 403);
        }
            
        // Insert the message
        $message = new Message();
        $message->group_id=$groupId;
        $message->user_id=$user->id;
        $message->content=$content;
              
        if (!$message->save()) {
            return $this->errorResponse($response, "Could not send message.", 500);
        }

        $response->getBody()->write(json_encode($message));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function listMessages(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
               // Check if the group exists
        $group=Group::getGroupById($groupId);   
        if ($group==null) {
           return $this->errorResponse($response, "Group not found", 404);
       }
        // List messages
        $messages=Message::getMessages($groupId);
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function errorResponse(Response $response, string $message, int $code): Response
    {
        $payload = ['error' => $message];
        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }
}
