<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class DeleteProfileAddressRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    public function authorize(): bool
    {
        return true;
    }
}
