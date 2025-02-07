<?php

namespace App\Controllers;

use App\Models\Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GroupController
{
    public function createGroup(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $groupName = $data['name'] ?? null;

        if (!$groupName) {
            return $this->errorResponse($response, "Group name is required", 400);
        }

        // Check if group already exists       
        if (Group::getGroupByName($groupName) != null) {
            return $this->errorResponse($response, "Group already exists", 409);
        }

        // Create the group
        $group = new Group($groupName);
        if (!$group->save()) {
            return $this->errorResponse($response, "Insert failed; no rows affected.", 500);
        }

        $response->getBody()->write(json_encode($group));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
    public function getGroup(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $groupName = $data['name'] ?? null;

        if (!$groupName) {
            return $this->errorResponse($response, "Group name is required", 400);
        }
        // Check if group dose not exists 
        $group = Group::getGroupByName($groupName);
        if (Group::getGroupByName($groupName) == null) {
            return $this->errorResponse($response, "Group not found", 409);
        }

        $response->getBody()->write(json_encode($group));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function joinGroup(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];

        // I expect a custom header or query param for the username  
        $username = $request->getHeaderLine('X-User-Name');
        if (!$username) {
            return $this->errorResponse($response, "Username is required in 'X-User-Name' header", 400);
        }

        // Make sure the user exists (create if needed)
        $user = UserController::getOrCreateUser($username);

        // Check if the group exists   

        $group = Group::getGroupById($groupId);
        if ($group == null) {
            return $this->errorResponse($response, "Group not found", 404);
        }


        // Check if user is already in the group

        if ($group->inGroup($user->id)) {
            // Already joined, return success anyway or handle differently
            return $response->withStatus(204);
        }

        // Join the group
        if ($group->joinGroup($user->id)) {
            return $response->withStatus(201); // Created

        } else {
            return $this->errorResponse($response, "Could not Join Group", 500);
        }
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
