<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;

/**
 * SocialAuthController - Handle social media authentication
 * 
 * @OA\Tag(
 *     name="Social Authentication",
 *     description="OAuth2 social login endpoints (Google, Facebook, X)"
 * )
 */
class SocialAuthController extends Controller
{
    /**
     * Supported social providers
     */
    private const PROVIDERS = ['google', 'facebook', 'twitter'];

    /**
     * Redirect to social provider.
     *
     * @OA\Get(
     *     path="/auth/{provider}/redirect",
     *     operationId="socialRedirect",
     *     tags={"Social Authentication"},
     *     summary="Redirect to social provider",
     *     description="Redirects to the OAuth2 provider's login page",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider (google, facebook, twitter)",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter"})
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to provider"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid provider"
     *     )
     * )
     */
    public function redirect(string $provider): mixed
    {
        if (!in_array($provider, self::PROVIDERS)) {
            return response()->json([
                'message' => 'Invalid provider',
                'valid_providers' => self::PROVIDERS,
            ], 400);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle callback from social provider.
     *
     * @OA\Get(
     *     path="/auth/{provider}/callback",
     *     operationId="socialCallback",
     *     tags={"Social Authentication"},
     *     summary="Handle OAuth2 callback",
     *     description="Handles the callback from the OAuth2 provider. Creates or updates user and returns API token.",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Social provider",
     *         @OA\Schema(type="string", enum={"google", "facebook", "twitter"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="is_new_user", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid provider or OAuth error"
     *     )
     * )
     */
    public function callback(string $provider): JsonResponse
    {
        if (!in_array($provider, self::PROVIDERS)) {
            return response()->json([
                'message' => 'Invalid provider',
            ], 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'OAuth authentication failed',
                'error' => $e->getMessage(),
            ], 400);
        }

        // Find existing user by provider or email
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        $isNewUser = false;

        if (!$user) {
            // Check if user exists with same email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link social account to existing user
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => null, // Social users don't need password
                    'email_verified_at' => now(),
                ]);
                $isNewUser = true;
            }
        } else {
            // Update avatar if changed
            $user->update([
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        // Generate API token
        $token = $user->createToken('social-auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'is_new_user' => $isNewUser,
        ]);
    }
}
