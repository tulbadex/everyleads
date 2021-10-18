<?php

namespace App\Http\Controllers\API\V1;

// use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Models\Validators\UserValidator;
use Illuminate\Http\{Request, Response};
use Illuminate\Validation\Rule;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function index(): JsonResource
    {
        $this->authorize('update', auth()->user());

        $user = User::query()
            ->paginate(20);

        return UserResource::collection(
            $user
        );
    }

    public function create() : JsonResource
    {
        abort_unless(auth()->user()->tokenCan('user.create'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', auth()->user());

        $attributes = (new UserValidator())->validate(
            $user = new User(),
            request()->all()
        );

        $user = User::create(
            $attributes
        );

        return UserResource::make($user);
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

        $attributes = (new UserValidator())->validate($user, request()->all());
        // auth()->id() == $user->id

        if ( request()->user->is($user) || auth()->user()->is_admin ) {
            $user->update($attributes);
        }


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
