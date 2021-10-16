<?php

namespace App\Http\Controllers\API\V1;

// use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\{Request, Response};
use Illuminate\Validation\Rule;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function index(): JsonResource
    {
        $user = User::query()
            ->paginate(20);

        return UserResource::collection(
            $user
        );
    }

    public function show(User $user) : JsonResource
    {
        return UserResource::make($user);
    }

    public function update(User $user): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('user.update'),
            Response::HTTP_FORBIDDEN
        );

        $data = validator(request()->all(), [
            'name' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'username' => [Rule::when($user->exists, 'sometimes'), 'required', 'string'],
            'email' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'email:rfc,dns'],
            'password' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:6'],
        ])->validate();

        $user->update($data);

        return UserResource::make(
            $user
        );

    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->tokenCan('user.delete'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $user);

        $user->delete();

        return response()->json(['message' => "User account deleted successfully"]);
    }
}
