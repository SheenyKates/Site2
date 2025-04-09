<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Traits\ApiResponser;

class UserController extends Controller
{
    use ApiResponser;

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get all users.
     */
    public function index()
    {
        $users = User::all();
        return $this->successResponse($users);
    }

    /**
     * Create a new user.
     */
    public function addUser(Request $request)
    {
        $rules = [
            'username' => 'required|max:20',
            'password' => 'required|max:20',
            'gender' => 'required|in:Male,Female',
        ];

        $this->validate($request, $rules);

        $user = User::create($request->all());
        return response()->json($user, Response::HTTP_CREATED);
    }

    /**
     * Get a user by ID.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'username' => 'max:20',
            'password' => 'max:20',
            'gender' => 'in:Male,Female',
        ];

        $this->validate($request, $rules);

        $user = User::findOrFail($id);
        $user->fill($request->all());

        if ($user->isClean()) {
            return response()->json(['error' => 'At least one value must change'], 422);
        }

        $user->save();
        return response()->json($user);
    }

    /**
     * Delete a user.
     */
    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
