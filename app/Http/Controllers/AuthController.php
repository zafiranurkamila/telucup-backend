<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        operationId: "registerUser",
        tags: ["Authentication"],
        summary: "Daftarkan akun panitia baru",
        description: "Membuat akun baru dengan role `panitia`. Registrasi publik hanya untuk panitia — pemain didaftarkan oleh PIC/panitia via `POST /api/players`."
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name",     type: "string", maxLength: 255, example: "Rifqi Setiawan"),
                new OA\Property(property: "email",    type: "string", format: "email", example: "rifqi@telkomuniversity.ac.id"),
                new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "rahasia123"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Registrasi berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "user",  ref: "#/components/schemas/UserObject"),
                new OA\Property(property: "token", type: "string",
                    example: "1|abcdefg1234567890",
                    description: "Bearer token untuk digunakan di header Authorization"),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "The email has already been taken."),
                new OA\Property(property: "errors",  type: "object",
                    properties: [
                        new OA\Property(property: "email", type: "array",
                            items: new OA\Items(type: "string", example: "The email has already been taken.")),
                    ]
                ),
            ]
        )
    )]
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'panitia',
        ]);

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        operationId: "loginUser",
        tags: ["Authentication"],
        summary: "Login dan dapatkan Bearer token",
        description: "Autentikasi pengguna dengan email dan password. Gunakan token yang dikembalikan sebagai `Authorization: Bearer <token>` pada request selanjutnya."
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email",    type: "string", format: "email", example: "rifqi@telkomuniversity.ac.id"),
                new OA\Property(property: "password", type: "string", format: "password", example: "rahasia123"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "user",  ref: "#/components/schemas/UserObject"),
                new OA\Property(property: "token", type: "string",
                    example: "2|xyz9876543210",
                    description: "Bearer token untuk digunakan di header Authorization"),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Kredensial tidak valid",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                new OA\Property(property: "errors",  type: "object",
                    properties: [
                        new OA\Property(property: "email", type: "array",
                            items: new OA\Items(type: "string", example: "Kredensial yang diberikan tidak cocok dengan data kami.")),
                    ]
                ),
            ]
        )
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        operationId: "logoutUser",
        tags: ["Authentication"],
        summary: "Logout dan cabut Bearer token",
        description: "Mencabut (revoke) token yang sedang digunakan sehingga tidak bisa dipakai lagi. Membutuhkan header `Authorization: Bearer <token>`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Logout berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Logout berhasil."),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
