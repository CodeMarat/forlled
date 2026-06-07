<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactUsRequest;
use Tests\TestCase;

class StoreContactUsRequestTest extends TestCase
{
    public function test_store_contact_us_request_has_expected_rules(): void
    {
        $request = new StoreContactUsRequest;

        $this->assertTrue($request->authorize());
        $this->assertSame([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ], $request->rules());
    }
}
