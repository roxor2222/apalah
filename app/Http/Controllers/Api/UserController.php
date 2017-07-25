<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = User::where('active', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($request->header('paginator'));

            $users = $paginator->getCollection();

            $response = fractal()
                ->collection($users, new UserTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $users = User::where('active', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $response = fractal()
            ->collection($users)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function show($username)
    {
        $username = substr($username, 1);

        $user = User::where('username', $username)
            ->where('active', 1)
            ->first();

        if (!$user) {
            return $this->resJsonError('Pengguna tidak ditemukan!.', 404);
        }

        $response = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function profile()
    {
        $user = User::find(Auth::user()->id);

        $response = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:20',
            'photo'    => 'image|mimes:jpeg,jpg,png|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        if ($request->hasFile('photo')) {
            Storage::disk('local')
                ->put('avatar/' . $imageName = time() . '.' . $request->photo->getClientOriginalExtension(),
                    File::get($request->file('photo'))
                );
        }

        $user = User::find(Auth::user()->id)->update([
            'name'     => $request->name,
            'photo'    => $imageName,
        ]);

        return $this->resJsonSuccess('Akun berhasil diperbarui.', 200);

    }

    public function destroy($username)
    {   
        $username = substr($username, 1);
        
        $user = User::where('username', $username)
            ->first();

        if (!$user) {
            return $this->resJsonError('Tidak menemukan pengguna yang akan dihapus!.', 404);
        }

        $user->delete();

        return $this->resJsonSuccess('Berhasil menghapus akun.', 200);
    }
}
