<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Api\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $sortField = request('sort_field', 'updated_at');
        $sortDirection = request('sort_direction', 'desc');

        $query = User::query()
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);

        return UserResource::collection($query);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(CreateUserRequest $request): UserResource
    {
        $data = $request->validated();
        $data['is_admin'] = true;
        $data['email_verified_at'] = date('Y-m-d H:i:s');
        $data['password'] = Hash::make($data['password']);

        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $user = User::query()->create($data);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['updated_by'] = $request->user()->id;

        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @return Response
     */
    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
