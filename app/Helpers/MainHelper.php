<?php

use Carbon\Carbon;
use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendFirebaseNotificationJob;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;

if (!function_exists('pushFirebaseNotification')) {
 
    function pushFirebaseNotification(string $token, string $title, string $body,array $data=[], string $queue = 'default' , $connection = null)
    {
        dispatch(new SendFirebaseNotificationJob($token, $title, $body,$data))
        ->onConnection($connection ?? config('queue.default') ?? 'sync')
        ->onQueue($queue);
    }
      
}


if (!function_exists('authorize')) {
    function authorize(string|array $permission, $allowGuestForNonAdminRoute = false)
    {
        if ($allowGuestForNonAdminRoute and !request()->is('*admin*'))
            return;

        if (!auth('sanctum')->check()) {
            throw new AuthenticationException();
        }

        $user = User::with(['roles', 'permissions'])->find(auth('sanctum')->id());

        if (!$user) {
            throw new AuthenticationException();
        }

        // Check if user has the admin role manually
        // if ($user->roles->contains('name', Constants::ADMIN_ROLE))
        //     return;

        if (is_string($permission) and !$user->permissions->contains('name', $permission)) {
            throw new AuthorizationException();
        }

        $hasAnyPermission = $user->permissions->pluck('name')->intersect($permission)->isNotEmpty();

        if (!$hasAnyPermission) {
            throw new AuthorizationException();
        }

    }
}

if (!function_exists('languageResponse')) {
    /**
     * Transform validated data into a JSON structure for translations.
     *
     * @param array $data The validated data array.
     * @param array $translated_suffix The translated attributes (e.g., 'name', 'text').
     * @param array $languages The array of language prefixes (e.g., ['en', 'ar', 'fr']).
     * @param \Illuminate\Http\Resources\Json\JsonResource $resource Optional. The resource to use for fetching existing translations 
     * @return array The transformed JSON structure.
     * @throws \Exception if a translation is missing and cannot be found in the data or the model.
     */
    function languageResponse(array $data, array $translated_suffix, JsonResource $resource, array $languages = ['ar', 'en', 'fr']): array
    {
        if (!auth('sanctum')->user()?->hasRole(Constants::ADMIN_ROLE))
            return $data;

        $response = [];

        foreach ($data as $key => $value) {
            if (!!!in_array($key, $translated_suffix)) {
                $response[$key] = $value;
                continue;
            }

            foreach ($languages as $lang) {
                // @phpstan-ignore-next-line
                $response[$lang . '_' . $key] = $resource->getTranslation($key, $lang);
            }

        }
        return $response;
    }
}

if (!function_exists('languageJson')) {
    /**
     * Transform validated data into a JSON structure for translations.
     *
     * @param array $data The validated data array.
     * @param string $suffix The suffix to use (e.g., 'name', 'text').
     * @param array $languages The array of language prefixes (e.g., ['en', 'ar', 'fr']).
     * @param \Illuminate\Database\Eloquent\Model|null $model Optional. The model to use for fetching existing translations during update
     * @return array The transformed JSON structure.
     * @throws \Exception if a translation is missing and cannot be found in the data or the model.
     */
    function languageJson(array $data, string $suffix, Model $model = null, array $languages = ['ar', 'en', 'fr']): array
    {
        $result = [];

        foreach ($languages as $lang) {
            $key = $lang . '_' . $suffix;
            if (isset($data[$key])) {
                $result[$lang] = $data[$key];
            } elseif ($model) {
                try {
                    // @phpstan-ignore-next-line
                    $result[$lang] = $model->getTranslation($suffix, $lang);
                } catch (\Exception $e) {
                    throw new \Exception("Translation missing for language '$lang' and suffix '$suffix'.");
                }
            } else {
                throw new \Exception("Translation missing for language '$lang' and suffix '$suffix'.");
            }
        }

        return $result;
    }
}


if (!function_exists('error')) {
    function error(string $message = null, $errors = null, $code = 401)
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors ?? [$message],
            'code' => (($code < 400 or $code > 503) ? 500 : $code),
        ], (($code < 400 or $code > 503) ? 500 : $code));
    }
}
if (!function_exists('success')) {
    function success($data = null, int $code = Response::HTTP_OK, $additionalData = [])
    {
        return response()->json(
            array_merge([
                'data' => $data ?? ['success' => true],
                'code' => $code
            ], $additionalData),
            $code
        );
    }
}
if (!function_exists('throwError')) {
    function throwError($message, $errors = null, int $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        throw new HttpResponseException(response()->json(
            [
                'message' => $message,
                'errors' => $errors ?? [$message],
            ],
            $code
        ));
    }
}


if (!function_exists('paginate')) {
    function paginate(&$data, $paginationLimit = null)
    {
        $paginationLimit = $paginationLimit ?? config('app.pagination_limit');
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedStudents = collect($data)->forPage($page, $paginationLimit);

        // Create a LengthAwarePaginator-like structure
        $paginator = new LengthAwarePaginator(
            $paginatedStudents,
            count($data),
            $paginationLimit,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // Convert the paginator to an array with numerically indexed data
        $data = $paginator->toArray();
        $data['data'] = collect($data['data'])->values()->all();

        return $data;
    }
}
if (!function_exists('diffForHumans')) {
    function diffForHumans($time)
    {
        return Carbon::parse($time)->diffForHumans(Carbon::now(), [
            'long' => true,
            'parts' => 2,
            'join' => true,
        ]);
    }
}
