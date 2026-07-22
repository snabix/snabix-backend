<?php

declare(strict_types=1);

namespace App\Notification\Http;

use App\Notification\Application\Services\NotificationPreferenceService;
use App\Notification\Domain\Enums\NotificationEventType;
use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class NotificationPreferencesController
{
    public function show(
        NotificationPreferencesRequest $request,
        NotificationPreferenceService $service,
    ): JsonResponse {
        return response()->json(['data' => ['items' => $service->listForUser($request->userId())]]);
    }

    public function update(
        NotificationPreferencesRequest $request,
        NotificationPreferenceService $service,
    ): JsonResponse {
        /** @var list<array{key: string, siteEnabled: bool, emailEnabled: bool}> $items */
        $items = $request->validated('items');

        return response()->json(['data' => ['items' => $service->replaceForUser($request->userId(), $items)]]);
    }

    public function reset(
        NotificationPreferencesRequest $request,
        NotificationPreferenceService $service,
    ): JsonResponse {
        return response()->json(['data' => ['items' => $service->resetForUser($request->userId())]]);
    }
}

class NotificationPreferencesRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        if (! $this->isMethod('PUT')) {
            return [];
        }

        return [
            'items'                => ['required', 'array'],
            'items.*.key'          => [
                'required',
                'string',
                Rule::in(array_map(
                    static fn(NotificationEventType $type): string => $type->value,
                    NotificationEventType::availableCases(),
                )),
            ],
            'items.*.siteEnabled'  => ['required', 'boolean'],
            'items.*.emailEnabled' => ['required', 'boolean'],
        ];
    }

    public function authorize(): true
    {
        return true;
    }
}
